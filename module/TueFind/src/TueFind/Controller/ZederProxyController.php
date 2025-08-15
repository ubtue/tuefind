<?php

namespace TueFind\Controller;

class ZederProxyController extends AbstractProxyController {
    protected $downloaderCacheId = 'zeder';
    protected $cacheOptionsSection = 'Zeder';

    protected $actions = [
        // The following URLs will only be available within the UB intranet
        // test
        'wert_zeigen_v01' => 'https://blei.ub.uni-tuebingen.de/zeder_ixtheo/cgi-bin/index.cgi/wert_zeigen_v01.json',

        // live (not yet available)
        //'wert_zeigen_v01' => 'https://www-ub.ub.uni-tuebingen.de/zeder_ixtheo/cgi-bin/index.cgi/wert_zeigen_v01.json',
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
            $locale = $this->getTranslatorLocale();
            $url .= '?lng=' . urlencode($locale);
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
