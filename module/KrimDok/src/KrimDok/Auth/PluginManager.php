<?php

namespace KrimDok\Auth;

class PluginManager extends \TueFind\Auth\PluginManager {
    public function __construct($configOrContainerInstance = null,
        array $v3config = []
    ) {
        $this->addOverride('aliases', 'database', Database::class);
        $this->addOverride('aliases', 'db', Database::class);
        $this->addOverride('factories', Database::class, \Laminas\ServiceManager\Factory\InvokableFactory::class);
        $this->applyOverrides();
        parent::__construct($configOrContainerInstance, $v3config);
    }
}
