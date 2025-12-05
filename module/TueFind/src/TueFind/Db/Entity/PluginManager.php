<?php

namespace TueFind\Db\Entity;

use Laminas\ServiceManager\Factory\InvokableFactory;

class PluginManager extends \VuFind\Db\Entity\PluginManager
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
        $this->addOverride('aliases', \VuFind\Db\Entity\UserEntityInterface::class, UserEntityInterface::class);

        $this->addOverride('aliases', PublicationEntityInterface::class, Publication::class);
        $this->addOverride('aliases', RedirectEntityInterface::class, Redirect::class);
        $this->addOverride('aliases', RssFeedEntityInterface::class, RssFeed::class);
        $this->addOverride('aliases', RssItemEntityInterface::class, RssItem::class);
        $this->addOverride('aliases', RssSubscriptionEntityInterface::class, RssSubscription::class);
        $this->addOverride('aliases', UserEntityInterface::class, User::class);
        $this->addOverride('aliases', UserAuthorityEntityInterface::class, UserAuthority::class);
        $this->addOverride('aliases', UserAuthorityHistoryEntityInterface::class, UserAuthorityHistory::class);

        $this->addOverride('factories', Publication::class, InvokableFactory::class);
        $this->addOverride('factories', Redirect::class, InvokableFactory::class);
        $this->addOverride('factories', RssFeed::class, InvokableFactory::class);
        $this->addOverride('factories', RssItem::class, InvokableFactory::class);
        $this->addOverride('factories', RssSubscription::class, InvokableFactory::class);
        $this->addOverride('factories', User::class, InvokableFactory::class);
        $this->addOverride('factories', UserAuthority::class, InvokableFactory::class);
        $this->addOverride('factories', UserAuthorityHistory::class, InvokableFactory::class);

        $this->applyOverrides();

        parent::__construct($configOrContainerInstance, $v3config);
    }
}
