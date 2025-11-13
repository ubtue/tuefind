<?php

namespace TueFind\RecordTab;

use Laminas\ServiceManager\Factory\InvokableFactory;

class PluginManager extends \VuFind\RecordTab\PluginManager
{
    public function __construct(
        $configOrContainerInstance = null,
        array $v3config = []
    ) {
        $this->aliases['AuthorityNameVariants'] = AuthorityNameVariants::class;
        $this->aliases['itemcollapseandexpand'] = ItemCollapseAndExpand::class;


        $this->factories[AuthorityNameVariants::class] = InvokableFactory::class;
        $this->factories[ItemCollapseAndExpand::class] = ItemCollapseExpandFactory::class;

        $this->addAbstractFactory(PluginFactory::class);
        parent::__construct($configOrContainerInstance, $v3config);
    }

}
