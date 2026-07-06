<?php

namespace TueFind\Service;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CmsSyncFactory implements FactoryInterface
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
            $container->get(\VuFind\Config\PluginManager::class)->get('tuefind')->CMS,
            $container->get('ViewHelperManager')->get('tuefind')->getTueFindSubsystem(),
            $container->get(\VuFind\Db\Service\PluginManager::class),
            $container->get(\VuFind\Auth\Manager::class)
        );
    }
}
