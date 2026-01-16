<?php

/*
 * Copyright 2025 (C) Universitaet Tuebingen, Germany
 *
 */

namespace VuFindCollapseExpand\Config;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Session\Container;
use Psr\Container\ContainerInterface;

/**
 * Factory for CollapseExpand
 * @package VuFindCollapseExpand\Config
 * @author Steven Lolong <steven.lolong@uni-tuebingen.de>
 */

class CollapseExpandFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ) {
        $config = $container->get(\VuFind\Config::class)->get('config')->get('CollapseExpand');
        $sesscontainer = new Container(
            'collapseExpandConfig',
            $container->get(\VuFind\SessionManager::class)
        );
        $response = $container->get('Response');
        $cookie = $container->get('Request')->getCookie();
        return new CollapseExpand($config, $sesscontainer, $response, $cookie);
    }
}
