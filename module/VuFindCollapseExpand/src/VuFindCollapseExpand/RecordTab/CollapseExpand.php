<?php

namespace VuFindCollapseExpand\RecordTab;

use VuFind\I18n\Translator\TranslatorAwareInterface;
use VuFind\I18n\Translator\TranslatorAwareTrait;

class CollapseExpand extends \VuFind\RecordTab\AbstractContent implements
    TranslatorAwareInterface,
    \VuFindCollapseExpand\Config\CollapseExpandConfigAwareInterface
{
    use \VuFindCollapseExpand\Config\CollapseExpandConfigAwareTrait;
    use TranslatorAwareTrait;

    protected $expandedDocumentsCount = 0;

    public function __construct(
        $viewHelperManager
    ) {
        $this->viewHelper = $viewHelperManager->get('collapseExpand');
    }

    public function getDescription()
    {
        return $this->translator->translate('expand results') . ' (' . $this->expandedDocumentsCount . ')';
    }

    public function isActive()
    {
        if ($this->collapseExpandConfig->isActive()) {
            $results = $this->viewHelper->getOtherDocuments($this->driver);
            $this->expandedDocumentsCount = $results->countExpandedDoc(
                $this->driver->getExpandField($this->collapseExpandConfig->getExpandField())
            );
            return $this->expandedDocumentsCount > 0;
        } else {
            return false;
        }
    }
}
