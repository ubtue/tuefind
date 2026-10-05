<?php

namespace TueFind\Http;

use VuFind\Exception\HttpDownloadException;

class CachingDownloader extends \VuFind\Http\CachingDownloader
{
    /**
     * Download a resource using the cache in the background.
     *
     * @param string    $url            URL
     * @param array     $params         Request parameters
     *                                  (e.g. additional headers)
     * @param ?callable $decodeCallback Callback for decoding
     *
     * @return mixed
     */
    public function download(
        $url,
        $params = [],
        ?callable $decodeCallback = null
    ) {
        $cache = $this->getDownloaderCache();
        $cacheItemKey = md5($url . http_build_query($params));

        if ($cache && $cache->hasItem($cacheItemKey)) {
            return $cache->getItem($cacheItemKey);
        }

        // Add new item to cache if not exists
        try {
            // TueFind: pass "params" as "headers", else User-Agent will have no effect,
            // temporary workaround, see #3961
            $response = $this->guzzleService->get($url, [], null, $params);
        } catch (\Exception $e) {
            throw new HttpDownloadException(
                'HttpService download failed (error)',
                $url,
                null,
                null,
                null,
                $e
            );
        }

        $body = $response->getBody()->getContents();
        $response->getBody()->rewind(); // later code might need to read the body again
        if ($response->getStatusCode() != 200) {
            throw new HttpDownloadException(
                'HttpService download failed (not ok)',
                $url,
                $response->getStatusCode(),
                $response->getHeaders(),
                $body
            );
        }

        $finalValue = $decodeCallback !== null
            ? $decodeCallback($response, $url) : $body;
        if ($cache) {
            $cache->addItem($cacheItemKey, $finalValue);
        }
        return $finalValue;
    }
}
