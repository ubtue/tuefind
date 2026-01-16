<?php

/**
 * Factory for the default SOLR backend.
 *
 * @category Ida
 * @package  Search
 * @author   <dku@outermedia.de>
 */

namespace VuFindCollapseExpand\Search\Factory;

use VuFindCollapseExpand\Backend\Solr\Response\Json\RecordCollectionFactory;
use VuFindSearch\Backend\Solr\Backend;
use VuFindSearch\Backend\Solr\Connector;

class SolrDefaultBackendFactory extends AbstractSolrBackendFactory
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->searchConfig = 'searches';
        $this->searchYaml = 'searchspecs.yaml';
        $this->facetConfig = 'facets';
        $this->defaultIndexName = 'biblio';
        $this->allowFallbackForIndexName = true;
    }

    /**
     * Get the Solr core.
     *
     * @return string
     */
    protected function getSolrCore()
    {
        $config = $this->config->get('config');

        return $config->Index->default_core ?? 'biblio';
    }

    /**
     * Create the SOLR backend.
     *
     * @param Connector $connector Connector
     *
     * @return Backend
     */
    protected function createBackend(Connector $connector)
    {
        $backend = parent::createBackend($connector);
        $manager = $this->serviceLocator->get(\VuFind\RecordDriverPluginManager::class);

        $factory = new RecordCollectionFactory([$manager, 'getSolrRecord'], $this->serviceLocator);
        $backend->setRecordCollectionFactory($factory);
        return $backend;
    }
}
