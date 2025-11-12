<?php

namespace TueFindSearch\Backend\Solr;

use VuFindSearch\ParamBag;
use VuFindSearch\Query\AbstractQuery;

class Backend extends \VufindCollapseAndExpand\Backend\Solr\Backend implements \TueFindSearch\Feature\GetSitemapFieldsInterface
{
    public function __construct(\VuFindSearch\Backend\Solr\Connector $connector)
    {
        parent::__construct($connector);
    }

    /**
     * For sitemap: Similar to getIds, but we also fetch additional fields needed for sitemap generation
     */
    public function getSitemapFields(
        AbstractQuery $query,
        $offset,
        $limit,
        ParamBag $params = null
    ) {
        $params = $params ?: new ParamBag();
        $this->injectResponseWriter($params);

        $params->set('rows', $limit);
        $params->set('start', $offset);
        $params->set('fl', [$this->getConnector()->getUniqueKey(), 'last_indexed']);
        $params->mergeWith($this->getQueryBuilder()->build($query));
        $response   = $this->connector->search($params);
        $collection = $this->createRecordCollection($response);
        $this->injectSourceIdentifier($collection);

        return $collection;
    }
}
