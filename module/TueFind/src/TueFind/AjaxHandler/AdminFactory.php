<?php

namespace TueFind\AjaxHandler;

use Psr\Container\ContainerInterface;
use VuFind\Auth\Manager;
use TueFind\Service\DSpace8;

class AdminFactory implements \Laminas\ServiceManager\Factory\FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ) {
        if (!empty($options)) {
            throw new \Exception('Unexpected options passed to factory.');
        }

        $dbServiceManager = $container->get(\VuFind\Db\Service\PluginManager::class);
        $dspace8 = $container->get(\TueFind\Service\DSpace8::class);

        return new $requestedName(
            $container->get(\Laminas\View\Renderer\PhpRenderer::class),
            $container->get(Manager::class)->getUserObject(),
            $dbServiceManager->get(\TueFind\Db\Service\UserServiceInterface::class),
            $dspace8
        );
    }
}
