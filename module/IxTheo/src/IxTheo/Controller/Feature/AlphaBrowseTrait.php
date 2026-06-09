<?php

namespace IxTheo\Controller\Feature;

use VuFindSearch\Command\AlphabeticBrowseCommand;

use function func_get_args;

trait AlphaBrowseTrait
{
    protected function alphabeticBrowse()
    {
        $service = $this->getService(\VuFindSearch\Service::class);

        $args = func_get_args();

        // IxTheo: Use result filter for different instances
        if (isset($args[4])) {
            $configManager = $this->getService(\VuFind\Config\ConfigManagerInterface::class);
            $config = $configManager->get('config');
            $resultFilter = $config->AlphaBrowse_Filter->filter ?? null;
            if (isset($resultFilter)) {
                $args[4]->set('filterBy', $resultFilter);
            }
        }

        $command = new AlphabeticBrowseCommand(
            $this->alphabrowseBackend,
            ...$args
        );
        return $service->invoke($command)->getResult();
    }
}
