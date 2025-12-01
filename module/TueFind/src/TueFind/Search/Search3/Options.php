<?php

namespace TueFind\Search\Search3;

use VuFind\Config\ConfigManagerInterface;

class Options extends \VuFind\Search\Solr\Options
{
    public function __construct(ConfigManagerInterface $configManager)
    {
        $this->mainIni = $this->searchIni = $this->facetsIni = 'Search3';
        parent::__construct($configManager);
    }


    public function getAdvancedSearchAction()
    {
        return false;
    }

    public function getVersionsAction()
    {
        return $this->displayRecordVersions ? 'search3-versions' : false;
    }

    public function getSearchAction()
    {
        return 'search3-results';
    }


    public function getFacetListAction()
    {
        return 'search3-facetlist';
    }
}
