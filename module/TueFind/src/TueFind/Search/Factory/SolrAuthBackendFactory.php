<?php


namespace TueFind\Search\Factory;

use TueFindSearch\Backend\Solr\QueryBuilder;
use VuFindSearch\Backend\Solr\LuceneSyntaxHelper;
use VuFind\I18n\Translator\TranslatorAwareInterface;
use VuFindSearch\Backend\Solr\Connector;
use VuFindSearch\Backend\Solr\HandlerMap;


class SolrAuthBackendFactory extends \VuFind\Search\Factory\SolrAuthBackendFactory implements TranslatorAwareInterface
{
    use \VuFind\I18n\Translator\TranslatorAwareTrait;
    
}
