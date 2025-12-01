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
    }


    public function onFinish(MvcEvent $e) {
        BotProtect::ProcessResponse($e);
    }
}
