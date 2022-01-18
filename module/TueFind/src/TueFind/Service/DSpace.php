<?php

/**
 * DSpace REST API implementation
 *
 * Protocol Documentation
 * - https://github.com/DSpace/RestContract/blob/main/README.md
 *
 * API Tutorials
 * - https://dspace-labs.github.io/DSpace7RestTutorial/
 *
 * Sometimes, when neither documentation nor tutorials help, "Use the Source, Luke!":
 * - https://github.com/DSpace/DSpace/tree/main/dspace-server-webapp/src/main/java/org/dspace/app/rest
 *
 * Demo instance (official)
 * - https://demo.dspace.org/
 * - https://api7.dspace.org/server/#/server/api
 */

namespace TueFind\Service;

class DSpace {

    const ENDPOINT_AUTH_LOGIN = '/api/authn/login';
    const ENDPOINT_AUTH_STATUS = '/api/authn/status';
    const ENDPOINT_CORE_COLLECTIONS = '/api/core/collections';
    const ENDPOINT_CORE_COMMUNITIES = '/api/core/communities';
    const ENDPOINT_CORE_ITEMS = '/api/core/items';
    const ENDPOINT_CORE_METADATASCHEMAS = '/api/core/metadataschemas';
    const ENDPOINT_WORKSPACE_ITEM = '/api/submission/workspaceitems';
    const ENDPOINT_WORKFLOW_ITEM = '/api/workflow/workflowitems';

    const HEADER_AUTHORIZATION = 'Authorization';
    const HEADER_CONTENT_TYPE = 'Content-Type';
    const HEADER_COOKIE_REQUEST = 'Cookie';
    const HEADER_COOKIE_RESPONSE = 'Set-Cookie';
    const HEADER_CSRF_REQUEST = 'X-XSRF-TOKEN';
    const HEADER_CSRF_RESPONSE = 'DSPACE-XSRF-TOKEN';

    const METHOD_DELETE = 'DELETE';
    const METHOD_GET = 'GET';
    const METHOD_HEAD = 'HEAD';
    const METHOD_PATCH = 'PATCH';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';

    protected $baseUrl;
    protected $username;
    protected $password;

    /**
     * The authentication bearer, returned in HTTP response after login
     *
     * @var string
     */
    protected $bearer;

    /**
     * These cookies will be returned via the API on first call
     * and need to be sent back to the API on all consecutive requests.
     *
     * @var array
     */
    protected $cookies = [];

    /**
     * This token will be returned via the API on the first call
     * and needs to be sent to the API on all consecutive requests.
     *
     * A CSRF token MUST be given before using any modifying operation
     * - Non-modifying: GET, HEAD
     * - Modifying: DELETE, PATCH, POST, PUT
     *
     * @var string
     */
    protected $csrfToken;

