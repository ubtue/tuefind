<?php

namespace TueFind\AjaxHandler;

use Laminas\Mvc\Controller\Plugin\Params;

class GetFacetDataCustom extends \VuFind\AjaxHandler\AbstractBase
{
    protected $searchResultsManager;

    public function __construct(\VuFind\Search\Results\PluginManager $searchResultsManager) {
        $this->searchResultsManager = $searchResultsManager;
    }

    public function handleRequest(Params $queryParams)
    {
        $facet = $queryParams->fromQuery('facet');
        $facetContains = $queryParams->fromQuery('facet_contains') ?? '';

        $results = $this->searchResultsManager->get('Solr');
        $params = $results->getParams();

        $params->initFromRequest($queryParams->getController()->getRequest()->getQuery());
        $params->getOptions()->spellcheckEnabled(false);
        $params->getOptions()->disableHighlighting();

        $params->addFacet($facet);
        if (!empty($facetContains)) {
            $params->setFacetContains($facetContains);
        }
        $results->setParams($params);
        $results->performAndProcessSearch();
        $partialFacets = $results->getPartialFieldFacets(
            [$facet],
            false,
            50, // limit
            'count', // count || index
            1,
            'OR' // AND || OR
        );
        return $this->formatResponse($partialFacets);
    }
}
