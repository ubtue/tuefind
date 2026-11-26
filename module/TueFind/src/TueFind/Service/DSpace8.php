<?php

/**
 * DSpace 8 REST API implementation (replacement for DSpace6)
 *
 * Documentation:
 * - https://wiki.lyrasis.org/display/DSDOC8x/REST+API
 * - https://github.com/DSpace/RestContract
 *
 * Demo instance:
 * - https://demo.dspace.org/server/api
 *
 * IMPORTANT: $baseUrl must now point to the REST root, e.g.
 *   https://dspace.example.org/server/api
 * (in DSpace 6 it was https://host/rest).
 */

namespace TueFind\Service;

class DSpace8 {

    const ENDPOINT_AUTHN_LOGIN = '/authn/login';
    const ENDPOINT_AUTHN_LOGOUT = '/authn/logout';
    const ENDPOINT_AUTHN_STATUS = '/authn/status';

    const ENDPOINT_COMMUNITIES = '/core/communities';
    const ENDPOINT_COLLECTIONS = '/core/collections';
    const ENDPOINT_ITEMS = '/core/items';
    const ENDPOINT_BUNDLES = '/core/bundles';
    const ENDPOINT_BITSTREAMS = '/core/bitstreams';

    const HEADER_ACCEPT = 'Accept';
    const HEADER_AUTHORIZATION = 'Authorization';
    const HEADER_CONTENT_TYPE = 'Content-Type';
    const HEADER_XSRF_REQUEST = 'X-XSRF-TOKEN';
    const HEADER_XSRF_RESPONSE = 'DSPACE-XSRF-TOKEN';

    const METHOD_DELETE = 'DELETE';
    const METHOD_GET = 'GET';
    const METHOD_HEAD = 'HEAD';
    const METHOD_PATCH = 'PATCH';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';

    // DSpace 7+ limits the maximum page size (default: 100), so we have to paginate.
    const PAGE_SIZE = 100;

    protected $baseUrl;
    protected $username;
    protected $password;

    /** JWT, received on login (header "Authorization: Bearer ...") */
    protected $bearerToken = null;

    /** CSRF token, DSpace sends a new one in header DSPACE-XSRF-TOKEN */
    protected $csrfToken = null;

