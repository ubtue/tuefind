<?php

namespace TueFind\Search\Solr;

class Results extends \VuFind\Search\Solr\Results
{
    /**
     * Get complete facet counts for several index fields
     *
     * Overwritten to sort translated_facets
     *
     * @param array  $facetfields  name of the Solr fields to return facets for
     * @param bool   $removeFilter Clear existing filters from selected fields (true)
     * or retain them (false)?
     * @param int    $limit        A limit for the number of facets returned, this
     * may be useful for very large amounts of facets that can break the JSON parse
     * method because of PHP out of memory exceptions (default = -1, no limit).
     * @param string $facetSort    A facet sort value to use (null to retain current)
     * @param int    $page         1 based. Offsets results by limit.
     * @param bool   $ored         Whether or not facet is an OR facet or not
     *
     * @return array list facet values for each index field with label and more bool
     */
    public function getPartialFieldFacets($facetfields, $removeFilter = true,
        $limit = -1, $facetSort = null, $page = null, $ored = false
    ) {
        $facets = parent::getPartialFieldFacets($facetfields, $removeFilter, $limit, $facetSort, $page, $ored);

        if ($facetSort == 'index') {
            foreach ($facets as $facet => $facetDetails) {
                $items = $facetDetails['data']['list'];
                array_multisort(array_column($items, 'displayText'), SORT_ASC, SORT_NATURAL, $items);
                $facets[$facet]['data']['list'] = $items;
            }
        }

        // Send back data:
        return $facets;
    }

    // TueFind: Similar to parent, with additional handling for "translatedFacetsUnassigned"
    protected function buildFacetList(array $facetList, array $filter = null): array
    {
        // If there is no filter, we'll use all facets as the filter:
        if (null === $filter) {
            $filter = $this->getParams()->getFacetConfig();
        }

        // Start building the facet list:
        $result = [];

        // Loop through every field returned by the result set
        $translatedFacets = $this->getOptions()->getTranslatedFacets();
        $translatedFacetsUnassigned = $this->getOptions()->getTranslatedFacetsUnassigned();
        $hierarchicalFacets
            = is_callable([$this->getOptions(), 'getHierarchicalFacets'])
            ? $this->getOptions()->getHierarchicalFacets()
            : [];
        $hierarchicalFacetSortSettings
            = is_callable([$this->getOptions(), 'getHierarchicalFacetSortSettings'])
            ? $this->getOptions()->getHierarchicalFacetSortSettings()
            : [];

        foreach (array_keys($filter) as $field) {
            $data = $facetList[$field] ?? [];
            // Skip empty arrays:
            if (count($data) < 1) {
                continue;
            }
            // Initialize the settings for the current field
            $result[$field] = [
                'label' => $filter[$field],
                'list' => [],
            ];
            // Should we translate values for the current facet?
            $translate = in_array($field, $translatedFacets);
            $translateUnassigned = in_array($field, $translatedFacetsUnassigned);
            $hierarchical = in_array($field, $hierarchicalFacets);
            $operator = $this->getParams()->getFacetOperator($field);
            $resultList = [];
            // Loop through values:
            foreach ($data as $value => $count) {
                $displayText = $this->getParams()
                    ->getFacetValueRawDisplayText($field, $value);
                if ($hierarchical) {
                    if (!$this->hierarchicalFacetHelper) {
                        throw new \Exception(
                            get_class($this)
                            . ': hierarchical facet helper unavailable'
                        );
                    }
                    $displayText = $this->hierarchicalFacetHelper
                        ->formatDisplayText($displayText);
                }
                $displayText = ($translate || ($translateUnassigned && $displayText == '[Unassigned]'))
                    ? $this->getParams()->translateFacetValue($field, $displayText)
                    : $displayText;
                $isApplied = $this->getParams()->hasFilter("$field:" . $value)
                    || $this->getParams()->hasFilter("~$field:" . $value);

                // Store the collected values:
                $resultList[] = compact(
                    'value',
                    'displayText',
                    'count',
                    'operator',
                    'isApplied'
                );
            }

            if ($hierarchical) {
                $sort = $hierarchicalFacetSortSettings[$field]
                    ?? $hierarchicalFacetSortSettings['*'] ?? 'count';
                $this->hierarchicalFacetHelper->sortFacetList($resultList, $sort);

                $resultList
                    = $this->hierarchicalFacetHelper->buildFacetArray($field, $resultList);
            }

            $result[$field]['list'] = $resultList;
        }
        return $result;
    }
}
