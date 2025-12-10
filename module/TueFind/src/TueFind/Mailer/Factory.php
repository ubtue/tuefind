<?php

namespace TueFind\Mailer;

use Psr\Container\ContainerInterface;

class Factory extends \VuFind\Mailer\Factory {

    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ) {
        $class = parent::__invoke($container, $requestedName, $options);

        // Load additional configurations:
        $config = $container->get(\VuFind\Config\ConfigManagerInterface::class)->getConfigArray('config');

        // Fallback to old config structure if new one is not set (local_overrides)
        $fromOverride = $config['Mail']['override_from'] ?? null;
        if ($fromOverride == null) {
            $class->setFromAddressOverride($config['Site']['email_from'] ?? null);
        }

        // Additional settings
        $class->setSiteAddress($config['Site']['email']);
        $class->setSiteTitle($config['Site']['title']);

        return $class;
    }
}
