<?php
namespace TueFindSearch\Backend\Solr\Response\Json;

class RecordCollectionFactory extends \VuFindCollapseExpand\Backend\Solr\Response\Json\RecordCollectionFactory {
    public function __construct(
        $recordFactory = null,
        $serviceLocator = null,
        $collectionClass = \TueFindSearch\Backend\Solr\Response\Json\RecordCollection::class
    ) {
        parent::__construct($recordFactory, $serviceLocator, $collectionClass);
    }
}
