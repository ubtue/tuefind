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
     * Create the Search3 backend using the standard factory lifecycle,
     * then select the Search3-specific record drivers.
     */
    protected function createBackend(Connector $connector)
    {
        $backend = parent::createBackend($connector);

        $manager = $this->serviceLocator->get(
            PluginManager::class
        );

        $recordFactory = new RecordCollectionFactory(
            static fn ($data) => $manager->getSolrRecord(
                $data,
                'Search3'
            ),
            $this->serviceLocator
        );

        $backend->setRecordCollectionFactory($recordFactory);

        return $backend;
    }
}
