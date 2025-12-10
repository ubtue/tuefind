<?php

namespace TueFind\Controller;

use Psr\Container\ContainerInterface;

class AbstractBaseWithDbServiceFactory extends \VuFind\Controller\AbstractBaseFactory
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
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName,
        array $options = null
    ) {
        $instance = parent::__invoke($container, $requestedName, $options);

        // this should be similar to VuFind\ServiceManager\ServiceInitializer
        if ($instance instanceof \VuFind\Db\Service\DbServiceAwareInterface) {
            $instance->setDbServiceManager(
                $container->get(\TueFind\Db\Service\PluginManager::class)
            );
        }

        return $instance;
    }
}
