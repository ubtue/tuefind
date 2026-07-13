<?php

namespace TueFind\Search\Solr;

use VuFind\Config\ConfigManagerInterface;

class Options extends \VuFind\Search\Solr\Options {
    // TueFind-specific: see facets.ini for more detailed description
    protected $translatedFacetsUnassigned = [];

    public function __construct(ConfigManagerInterface $configManager) {
        parent::__construct($configManager);

        $facetSettings = $configManager->getConfigObject($this->facetsIni);

        if (
            isset($facetSettings->Advanced_Settings->translated_facets_unassigned)
            && count($facetSettings->Advanced_Settings->translated_facets_unassigned) > 0
        ) {
            $this->translatedFacetsUnassigned = $facetSettings->Advanced_Settings->translated_facets_unassigned->toArray();
        }
    }

    public function getTranslatedFacetsUnassigned(): array {
        return $this->translatedFacetsUnassigned;
    }
}
