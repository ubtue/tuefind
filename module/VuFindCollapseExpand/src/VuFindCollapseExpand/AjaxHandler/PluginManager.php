<?php

namespace VuFindCollapseExpand\AjaxHandler;

class PluginManager extends \VuFind\AjaxHandler\PluginManager
{
    public function __construct(
        $configOrContainerInstance = null,
        array $v3config = []
    ) {
        $this->addOverride('aliases', 'getItemCollpaseAndExpand', GetItemCollapseExpand::class);
        $this->addOverride('factories', GetItemCollapseExpand::class, GetItemCollapseExpandFactory::class);
        parent::__construct($configOrContainerInstance, $v3config);
    }
}