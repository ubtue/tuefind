<?php

namespace TueFind\AjaxHandler;

class PluginManager extends \VuFind\AjaxHandler\PluginManager
{
    use \TueFind\PluginManagerExtensionTrait;

    public function __construct(
        $configOrContainerInstance = null,
        array $v3config = []
    ) {
        $this->addOverride('aliases', 'getSubscriptionBundleEntries', GetSubscriptionBundleEntries::class);
        $this->addOverride('aliases', 'getItemCollpaseAndExpand', GetItemCollapseAndExpand::class);
        $this->addOverride('factories', GetSubscriptionBundleEntries::class, GetSubscriptionBundleEntriesFactory::class);
        $this->addOverride('factories', GetItemCollapseAndExpand::class, GetItemCollapseAndExpandFactory::class);
        $this->applyOverrides();
        parent::__construct($configOrContainerInstance, $v3config);
    }
}
