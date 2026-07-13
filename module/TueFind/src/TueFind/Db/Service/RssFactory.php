<?php

namespace TueFind\Db\Service;

use Psr\Container\ContainerInterface;

class RssFactory extends \VuFind\Db\Service\AbstractDbServiceFactory
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ) {
        $service = parent::__invoke($container, $requestedName, $options);
        $instance = $container->get('ViewHelperManager')->get('tuefind')->getTueFindInstance();
        $service->setInstance($instance);
        return $service;
    }
}
