<?php

namespace TueFind\AjaxHandler;

use Laminas\Mvc\Controller\Plugin\Params;
use TueFind\Service\CmsSync;
use VuFind\Db\Entity\UserEntityInterface;

class CmsEntries extends \VuFind\AjaxHandler\AbstractBase
{
    use \VuFind\I18n\Translator\TranslatorAwareTrait;

    protected $searchResultsManager;

    protected CmsSync $cmsSync;

    public function __construct(\VuFind\Search\Results\PluginManager $searchResultsManager, protected ?UserEntityInterface $user, CmsSync $cmsSync)
    {
        $this->searchResultsManager = $searchResultsManager;
        $this->cmsSync = $cmsSync;
    }

    /**
     * Handle a request.
     *
     * @param Params $params Parameter helper from controller
     *
     * @return array [response data, HTTP status code]
     */
    public function handleRequest(Params $params)
    {
        // check if user is logged in
        if (!$this->user) {
            return $this->formatResponse([
                'status' => 'ERROR',
                'data' => $this->translate('You must be logged in first'),
            ], self::STATUS_HTTP_NEED_AUTH);
        }

        $result = [];

        if ($params->fromQuery('action') === 'gitPull') {
            try {
                $result = $this->cmsSync->pullRepository();
                return $this->formatResponse($result);
            } catch (\Exception $e) {
                return $this->formatResponse(['success' => false, 'message' => $e->getMessage()]);
            }
        }

        if ($params->fromQuery('action') === 'gitImport') {
            try {
                $result = $this->cmsSync->importPagesFromRepository();
                return $this->formatResponse($result);
            } catch (\Exception $e) {
                return $this->formatResponse(['success' => false, 'message' => $e->getMessage()]);
            }
        }

        if ($params->fromQuery('action') === 'gitPush') {
            try {
                $result = $this->cmsSync->exportPagesToRepository();
                return $this->formatResponse($result);
            } catch (\Exception $e) {
                return $this->formatResponse(['success' => false, 'message' => $e->getMessage()]);
            }
        }

        return $this->formatResponse($result);
    }
}