    public function __construct(string $baseUrl, string $username, string $password)
    {
        $this->baseUrl = $baseUrl;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Start the workflow (e.g. publication) for an existing workspace item (e.g. uploaded PDF file).
     *
     * @param string $workspaceItemId
     */
    public function addWorkflowItem(string $workspaceItemId)
    {
        $this->call(self::ENDPOINT_WORKFLOW_ITEM, self::METHOD_POST, [self::HEADER_CONTENT_TYPE => 'text/uri-list'], self::ENDPOINT_WORKSPACE_ITEM . '/' . urlencode($workspaceItemId));
    }

    /**
     * Add a workspace item (e.g. upload a PDF file)
     *
     * @param string $collectionId
     * @param string $documentUrl
     */
    public function addWorkspaceItem(string $documentUrl, string $collectionId=null)
    {
        $endpointUrl = self::ENDPOINT_WORKSPACE_ITEM;
        if ($collectionId != null)
            $endpointUrl .= '?owningCollection=' . urlencode($collectionId);

        // Direct approach: pass URL
        return $this->call($endpointUrl, self::METHOD_POST, [self::HEADER_CONTENT_TYPE => 'text/uri-list'], $documentUrl);

        // Indirect approach: POST the whole file
        $fileHandle = fopen($documentUrl, "rb");
        $fileContents = stream_get_contents($fileHandle);
        fclose($fileHandle);

        return $this->call($endpointUrl, self::METHOD_POST, [self::HEADER_CONTENT_TYPE => 'multipart/form-data'], $fileContents);
    }

    public function getAuthenticationStatus()
    {
        if (!isset($this->bearer))
            throw new \Exception('No bearer value present yet that we could check against (not yet logged in?).');

        return $this->call(self::ENDPOINT_AUTH_STATUS, self::METHOD_GET, [self::HEADER_AUTHORIZATION => 'Bearer ' . $this->bearer]);
    }

    public function getCollectionByName(string $name, string $communityId=null)
    {
        $result = null;

        $collections = $this->getCollections($communityId);
        foreach ($collections->_embedded->collections as $collection) {
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

    public function getCollections(string $communityId=null)
    {
        if (isset($communityId))
            return $this->call(self::ENDPOINT_CORE_COMMUNITIES . '/' . urlencode($communityId) . '/collections', self::METHOD_GET);
        else
            return $this->call(self::ENDPOINT_CORE_COLLECTIONS, self::METHOD_GET);
    }

    public function getCommunities()
    {
        return $this->call(self::ENDPOINT_CORE_COMMUNITIES, self::METHOD_GET);
    }

    public function getItem(string $id)
    {
        return $this->call(self::ENDPOINT_CORE_ITEMS . '/' . urlencode($id), self::METHOD_GET);
    }

    public function getMetadataSchema(string $id)
    {
        return $this->call(self::ENDPOINT_CORE_METADATASCHEMAS . '/' . urlencode($id), self::METHOD_GET);
    }

    public function getSubCommunities(string $communityId)
    {
        return $this->call(self::ENDPOINT_CORE_COMMUNITIES . '/' . urlencode($communityId) . '/subcommunities', self::METHOD_GET);
    }

    public function getWorkspaceItem(string $id)
    {
        return $this->call(self::ENDPOINT_WORKSPACE_ITEM . '/' . urlencode($id), self::METHOD_GET);
    }

    /**
     * Try to login using the given credentials.
     *
     * @throws \Exception
     */
    public function login(): void
    {
        // Since this is a POST operation it requires a crsfToken before using it,
        // so we just call another GET/HEAD operation in case it is missing so far.
        if (!isset($this->csrfToken))
            $this->hasMetadataSchema(1);

        $params = ['user' => $this->username, 'password' => $this->password];
        $postData = http_build_query($params);
        $headers = ['Content-Type' => 'application/x-www-form-urlencoded',
                    'Content-Length' => strlen($postData)];

        $this->call(self::ENDPOINT_AUTH_LOGIN, self::METHOD_POST, $headers, $postData);
    }

    /**
     * Call the API & return its result
     *
     * @param string $endpoint  One of the ENDPOINT_... class constants
     * @param string $method    One of the METHOD_... class constants
     * @param array  $headers   Array with additional headers to be sent
     *                          (please use HEADER_... class constants)
     * @param string $data      The encoded data, matching the format in $headers
     *
     * @return The decoded JSON response.
     */
    protected function call(string $endpoint, string $method, array $headers=[], string $data=null)
    {
        $fullUrl = $this->baseUrl . $endpoint;

        $opts = ['http' => ['method' => $method, 'header' => '']];
        if (isset($data))
            $opts['http']['content'] = $data;

        if (isset($this->csrfToken))
            $headers[self::HEADER_CSRF_REQUEST] = $this->csrfToken;

        if ($this->cookies != []) {
            $cookiesString = '';
            foreach ($this->cookies as $cookieId => $cookieValue) {
                if ($cookiesString != '')
                    $cookiesString .= '; ';
                $cookiesString .= $cookieId . '=' . $cookieValue;
            }
            $headers[self::HEADER_COOKIE_REQUEST] = $cookiesString;
        }
        if ($headers != []) {
            $headerString = '';
            foreach ($headers as $headerName => $headerValue)
                $headerString .= $headerName . ': ' . $headerValue . "\r\n";
            $opts['http']['header'] .= $headerString;
        }

        $context = stream_context_create($opts);
        $json = file_get_contents($fullUrl, false, $context);

        // The server will send a token either on the first response
        // or on any other response, but will not send it in all requests.
        // But whenever he sends one back, we need to use the new one from now on.
        $responseHeaders = get_headers($fullUrl, true, $context);
        /*
        print "<br><br><br>\n\n\n" . $endpoint . "<br><br><br>\n\n\n";
        print_r($responseHeaders);
        if (str_contains($endpoint, self::ENDPOINT_WORKSPACE_ITEM)) {
            print 'WORKSPACE ITEM';
            print '<pre>';
            print_r($json);
            print '</pre>';
            die();
        }*/
        if (isset($responseHeaders[self::HEADER_CSRF_RESPONSE]))
            $this->csrfToken = $responseHeaders[self::HEADER_CSRF_RESPONSE];
        if (isset($responseHeaders[self::HEADER_COOKIE_RESPONSE])) {
            $cookies = $responseHeaders[self::HEADER_COOKIE_RESPONSE];
            if (!is_array($cookies))
                $cookies = [$cookies];
            foreach ($cookies as $cookie) {
                if (preg_match('"^([^=]+)=([^=;]+)"', $cookie, $hits))
                    $this->cookies[$hits[1]] = $hits[2];
            }
        }
        if (isset($responseHeaders[self::HEADER_AUTHORIZATION])) {
            if (preg_match('"Bearer (.+)"', $responseHeaders[self::HEADER_AUTHORIZATION], $hits))
                $this->bearer = $hits[1];
        }

        return json_decode($json);
    }

}