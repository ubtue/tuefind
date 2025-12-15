<?php

namespace IxTheo\Db\Service;

use VuFind\Db\Service\AbstractDbServiceFactory;

class PluginManager extends \TueFind\Db\Service\PluginManager
{

    use \TueFind\PluginManagerExtensionTrait;

    /**
     * Constructor
     *
     * Make sure plugins are properly initialized.
     *
     * @param mixed $configOrContainerInstance Configuration or container instance
     * @param array $v3config                  If $configOrContainerInstance is a
     * container, this value will be passed to the parent constructor.
     */
    public function __construct($configOrContainerInstance = null,
        array $v3config = []
    ) {
        $this->addOverride('aliases', \VuFind\Db\Service\UserServiceInterface::class, UserServiceInterface::class);
        $this->addOverride('aliases', \TueFind\Db\Service\UserServiceInterface::class, UserServiceInterface::class);

        $this->addOverride('aliases', PDASubscriptionServiceInterface::class, PDASubscriptionService::class);
        $this->addOverride('aliases', PublicationServiceInterface::class, PublicationService::class);
        $this->addOverride('aliases', SubscriptionServiceInterface::class, SubscriptionService::class);
        $this->addOverride('aliases', UserServiceInterface::class, UserService::class);
        $this->addOverride('aliases', UserAuthorityServiceInterface::class, UserAuthorityService::class);
        $this->addOverride('aliases', UserAuthorityHistoryServiceInterface::class, UserAuthorityHistoryService::class);

        $this->addOverride('factories', PDASubscriptionService::class, AbstractDbServiceFactory::class);
        $this->addOverride('factories', PublicationService::class, AbstractDbServiceFactory::class);
        $this->addOverride('factories', SubscriptionService::class, AbstractDbServiceFactory::class);
        $this->addOverride('factories', UserService::class, \VuFind\Db\Service\UserServiceFactory::class);
        $this->addOverride('factories', UserAuthorityService::class, AbstractDbServiceFactory::class);
        $this->addOverride('factories', UserAuthorityHistoryService::class, AbstractDbServiceFactory::class);

        $this->applyOverrides();

        parent::__construct($configOrContainerInstance, $v3config);
    }
}
