<?php

namespace IxTheo\Db\Entity;

use Laminas\ServiceManager\Factory\InvokableFactory;

class PluginManager extends \TueFind\Db\Entity\PluginManager
{
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
        $this->addOverride('aliases', \TueFind\Db\Entity\UserEntityInterface::class, UserEntityInterface::class);
        $this->addOverride('aliases', \TueFind\Db\Entity\User::class, User::class);

        $this->addOverride('aliases', PDASubscriptionEntityInterface::class, PDASubscription::class);
        $this->addOverride('aliases', SubscriptionEntityInterface::class, Subscription::class);
        $this->addOverride('aliases', UserEntityInterface::class, User::class);

        $this->addOverride('factories', PDASubscription::class, InvokableFactory::class);
        $this->addOverride('factories', Subscription::class, InvokableFactory::class);
        $this->addOverride('factories', User::class, InvokableFactory::class);

        $this->applyOverrides();

        parent::__construct($configOrContainerInstance, $v3config);
    }
}
