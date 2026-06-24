<?php

namespace TueFind\Search\Factory;

// Former inheritance from SolrDefaultBackendFactory, changed after PluginManager got removed
class Search3BackendFactory extends AbstractSolrBackendFactory
{
    public function __construct()
    {
        parent::__construct();
        $this->mainConfig = $this->searchConfig = $this->facetConfig = 'Search3';
        $this->searchYaml = 'searchspecs3.yaml';
    }
    
    // Additional SolrDefaultBackendFactory-related changes, no longer active
    protected $createRecordMethod = 'getSearch3Record';

    public function getSearch3Record($data, $defaultKeySuffix = 'Default')
    {
        return $this->getSolrRecord($data, 'Search3', $defaultKeySuffix);
    }
}
