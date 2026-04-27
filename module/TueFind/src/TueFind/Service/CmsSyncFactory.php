<?php

namespace TueFind\Service;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CmsSyncFactory implements FactoryInterface {
    public function __invoke(ContainerInterface $container, $requestedName,
        array $options = null
    ) {
        if (!empty($options)) {
            throw new \Exception('Unexpected options passed to factory.');
        }

        return new $requestedName(
            $container->get(\VuFind\Config\PluginManager::class)->get('tuefind')->CMS,
            $container->get('ViewHelperManager')->get('tuefind')->getTueFindSubsystem(),
            $container->get(\VuFind\Db\Service\PluginManager::class)
        );
    }
}
