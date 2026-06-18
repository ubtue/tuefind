<?php

namespace IxTheo\RecordDataFormatter\Specs;

use Psr\Container\ContainerInterface;

class DefaultRecordFactory extends \VuFind\RecordDataFormatter\Specs\DefaultRecordFactory
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ) {
        $defaultRecord = parent::__invoke($container, $requestedName, $options);
        $defaultRecord->initWithContainer($container);
        return $defaultRecord;
    }
}
