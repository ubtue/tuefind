<?php

namespace KrimDok\Db\Row;

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
        $this->aliases['user']                              = User::class;
        $this->factories[User::class]                       = \VuFind\Db\Row\UserFactory::class;
        parent::__construct($configOrContainerInstance, $v3config);
    }
}
