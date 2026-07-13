<?php

namespace TueFind\Search\Solr;

use function get_class;
use function in_array;
use function is_callable;

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
    public function getPartialFieldFacets(
        $facetfields,
        $removeFilter = true,
        $limit = -1,
        $facetSort = null,
        $page = null,
        $ored = false
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

    /**
     * TueFind: Similar to parent, with additional handling for "translatedFacetsUnassigned"
     * (facets like author/publisher, where only the "[Unassigned]" Entry should be translated)
     */
    protected function setDisplayTextForFacetValues(array &$result, array $hierarchicalFacets, object $options): void
    {
        $translatedFacets = $options->getTranslatedFacets();
        $translatedFacetsUnassigned = $translatedFacetsUnassigned = is_callable([$this->getOptions(), 'getTranslatedFacetsUnassigned'])
            ? $this->getOptions()->getTranslatedFacetsUnassigned()
            : [];

        foreach ($result as $field => $fieldResult) {
            $resultList = $fieldResult['list'];
            $hierarchical = in_array($field, $hierarchicalFacets);
            $translate = in_array($field, $translatedFacets);
            $translateUnassigned = in_array($field, $translatedFacetsUnassigned);
            foreach ($resultList as $index => $valueResult) {
                $value = $valueResult['value'];
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
                $valueResult['displayText'] = $displayText;
                $resultList[$index] = $valueResult;
            }
            $result[$field]['list'] = $resultList;
        }
    }
}
