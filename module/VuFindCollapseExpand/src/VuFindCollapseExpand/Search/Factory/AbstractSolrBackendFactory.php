<?php

/**
 * Abstract factory for SOLR backends.
 *
 * @category Ida
 * @package  Search
 * @author   <dku@outermedia.de>
 */

namespace VuFindCollapseExpand\Search\Factory;

use VuFindCollapseExpand\Backend\Solr\Backend;
use VuFindSearch\Backend\Solr\Connector;
use Psr\Container\ContainerInterface;
use TueFind\Search\Solr\InjectFulltextMatchIdsListener;
use Laminas\Config\Config;

abstract class AbstractSolrBackendFactory extends \VuFind\Search\Factory\AbstractSolrBackendFactory
{
    /**
     * Create the SOLR backend.
     *
     * @param Connector $connector Connector
     *
     * @return Backend
     */

    protected function createBackend(Connector $connector)
    {
        $backend = new $this->backendClass($connector);
        $pageSize = $this->getIndexConfig('record_batch_size', 100);
        $maxClauses = $this->getIndexConfig('maxBooleanClauses', $pageSize);
        if ($pageSize > 0 && $maxClauses > 0) {
            $backend->setPageSize(min($pageSize, $maxClauses));
        }
        $backend->setQueryBuilder($this->createQueryBuilder());
        $backend->setSimilarBuilder($this->createSimilarBuilder());
        if ($this->logger) {
            $backend->setLogger($this->logger);
        }
        $backend->setRecordCollectionFactory($this->createRecordCollectionFactory());
        return $backend;
    }

    // These three functions below for KrimDok Compatibility
    public function __invoke(ContainerInterface $sm, $name, array $options = null)
    {
        $this->serviceLocator = $sm;
        $this->config = $this->serviceLocator->get(\VuFind\Config\PluginManager::class);
        if ($this->serviceLocator->has(\VuFind\Log\Logger::class)) {
            $this->logger = $this->serviceLocator->get(\VuFind\Log\Logger::class);
        }
        $connector = $this->createConnector();
        $backend   = $this->createBackend($connector);
        $backend->setIdentifier($name);
        $this->createListeners($backend);
        return $backend;
    }


    protected function createListeners(\VuFindSearch\Backend\Solr\Backend $backend)
    {
        parent::createListeners($backend);
        $events = $this->serviceLocator->get('SharedEventManager');
        $search = $this->config->get($this->searchConfig);
        //        if (isset($search->FulltextMatchIds)) {
        $this->getInjectFulltextMatchIdsListener($backend, $search)->attach($events);
        //        }
    }


    protected function getInjectFulltextMatchIdsListener(
        \VuFindSearch\Backend\BackendInterface $backend,
        Config $search
    ) {
        return new InjectFulltextMatchIdsListener($backend);
    }
}