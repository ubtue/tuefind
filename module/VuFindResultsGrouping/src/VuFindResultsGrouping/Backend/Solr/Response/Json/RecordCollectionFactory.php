<?php

/**
 * Simple JSON-based factory for record collection.
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

use VuFindSearch\Backend\Solr\Response\Json\Record;
use VuFindSearch\Exception\InvalidArgumentException;
use VuFindSearch\Response\RecordCollectionFactoryInterface;

class RecordCollectionFactory extends \VuFindSearch\Backend\Solr\Response\Json\RecordCollectionFactory implements RecordCollectionFactoryInterface
{
    /**
     * Constructor.
     *
     * @param Callable $recordFactory   Callback to construct records
     * @param string   $collectionClass Class of collection
     *
     * @return void
     */
    public function __construct(
        $recordFactory = null,
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

        $pluginManager = $this->recordFactory[0];
        $solrDef = $pluginManager->get('IxTheo\RecordDriver\SolrDefault');
        $container = $solrDef->getContainer();
        $config = $container->get(\VuFind\Config\PluginManager::class)->get('config');
        $index = $config->get('Index');
        $group_expand = $index->get('group.expand');


        $collection = new $this->collectionClass($response);
        $collectionHasGroups = $collection->hasExpanded();

        if (true === $collectionHasGroups) {
            if (isset($response['response']['docs'])) {
                foreach ($response['response']['docs'] as $doc) {

                    if (array_key_exists($doc[$group_expand], $response['expanded']) && true === is_array($response['expanded'][$doc[$group_expand]]['docs'])) {
                        $docFirst = $doc;
                        $topics = [];
                        $collectionSub = new $this->collectionClass($doc);

                        foreach ($response['expanded'][$doc[$group_expand]]['docs'] as $sub_doc) {
                            $sub_doc['_isSubRecord'] = true;
                            $collectionSub->add(call_user_func($this->recordFactory, $sub_doc));
                            if (array_key_exists('topic', $sub_doc) && true === is_array($sub_doc['topic'])) {
                                $topics = array_merge($topics, $sub_doc['topic']);
                            }
                        }
                        $docFirst['topic'] = array_unique($topics);
                        $docFirst['_subRecords'] = $collectionSub;

                        $collection->add(call_user_func($this->recordFactory, $docFirst));
                    } else {
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
        die();
        return $collection;
    }
}
