<?php

namespace VuFindCollapseExpand\Config;

trait CollapseExpandConfigAwareTrait
{
    protected CollapseExpand $collapseExpandConfig;

    public function setCollapseExpandConfig(CollapseExpand $config)
    {
        $this->collapseExpandConfig = $config;
    }
}
