<?php

namespace TueFind\Db\Service;

use VuFind\Db\Service\AbstractDbServiceFactory;

class PluginManager extends \VuFind\Db\Service\PluginManager
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

        $this->addOverride('aliases', PublicationServiceInterface::class, PublicationService::class);
        $this->addOverride('aliases', RedirectServiceInterface::class, RedirectService::class);
        $this->addOverride('aliases', RssFeedServiceInterface::class, RssFeedService::class);
        $this->addOverride('aliases', RssItemServiceInterface::class, RssItemService::class);
        $this->addOverride('aliases', RssSubscriptionServiceInterface::class, RssSubscriptionService::class);
        $this->addOverride('aliases', UserServiceInterface::class, UserService::class);
        $this->addOverride('aliases', UserAuthorityServiceInterface::class, UserAuthorityService::class);
        $this->addOverride('aliases', UserAuthorityHistoryServiceInterface::class, UserAuthorityHistoryService::class);
        $this->addOverride('aliases', CmsPagesServiceInterface::class, CmsPagesService::class);
        $this->addOverride('aliases', CmsPagesTranslationServiceInterface::class, CmsPagesTranslationService::class);
        $this->addOverride('aliases', CmsPagesHistoryServiceInterface::class, CmsPagesHistoryService::class);
        $this->addOverride('aliases', SubsystemsServiceInterface::class, SubsystemsService::class);

        $this->addOverride('factories', PublicationService::class, AbstractDbServiceFactory::class);
        $this->addOverride('factories', RedirectService::class, AbstractDbServiceFactory::class);
        $this->addOverride('factories', RssFeedService::class, RssFactory::class);
        $this->addOverride('factories', RssItemService::class, RssFactory::class);
        $this->addOverride('factories', RssSubscriptionService::class, RssFactory::class);
        $this->addOverride('factories', UserService::class, \VuFind\Db\Service\UserServiceFactory::class);
        $this->addOverride('factories', UserAuthorityService::class, AbstractDbServiceFactory::class);
        $this->addOverride('factories', UserAuthorityHistoryService::class, AbstractDbServiceFactory::class);
        $this->addOverride('factories', CmsPagesService::class, AbstractDbServiceFactory::class);
        $this->addOverride('factories', CmsPagesTranslationService::class, AbstractDbServiceFactory::class);
        $this->addOverride('factories', CmsPagesHistoryService::class, AbstractDbServiceFactory::class);
        $this->addOverride('factories', SubsystemsService::class, AbstractDbServiceFactory::class);

        $this->applyOverrides();

        parent::__construct($configOrContainerInstance, $v3config);
    }
}
