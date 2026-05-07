<?php

/**
 * Simple JSON-based factory for record collection.
 * Collapse and Expand.
 * Update the collection
 *
 * @author Steven Lolong <steven.lolong@uni-tuebingen.de>
 */

namespace VuFindCollapseExpand\Backend\Solr\Response\Json;

use VuFindSearch\Backend\Solr\Response\Json\Record;
use VuFindSearch\Exception\InvalidArgumentException;
use VuFindSearch\Response\RecordCollectionFactoryInterface;

use function array_key_exists;
use function call_user_func;
use function gettype;
use function is_array;
use function sprintf;

class RecordCollectionFactory extends \VuFindSearch\Backend\Solr\Response\Json\RecordCollectionFactory implements
    RecordCollectionFactoryInterface
{
    protected $expandFieldName;

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
        $serviceLocator = null,
        $collectionClass = \VuFindCollapseExpand\Backend\Solr\Response\Json\RecordCollection::class
    ) {
        if (null === $recordFactory) {
            $this->recordFactory = function ($data) {
                return new Record($data);
            };
        } else {
            $this->recordFactory = $recordFactory;
        }

        $this->collectionClass = $collectionClass;

        $config = $serviceLocator->get(\VuFindCollapseExpand\Config\CollapseExpand::class);
        $serDef = $config->getCurrentSettings();
        $this->expandFieldName = $serDef['expand.field'];
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
        $collectionHasGroups = $collection->hasExpanded();
        $hlDetails = $response['highlighting'] ?? [];

        if (true === $collectionHasGroups) {
            if (isset($response['response']['docs'])) {
                foreach ($response['response']['docs'] as $doc) {
                    if (
                        array_key_exists($doc[$this->expandFieldName], $response['expanded'])
                        && is_array($response['expanded'][$doc[$this->expandFieldName]]['docs'])
                    ) {
                        $docFirst = $doc;
                        $topics = [];
                        $collectionSub = new $this->collectionClass($doc);

                        foreach ($response['expanded'][$doc[$this->expandFieldName]]['docs'] as $sub_doc) {
                            $sub_doc['_isSubRecord'] = true;

                            // If highlighting details were provided, merge them into the record for future use:
                            if (isset($sub_doc['id']) && ($hl = $hlDetails[$sub_doc['id']] ?? [])) {
                                $sub_doc['__highlight_details'] = $hl;
                            }

                            $collectionSub->add(call_user_func($this->recordFactory, $sub_doc));
                            if (array_key_exists('topic', $sub_doc) && is_array($sub_doc['topic'])) {
                                $topics = array_merge($topics, $sub_doc['topic']);
                            }
                        }
                        $docFirst['topic'] = array_unique($topics);
                        $docFirst['_subRecords'] = $collectionSub;

                        // If highlighting details were provided, merge them into the record for future use:
                        if (isset($docFirst['id']) && ($hl = $hlDetails[$docFirst['id']] ?? [])) {
                            $docFirst['__highlight_details'] = $hl;
                        }
                        $collection->add(call_user_func($this->recordFactory, $docFirst));
                    } else {
                        // If highlighting details were provided, merge them into the record for future use:
                        if (isset($doc['id']) && ($hl = $hlDetails[$doc['id']] ?? [])) {
                            $doc['__highlight_details'] = $hl;
                        }
                        $collection->add(call_user_func($this->recordFactory, $doc));
                    }
                }
            }
        } else {
            if (isset($response['response']['docs'])) {
                foreach ($response['response']['docs'] as $doc) {
                    // If highlighting details were provided, merge them into the record for future use:
                    if (isset($doc['id']) && ($hl = $hlDetails[$doc['id']] ?? [])) {
                        $doc['__highlight_details'] = $hl;
                    }
                    $collection->add(call_user_func($this->recordFactory, $doc));
                }
            }
        }

        return $collection;
    }
}
