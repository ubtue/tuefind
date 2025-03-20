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

use VuFindSearch\ParamBag;

/**
 * This trait adds some accessor methods to VuFind params
 *
 * Trait ParamTrait
 * @package VuFindResultsGrouping\Search\Solr
 * @author Cornelius Amzar <cornelius.amzar@bsz-bw.de>
 * @author Robert Lange <lange@ub.uni-leipzig.de>
 *
 *
 * Controlling Result is changed from Result Grouping to Collapse and Expand
 * @author Steven Lolong <steven.lolong@uni-tuebingen.de>
 *
 */
trait ParamsTrait
{
    /**
     * Return the current filters as an array of strings ['field:filter']
     *
     * @return array $filterQuery
     */
    public function getFilterSettings()
    {
        // Define Filter Query
        $filterQuery = [];
        $orFilters = [];
        $filterList = array_merge(
            $this->getHiddenFilters(),
            $this->filterList
        );
        foreach ($filterList as $field => $filter) {
            if ($orFacet = (substr($field, 0, 1) == '~')) {
                $field = substr($field, 1);
            }
            if ($filter === '') {
                continue;
            }
            foreach ($filter as $value) {
                // Special case -- complex filter, that should be taken as-is:
                if ($field == '#') {
                    $q = $value;
                } elseif (substr($value, -1) == '*'
                    || preg_match('/\[[^\]]+\s+TO\s+[^\]]+\]/', $value)
                ) {
                    // Special case -- allow trailing wildcards and ranges
                    $q = $field . ':' . $value;
                } else {
                    $q = $field . ':"' . addcslashes($value, '"\\') . '"';
                }
                if ($orFacet) {
                    $orFilters[$field] = $orFilters[$field] ?? [];
                    $orFilters[$field][] = $q;
                } else {
                    $filterQuery[] = $q;
                }
            }
        }
        foreach ($orFilters as $field => $parts) {
            $filterQuery[] = '{!tag=' . $field . '_filter}' . $field
                . ':(' . implode(' OR ', $parts) . ')';
        }
        return $filterQuery;
    }

    /**
     * Create search backend parameters for advanced features.
     *
     * @return ParamBag
     */
    public function getBackendParameters()
    {
        $backendParams = new ParamBag();
        $backendParams->add('year', (int)date('Y') + 1);

        $this->restoreFromCookie();

        // Fetch group params for grouping
        $config = $this->configLoader->get('config');
        $index = $config->get('Index');
        $group = false;

        $groupingParams = $this->grouping->getCurrentSettings();

        if (isset($groupingParams['group'])) {
            $group = $groupingParams['group'];
        } elseif ($index->get('group') !== null) {
            $group = $index->get('group');
        }

        if ((bool)$group === true) {
            $backendParams->add('expand', 'true');

            $group_field = '';
            $group_limit = 0;
            $group_expand = '';

            if (isset($groupingParams['group_field'])) {
                $group_field = $groupingParams['group_field'];
            } elseif ($index->get('group.field') !== null) {
                $group_field = $index->get('group.field');
            }
            // $backendParams->add('group.field', $group_field);

            if (isset($groupingParams['group_limit'])) {
                $group_limit = $groupingParams['group_limit'];
            } elseif ($index->get('group.limit') !== null) {
                $group_limit = $index->get('group.limit');
            }
            if (isset($groupingParams['group_expand'])) {
                $group_expand = $groupingParams['group_expand'];
            } elseif ($index->get('group.expand') !== null) {
                $group_limit = $index->get('group.expand');
            }

            // collapse and expand
            for ($i = 0; $i < count($group_field); $i++) {
                $backendParams->add('fq', '{!collapse field=' . $group_field[$i] . '}');
            }

            $backendParams->add('expand.rows', $group_limit);
            $backendParams->add('expand.field', $group_expand);
        }
        // search those shards that answer, accept partial results
        $backendParams->add('shards.tolerant', 'true');

        // maximum search time in ms
        // $backendParams->add('timeAllowed', '4000');

        // defaultOperator=AND was removed in schema.xml
        $backendParams->add('q.op', "AND");

        // increase performance for facet queries
        $backendParams->add('facet.threads', "4");

        // Spellcheck
        $backendParams->set(
            'spellcheck',
            $this->getOptions()->spellcheckEnabled() ? 'true' : 'false'
        );

        // Facets
        $facets = $this->getFacetSettings();
        if (!empty($facets)) {
            $backendParams->add('facet', 'true');

            foreach ($facets as $key => $value) {
                // prefix keys with "facet" unless they already have a "f." prefix:
                $fullKey = substr($key, 0, 2) == 'f.' ? $key : "facet.$key";
                $backendParams->add($fullKey, $value);
            }
            $backendParams->add('facet.mincount', 1);
        }

        // Filters
        $filters = $this->getFilterSettings();
        foreach ($filters as $filter) {
            $backendParams->add('fq', $filter);
        }

        // Shards
        $allShards = $this->getOptions()->getShards();
        $shards = $this->getSelectedShards();
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
            $backendParams->add('shards', implode(',', $selectedShards));
        }

        // Sort
        $sort = $this->getSort();
        if ($sort) {
            // If we have an empty search with relevance sort, see if there is
            // an override configured:
            if ($sort == 'relevance' && $this->getQuery()->getAllTerms() == ''
                && ($relOv = $this->getOptions()->getEmptySearchRelevanceOverride())
            ) {
                $sort = $relOv;
            }
            $backendParams->add('sort', $this->normalizeSort($sort));
        }

        // Highlighting disabled
        $backendParams->add('hl', 'false');

        // Pivot facets for visual results

        if ($pf = $this->getPivotFacets()) {
            $backendParams->add('facet.pivot', $pf);
        }

        return $backendParams;
    }

    /**
     * This method reads the cookie and stores the information into the session
     * So we only need to process session bwlow.
     *
     */
    protected function restoreFromCookie()
    {
        if (isset($this->cookie)) {
            if (isset($this->cookie->group)) {
                $this->container->offsetSet('group', $this->cookie->group);
            }
            if (isset($this->cookie->group_field)) {
                $this->container->offsetSet('group_field', $this->cookie->group_field);
            }
            if (isset($this->cookie->group_limit)) {
                $this->container->offsetSet('group_limit', $this->cookie->group_limit);
            }
            if (isset($this->cookie->group_expand)) {
                $this->container->offsetSet('group_expand', $this->cookie->group_expand);
            }
        }
    }
}
