<?php

namespace IxTheo\Search\PDASubscriptions;

use Psr\Container\ContainerInterface;

class ResultsFactory extends \VuFind\Search\Results\ResultsFactory
{
    public function __invoke(ContainerInterface $container, $requestedName,
        array $options = null
    ) {
        $results = parent::__invoke($container, $requestedName, $options);
        $results->setPDASubscriptionService($container->get('VuFind\Db\Service\PluginManager')->get(\IxTheo\Db\Service\PDASubscriptionServiceInterface::class));

        //$init = new \Lmc\Rbac\Mvc\Initializer\AuthorizationServiceInitializer();
        //$init($container, $results);

        return $results;
    }
}
