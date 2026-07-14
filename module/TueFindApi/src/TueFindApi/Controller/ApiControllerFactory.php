<?php

namespace TueFindApi\Controller;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface as ContainerException;
use Psr\Container\ContainerInterface;

class ApiControllerFactory implements FactoryInterface
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
        ?array $options = null
    ) {
        if (!empty($options)) {
            throw new \Exception('Unexpected options passed to factory.');
        }
        $controller = new $requestedName($container);
        $controllerManager = $container->get('ControllerManager');
        foreach ($this->getApiControllersToRegister($container) as $apiName) {
            $controller->addApi($controllerManager->get($apiName));
        }
        return $controller;
    }

    /**
     * Get the API controllers to register with ApiController
     *
     * @param ContainerInterface $container Service manager
     *
     * @return array
     */
    protected function getApiControllersToRegister(ContainerInterface $container)
    {
        $config = $container->get('Config');
        return $config['vufind_api']['register_controllers'];
    }
}
