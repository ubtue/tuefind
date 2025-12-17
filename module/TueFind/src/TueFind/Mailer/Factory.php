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

    protected function getDSN(array $config): string
    {
        $dsn = parent::getDSN($config);

        // Allow self-signed certificates for localhost setups
        if (preg_match('"^smtp://(localhost|127\.0\.0\.)"', $dsn)) {
            $additionalParams = [
                'allow_self_signed' => 'true',
                'verify_peer' => 'false',
                'verify_peer_name' => 'false',
            ];

            if (str_contains($dsn, '?'))
                $dsn .= '&';
            else
                $dsn .= '?';

            $dsn .= http_build_query($additionalParams);
        }
        return $dsn;
    }
}
