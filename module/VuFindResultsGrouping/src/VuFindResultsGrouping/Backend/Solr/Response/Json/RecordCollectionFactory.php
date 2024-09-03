<?php

/**
 * Simple JSON-based factory for record collection.
 *
 * @category Ida
 * @package  Search
 * @author   <dku@outermedia.de>
 */
namespace VuFindResultsGrouping\Backend\Solr\Response\Json;

use VuFindSearch\Backend\Solr\Response\Json\Record;
use VuFindSearch\Exception\InvalidArgumentException;
use VuFindSearch\Response\RecordCollectionFactoryInterface;

class RecordCollectionFactory implements RecordCollectionFactoryInterface
{
    /**
     * Constructor.
     *
     * @param Callable $recordFactory   Callback to construct records
     * @param string   $collectionClass Class of collection
     *
     * @return void
     */
    public function __construct($recordFactory = null,
        $collectionClass = 'VuFindResultsGrouping\Backend\Solr\Response\Json\RecordCollection'
    ) {
        if (null === $recordFactory) {
            $this->recordFactory = function ($data) {
                return new Record($data);
            };
        } else {
            $this->recordFactory = $recordFactory;
        }

        $this->collectionClass = $collectionClass;
    }

    /**
     * Return record collection.
     *
     * @param array $response Deserialized JSON response
     *
     * @return RecordCollection
     */
    public function factory($response)
    {
        if (!is_array($response)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unexpected type of value: Expected array, got %s',
                    gettype($response)
                )
            );
        }

        $collection = new $this->collectionClass($response);

        // todo: unterscheide "has groups" oä, wichtig fuer record-ansicht, zb http://localhost/meta/Record/36855fmt
        $collectionGroups = $collection->getGroups();
        $collectionHasGroups = 0 < count($collectionGroups);

        if (true === $collectionHasGroups) {
            $keys = array_keys($collectionGroups);
            $groupFieldId = reset($keys);

            foreach ($collectionGroups[$groupFieldId]['groups'] as $group) {
                if (!is_null($group['groupValue'])) {
                    $docs = $group['doclist']['docs'];

                    // Get first doc (as parent doc)
                    $docFirst = reset($docs);

                    // Do sub records exist?
                    if (1 < count($docs)) {

                        // We skip the masterrecord in group list
                        array_shift($docs);

                        // Create new collection for sub records
                        $collectionSub = new $this->collectionClass($docs);

                        // Merge topics of all sub records
                        $topics = [];

                        foreach ($docs as $doc) {
                            $doc['_isSubRecord'] = true;

                            // Add each grouped record to sub collection
                            $collectionSub->add(call_user_func($this->recordFactory, $doc));

                            // Merge topics, if available
                            if (array_key_exists('topic', $doc) && true === is_array($doc['topic'])) {
                                $topics = array_merge($topics, $doc['topic']);
                            }
                        }

                        // Remove topic duplicates
                        $docFirst['topic'] = array_unique($topics);

                        $docFirst['_subRecords'] = $collectionSub;
                    }

                    $collection->add(call_user_func($this->recordFactory, $docFirst));
                } else {
                    // if records exist with unset group value, those will be grouped in a group
                    // with groupValue=null - so handle these documents as single ungrouped docs
                    foreach ($group['doclist']['docs'] as $doc) {
                        $collection->add(call_user_func($this->recordFactory, $doc));
                    }
                }
            }
        } else {
            if (isset($response['response']['docs'])) {
                foreach ($response['response']['docs'] as $doc) {
                    $collection->add(call_user_func($this->recordFactory, $doc));
                }
            }
        }

        return $collection;
    }
}
