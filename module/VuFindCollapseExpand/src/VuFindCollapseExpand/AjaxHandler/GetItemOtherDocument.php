<?php

/**
 * AJAX handler for fetching item collapse and expand
 *
 * PHP version 8
 *
 * Copyright (C) The Library of Tuebingen University 2025
 *
 * @category VuFindCollapseExpand
 * @package  AJAX
 * @author   Steven Lolong <steven.lolong@uni-tuebingen.de>
 *
 */

namespace VuFindCollapseExpand\AjaxHandler;

use Laminas\Mvc\Controller\Plugin\Params;
use VuFind\Record\Loader;
use VuFind\RecordTab\TabManager;
use VuFind\Session\Settings as SessionSettings;
use VuFind\View\Helper\Root\Record;

class GetItemOtherDocument extends \VuFind\AjaxHandler\AbstractBase
{
    /**
     * Record loader
     *
     * @var Loader
     */
    protected $recordLoader;

    /**
     * Record plugin
     *
     * @var Record
     */
    protected $recordPlugin;

    /**
     * Tab manager
     *
     * @var TabManager
     */
    protected $tabManager;

    /**
     * Constructor
     *
     * @param SessionSettings $ss     Session settings
     * @param Loader          $loader Record loader
     * @param Record          $rp     Record plugin
     * @param TabManager      $tm     Tab manager
     */
    public function __construct(
        SessionSettings $ss,
        Loader $loader,
        Record $rp,
        TabManager $tm
    ) {
        parent::__construct($ss);
        $this->recordLoader = $loader;
        $this->recordPlugin = $rp;
        $this->tabManager = $tm;
    }
    /**
     * Get item collapse and expand
     *
     * @return array
     */
    public function getItemOtherDocument($id, $source, $searchId)
    {
        $driver = $this->recordLoader->load($id, $source, $searchId);
        $tabs = $this->tabManager->getTabsForRecord($driver);
        $full = true;

        return ($this->recordPlugin)($driver)->renderTemplate(
            'versions-link.phtml',
            compact('driver', 'tabs', 'full', 'searchId')
        );
    }
    /**
     * Handle a request.
     *
     * @param Params $params Parameter helper from controller
     *
     * @return array
     */
    public function handleRequest($params)
    {
        $this->setSessionSettings($params);
        $this->setRequest($params->getRequest());
        $this->setResponse($params->getResponse());
        return $this->getItemOtherDocument();
    }
}