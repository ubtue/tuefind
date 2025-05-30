<?php

/**
 * Logic fo r collapse and expand functionality.
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

namespace TueFind\RecordDriver\Feature;

use TueFindSearch\ParamBag;
use VuFindSearch\Command\SearchCommand;
use VuFindSearch\Query\Query;
use VuFindSearch\Backend\Solr\Response\Json\Record;
use VuFindResultsGrouping\Backend\Solr\Response\Json\RecordCollectionFactory;

trait CollapseAndExpandTrait
{
    /**
     * Collapse and expand variable from config
     */
    protected $groupLimit = 0;
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

        // $cookie = new Cookies();
        // $coo = $cookie->get('grouping');
        // if ($cookie->get('grouping') !== null) {
        //     echo empty((string) $cookie->get('grouping'));
        // } else {
        //     echo "disable";
        // }

        if (!isset($this->otherDocument)) {
            $container = $this->getContainer();

            $config = $container->get('VuFind\Config\PluginManager')->get('config');
            $configIndex = $config->get("Index");
            // $cookie = $container->get('Request')->getCookie();
            $group = $configIndex->get('group');


            if ((bool)$group === true) {
                $default_field = array('title_sort');
                $group_field =  ($configIndex->get('group.field') !== null) ? explode(":", $configIndex->get('group.field')) : $default_field;
                $this->groupLimit = ($configIndex->get('group.limit') !== null) ? $configIndex->get('group.limit') : 10;
                $this->expandField = ($configIndex->get('expand.field') !== null) ? $configIndex->get('expand.field') : $default_field[0];

                // $searchCommand = new SearchCommand($this->backendId,  $query, 0, 0, $params);
                $params = new ParamBag();
                $params->add('expand', 'true');
                $params->add('expand.rows', $this->groupLimit);
                $params->add('expand.field', $this->expandField);
                $params->add('fl', '*');

                for ($i = 0; $i < count($group_field); $i++) {
                    $params->add('fq', '{!collapse field=' . $group_field[$i] . '}');
                }

                $query_string = 'title_sort:"' . $keyword . '"';
                $query = new Query(
                    $query_string
                );

                $command = new SearchCommand(
                    $this->getSourceIdentifier(),
                    $query,
                    0,
                    $this->groupLimit,
                    $params
                );
                $this->otherDocuments =  $this->searchService->invoke($command)->getResult();
            }

        }
        return $this->otherDocuments;
    }


}
