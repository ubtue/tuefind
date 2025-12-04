<?php

namespace KrimDok\Db\Service;

class PluginManager extends \TueFind\Db\Service\PluginManager {

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
        $this->addOverride('aliases', UserServiceInterface::class, UserService::class);
        $this->addOverride('factories', UserService::class, \VuFind\Db\Service\UserServiceFactory::class);
        $this->applyOverrides();

        parent::__construct($configOrContainerInstance, $v3config);
    }
}
