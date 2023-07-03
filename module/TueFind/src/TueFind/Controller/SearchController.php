<?php

namespace TueFind\Controller;

class SearchController extends \VuFind\Controller\SearchController {
    public function facetListAction()
    {
        $this->disableSessionWrites();  // avoid session write timing bug
        // Get results
        $results = $this->getResultsManager()->get($this->searchClassId);
        $params = $results->getParams();
        $params->initFromRequest($this->getRequest()->getQuery());
        // Get parameters
        $facet = $this->params()->fromQuery('facet');
        $page = (int)$this->params()->fromQuery('facetpage', 1);
        $options = $results->getOptions();
        $facetSortOptions = $options->getFacetSortOptions($facet);
        $sort = $this->params()->fromQuery('facetsort', null);
        if ($sort === null || !in_array($sort, array_keys($facetSortOptions))) {
            $sort = empty($facetSortOptions)
                ? 'count'
                : current(array_keys($facetSortOptions));
        }
        $config = $this->serviceLocator->get(\VuFind\Config\PluginManager::class)
            ->get($options->getFacetsIni());
        $limit = $config->Results_Settings->lightboxLimit ?? 50;
        $limit = $this->params()->fromQuery('facetlimit', $limit);
        $facets = $results->getPartialFieldFacets(
            [$facet],
            false,
            $limit,
            $sort,
            $page,
            $this->params()->fromQuery('facetop', 'AND') == 'OR'
        );
        $list = $facets[$facet]['data']['list'] ?? [];
        $facetLabel = $params->getFacetLabel($facet);

        $view = $this->createViewModel(
            [
                'data' => $list,
                'exclude' => intval($this->params()->fromQuery('facetexclude', 0)),
                'facet' => $facet,
                'facetLabel' => $facetLabel,
                'operator' => $this->params()->fromQuery('facetop', 'AND'),
                'page' => $page,
                'results' => $results,
                'anotherPage' => $facets[$facet]['more'] ?? '',
                'sort' => $sort,
                'sortOptions' => $facetSortOptions,
                'baseUriExtra' => $this->params()->fromQuery('baseUriExtra'),
            ]
        );
        $view->setTemplate('search/facet-list-partial-wrapper');
        return $view;
    }
}
