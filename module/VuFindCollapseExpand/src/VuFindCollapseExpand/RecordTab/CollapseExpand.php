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

    protected $numOfExpandedDoc = 0;

    public function __construct(
        $viewHelperManager
    ) {
        $this->viewHelper = $viewHelperManager->get('collapseExpand');
    }

    public function getDescription()
    {
        return $this->translator->translate('show_grouped_items') . ' (' . $this->numOfExpandedDoc . ')';
    }

    public function isActive()
    {
        $config = $this->viewHelper->getConfig();
        if ($config->isActive()) {
            $results = $this->viewHelper->getOtherDocuments($this->driver);
            $this->numOfExpandedDoc = $results->countExpandedDoc(
                $this->driver->getExpandField($config->getExpandField())
            );
            return $this->numOfExpandedDoc > 0;
        } else {
            return false;
        }
    }
}
