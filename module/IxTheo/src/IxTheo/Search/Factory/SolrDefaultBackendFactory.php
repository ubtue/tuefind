<?php


namespace IxTheo\Search\Factory;

use IxTheo\Search\Backend\Solr\Backend;
use IxTheo\Search\Backend\Solr\LuceneSyntaxHelper;
use IxTheo\Search\Backend\Solr\QueryBuilder;
use VuFind\I18n\Translator\TranslatorAwareInterface;
use VuFindSearch\Backend\Solr\Connector;
use VuFindSearch\Backend\Solr\HandlerMap;
use TueFindSearch\Backend\Solr\Response\Json\RecordCollectionFactory;

class SolrDefaultBackendFactory extends \TueFind\Search\Factory\SolrDefaultBackendFactory implements TranslatorAwareInterface
{
    
    use \VuFind\I18n\Translator\TranslatorAwareTrait;

}
