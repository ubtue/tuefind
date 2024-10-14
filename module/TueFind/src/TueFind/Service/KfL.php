<?php


namespace TueFind\Service;

/**
 * Class to communicate with KfL/HAN Proxy.
 *
 * For API documentation, see:
 * https://www.hh-han.com/webhelp-de/han_web_api.htm
 */
class KfL
{
    protected $authManager;
    protected $tuefindInstance;
    protected $recordLoader;

    protected $baseUrl;
    protected $apiId;
    protected $encryptionKey;
    protected $cipher;
    protected $licenses;

    const RETURN_REDIRECT = 0;
    const RETURN_JSON = 1;
    const RETURN_TEMPLATE = 2;

    /**
     * Constructor
     *
     * @param Config $config            Configuration entries
     * @param Manager $authManager      Auth Manager
     * @param string $tuefindInstance   TueFind instance
     * @param Loader $recordLoader      Record loader
     */
    public function __construct($config, $authManager, $tuefindInstance, $recordLoader)
    {
        $this->baseUrl = $config->base_url;
        $this->apiId = $config->api_id;
        $this->cipher = $config->cipher;
        $this->encryptionKey = $config->encryption_key;

        $licenses = $config->licenses ?? [];
        $parsedLicenses = [];
        foreach ($licenses as $license) {
            $licenseDetails = explode(';', $license);
            $parsedLicenses[] = ['hanId' => $licenseDetails[0],
                                 'entitlement' => $licenseDetails[1],
                                 'countryMode' => $licenseDetails[2] ?? null,
            ];
        }
        $this->licenses = $parsedLicenses;

        $this->authManager = $authManager;
        $this->tuefindInstance = $tuefindInstance;
        $this->recordLoader = $recordLoader;
    }

    /**
     * Generate an URL with all the GET params
     *
     * @param array $requestData Additional params to add to the base URL
     *
     * @return string
     */
    protected function generateUrl(array $requestData): string
    {
        $url = $this->baseUrl;
        $i = 0;
        foreach ($requestData as $key => $value) {
            if ($i == 0)
                $url .= '?';
            else
                $url .= '&';
            $url .= urlencode($key) . '=' . urlencode($value);
            ++$i;
        }
        return $url;
    }

    /**
     * Execute call and return result
     *
     * @param array $requestData
     */
    protected function call(array $requestData)
    {
        $url = $this->generateUrl($requestData);
        return file_get_contents($url);
    }

    /**
     * Decode a given SSO string (for debugging purposes only)
     */
    public function decodeSso(string $ssoHex)
    {
        $ssoBin = hex2bin($ssoHex);
        $ssoJson = openssl_decrypt($ssoBin, $this->cipher, $this->encryptionKey, OPENSSL_RAW_DATA);

        $error = '';
        while (($errorLine = openssl_error_string()) != false)
            $error .= $errorLine . "\n";
        rtrim($error);

        if ($error != '')
            return $error;

        $ssoArray = json_decode($ssoJson);
        return $ssoArray;
    }

    /**
     * Generate token that represents the frontend user
     *
     * @return string
     *
     * @throws \Exception
     */
    protected function getFrontendUserToken(): string
    {
        // Check if user is logged-in:
        // This should be checked at the latest possible point.
        // An earlier implementation checked it in the factory, which led
        // to errors in other actions in the same controller, which should still
        // be possible if the user is not logged in.
        $user = $this->authManager->isLoggedIn();
        if (!$user)
            throw new \Exception('Could not generate KfL Frontend User Token, user is not logged in!');

        if ($user->isLicenseAccessLocked())
            throw new \Exception('Could not generate KfL Frontend User Token, user\'s access to resources has been locked!');

        // We pass an anonymized version of the user id (tuefind_uuid) together with host+tuefind instance.
        // This value will be saved by the proxy and reported back to us in case of abuse.
        return implode('#', [gethostname(), $this->tuefindInstance, $user->tuefind_uuid]);
    }

    /**
     * Get encrypted Single Sign On part of the request (including user credentials)
     *
     * @param string $entitlement   Entitlement (=license) for the given title, mandatory for redirects.
     *
     * @return string
     *
     * @throws Exception
     */
    protected function getSso($entitlement=null): string
    {
        $env = [];
        if ($entitlement != null)
            $env[] = ['name' => 'entitlement', 'value' => $entitlement];

        // Amount of seconds from now until the URL is valid:
        $validTimespan = 60*60*24*1; // 1 day

        $sso = ['user' => $this->getFrontendUserToken(),
                'timestamp' => time() + $validTimespan,
                'env' => $env,
        ];

        $encryptedData = openssl_encrypt(json_encode($sso), $this->cipher, $this->encryptionKey, OPENSSL_RAW_DATA);
        if ($encryptedData === false)
            throw new Exception('Could not encrypt data!');
        return bin2hex($encryptedData);
    }

