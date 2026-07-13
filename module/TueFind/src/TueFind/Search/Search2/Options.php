<?php

namespace TueFind\Search\Search2;

use VuFind\Config\ConfigManagerInterface;

class Options extends \VuFind\Search\Search2\Options
{
    public function __construct(ConfigManagerInterface $configManager)
    {
        parent::__construct($configManager);
    }


    public function getAdvancedSearchAction()
    {
        return false;
    }
}
