<?php

namespace TueFind\Form\Handler;

class PluginManager extends \VuFind\Form\Handler\PluginManager {
    use \TueFind\PluginManagerExtensionTrait;

    public function __construct(
        $configOrContainerInstance = null,
        array $v3config = []
    ) {
        $this->addOverride('aliases', 'email', Email::class);
        $this->addOverride('factories', Email::class, \VuFind\Form\Handler\EmailFactory::class);
        $this->applyOverrides();
        parent::__construct($configOrContainerInstance, $v3config);
    }
}
