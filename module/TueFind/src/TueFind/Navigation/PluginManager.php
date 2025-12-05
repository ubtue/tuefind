<?php

namespace TueFind\Navigation;

class PluginManager extends \VuFind\Navigation\PluginManager
{
    use \TueFind\PluginManagerExtensionTrait;

    public function __construct($configOrContainerInstance = null,
        array $v3config = []
    ) {
        $this->addOverride('aliases', \VuFind\Navigation\AccountMenu::class, AccountMenu::class);
        $this->addOverride('factories', AccountMenu::class, \VuFind\Navigation\AccountMenuFactory::class);
        $this->applyOverrides();

        parent::__construct($configOrContainerInstance, $v3config);
    }
}
