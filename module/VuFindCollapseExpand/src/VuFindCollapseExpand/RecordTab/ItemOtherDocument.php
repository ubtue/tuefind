<?php

/**
 * ItemCollapseExpand tab
 *
 * @category TueFind
 * @package  RecordTabs
 * @author   Steven Lolong <steven.lolong@uni-tuebingen.de>
 *
 */

namespace VuFindCollapseExpand\RecordTab;

use VuFind\I18n\Translator\TranslatorAwareInterface;
use VuFind\I18n\Translator\TranslatorAwareTrait;

class ItemOtherDocument extends \VuFind\RecordTab\AbstractContent implements TranslatorAwareInterface
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

    protected $numOfExpandedDoc = 0;

    protected $results = null;

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
        $this->accessPermission = 'access.ItemCollapseExpand';

    }

    public function getDescription()
    {
        return 'Other Documents (' . $this->numOfExpandedDoc . ')';
    }

    public function isActive()
    {   
        if ($this->driver->isActiveCnEParams()) {
            if ($this->results == null) {

                $expand_field = $this->driver->getExpandField();
                $results = $this->driver->tryMethod('getOtherDocument', [$this->driver->getContainerExpandField($expand_field)]);
                $this->numOfExpandedDoc = $results->countExpandedDoc($this->driver->getContainerExpandField($expand_field));

            }
            return $this->numOfExpandedDoc > 0 ? true : false;
        } else {
            return false;
        }
    }
}