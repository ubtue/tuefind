<?php

/**
 * Factory for GetItemCollapseAndExpand AJAX handler
 *
 * PHP version 8
 *
 * Copyright (C) The Library of Tuebingen University 2025
 *
 * @category TueFind
 * @package  AJAX
 * @author   Steven Lolong <steven.lolong@uni-tuebingen.de>
 *
  */

namespace TueFind\AjaxHandler;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface as ContainerException;
use Psr\Container\ContainerInterface;

class GetItemCollapseAndExpandFactory implements \Laminas\ServiceManager\Factory\FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container     Service manager
     * @param string             $requestedName Service being created
     * @param null|array         $options       Extra options (optional)
     *
     * @return object
     *
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     * creating a service.
     * @throws ContainerException&\Throwable if any other error occurs
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ) {
        if (!empty($options)) {
            throw new \Exception('Unexpected options passed to factory.');
        }
        return new $requestedName(
            $container->get(\VuFind\Session\Settings::class),
            $container->get(\VuFind\Record\Loader::class),
            $container->get('ViewRenderer')->plugin('record'),
            $container->get(\VuFind\RecordTab\TabManager::class)
        );
    }
}
