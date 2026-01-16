<?php

namespace TueFind\RecordTab;

use Laminas\ServiceManager\Factory\InvokableFactory;

class PluginManager extends \VuFind\RecordTab\PluginManager
{
    use \TueFind\PluginManagerExtensionTrait;

    public function __construct(
        $configOrContainerInstance = null,
        array $v3config = []
    ) {
        $this->addOverride('aliases', 'AuthorityNameVariants', AuthorityNameVariants::class);

        $this->addOverride('factories', AuthorityNameVariants::class, InvokableFactory::class);
        
        $this->applyOverrides();
        
        $this->addAbstractFactory(PluginFactory::class);

        parent::__construct($configOrContainerInstance, $v3config);
    }

}