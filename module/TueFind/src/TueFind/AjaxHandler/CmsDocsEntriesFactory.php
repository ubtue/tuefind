<?php

namespace TueFind\AjaxHandler;

use Laminas\View\Renderer\PhpRenderer;
use Psr\Container\ContainerInterface;

class CmsDocsEntriesFactory implements \Laminas\ServiceManager\Factory\FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (!empty($options)) {
            throw new \Exception('Unexpected options passed to factory.');
        }

        return new $requestedName(
            $container->get(\VuFind\Search\Results\PluginManager::class), // 1-й: Search Manager
            $container->get(\Laminas\View\Renderer\PhpRenderer::class),  // 2-й: PhpRenderer
            $container->get(\VuFind\Config\PluginManager::class),
            $container->get(\VuFind\Auth\Manager::class)->getUserObject() // 3-й: User Object
        );
    }
}
