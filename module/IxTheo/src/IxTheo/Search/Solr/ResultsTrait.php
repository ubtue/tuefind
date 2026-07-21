<?php

namespace IxTheo\Search\Solr;

use function array_slice;
use function count;
use function in_array;
use function is_callable;

/**
 * This trait will be re-used in
 * - Solr\Results
 * - Search2\Results
 */

trait ResultsTrait
{
    /**
     * Filter configured facets after their display text has been translated.
     * This is useful for facets like ixtheo_notation_facet, relbib_notation_facet, dewey-hundreds, callnumber-first, where the facet values are not translated at index time.
     * Overrides the parent method to filter the facets after their display text has been translated.
     */
    public function getPartialFieldFacets(
        $facetfields,
        $removeFilter = true,
        $limit = -1,
        $facetSort = null,
        $page = null,
        $ored = false
    ) {
        $params = $this->getParams();
        $options = $params->getOptions();
        if (
            !is_callable([$options, 'getLocalizedFacetFilters'])
            || !is_callable([$params, 'getLocalizedFacetContains'])
        ) {
            return parent::getPartialFieldFacets(
                $facetfields,
                $removeFilter,
                $limit,
                $facetSort,
                $page,
                $ored
            );
        }
        $localizedFields = array_intersect(
            $facetfields,
            $options->getLocalizedFacetFilters()
        );
        $contains = $params->getLocalizedFacetContains();
        if (!$localizedFields || $contains === null || $contains === '') {
            return parent::getPartialFieldFacets(
                $facetfields,
                $removeFilter,
                $limit,
                $facetSort,
                $page,
                $ored
            );
        }

        $facets = parent::getPartialFieldFacets(
            $facetfields,
            $removeFilter,
            -1,
            $facetSort,
            null,
            $ored
        );
        foreach ($localizedFields as $field) {
            $items = $facets[$field]['data']['list'] ?? [];
            $items = array_values(array_filter(
                $items,
                fn ($item) => mb_stripos((string)$item['displayText'], $contains) !== false
            ));
            $offset = $page !== null && $limit !== -1 ? ($page - 1) * $limit : 0;
            $facets[$field]['more'] = $limit !== -1 && count($items) > $offset + $limit;
            $facets[$field]['data']['list'] = $limit === -1
                ? $items
                : array_slice($items, $offset, $limit);
        }
        return $facets;
    }

    /**
     * Returns the stored list of facets for the last search
     *
     * Contains special translation logic for ixtheo/relbib notation facets
     *
     * @param array $filter Array of field => on-screen description listing
     * all of the desired facet fields; set to null to get all configured values.
     *
     * @return array        Facets data arrays
     */
    public function getFacetList($filter = null)
    {
        $list = parent::getFacetList($filter);
        foreach ($list as $facetKey => $facet) {
            // Note: This is only for results, make sure to also check IxTheo\Search\Solr\Params for similar logic!
            if (in_array($facetKey, ['ixtheo_notation_facet', 'relbib_notation_facet'])) {
                $prefix = 'ixtheo-';
                foreach ($facet['list'] as $listKey => $listItem) {
                    $list[$facetKey]['list'][$listKey]['displayText'] = $this->translate($prefix . $listItem['displayText']);
                }
            }
            if (preg_match('"^dewey-"i', $facetKey)) {
                foreach ($facet['list'] as $listKey => $listItem) {
                    if (preg_match('"^\d{3}\b"', $listItem['value'], $hits)) {
                        $ddcNumber = $hits[0];
                        $list[$facetKey]['list'][$listKey]['displayText'] = $ddcNumber . ' - ' . $this->translate(['DDC23', $ddcNumber]);
                    }
                }
            }
        }
        return $list;
    }
}
