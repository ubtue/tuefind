<?php

namespace IxTheo\Search\Subscriptions;

use Psr\Container\ContainerInterface;

class ResultsFactory extends \VuFind\Search\Results\ResultsFactory
{
    public function __invoke(ContainerInterface $container, $requestedName,
        array $options = null
    ) {
        $results = parent::__invoke($container, $requestedName, $options);
        $results->setSubscriptionTable($container->get('VuFind\Db\Table\PluginManager')->get('subscription'));

        $init = new \LmcRbacMvc\Initializer\AuthorizationServiceInitializer();
        $init($container, $results);

        return $results;
    }
}
