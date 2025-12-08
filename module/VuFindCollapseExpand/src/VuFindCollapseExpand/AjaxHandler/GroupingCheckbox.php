<?php
/*
 * Copyright 2021 (C) Bibliotheksservice-Zentrum Baden-
 * Württemberg, Konstanz, Germany
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 */

namespace VuFindCollapseExpand\AjaxHandler;

// use VuFindCollapseExpand\Config\Grouping;
use VuFindCollapseExpand\Config\CollapseExpandGrouping;
use VuFind\I18n\Translator\TranslatorAwareInterface;
use Laminas\Mvc\Controller\Plugin\Params;

/**
 * Class GroupingCheckbox
 * @package  VuFindCollapseExpand\AjaxHandler
 * @author   Cornelius Amzar <cornelius.amzar@bsz-bw.de>
 * 
 * Controlling Result is changed from Result Grouping to Collapse and Expand
 * @author Steven Lolong <steven.lolong@uni-tuebingen.de>
 */
class GroupingCheckbox extends \VuFind\AjaxHandler\AbstractBase implements TranslatorAwareInterface
{
    use \VuFind\I18n\Translator\TranslatorAwareTrait;

    /**
     *
     * @var CollapseExpandGrouping
     */
    protected $collapse_expand_grouping;

    /**
     * Constructor
     *
     * @param CollapseExpandGrouping $collapse_expand_grouping
     */
    public function __construct(CollapseExpandGrouping $collapse_expand_grouping)
    {
        $this->collapse_expand_grouping = $collapse_expand_grouping;
    }

    /**
     * Handle a request.
     *
     * @param Params $params Parameter helper from controller
     *
     * @return array [response data, HTTP status code]
     */
    public function handleRequest(Params $params)
    {
        $status = $params->fromPost('status');
        $status = $status == 'true' ? true : false;
        $this->collapse_expand_grouping->store(['collapse.enabled' => $status]);
        return $this->formatResponse([], 200);
    }
}