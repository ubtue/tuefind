<?php

namespace TueFind\Config\Handler;

use VuFind\Config\Location\ConfigLocationInterface;

class Ini extends \VuFind\Config\Handler\Ini {

    /**
     * TueFind: Override this function in 11.0 as workaround to lost "@include" behaviour
     *          due to laminas-config being replaced by native parse_ini_file.
     *          This will hopefully be replaced by a better solution in the future.
     *          (see vufind-tech mailing list).
     */
    public function parseConfig(ConfigLocationInterface $configLocation, bool $handleParentConfig = true): array
    {
        $config = parent::parseConfig($configLocation, $handleParentConfig);
        if (isset($config['data'])) {
            foreach ($config['data'] as $sectionName => $sectionSettings) {
                if (isset($config['data'][$sectionName]['@include'])) {
                    $fullPath = getenv('VUFIND_LOCAL_DIR') . '/config/vufind/' . $config['data'][$sectionName]['@include'];
                    $additionalSettings = parse_ini_file($fullPath);
                    foreach ($additionalSettings as $additionalKey => $additionalValue) {
                        $config['data'][$sectionName][$additionalKey] = $additionalValue;
                    }
                    unset($config['data'][$sectionName]['@include']);
                }
            }
        }

        return $config;
    }
}
