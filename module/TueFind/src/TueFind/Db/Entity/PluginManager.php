<?php

namespace TueFind\Db\Entity;

use Laminas\ServiceManager\Factory\InvokableFactory;

class PluginManager extends \VuFind\Db\Entity\PluginManager {
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

        $this->aliases[PublicationEntityInterface::class]           = Publication::class;
        $this->aliases[RedirectEntityInterface::class]              = Redirect::class;
        $this->aliases[RssFeedEntityInterface::class]               = RssFeed::class;
        $this->aliases[RssItemEntityInterface::class]               = RssItem::class;
        $this->aliases[RssSubscriptionEntityInterface::class]       = RssSubscription::class;
        $this->aliases[UserEntityInterface::class]                  = User::class;
        $this->aliases[UserAuthorityEntityInterface::class]         = UserAuthority::class;
        $this->aliases[UserAuthorityHistoryEntityInterface::class]  = UserAuthorityHistory::class;

        $this->factories[Publication::class]                        = InvokableFactory::class;
        $this->factories[Redirect::class]                           = InvokableFactory::class;
        $this->factories[RssFeed::class]                            = InvokableFactory::class;
        $this->factories[RssItem::class]                            = InvokableFactory::class;
        $this->factories[RssSubscription::class]                    = InvokableFactory::class;
        $this->factories[User::class]                               = InvokableFactory::class;
        $this->factories[UserAuthority::class]                      = InvokableFactory::class;
        $this->factories[UserAuthorityHistory::class]               = InvokableFactory::class;

        parent::__construct($configOrContainerInstance, $v3config);
    }
}
