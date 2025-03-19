<?php

/**
 * Simple JSON-based record collection.
 *
 * @category Ida
 * @package  Search
 * @author   <dku@outermedia.de>
 */

namespace VuFindResultsGrouping\Backend\Solr\Response\Json;

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
        // echo $this->isGrouped() ? 'true' : 'false';
        // echo '<pre>';
        // print_r($response);
        // echo '</pre>';
        // die();
        // Fetch group params for grouping

        $this->response = array_replace_recursive(static::$template, $response);

        if (true === $this->isGrouped()) {

            // Extract grouping field name
            $keys = $this->getGroups();
            $reset = array_keys($keys);
            $this->groupFieldName = reset($reset);

            $this->offset = 0; // TODO: No "start" info provided
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

    /**
     * Get extended results.
     *
     * @return array
     */
    public function getExpanded()
    {
        return $this->response['expanded'] ?? [];
    }

}
