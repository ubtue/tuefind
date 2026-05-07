<?php

namespace VuFindCollapseExpand\RecordTab;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerInterface;

/**
 * Factory for building the CollapseExpand tab.
 *
 * @package VuFindCollapseExpand\RecordTab
 * @author  Steven Lolong <steven.lolong@uni-tuebingen.de>
 */
class CollapseExpandFactory implements \Laminas\ServiceManager\Factory\FactoryInterface
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
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
            $container->get('ViewHelperManager'),
        );
    }
}
