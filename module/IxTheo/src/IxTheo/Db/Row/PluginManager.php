<?php

namespace IxTheo\Db\Row;

use VuFind\Db\Row\RowGatewayFactory;

class PluginManager extends \TueFind\Db\Row\PluginManager {
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
        $this->aliases['pdasubscription']                   = PDASubscription::class;
        $this->aliases['subscription']                      = Subscription::class;
        $this->aliases['user']                              = User::class;
        $this->aliases['user_authority']                    = UserAuthority::class;
        $this->aliases['user_authority_history']            = UserAuthorityHistory::class;

        $this->factories[PDASubscription::class]            = RowGatewayFactory::class;
        $this->factories[Subscription::class]               = RowGatewayFactory::class;
        $this->factories[User::class]                       = \VuFind\Db\Row\UserFactory::class;
        $this->factories[UserAuthority::class]              = RowGatewayFactory::class;
        $this->factories[UserAuthorityHistory::class]       = RowGatewayFactory::class;
        parent::__construct($configOrContainerInstance, $v3config);
    }
}
