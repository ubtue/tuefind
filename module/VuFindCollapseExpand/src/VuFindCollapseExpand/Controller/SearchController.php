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

namespace VuFindCollapseExpand\Controller;

use function in_array;

/**
 * This adds grouping handling to VuFinds search controller
 *
 * Class SearchController
 *
 * @package VuFindCollapseExpand\Controller
 * @author  Cornelius Amzar <cornelius.amzar@bsz-bw.de>
 *
 * Controlling Result is changed from Result Grouping to Collapse and Expand
 * @author  Steven Lolong <steven.lolong@uni-tuebingen.de>
 */
class SearchController extends \VuFind\Controller\SearchController
{
    public function resultsAction()
    {
        $collapseExpandConfig = $this->serviceLocator->get(\VuFindCollapseExpand\Config\CollapseExpand::class);

        $view = parent::resultsAction();
        $view->collapseExpandConfig = $collapseExpandConfig->isActive();
        return $view;
    }

    /**
     * Taken from AbstractSolrSearch class
     * Process the facets to be used as limits on the Advanced Search screen.
     *
     * @param array  $facetList          The advanced facet values
     * @param object $searchObject       Saved search object (false if none)
     * @param array  $hierarchicalFacets Hierarchical facet list (if any)
     * @param array  $hierarchicalFacetsSortOptions Hierarchical facet sort options
     * (if any)
     *
     * @return array               Sorted facets, with selected values flagged.
     */
    protected function processAdvancedFacets(
        $facetList,
        $searchObject = false,
        $hierarchicalFacets = [],
        $hierarchicalFacetSortOptions = []
    ) {
        // Process the facets
        $facetHelper = null;
        if (!empty($hierarchicalFacets)) {
            $facetHelper = $this->serviceLocator
                ->get(\VuFind\Search\Solr\HierarchicalFacetHelper::class);
        }
        foreach ($facetList as $facet => &$list) {
            // Hierarchical facets: format display texts and sort facets
            // to a flat array according to the hierarchy
            if (in_array($facet, $hierarchicalFacets)) {
                $tmpList = $list['list'];
                $facetHelper->sortFacetList($tmpList, true);
                $tmpList = $facetHelper->buildFacetArray(
                    $facet,
                    $tmpList
                );
                $list['list'] = $facetHelper->flattenFacetHierarchy($tmpList);
            }

            foreach ($list['list'] as $key => $value) {
                // Build the filter string for the URL:
                $fullFilter = ($value['operator'] == 'OR' ? '~' : '')
                    . $facet . ':"' . $value['value'] . '"';

                // If we haven't already found a selected facet and the current
                // facet has been applied to the search, we should store it as
                // the selected facet for the current control.
                if ($searchObject && $searchObject->getParams()->hasFilter($fullFilter)) {
                    $list['list'][$key]['selected'] = true;
                    // Remove the filter from the search object -- we don't want
                    // it to show up in the "applied filters" sidebar since it
                    // will already be accounted for by being selected in the
                    // filter select list!
                    $searchObject->getParams()->removeFilter($fullFilter);
                }
            }
        }
        return $facetList;
    }
}