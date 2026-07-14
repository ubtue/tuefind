<?php

namespace IxTheo\Search\Solr;

use VuFind\Config\ConfigManagerInterface;

class Options extends \TueFind\Search\Solr\Options
{
    /**
     * Searches with forced own default sort only
     *
     * @var array
     */
    protected $forceDefaultSortSearches = [];

    /**
     * Facets whose modal filter matches translated display text
     *
     * @var array
     */
    protected $localizedFacetFilters = [];

    /**
     * Constructor, used to parse IxTheo-specific options
     */
    public function __construct(ConfigManagerInterface $configManager)
    {
        parent::__construct($configManager);
        $searchSettings = $configManager->getConfigObject($this->searchIni);
        // Get the facet settings from the facets.ini file, so that we can use them in the Solr\Params and Solr\ResultsTrait classes.
        $facetSettings = $configManager->getConfigObject($this->facetsIni);

        if (isset($searchSettings->IxTheo->forceDefaultSortSearches)) {
            $this->forceDefaultSortSearches = $searchSettings->IxTheo->forceDefaultSortSearches->toArray();
        }

        // Taking the localized facet filters from the facets.ini file, so that we can use them in the Solr\Params and Solr\ResultsTrait classes.
        if (isset($facetSettings->Advanced_Settings->localized_facet_filters)) {
            $this->localizedFacetFilters
                = $facetSettings->Advanced_Settings->localized_facet_filters->toArray();
        }
    }

    /**
     * Get searches with forced own default sort only
     *
     * @return array
     */
    public function getForceDefaultSortSearches()
    {
        return $this->forceDefaultSortSearches;
    }

    /**
     * Get facets whose modal filter matches translated display text.
     *
     * @return array
     */
    public function getLocalizedFacetFilters(): array
    {
        return $this->localizedFacetFilters;
    }
}
