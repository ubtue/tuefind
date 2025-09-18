<?php

namespace TueFind\Config;

use Psr\Container\ContainerInterface;

class AccountCapabilitiesFactory extends \VuFind\Config\AccountCapabilitiesFactory
{
    public function __invoke(ContainerInterface $container, $requestedName,
        array $options = null
    ) {
        if (!empty($options)) {
            throw new \Exception('Unexpected options sent to factory.');
        }
        return new $requestedName(
            $container->get(\VuFind\Config\PluginManager::class)->get('config'),
            function () use ($container) {
                return $container->get(\VuFind\Auth\Manager::class);
            },
            $container->get(\VuFind\Config\PluginManager::class)->get('tuefind')
        );
    }
}
