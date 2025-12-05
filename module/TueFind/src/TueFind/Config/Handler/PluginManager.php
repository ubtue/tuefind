<?php

namespace TueFind\Config\Handler;

use VuFind\Config\Handler\DefaultHandlerFactory;

class PluginManager extends \VuFind\Config\Handler\PluginManager
{
    public function __construct(
        $configOrContainerInstance = null,
        array $v3config = []
    ) {
        $this->aliases['ini'] = Ini::class;
        $this->factories[Ini::class] = DefaultHandlerFactory::class;
        parent::__construct($configOrContainerInstance, $v3config);
    }
}
