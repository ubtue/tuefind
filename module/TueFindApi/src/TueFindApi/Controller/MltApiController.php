<?php

namespace TueFindApi\Controller;

use VuFindSearch\Command\SimilarCommand;

use function count;
use function is_array;

class MltApiController extends \VuFindApi\Controller\SearchApiController
{
    protected $mltRoute = 'mlt';

    public function similarAction()
    {
        // Disable session writes
        $this->disableSessionWrites();

        $this->determineOutputMode();

        if ($result = $this->isAccessDenied($this->recordAccessPermission)) {
            return $result;
        }

        $request = $this->getRequest()->getQuery()->toArray()
            + $this->getRequest()->getPost()->toArray();

        if (!isset($request['id'])) {
            return $this->output([], self::STATUS_ERROR, 400, 'Missing id');
        }

        if (is_array($request['id'])) {
            return $this->output([], self::STATUS_ERROR, 400, 'Multiple ids unsupported');
        }

        $loader = $this->serviceLocator->get(\VuFind\Record\Loader::class);
        try {
            $results[] = $loader->load($request['id'], $this->searchClassId);
        } catch (\Exception $e) {
            return $this->output(
                [],
                self::STATUS_ERROR,
                400,
                'Error loading record ' . $request['id']
            );
        }

        $searchService = $this->serviceLocator->get(\VuFindSearch\Service::class);
        try {
            $command = new SimilarCommand($this->searchClassId, $request['id']);
            $results = $searchService->invoke($command)->getResult();
        } catch (\Exception $e) {
            return $this->output(
                [],
                self::STATUS_ERROR,
                400,
                'Error determining similar records'
            );
        }

        $response = [
            'resultCount' => count($results),
        ];
        $requestedFields = $this->getFieldList($request);
        if ($records = $this->recordFormatter->format($results, $requestedFields)) {
            $response['records'] = $records;
        }

        return $this->output($response, self::STATUS_OK);
    }

    public function getApiSpecFragment()
    {
        $config = $this->getConfig();
        $results = $this->getResultsManager()->get($this->searchClassId);
        $options = $results->getOptions();
        $params = $results->getParams();

        error_log('SWAGGER CALLED');
        $viewParams = [
            'config' => $config,
            'version' => \VuFind\Config\Version::getBuildVersion(),
            'searchTypes' => $options->getBasicHandlers(),
            'defaultSearchType' => $options->getDefaultHandler(),
            'recordFields' => $this->recordFormatter->getRecordFieldSpec(),
            'defaultFields' => $this->defaultRecordFields,
            'facetConfig' => $params->getFacetConfig(),
            'sortOptions' => $options->getSortOptions(),
            'defaultSort' => $options->getDefaultSortByHandler(),
            'recordRoute' => $this->recordRoute,
            'searchRoute' => $this->searchRoute,
            'mltRoute' => $this->mltRoute,
            'searchIndex' => $this->searchClassId,
            'indexLabel' => $this->indexLabel,
            'modelPrefix' => $this->modelPrefix,
            'maxLimit' => $this->maxLimit,
        ];
        $json = $this->getViewRenderer()->render(
            'mltapi/swagger',
            $viewParams
        );
        return $json;
    }
}
