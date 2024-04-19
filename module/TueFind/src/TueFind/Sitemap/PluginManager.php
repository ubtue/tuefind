<?php

namespace TueFind\Sitemap;

class PluginManager extends \VuFind\Sitemap\PluginManager
{
    public function __construct(
        $configOrContainerInstance = null,
        array $v3config = []
    ) {
        $this->aliases['Index'] = Plugin\Index::class;
        $this->factories[Plugin\Index::class] = Plugin\IndexFactory::class;
        parent::__construct($configOrContainerInstance, $v3config);
    }
}
