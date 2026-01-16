<?php

/**
 * Simple JSON-based record collection.
 *
 * Collapse and Expand.
 * Update the collection
 *
 * @author Steven Lolong <steven.lolong@uni-tuebingen.de>
 */

namespace VuFindCollapseExpand\Backend\Solr\Response\Json;

use function count;
use function is_array;

class RecordCollection extends \VuFindSearch\Backend\Solr\Response\Json\RecordCollection
{
    /**
     * Grouping field name if exists.
     *
     * @var string
     */
    protected $groupFieldName;

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

        if ($this->isGrouped()) {
            // Extract grouping field name
            $keys = $this->getGroups();
            $reset = array_keys($keys);
            $this->groupFieldName = reset($reset);

            $this->offset = 0;
        } else {
            $this->offset = $this->response['response']['start'];
        }

        $this->expanded = isset($this->response['expanded']) && is_array($response['expanded']);
        $this->rewind();
    }

    public function isGrouped(): bool
    {
        $groups = $this->getGroups();

        return 0 < count($groups);
    }

    public function hasExpanded(): bool
    {
        return $this->expanded;
    }

    public function getResponseDocs(): array
    {
        return $this->response['response']['docs'] ?? [];
    }

    public function getResponse(): array
    {
        return $this->response;
    }

    public function countExpandedDoc($expandFieldName): int
    {
        if (
            isset($this->response['expanded'][$expandFieldName])
                && is_array($this->response['expanded'][$expandFieldName]['docs'])
        ) {
            return count($this->response['expanded'][$expandFieldName]['docs']);
        }
        return 0;
    }
}