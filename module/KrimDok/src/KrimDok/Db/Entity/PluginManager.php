<?php

namespace KrimDok\Db\Entity;

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
        $this->aliases[UserEntityInterface::class]          = User::class;
        $this->factories[User::class]                       = InvokableFactory::class;
        parent::__construct($configOrContainerInstance, $v3config);
    }
}