    public function __construct(string $baseUrl, string $username, string $password)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->username = trim($username);
        $this->password = trim($password);
    }

    protected $curlHandle = null;

    public function __destruct()
    {
        if ($this->curlHandle !== null) {
            curl_close($this->curlHandle);
        }
    }

    /**
     * Call the API & return its result
     *
     * @param string       $endpoint One of the ENDPOINT_... class constants (+ path/query)
     * @param string       $method   One of the METHOD_... class constants
     * @param array        $headers  Additional headers (name => value)
     * @param string|array $data     Request body (string) or array with CURLFile for multipart
     * @param bool         $retry    Internal: retry once if CSRF token was refreshed
     *
     * @return mixed The decoded JSON response (null if empty).
     */
    protected function call(string $endpoint, string $method, array $headers=[], $data=null, bool $retry=true)
    {
        $fullUrl = $this->baseUrl . $endpoint;

        if ($this->curlHandle === null) {
            $this->curlHandle = curl_init();
        } else {
            curl_reset($this->curlHandle); // reset options, connection and cookies remain
        }
        $curlHandle = $this->curlHandle;

        curl_setopt($curlHandle, CURLOPT_URL, $fullUrl);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curlHandle, CURLOPT_USERAGENT, 'TueFind/1.0 (DSpace client)');
        curl_setopt($curlHandle, CURLOPT_COOKIEFILE, ''); // cookie-engine in memory, without file

        if (!isset($headers[self::HEADER_ACCEPT])) {
            $headers[self::HEADER_ACCEPT] = 'application/json';
        }
        if ($this->bearerToken !== null) {
            $headers[self::HEADER_AUTHORIZATION] = $this->bearerToken;
        }
        if ($this->csrfToken !== null) {
            $headers[self::HEADER_XSRF_REQUEST] = $this->csrfToken;
        }

        $curlHeaders = [];
        foreach ($headers as $headerName => $headerValue) {
            $curlHeaders[] = $headerName . ': ' . $headerValue;
        }
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $curlHeaders);

        if ($data !== null && $data !== '') {
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $data);
        }

        $responseHeaders = [];
        curl_setopt($curlHandle, CURLOPT_HEADERFUNCTION, function ($ch, $line) use (&$responseHeaders) {
            $parts = explode(':', $line, 2);
            if (count($parts) == 2) {
                $responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
            }
            return strlen($line);
        });

        $body = curl_exec($curlHandle);
        if ($body === false) {
            $error = curl_error($curlHandle);
            throw new \Exception('Error when calling ' . $fullUrl . ' (method: ' . $method . '), message: ' . $error);
        }
        $httpCode = curl_getinfo($curlHandle, CURLINFO_RESPONSE_CODE);

        $oldCsrfToken = $this->csrfToken;
        $csrfHeader = strtolower(self::HEADER_XSRF_RESPONSE);
        if (isset($responseHeaders[$csrfHeader])) {
            $this->csrfToken = $responseHeaders[$csrfHeader];
        }
        if (isset($responseHeaders['authorization']) && stripos($responseHeaders['authorization'], 'Bearer ') === 0) {
            $this->bearerToken = $responseHeaders['authorization'];
        }

        if ($httpCode == 403 && $retry && $this->csrfToken !== null && $this->csrfToken !== $oldCsrfToken) {
            return $this->call($endpoint, $method, $headers, $data, false);
        }

        if ($httpCode >= 400) {
            throw new \Exception('Error when calling ' . $fullUrl . ' (method: ' . $method . '), HTTP ' . $httpCode . ': ' . $body);
        }

        if ($body === '' || $httpCode == 204) {
            return null;
        }
        return json_decode($body);
    }

    /**
     * Fetch all pages of a paginated HAL collection.
     *
     * @param string $endpoint     Endpoint, may already contain a query string
     * @param string $embeddedKey  Key below "_embedded", e.g. "collections"
     *
     * @return array
     */
    protected function getAllPages(string $endpoint, string $embeddedKey): array
    {
        $result = [];
        $page = 0;
        $separator = (strpos($endpoint, '?') === false) ? '?' : '&';

        do {
            $response = $this->call($endpoint . $separator . 'size=' . self::PAGE_SIZE . '&page=' . $page, self::METHOD_GET);
            if (isset($response->_embedded->$embeddedKey)) {
                $result = array_merge($result, $response->_embedded->$embeddedKey);
            }
            $totalPages = $response->page->totalPages ?? 1;
            $page++;
        } while ($page < $totalPages);

        return $result;
    }

    /**
     * Get (or create) the bundle with the given name (e.g. ORIGINAL) of an item.
     */
    protected function getOrCreateBundle(string $itemId, string $bundleName='ORIGINAL')
    {
        $bundles = $this->getAllPages(self::ENDPOINT_ITEMS . '/' . urlencode($itemId) . '/bundles', 'bundles');
        foreach ($bundles as $bundle) {
            if ($bundle->name == $bundleName) {
                return $bundle;
            }
        }

        $postData = json_encode(['name' => $bundleName]);
        return $this->call(self::ENDPOINT_ITEMS . '/' . urlencode($itemId) . '/bundles',
                           self::METHOD_POST,
                           [self::HEADER_CONTENT_TYPE => 'application/json'],
                           $postData);
    }

    /**
     * Upload a file to the ORIGINAL bundle of an item.
     */
    public function addBitstream(string $itemId, string $name, string $path)
    {
        $bundle = $this->getOrCreateBundle($itemId, 'ORIGINAL');

        $mimeType = mime_content_type($path) ?: 'application/pdf';
        $data = [
            'file' => new \CURLFile($path, $mimeType, $name),
            'properties' => json_encode(['name' => $name]),
        ];

        // Content-Type (multipart + boundary) is set automatically by curl for array data
        return $this->call(self::ENDPOINT_BUNDLES . '/' . urlencode($bundle->uuid) . '/bitstreams',
                           self::METHOD_POST, [], $data);
    }

    /**
     * Create an item in a collection.
     *
     * Metadata may be given in the new format
     *   'metadata' => ['dc.title' => [['value' => 'Foo', 'language' => 'de']]]
     * or in the old DSpace 6 format, which is converted automatically:
     *   'metadata' => [['key' => 'dc.title', 'value' => 'Foo', 'language' => 'de']]
     *
     * Example Item: https://github.com/DSpace/RestContract/blob/main/items.md
     */
    public function addItem(string $collectionId, array $item)
    {
        if (isset($item['metadata']) && array_is_list($item['metadata'])) {
            $converted = [];
            foreach ($item['metadata'] as $entry) {
                $converted[$entry['key']][] = ['value' => $entry['value'],
                                               'language' => $entry['language'] ?? null];
            }
            $item['metadata'] = $converted;
        }

        $item += ['inArchive' => true, 'discoverable' => true, 'withdrawn' => false, 'type' => 'item'];
        $postData = json_encode($item);
        $headers = [self::HEADER_CONTENT_TYPE => 'application/json'];

        return $this->call(self::ENDPOINT_ITEMS . '?owningCollection=' . urlencode($collectionId),
                           self::METHOD_POST, $headers, $postData);
    }

    /**
     * @return array of collection objects
     */
    public function getCollections(string $communityId=null)
    {
        if (isset($communityId))
            return $this->getAllPages(self::ENDPOINT_COMMUNITIES . '/' . urlencode($communityId) . '/collections', 'collections');
        else
            return $this->getAllPages(self::ENDPOINT_COLLECTIONS, 'collections');
    }

    public function getCollectionByName(string $name, string $communityId=null)
    {
        $result = null;

        $collections = $this->getCollections($communityId);
        foreach ($collections as $collection) {
            if ($collection->name == $name) {
                if ($result != null)
                    throw new \Exception('Multiple collections found with the same name: ' . $name);
                $result = $collection;
            }
        }

        if ($result == null)
            throw new \Exception('Collection not found: ' . $name);

        return $result;
    }

    /**
     * Get an item and its properties.
     * Metadata is always included in DSpace 7+.
     *
     * @param string $id     The item UUID
     * @param string $embed  Optional, embed linked resources, e.g. "bundles" or "bundles/bitstreams"
     * @return \stdClass
     */
    public function getItem(string $id, string $embed=null)
    {
        $url = self::ENDPOINT_ITEMS . '/' . urlencode($id);
        if ($embed != null) {
            $url .= '?embed=' . urlencode($embed);
        }
        return $this->call($url, self::METHOD_GET);
    }

    public function getStatus()
    {
        return $this->call(self::ENDPOINT_AUTHN_STATUS, self::METHOD_GET);
    }

    /**
     * Try to login using the given credentials.
     *
     * @throws \Exception
     */
    public function login()
    {
        $cookiePath = $this->getCookiePath();
        if (file_exists($cookiePath)) {
            unlink($cookiePath);
        }
    
        $this->bearerToken = null;
        $this->csrfToken = null;

        // 1) Any GET request returns a fresh CSRF token (header + cookie)
        $this->call(self::ENDPOINT_AUTHN_STATUS, self::METHOD_GET);

        // 2) Login, JWT is returned in the "Authorization" response header
        $params = ['user' => $this->username, 'password' => $this->password];
        $postData = http_build_query($params);
        $headers = [self::HEADER_CONTENT_TYPE => 'application/x-www-form-urlencoded'];

        $this->call(self::ENDPOINT_AUTHN_LOGIN, self::METHOD_POST, $headers, $postData);

        if ($this->bearerToken === null) {
            throw new \Exception('Login failed: no bearer token received from DSpace');
        }
    }

    public function logout()
    {
        $this->call(self::ENDPOINT_AUTHN_LOGOUT, self::METHOD_POST);
        $this->bearerToken = null;
    }

    protected function getCookiePath(): string
    {
        return sys_get_temp_dir() . '/DSpaceCookies_' . md5($this->baseUrl . $this->username);
    }

}