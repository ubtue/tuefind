<?php

namespace TueFind\Controller;

/**
 * Use Findbuch API to search for BEACON links (via GND number)
 * - Sample API call: https://beacon.findbuch.de/seealso/pnd-aks?format=seealso&id=100001718
 * - API documentation: https://beacon.findbuch.de/seealso/pnd-aks
 *
 * Note: This service is no longer available since End 2023,
 *       so this class will also no longer be actively maintained
 *       and we e.g. won't optimize it for cachingDownloader changes
 *       related to VuFind 9.1.
 *
 */
class FindbuchProxyController extends AbstractProxyController
{
    // Subsections like "/-ixtheo/" mean that the corresponding BEACON file will be ignored.
    const API_URL_BASE = 'http://beacon.findbuch.de/seealso/pnd-aks/';
    const API_URL_PARAMS = '?format=seealso&id=';

    const EXCLUSION_LIST = ['adbreg',
                            'archivportal',
                            'bbkl@ap',
                            'commons@pd',
                            'cultword',
                            'gersac_brabis',
                            'heidi',
                            'ixtheo',
                            'kalliope',
                            'leobw-kglbio',
                            'mghopac',
                            'orcid@wd',
                            'pw_allmusic',
                            'pw_discogs',
                            'pw_eb',
                            'pw_imslp',
                            'pw_munzinger_pop',
                            'relbib',
                            'unibib_rub',
                            'wikidata'];

    protected $downloaderCacheId = 'findbuch';

    public function loadAction()
    {
        $query = $this->getRequest()->getUri()->getQuery();
        $parameters = [];
        parse_str($query, $parameters);

        if (!isset($parameters['gnd']))
            throw new \Exception('Mandatory parameter "gnd" is missing!');

        $json = $this->callAPI($parameters['gnd']);
        return $this->generateResponse($json);
    }

    protected function callAPI($gndNumber)
    {
        $apiUrlFull = self::API_URL_BASE;
        foreach (self::EXCLUSION_LIST as $exclusion)
            $apiUrlFull .= '-' . $exclusion . '/';
        $apiUrlFull .= self::API_URL_PARAMS . urlencode($gndNumber);

        $response = $this->cachingDownloader->download($apiUrlFull);
        return $response;
    }

    protected function generateResponse($json) {
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent($json);
        return $response;
    }
}
