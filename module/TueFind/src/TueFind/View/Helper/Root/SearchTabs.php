<?php

/**
 * "Search tabs" view helper
 * 
 * PHP version 7
 * 
 * Copyright (C) Universität Tübingen 2023
 * 
 * Author Steven Lolong (steven.lolong@uni-tuebingen.de)
 */

namespace TueFind\View\Helper\Root;

use VuFind\Search\UrlQueryHelper;

class SearchTabs extends \VuFind\View\Helper\Root\SearchTabs {

    protected function remapBasicSearch($activeOptions, $targetClass, $query,
        $handler, $filters
    ) {
        // Set up results object for URL building:
        $results = $this->results->get($targetClass);
        $params = $results->getParams();
        foreach ($filters as $filter) {
            $params->addHiddenFilter($filter);
        }

        // Overwrite VuFind default functionality
        // On change of tab fall back on the Tab Default hander to avoid
        // selecting non existing handlers in the other tab
        $options = $results->getOptions();
        $targetHandler = $options->getDefaultHandler();
         
        // Build new URL:
        $results->getParams()->setBasicSearch($query, $targetHandler);
        return $this->url->__invoke($options->getSearchAction())
            . $results->getUrlQuery()->getParams(false);
    }

    /**
     * Get current hidden filters as a string suitable for search URLs
     *
     * @param string $searchClassId            Active search class
     * @param bool   $ignoreHiddenFilterMemory Whether to ignore hidden filters in
     * search memory
     * @param string $prepend                  String to prepend to the hidden
     * filters if they're not empty
     *
     * @return string
     */
    public function getCurrentHiddenFilterParams(
        $searchClassId,
        $ignoreHiddenFilterMemory = false,
        $prepend = '&amp;'
    ) {
        if (!isset($this->cachedHiddenFilterParams[$searchClassId])) {
            $view = $this->getView();
            $hiddenFilters = $this->getHiddenFilters(
                $searchClassId,
                $ignoreHiddenFilterMemory
            );
            if (empty($hiddenFilters) && !$ignoreHiddenFilterMemory) {
                $hiddenFilters = $view->plugin('searchMemory')
                    ->getLastHiddenFilters($searchClassId);
                if (empty($hiddenFilters)) {
                    $hiddenFilters = $this->getHiddenFilters($searchClassId);
                }
            }

            $results = $this->results->get($searchClassId);
            $params = $results->getParams();
            foreach ($hiddenFilters as $field => $filter) {
                foreach ($filter as $value) {
                    $params->addHiddenFilterForField($field, $value);
                }
            }
            if ($hiddenFilters = $params->getHiddenFiltersAsQueryParams()) {
                $this->cachedHiddenFilterParams[$searchClassId]
                    = UrlQueryHelper::buildQueryString(
                        [
                            'hiddenFilters' => $hiddenFilters
                        ]
                    );
            } else {
                $this->cachedHiddenFilterParams[$searchClassId] = "";
            }
        }
        /**
         * TueFind version, if the hidden value is empty then return empty string
         * else build the string and return it
         */
        return (empty($this->cachedHiddenFilterParams[$searchClassId]) ? "" :
        $prepend . $this->cachedHiddenFilterParams[$searchClassId]);
    }
}
