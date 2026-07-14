<?php

namespace TueFindApi\Controller;

use function in_array;

class ApiController extends \VuFind\Controller\AbstractBase
{
    use \VuFindApi\Controller\ApiTrait;

    /**
     * Array of available API controllers
     *
     * @var array
     */
    protected $apiControllers = [];

    /**
     * Add an API controller to the list of available controllers
     *
     * @param Laminas\Mvc\Controller\AbstractActionController $controller API
     * Controller
     *
     * @return void
     */
    public function addApi($controller)
    {
        if (!in_array($controller, $this->apiControllers)) {
            $this->apiControllers[] = $controller;
        }
    }

    /**
     * Index action
     *
     * Return API specification or redirect to Swagger UI
     *
     * @return \Laminas\Http\Response
     */
    public function indexAction()
    {
        // Disable session writes
        $this->disableSessionWrites();

        if (
            null === $this->getRequest()->getQuery('swagger')
            && null === $this->getRequest()->getQuery('openapi')
        ) {
            $urlHelper = $this->getViewRenderer()->plugin('url');
            $base = rtrim($urlHelper('home'), '/');
            $url = "$base/swagger-ui/?url=" . urlencode("$base/api?openapi");
            return $this->redirect()->toUrl($url);
        }
        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-type', 'application/json');
        $json = json_encode($this->getApiSpecs(), JSON_PRETTY_PRINT);
        $response->setContent($json);
        return $response;
    }

    /**
     * Get API specification JSON fragment for the root nodes
     *
     * @return string
     */
    protected function getApiSpecFragment()
    {
        $config = $this->getConfigArray();
        $this->initApiKeySettings($config['API_Keys'] ?? []);
        $params = [
            'config' => $config,
            'apiKeysEnabled' => $this->developerSettingsService?->apiKeysEnabled() ?? false,
            'apiKeyHeaderField' => $this->apiKeyHeaderField,
            'apiKeyMode' => $this->developerSettingsService?->getApiKeyMode(),
            'version' => \VuFind\Config\Version::getBuildVersion(),
        ];
        return $this->getViewRenderer()->render('api/openapi', $params);
    }

    /**
     * Merge specification fragments from all APIs to an array
     *
     * @return array
     */
    protected function getApiSpecs(): array
    {
        $results = [];

        foreach (array_merge([$this], $this->apiControllers) as $controller) {
            $api = $controller->getApiSpecFragment();
            $specs = json_decode($api, true);
            if (null === $specs) {
                throw new \Exception(
                    'Could not parse API spec fragment of '
                    . $controller::class . ': ' . json_last_error_msg()
                );
            }
            foreach ($specs as $key => $spec) {
                if (isset($results[$key])) {
                    if ('components' === $key) {
                        $results['components']['schemas'] = array_merge(
                            $results['components']['schemas'] ?? [],
                            $spec['schemas'] ?? []
                        );
                    } else {
                        $results[$key] = array_merge($results[$key], $spec);
                    }
                } else {
                    $results[$key] = $spec;
                }
            }
        }

        return $results;
    }
}
