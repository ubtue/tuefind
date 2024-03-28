<?php

namespace TueFind\Controller;

/**
 * This controller is used to redirect to a given URL and save it with a timestamp.
 * (e.g. to Track how many times an external service is used, without storing person-related data.)
 */
class RedirectController extends \VuFind\Controller\AbstractBase implements \VuFind\Db\Table\DbTableAwareInterface
{
    use \VuFind\Db\Table\DbTableAwareTrait;

    /**
     * Decoder for URL in GET params
     * @var \TueFind\View\Helper\TueFind\TueFind
     */
    protected $decoder;

    /**
     * KfL service for license redirects
     * @var \TueFind\Service\KfL
     */
    protected $kfl;

    public function setDecoder(\TueFind\View\Helper\TueFind\TueFind $decoder) {
        $this->decoder = $decoder;
    }

    public function setKflService(\TueFind\Service\KfL $kfl)
    {
        $this->kfl = $kfl;
    }

    public function redirectAction()
    {
        /**
        * Use HTML Meta redirect page instead of HTTP header.
        * HTTP header redirect may fail when using php-fpm if the header
        * is larger than 8192 Bytes.
        *
        * See https://maxchadwick.xyz/blog/http-response-header-size-limit-with-mod-proxy-fcgi
        */
        if ($url = $this->params('url')) {
            // URL needs to be base64, else we will have problems with slashes,
            // even if they are url encoded
            $url = $this->decoder->base64UrlDecode($url);
            if (strpos($url, '://') !== false) {
                $group = $this->params('group') ?? null;
                $this->getDbTable('redirect')->insertUrl($url, $group);
                $view = $this->createViewModel();
                $view->redirectTarget = $url;
                $view->redirectDelay = 0;
                return $view;
            }
        }

        $this->getResponse()->setStatusCode(404);
    }

    public function licenseAction()
    {
        $user = $this->getUser();
        if ($user == false) {
            return $this->forceLogin();
        }

        $id = $this->params()->fromRoute('id');

        if (preg_match('"^rx-"', $id)) {
            // This call will happen when the user opened the document earlier
            // and the KfL HAN server will have a timeout, so the proxy will call
            // our page again with the HAN ID and a Proxy URL containing a direct
            // link to the exact document and page id which the user has navigated
            // to before.
            // The timeout will happen after 1 hour of inactivity + trying to navigate
            // inside the document afterwards.
            if ($user->isLicenseAccessLocked()) {
                throw new \Exception("The user's access has been locked!");
            } else {
                // Example for a URL sent by the proxy (will not work if you try manually):
                // id: rx-hdr
                // proxy-url: https://www-1handbuch-2religionen-1de-1wen6n5xi0b66.proxy.fid-lizenzen.de/#doc/69047/7
                // (note: rx-hdr has been moved to a different publisher so the URL might be different now)
                $proxyUrl = $this->params()->fromRoute('proxy-url');
                $base64UrlDecoder = new \TueFind\Crypt\Base64Url();
                $redirectUrl = $this->kfl->getUrlByHanID($id, $base64UrlDecoder->decodeString($proxyUrl));
                $this->redirect()->toUrl($redirectUrl);
            }
        } else {
            // This is the regular case, a user requesting fulltext access via the frontend.
            $viewParams = [];
            $viewParams['driver'] = $this->getRecordLoader()->load($id);
            $viewParams['locked'] = $user->isLicenseAccessLocked();

            // Check country restriction
            $viewParams['countryMode'] = $this->kfl->getCountryModeByDriver($viewParams['driver']);
            if ($viewParams['countryMode'] == 'DACH') {
                $viewParams['countryAllowed'] = in_array($user->tuefind_country, ['DE', 'AT', 'CH']);
            } else {
                $viewParams['countryAllowed'] = true;
            }

            $viewParams['licenseUrl'] = !$viewParams['locked'] ? $this->kfl->getUrlByDriver($viewParams['driver']) : null;
            return $this->createViewModel($viewParams);
        }
    }
}
