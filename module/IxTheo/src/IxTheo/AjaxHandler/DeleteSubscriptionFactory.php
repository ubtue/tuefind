<?php

namespace IxTheo\AjaxHandler;

use Psr\Container\ContainerInterface;

class DeleteSubscriptionFactory implements \Laminas\ServiceManager\Factory\FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ) {
        if (!empty($options)) {
            throw new \Exception('Unexpected options passed to factory.');
        }
        $servicePluginManager = $container->get(\VuFind\Db\Service\PluginManager::class);
        return new $requestedName(
            $servicePluginManager->get(\IxTheo\Db\Service\SubscriptionService::class),
            $container->get(\VuFind\Auth\Manager::class)->getUserObject()
        );
    }
}
