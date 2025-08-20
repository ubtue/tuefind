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

namespace VuFindResultsGrouping\Search\Solr;

use VuFind\Config\PluginManager;
use VuFindResultsGrouping\Config\Grouping;

/**
 * Description of Params
 *
 * @author Cornelius Amzar <cornelius.amzar@bsz-bw.de>
 * @author Robert Lange <lange@ub.uni-leipzig.de>
 */
class AuthorParams extends \VuFind\Search\SolrAuthor\Params
{
    use ParamsTrait;

    protected $grouping;
    protected $limit = 10;

    /**
     * Params constructor.
     *
     * @param $options
     * @param PluginManager $configLoader
     * @param HierarchicalFacetHelper|null $facetHelper
     * @param Grouping|null $grouping
     */
    public function __construct(
        $options,
        PluginManager $configLoader,
        HierarchicalFacetHelper $facetHelper = null,
        Grouping $grouping = null
    ) {
        parent::__construct($options, $configLoader);
        $this->grouping = $grouping;
    }
}
