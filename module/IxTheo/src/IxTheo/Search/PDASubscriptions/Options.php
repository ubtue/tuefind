<?php

namespace IxTheo\Search\PDASubscriptions;

use VuFind\Config\ConfigManagerInterface;

class Options extends \VuFind\Search\Base\Options
{
    /**
     * Constructor
     *
     * @param \VuFind\Config\PluginManager $configLoader Config loader
     */
    public function __construct(ConfigManagerInterface $configManager)
    {
        parent::__construct($configManager);

        $this->defaultSort = 'book_title';
        $this->sortOptions = [
            'book_title' => 'sort_title', 'book_author' => 'sort_author',
            'book_year DESC' => 'sort_year', 'book_year' => 'sort_year asc'
        ];
    }

    /**
     * Return the route name for the search results action.
     *
     * @return string
     */
    public function getSearchAction()
    {
        return 'myresearch-pdasubscription';
    }

    /**
     * Load all recommendation settings from the relevant ini file.  Returns an
     * associative array where the key is the location of the recommendations (top
     * or side) and the value is the settings found in the file (which may be either
     * a single string or an array of strings).
     *
     * @param string $handler Name of handler for which to load specific settings.
     *
     * @return array associative: location (top/side/etc.) => search settings
     */
    public function getRecommendationSettings($handler = null)
    {
        return [];
    }
}
