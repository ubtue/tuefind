<?php

namespace TueFind\Search\Solr;

class Options extends \VuFind\Search\Solr\Options {
    // TueFind-specific: see facets.ini for more detailed description
    protected $translatedFacetsUnassigned = [];

    public function __construct(\VuFind\Config\PluginManager $configLoader) {
        parent::__construct($configLoader);

        $facetSettings = $configLoader->get($this->facetsIni);

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
