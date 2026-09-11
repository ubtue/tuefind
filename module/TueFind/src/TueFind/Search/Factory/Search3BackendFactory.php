<?php

namespace TueFind\Search\Factory;

use VuFind\RecordDriver\PluginManager;
use VuFindCollapseExpand\Backend\Solr\Response\Json\RecordCollectionFactory;
use VuFindSearch\Backend\Solr\Connector;

class Search3BackendFactory extends AbstractSolrBackendFactory
{
    public function __construct()
    {
        parent::__construct();
        $this->mainConfig = $this->searchConfig = $this->facetConfig = 'Search3';
        $this->searchYaml = 'searchspecs3.yaml';
    }

    /**
     * Create Search3 backend.
     *
     * Search3 needs its own record-driver prefix. The parent
     * SolrDefaultBackendFactory installs a generic getSolrRecord callback,
     * which uses the "Solr" prefix. Replace that callback here so Search3
     * results are instantiated as Search3* record drivers.
     */
    protected function createBackend(Connector $connector)
    {
        $backend = parent::createBackend($connector);

        $manager = $this->serviceLocator->get(PluginManager::class);

        $recordFactory = new RecordCollectionFactory(
            static fn ($data) => $manager->getSolrRecord($data, 'Search3'),
            $this->serviceLocator
        );

        $backend->setRecordCollectionFactory($recordFactory);

        return $backend;
    }
}
