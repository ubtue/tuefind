<?php

namespace TueFind\Http;

use VuFind\Exception\HttpDownloadException;


class CachingDownloader extends \VuFind\Http\CachingDownloader {
    static public function DecodeCallbackJson(\Laminas\Http\Response $response, string $url) {
        $json = json_decode($response->getBody());

        if ($json === null) {
            throw new HttpDownloadException(
                'Invalid response body (JSON)',
                $url,
                $response->getStatusCode(),
                $response->getJson(),
                $response->getBody(),
            );
        }

        return $json;
    }
}
