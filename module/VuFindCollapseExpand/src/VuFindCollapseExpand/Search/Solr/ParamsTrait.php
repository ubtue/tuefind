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

namespace VuFindCollapseExpand\Search\Solr;

use VuFindSearch\ParamBag;

/**
 * This trait adds some accessor methods to VuFind params
 *
 * Trait ParamTrait
 *
 * @package VuFindCollapseExpand\Search\Solr
 *
 * @author Cornelius Amzar <cornelius.amzar@bsz-bw.de>
 * @author Robert Lange <lange@ub.uni-leipzig.de>
 *
 *
 * Controlling Result is changed from Result Grouping to Collapse and Expand
 * @author Steven Lolong <steven.lolong@uni-tuebingen.de>
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
            if ($orFacet = (substr($field, 0, 1) === '~')) {
                $field = substr($field, 1);
            }
            if ($filter === '') {
                continue;
            }
            foreach ($filter as $value) {
                // Special case -- complex filter, that should be taken as-is:
                if ($field == '#') {
                    $q = $value;
                } elseif (
                    substr($value, -1) === '*'
                    || preg_match('/\[[^\]]+\s+TO\s+[^\]]+\]/', $value)
                ) {
                    // Special case -- allow trailing wildcards and ranges
                    $q = $field . ':' . $value;
                } else {
                    $q = $field . ':"' . addcslashes($value, '"\\') . '"';
                }
                if ($orFacet) {
                    $orFilters[$field] ??= [];
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

    // Construct collapse parameters
    public function constructingCollapseParams()
    {
        $collapseConfig = $this->collapseExpandConfig->getCollapseConfig();
        $currentSettings = $this->collapseExpandConfig->getCurrentSettings();

        $param = '{!collapse ';
        foreach (array_keys($collapseConfig) as $key) {
            if (isset($currentSettings[$key]) && $currentSettings[$key] != null) {
                $param .= explode('.', $key)[1] . '=' . $currentSettings[$key] . ' ';
            }
        }
        $param .= '}';
        return $param;
    }

    // Construct expand parameters
    public function constructingExpandParams()
    {
        $expandConfig = $this->collapseExpandConfig->getExpandConfig();
        $currentSettings = $this->collapseExpandConfig->getCurrentSettings();
        $params = [];

        foreach (array_keys($expandConfig) as $key) {
            if (isset($currentSettings[$key]) && $currentSettings[$key] != null) {
                $params[$key] = $currentSettings[$key];
            }
        }
        return $params;
    }

    /**
     * Create search backend parameters for advanced features.
     *
     * @return ParamBag
     */
    public function getBackendParameters()
    {
        $backendParams = new ParamBag();
        // restore grouping settings from cookie
        $this->collapseExpandConfig->restoreFromCookie();

        // check if grouping is enabled in the configuration && by the user in the session (frontend)
        if ($this->collapseExpandConfig->isEnabled() && $this->collapseExpandConfig->isActive()) {
            $backendParams->add('expand', 'true');

            // construct collapse parameters
            $backendParams->add('fq', $this->constructingCollapseParams());

            // enabling expand
            // construct expand parameters
            foreach ($this->constructingExpandParams() as $key => $value) {
                $backendParams->add($key, $value);
            }
        }
        // search those shards that answer, accept partial results
        $backendParams->add('shards.tolerant', 'true');

        // maximum search time in ms
        // $backendParams->add('timeAllowed', '4000');

        // defaultOperator=AND was removed in schema.xml
        $backendParams->add('q.op', 'AND');

        // increase performance for facet queries
        $backendParams->add('facet.threads', '4');

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
                $fullKey = substr($key, 0, 2) === 'f.' ? $key : "facet.$key";
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
            if (
                $sort == 'relevance' && $this->getQuery()->getAllTerms() == ''
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

    public function isEnableCollapseExpand(): bool
    {
        return $this->collapseExpandConfig->isEnabled();
    }

    public function isActivatedCollapseExpand(): bool
    {
        return $this->collapseExpandConfig->isActive();
    }

    public function getExpandField(): string
    {
        return $this->collapseExpandConfig->getExpandField();
    }
}
