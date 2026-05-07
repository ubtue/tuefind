<?php

namespace VuFindCollapseExpand\View\Helper\CollapseExpand;

use Laminas\View\Helper\AbstractHelper;
use VuFindCollapseExpand\Config\CollapseExpandConfigAwareInterface;
use VuFindCollapseExpand\Config\CollapseExpandConfigAwareTrait;
use VuFindSearch\Backend\Solr\LuceneSyntaxHelper;
use VuFindSearch\Command\SearchCommand;
use VuFindSearch\Query\Query;

class CollapseExpand extends AbstractHelper implements CollapseExpandConfigAwareInterface
{
    use CollapseExpandConfigAwareTrait;

    public function __construct(
        \VuFind\Search\Options\PluginManager $som,
        \VuFind\Search\Results\PluginManager $srm,
        \VuFindSearch\Service $ss
    ) {
        $this->searchOptionsManager = $som;
        $this->searchResultsManager = $srm;
        $this->searchService = $ss;
    }

    /**
     * Cached result of other Document count
     *
     * @var int
     */
    protected $otherDocumentCount = null;

    /**
     * Cached result of other Document
     *
     * @var \VuFindSearch\Response\RecordCollectionInterface
     */
    public $otherDocuments;

    /**
     * Return count of other Document available
     * show on the record tab next to the title
     *
     * @return int
     */
    public function getOtherDocumentCount()
    {
        return $this->otherDocumentCount;
    }

    /**
     * Return other Document
     *
     * @return \VuFindSearch\Response\RecordCollectionInterface
     */
    public function getOtherDocuments($record)
    {
        if (!isset($this->otherDocuments)) {
            $pluginManager = $this->searchResultsManager;
            $pluginManagerSolr = $pluginManager->get('Solr');
            $defaultParams = $pluginManagerSolr->getParams();

            if ($this->collapseExpandConfig->isActive()) {
                $expandConfig = $this->collapseExpandConfig->getExpandConfig();
                $expandRow = $expandConfig['expand.rows'];
                $expandField = $expandConfig['expand.field'];

                $keyword = $record->getExpandField($expandField);
                $syntaxHelper = new LuceneSyntaxHelper();
                $queryString = $expandField . ':"' . $syntaxHelper->normalizeSearchString($keyword) . '"';
                $query = new Query(
                    $queryString
                );

                $command = new SearchCommand(
                    $record->getSourceIdentifier(),
                    $query,
                    0,
                    $expandRow,
                    $defaultParams->getBackendParameters(),
                );
                $this->otherDocuments = $this->searchService->invoke($command)->getResult();
            }
        }
        return $this->otherDocuments;
    }
}
