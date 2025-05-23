<?php

/**
 * ItemCollapseAndExpand tab
 *
 * @category TueFind
 * @package  RecordTabs
 * @author   Steven Lolong <steven.lolong@uni-tuebingen.de>
 *
 */

namespace TueFind\RecordTab;

use VuFind\I18n\Translator\TranslatorAwareInterface;
use VuFind\I18n\Translator\TranslatorAwareTrait;

class ItemCollapseAndExpand extends \VuFind\RecordTab\AbstractContent implements TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    /**
     * Main configuration
     */
    protected $config;

    /**
     * Search options plugin manager
     *
     * @var \VuFind\Search\Options\PluginManager
     */
    protected $searchOptionsManager;

    /**
     * Constructor
     *
     * @param \Laminas\Config\Config               $config Configuration
     * @param \VuFind\Search\Options\PluginManager $som    Search options plugin
     * manager
     */
    public function __construct(
        \Laminas\Config\Config $config,
        \VuFind\Search\Options\PluginManager $som
    ) {
        $this->config = $config;
        $this->searchOptionsManager = $som;
        $this->accessPermission = 'access.ItemCollapseAndExpand';
    }

    public function getDescription()
    {
        // return $this->translate('collapse_and_expand_tab_description');
        return 'Other Document';
    }

    public function isActive()
    {
        //    return $this->getRecordDriver()->tryMethod('hasFulltext');
        return true;
    }
}
