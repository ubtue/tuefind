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

namespace VufindCollapseAndExpand\AjaxHandler;

use VufindCollapseAndExpand\Config\Grouping;
use VuFind\I18n\Translator\TranslatorAwareInterface;
use Laminas\Mvc\Controller\Plugin\Params;

/**
 * Class GroupingCheckbox
 * @package  VufindCollapseAndExpand\AjaxHandler
 * @author   Cornelius Amzar <cornelius.amzar@bsz-bw.de>
 */
class GroupingCheckbox extends \VuFind\AjaxHandler\AbstractBase implements TranslatorAwareInterface
{
    use \VuFind\I18n\Translator\TranslatorAwareTrait;

    /**
     *
     * @var Grouping
     */
    protected $grouping;

    /**
     * Constructor
     *
     * @param Grouping $grouping
     */
    public function __construct(Grouping $grouping)
    {
        $this->grouping = $grouping;
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
        $this->grouping->store(['group' => $status]);
        return $this->formatResponse([], 200);
    }
}