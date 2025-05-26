<?php

/**
 * Simple JSON-based record collection.
 *
 * @category Ida
 * @package  Search
 * @author   <dku@outermedia.de>
 *
 * Controlling Result is changed from Result Grouping to Collapse and Expand.
 * Update the collection
 * @author Steven Lolong <steven.lolong@uni-tuebingen.de>
 */

namespace VuFindResultsGrouping\Backend\Solr\Response\Json;

use VuFindResultsGrouping\Backend\Solr\Response\Json\RecordCollectionFactory;

class RecordCollection extends \VuFindSearch\Backend\Solr\Response\Json\RecordCollection
{
    /**
     * Grouping field name if exists.
     *
     * @var string
     */
    protected $groupFieldName;

    /**
     * @var boolean
     */
    protected $expanded;
    /**
     * Constructor.
     *
     * @param array $response Deserialized SOLR response
     *
     * @return void
     */
    public function __construct(array $response)
    {

        $this->response = array_replace_recursive(static::$template, $response);

        if (true === $this->isGrouped()) {

            // Extract grouping field name
            $keys = $this->getGroups();
            $reset = array_keys($keys);
            $this->groupFieldName = reset($reset);

            $this->offset = 0;
        } else {
            $this->offset = $this->response['response']['start'];
        }

        $this->expanded = isset($this->response['expanded']) && true === is_array($response['expanded']) ? true : false;
        $this->rewind();
    }

    public function isGrouped()
    {
        $groups = $this->getGroups();

        return 0 < count($groups);
    }

    /**
     *
     * @return boolean
     */
    public function hasExpanded()
    {
        return $this->expanded;
    }

    public function getResponseDocs()
    {
        return $this->response['response']['docs'] ?? [];
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function isNotEmptyExpanded($expandFieldName)
    {
        if (isset($this->response['response']['docs']) && is_array($this->response['response']['docs'])) {
            foreach ($this->response['response']['docs'] as $doc) {
                if (isset($doc[$expandFieldName]) && !empty($doc[$expandFieldName])) {
                    return true;
                }
            }
        }
        return false;
    }
}
