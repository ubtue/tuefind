<?php

namespace VuFindCollapseExpand\View\Helper\CollapseExpand;

use Psr\Container\ContainerInterface;

class CollapseExpandFactory
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ) {
        if (!empty($options)) {
            throw new \Exception('Unexpected options passed to factory.');
        }
        return new $requestedName(
            $container->get(\VuFind\Search\Options\PluginManager::class),
            $container->get(\VuFind\Search\Results\PluginManager::class),
            $container->get(\VuFindSearch\Service::class),
        );
    }
}
