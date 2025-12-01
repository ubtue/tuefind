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
     * Constructor, used to parse IxTheo-specific options from searches.ini
     */
    public function __construct(ConfigManagerInterface $configManager)
    {
        parent::__construct($configManager);
        $searchSettings = $configManager->getConfigObject($this->searchIni);

        if (isset($searchSettings->IxTheo->forceDefaultSortSearches)) {
            $this->forceDefaultSortSearches = $searchSettings->IxTheo->forceDefaultSortSearches->toArray();
        }
    }

    /**
     * Get searches with forced own default sort only
     *
     * @return array
     */
    public function getForceDefaultSortSearches() {
        return $this->forceDefaultSortSearches;
    }
}
