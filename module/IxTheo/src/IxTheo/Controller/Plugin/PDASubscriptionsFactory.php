<?php
namespace IxTheo\Controller\Plugin;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PDASubscriptionsFactory implements FactoryInterface {

   public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {
       return new PDASubscriptions($container->get(\VuFind\Db\Service\PluginManager::class),
                                   $container->get(\TueFind\Mailer\Mailer::class),
                                   $container->get(\VuFind\RecordLoader::class),
                                   $container->get(\VuFind\Config::class),
                                   $container->get('ViewRenderer'));

   }

}
