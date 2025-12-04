<?php

namespace IxTheo\Db\Entity;

use Laminas\ServiceManager\Factory\InvokableFactory;

class PluginManager extends \TueFind\Db\Entity\PluginManager {
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
        $this->aliases[PDASubscriptionEntityInterface::class]       = PDASubscription::class;
        $this->aliases[SubscriptionEntityInterface::class]          = Subscription::class;
        $this->aliases[UserEntityInterface::class]                  = User::class;
        $this->aliases[UserAuthorityEntityInterface::class]         = UserAuthority::class;
        $this->aliases[UserAuthorityHistoryEntityInterface::class]  = UserAuthorityHistory::class;

        $this->factories[PDASubscription::class]                    = InvokableFactory::class;
        $this->factories[Subscription::class]                       = InvokableFactory::class;
        $this->factories[User::class]                               = InvokableFactory::class;
        $this->factories[UserAuthority::class]                      = InvokableFactory::class;
        $this->factories[UserAuthorityHistory::class]               = InvokableFactory::class;
        parent::__construct($configOrContainerInstance, $v3config);
    }
}
