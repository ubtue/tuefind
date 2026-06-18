<?php

namespace IxTheo\AjaxHandler;

class PluginManager extends \TueFind\AjaxHandler\PluginManager
{
    public function __construct(
        $configOrContainerInstance = null,
        array $v3config = []
    ) {
        $this->addOverride('aliases', 'deleteSubscription', DeleteSubscription::class);
        $this->addOverride('factories', DeleteSubscription::class, DeleteSubscriptionFactory::class);

        $this->addOverride('aliases', 'deletePDASubscription', DeletePDASubscription::class);
        $this->addOverride('factories', DeletePDASubscription::class, DeletePDASubscriptionFactory::class);

        $this->applyOverrides();
        parent::__construct($configOrContainerInstance, $v3config);
    }
}