    /**
     * Get basic request template needed for every request
     * (containing user credentials and so on)
     *
     * @param string $entitlement   Entitlement (=license) for the given title, mandatory for redirects.
     *
     * @return array
     */
    protected function getRequestTemplate($entitlement=null): array
    {
        $requestData = [];
        $requestData['id'] = $this->apiId;
        $requestData['sso'] = $this->getSso($entitlement);
        return $requestData;
    }

    /**
     * Get the URL to access the given record via the KfL proxy.
     *
     * @param array $licenseInfo
     * @param string $url
     * @param title $title
     *
     * @return string
     */
    protected function getUrl(array $licenseInfo, ?string $url=null, ?string $title=null): string
    {
        $requestData = $this->getRequestTemplate($licenseInfo['entitlement']);
        $requestData['method'] = 'getHANID';
        $requestData['return'] = self::RETURN_REDIRECT;
        $requestData['hanid'] = $licenseInfo['hanId'];
        if (!empty($url))
            $requestData['url'] = $url;
        if (!empty($title))
            $requestData['title'] = $title;

        return $this->generateUrl($requestData);
    }

    /**
     * Get the relevant country mode for the hanId in this record.
     *
     * @param SolrMarc $driver
     *
     * @return string
     */
    public function getCountryModeByDriver(\TueFind\RecordDriver\SolrMarc $driver): ?string
    {
        $licenseInfo = $this->getLicenseInfoByDriver($driver);
        return $licenseInfo['countryMode'] ?? null;
    }

    /**
     * Get the URL to access the given record via the KfL proxy.
     *
     * @param SolrMarc $driver
     *
     * @return string
     */
    public function getUrlByDriver(\TueFind\RecordDriver\SolrMarc $driver)
    {
        // Main: Get Han ID from License URL
        $url = $driver->getKflUrl();
        $licenseInfo = $this->getLicenseInfoByDriver($driver);
        if (empty($licenseInfo))
            throw new \Exception("No License found for record " . $driver->getUniqueId());
        $title = $driver->getTitle();

        return $this->getUrl($licenseInfo, $url, $title);
    }

    /**
     * Get the URL to access the given record via the KfL proxy.
     *
     * @param string $hanId
     * @param string $url
     *
     * @return string
     */
    public function getUrlByHanID(string $hanId, ?string $url=null)
    {
        return $this->getUrl($this->getLicenseInfoByHanID($hanId), $url);
    }

    /**
     * Get license information by driver
     *
     * Note: We do not want the whole license information to be publically available outside this class,
     *       since it might contain entitlements & other security-related functions.
     *       If you need specific information (e.g. countryMode), please use a separate public getter.
     *
     *
     * @param SolrMarc $driver
     *
     * @return array|null
     */
    protected function getLicenseInfoByDriver(\TueFind\RecordDriver\SolrMarc $driver): ?array
    {
        $url = $driver->getKflUrl();
        if ($url) {
            $urlInfo = $this->parseKflUrl($url);
            foreach($this->licenses as $license) {
                if ($license['hanId'] == $urlInfo['hanId'])
                    return $license;
            }
        }

        return null;
    }

    /**
     * Get license information by hand id only
     *
     * @param string $hanId
     *
     * @return array
     */
    protected function getLicenseInfoByHanID(string $hanId): array
    {
        foreach ($this->licenses as $license) {
            if ($license['hanId'] == $hanId)
                return $license;
        }

        throw new \Exception('KfL license information missing for HAN ID: ' . $hanId);
    }

    /**
     * Get information from the KfL URL (e.g. HanId)
     *
     * @param string $url
     *
     * @return array
     */
    protected function parseKflUrl($url): array
    {
        if (strstr($url, 'proxy.fid-lizenzen.de') === false)
            throw new \Exception("Invalid KfL URL: " . $url);

        $path = parse_url($url, PHP_URL_PATH);

        // example: /han/rx-ebookcentral/ebookcentral.proquest.com/lib/fidreli/detail.action

        $pathParts = explode('/', $path);
        return ['hanId' => $pathParts[2]];
    }

    /**
     * Is the given record available via the KfL?
     *
     * @param SolrMarc $driver
     *
     * @return bool
     */
    public function hasTitle(\TueFind\RecordDriver\SolrMarc $driver): bool
    {
        return $this->getLicenseInfoByDriver($driver) !== null;
    }
}
