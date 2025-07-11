<?php

namespace TueFind\Controller;

class ZederProxyController extends AbstractProxyController {
    protected $downloaderCacheId = 'zeder';

    protected $actions = [
        // The following URLs will only be available within the UB intranet
        // test
        'wert_zeigen_v01' => 'https://blei.ub.uni-tuebingen.de/zeder_ixtheo/cgi-bin/index.cgi/wert_zeigen_v01.json',

        // live (not yet available)
        //'wert_zeigen_v01' => 'https://www-ub.ub.uni-tuebingen.de/zeder_ixtheo/cgi-bin/index.cgi/wert_zeigen_v01.json',
    ];

    public function loadAction()
    {
        $query = $this->getRequest()->getUri()->getQuery();
        $parameters = [];
        parse_str($query, $parameters);

        if (!isset($parameters['action']) || !isset($this->actions[$parameters['action']])) {
            $response = $this->getResponse();
            $response->setStatusCode(\Laminas\Http\Response::STATUS_CODE_400);
            $response->setContent('400 Bad Request - Missing or invalid parameters');
            return $response;
        } else {
            $json = $this->cachingDownloader->download($this->actions[$parameters['action']]);
            $response = $this->getResponse();
            $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
            $response->setContent($json);
            return $response;
        }
    }

    public function viewAction()
    {
        $query = $this->getRequest()->getUri()->getQuery();
        $parameters = [];
        parse_str($query, $parameters);

        if (!isset($parameters['action']) || !isset($this->actions[$parameters['action']])) {
            $response = $this->getResponse();
            $response->setStatusCode(\Laminas\Http\Response::STATUS_CODE_400);
            $response->setContent('400 Bad Request - Missing or invalid parameters');
            return $response;
        } else {
            return $this->createViewModel(['action' => $parameters['action']]);
        }
    }
}
