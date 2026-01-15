<?php

namespace TueFind\Controller;

class ZederProxyController extends AbstractProxyController {
    protected $downloaderCacheId = 'zeder';
    protected $cacheOptionsSection = 'Zeder';

    protected $actions = [
        // The following URLs will only be available within the UB intranet

        // First draft:
        //'wert_zeigen_v01' => 'https://www-ub.ub.uni-tuebingen.de/zeder_ixtheo/cgi-bin/index.cgi/wert_zeigen_v01.json',
        //'wert_zeigen_v02' => 'https://www-ub.ub.uni-tuebingen.de/zeder_ixtheo/cgi-bin/index.cgi/wert_zeigen_v02.json',

        // Changed to:
        'wert_zeigen_v01' => 'https://www-ub.ub.uni-tuebingen.de/zeder_ixtheo/cgi-bin/index.cgi/wert_zeigen_view?View=1',
        'wert_zeigen_v02' => 'https://www-ub.ub.uni-tuebingen.de/zeder_ixtheo/cgi-bin/index.cgi/wert_zeigen_view?View=2',
    ];

    /**
     * This action is used to expose URLs from Zeder which are usually only reachable via the intranet (e.g. for JSON files).
     * Results will be Cached via the CachingDownloader.
     */
    public function proxyAction()
    {
        $targetId = $this->params()->fromRoute('targetId');
        if (!isset($targetId) || !isset($this->actions[$targetId])) {
            $response = $this->getResponse();
            $response->setStatusCode(\Laminas\Http\Response::STATUS_CODE_400);
            $response->setContent('400 Bad Request - Missing or invalid parameters');
            return $response;
        } else {
            $url = $this->actions[$targetId];

            $addParams = [  's_ausgabeformat' => 'json',
                            's_datenexport' => '1',
                            'lng' => $this->getTranslatorLocale()];
            foreach ($addParams as $addKey => $addValue) {
                // Note that some examples in the mail communication also use ; instead of &, but both will work
                $url .= (str_contains($url, '?') ? '&' : '?');
                $url .= $addKey . '=' . urlencode($addValue);
            }

            $json = $this->cachingDownloader->download($url);
            $response = $this->getResponse();
            $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
            $response->setContent($json);
            return $response;
        }
    }

    /**
     * This action is used to generate Views from Zeder.
     */
    public function viewAction()
    {
        $viewId = $this->params()->fromRoute('viewId');
        if (!isset($viewId) || !isset($this->actions[$viewId])) {
            $response = $this->getResponse();
            $response->setStatusCode(\Laminas\Http\Response::STATUS_CODE_400);
            $response->setContent('400 Bad Request - Missing or invalid parameters');
            return $response;
        } else {
            return $this->createViewModel(['viewId' => $viewId]);
        }
    }
}
