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
}
