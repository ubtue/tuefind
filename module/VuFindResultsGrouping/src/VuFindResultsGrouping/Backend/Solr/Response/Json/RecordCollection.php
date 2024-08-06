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

            $this->offset = 0; // TODO: No "start" info provided
        } else {
            $this->offset = $this->response['response']['start'];
        }

        $this->rewind();
    }

    public function isGrouped()
    {
        $groups = $this->getGroups();

        return 0 < count($groups);
    }

    /**
     * Return total number of records found.
     *
     * @return int
     */
    public function getTotal()
    {
        /*return true === $this->isGrouped()
            ? $this->response['stats']['stats_fields'][$this->groupFieldName]['cardinality']
            : $this->response['response']['numFound'];
         */

        if ($this->isGrouped()) {
            // Careful: This will output the whole number of records, not the number of groups
            //return $this->response['grouped'][$this->groupFieldName]['matches'];
            $total = 0;
            foreach ($this->response['grouped'][$this->groupFieldName]['groups'] as $group) {
                $total += $group['doclist']['numFound'];
            }
            return $total;
        } else {
            return $this->response['response']['numFound'];
        }
    }
}
