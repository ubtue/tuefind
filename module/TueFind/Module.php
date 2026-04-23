<?php
namespace TueFind;
use Laminas\ModuleManager\ModuleManager,
    Laminas\Mvc\MvcEvent;

class Module
{
    /**
     * Get module configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Get autoloader configuration
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return [
            'Laminas\Loader\StandardAutoloader' => [
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ],
        ];
    }

    /**
     * Get view helper configuration.
     *
     * @return array
     */
    public function getViewHelperConfig()
    {
        return [
            'factories' => [
                View\Helper\ImageLink::class => \VuFindTheme\View\Helper\ImageLinkFactory::class,
            ],
            'aliases' => [
                'imageLink' => View\Helper\ImageLink::class,            ],
        ];
    }

    /**
     * Initialize the module
     *
     * @param ModuleManager $m Module manager
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function init(ModuleManager $m)
    {
    }

    /**
     * Bootstrap the module
     *
     * @param MvcEvent $e Event
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function onBootstrap(MvcEvent $e)
    {
        $bootstrapper = new Bootstrapper($e);
        $bootstrapper->bootstrap();

        BotProtect::ProcessRequest($e);

        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_FINISH, array($this, 'onFinish'), 1000);

        // Set referrer policy for certain sub-pages, see Issue #3611 (OpenStreetMap)
        // This did NOT work in other locations
        // - apache conf: If you set it for /, it will always override sub-pages (tested with several types of config)
        // - CspContentGenerator: Uses Laminas, which will only allow a specific set of headers, but not Referrer-Policy
        $eventManager->attach(MvcEvent::EVENT_FINISH, function (MvcEvent $e) {
            $request = $e->getRequest();
            $response = $e->getResponse();

            if (!method_exists($request, 'getUri')) {
                return;
            }

            $referrerPolicyDefault = 'same-origin';
            $referrerPolicyMap = [
                'strict-origin-when-cross-origin' => [
                    '/Content/Networking',

                    // Derivatives of "Networking"-Page
                    '/Content/Bibliographies',
                    '/Content/LibrarianAssociations',
                    '/Content/Libraries',
                    '/Content/SpecialistInformationServices',
                    '/Content/TheologicalCommunity',
                ],
            ];

            $currentPath = $request->getUri()->getPath();
            $specialPolicyFound = false;
            foreach ($referrerPolicyMap as $referrerPolicy => $referrerPaths) {
                foreach ($referrerPaths as $referrerPath) {
                    if (str_starts_with($currentPath, $referrerPath)) {
                        $response->getHeaders()->addHeaderLine('Referrer-Policy', $referrerPolicy);
                        $specialPolicyFound = true;
                        break 2;
                    }
                }
            }

            if (!$specialPolicyFound) {
                $response->getHeaders()->addHeaderLine('Referrer-Policy', $referrerPolicyDefault);
            }
        });
    }


    public function onFinish(MvcEvent $e) {
        BotProtect::ProcessResponse($e);
    }
}
