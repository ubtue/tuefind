<?php

namespace IxTheo\Navigation;

class PluginManager extends \TueFind\Navigation\PluginManager
{
    public function __construct($configOrContainerInstance = null,
        array $v3config = []
    ) {
        $this->addOverride('aliases', \TueFind\Navigation\AccountMenu::class, AccountMenu::class);
        $this->addOverride('factories', AccountMenu::class, \VuFind\Navigation\AccountMenuFactory::class);
        $this->applyOverrides();

        parent::__construct($configOrContainerInstance, $v3config);
    }
}
