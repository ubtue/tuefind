<?php

namespace IxTheo\Search;

use Psr\Container\ContainerInterface;

class BackendManagerFactory extends \VuFind\Search\BackendManagerFactory {

    // same as parent, but we return IxTheo\Search\BackendRegistry instead
    protected function getRegistry(ContainerInterface $container)
    {
        $config = $container->get('config');
        return new BackendRegistry(
            $container, $config['vufind']['plugin_managers']['search_backend']
        );
    }
}
