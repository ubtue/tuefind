<?php
/*
 * Copyright 2021 (C) Bibliotheksservice-Zentrum Baden-
 * Württemberg, Konstanz, Germany
 * Copyright (C) Leipzig University Library 2022.
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

namespace VufindCollapseAndExpand\Controller;

/**
 * This adds grouping handling to VuFinds search author controller
 *
 * Class SearchController
 * @package VufindCollapseAndExpand\Controller
 * @author  Robert Lange <lange@ub.uni-leipzig.de>
 */
class AuthorController extends \VuFind\Controller\AuthorController
{
    /**
     * Sets the configuration for displaying author results
     *
     * @return mixed
     */
    public function resultsAction()
    {
        $grouping = $this->serviceLocator->get('VufindCollapseAndExpand\Config\Grouping');

        $view = \VuFind\Controller\AuthorController::resultsAction();
        $view->grouping = $grouping->isActive();
        return $view;
    }
}