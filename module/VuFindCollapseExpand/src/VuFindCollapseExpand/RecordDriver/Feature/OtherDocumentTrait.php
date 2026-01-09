<?php

/**
 * Logic for collapse and expand functionality.
 *
 * PHP version 8
 *
 * Copyright (C) The Library of Tuebingen University 2025
 *
 * @category TueFind
 * @package  RecordDrivers
 * @author   Steven Lolong <steven.lolong@uni-tuebingen.de>
 *
  */

namespace VuFindCollapseExpand\RecordDriver\Feature;

use TueFindSearch\ParamBag;
use VuFindSearch\Command\SearchCommand;
use VuFindSearch\Query\Query;

trait OtherDocumentTrait 
{
    /**
     * Collapse and expand variable from config
     */
    protected $expand_row = 0;
    protected $expandField = '';



    /**
     * Cached result of other Document count
     *
     * @var int
     */
    protected $otherDocumentCount = null;


    /**
     * Cached result of other Document
     *
     * @var \VuFindSearch\Response\RecordCollectionInterface
     */
    public $otherDocuments;

    /**
     * Return count of other Document available
     * show on the record tab next to the title
     *
     * @return int
     */
    public function getOtherDocumentCount()
    {
        return $this->otherDocumentCount;
    }

    /**
     * Return other Document
     *
     * @return \VuFindSearch\Response\RecordCollectionInterface
     */
    public function getOtherDocument($keyword)
    {
        if (null === $this->searchService) {
            return false;
        }

        if (!isset($this->otherDocument)) {
            $container = $this->getContainer();

            $plugin_manager_solr = $container->get('VuFind\SearchResultsPluginManager')->get('Solr');
            $default_params = $plugin_manager_solr->getParams();

            if($default_params->isActivatedCollapseExpand()){
                // $searchCommand = new SearchCommand($this->backendId,  $query, 0, 0, $params);
                $params = new ParamBag();
                
                $params->add('expand', 'true');

                $params->add('fq', $default_params->constructingCollapseParams());

                foreach($default_params->constructingExpandParams() as $key => $value){
                    $params->add($key, $value);
                }
                $expand_config = $default_params->collapse_expand_grouping->getExpandConfig();
                $this->expand_row = $expand_config['expand.rows'];
                $this->expandField = $expand_config['expand.field'];
                
                $params->add('fl', '*');
                
                // search those shards that answer, accept partial results
                $params->add('shards.tolerant', 'true');

                // defaultOperator=AND was removed in schema.xml
                $params->add('q.op', "AND");

                // increase performance for facet queries
                $params->add('facet.threads', "4");

                // Spellcheck
                $params->set(
                    'spellcheck',
                    $default_params->getOptions()->spellcheckEnabled() ? 'true' : 'false'
                );

                // Facets
                $facets = $default_params->getFacetSettings();
                if (!empty($facets)) {
                    $params->add('facet', 'true');

                    foreach ($facets as $key => $value) {
                        // prefix keys with "facet" unless they already have a "f." prefix:
                        $fullKey = substr($key, 0, 2) == 'f.' ? $key : "facet.$key";
                        $params->add($fullKey, $value);
                    }
                    $params->add('facet.mincount', 1);
                }

                // Filters
                $filters = $default_params->getFilterSettings();
                foreach ($filters as $filter) {
                    $params->add('fq', $filter);
                }

                // Shards
                $allShards = $default_params->getOptions()->getShards();
                $shards = $default_params->getSelectedShards();
                if (empty($shards)) {
                    $shards = array_keys($allShards);
                }

                // If we have selected shards, we need to format them:
                if (!empty($shards)) {
                    $selectedShards = [];
                    foreach ($shards as $current) {
                        $selectedShards[$current] = $allShards[$current];
                    }
                    $shards = $selectedShards;
                    $params->add('shards', implode(',', $selectedShards));
                }

                // Sort
                $sort = $default_params->getSort();
                if ($sort) {
                    // If we have an empty search with relevance sort, see if there is
                    // an override configured:
                    if ($sort == 'relevance' && $default_params->getQuery()->getAllTerms() == ''
                        && ($relOv = $default_params->getOptions()->getEmptySearchRelevanceOverride())
                    ) {
                        $sort = $relOv;
                    }
                    $params->add('sort', $default_params->normalizeSort($sort));
                }

                // Highlighting disabled
                $params->add('hl', 'false');

                // Pivot facets for visual results

                if ($pf = $default_params->getPivotFacets()) {
                    $params->add('facet.pivot', $pf);
                }


                $query_string = "$this->expandField:" . '"'  . $keyword . '"';

                $query = new Query(
                    $query_string
                );

                $command = new SearchCommand(
                    $this->getSourceIdentifier(),
                    $query,
                    0,
                    $this->expand_row,
                    $params
                );
                $this->otherDocuments =  $this->searchService->invoke($command)->getResult();
            }

        }
        return $this->otherDocuments;
    }

    public function isActiveCnEParams()
    {
        $container = $this->getContainer();

        $plugin_manager_solr = $container->get('VuFind\SearchResultsPluginManager')->get('Solr');
        $params = $plugin_manager_solr->getParams();
        
        // params->isActivatedCollapseExpand() is a function in ParamsTrait.php
        return $params->isActivatedCollapseExpand();
    }

    public function getExpandField()
    {
        $container = $this->getContainer();

        $plugin_manager_solr = $container->get('VuFind\SearchResultsPluginManager')->get('Solr');
        $params = $plugin_manager_solr->getParams();

        // params->getExpandField() is a function in ParamsTrait.php
        return $params->getExpandField();
    }
}