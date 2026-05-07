<?php

namespace VuFindCollapseExpand\ServiceManager;

use Psr\Container\ContainerInterface;

class ServiceInitializer extends \VuFind\ServiceManager\ServiceInitializer
{
    public function __invoke(ContainerInterface $sm, $instance)
    {
        $instance = parent::__invoke($sm, $instance);
        if ($instance instanceof \VuFindCollapseExpand\Config\CollapseExpandConfigAwareInterface) {
            $instance->setCollapseExpandConfig(
                $sm->get(\VuFindCollapseExpand\Config\CollapseExpand::class)
            );
        }
        return $instance;
    }
}
