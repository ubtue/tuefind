<?php
/**
 * Factory for TueFind ResultScroller controller plugin.
 *
 * @category TueFind
 * @package  Controller_Plugins
 * @author   Steven Lolong <steven.lolong@uni-tuebingen.de>
 */

namespace TueFind\Controller\Plugin;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface as ContainerException;
use Psr\Container\ContainerInterface;

class ResultScrollerFactory implements FactoryInterface
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
            throw new \Exception('Unexpected options sent to factory.');
        }
        return new $requestedName(
            new \Laminas\Session\Container(
                'ResultScroller',
                $container->get(\Laminas\Session\SessionManager::class)
            ),
            $container->get(\VuFind\Search\Results\PluginManager::class)
        );
    }
}