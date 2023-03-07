<?php

namespace IxTheo\Search\Solr;


class Params extends \TueFind\Search\Solr\Params implements \VuFind\I18n\Translator\TranslatorAwareInterface
{

    use \VuFind\I18n\Translator\TranslatorAwareTrait;
    /**
     * Overwrite sort for BibleRangeSearch
     *
     * @param \Laminas\StdLib\Parameters $request Parameter object representing user
     * request.
     *
     * @return string
     */
    protected function initSort($request)
    {
        if ($this->query instanceof \VuFindSearch\Query\Query && in_array($this->query->getHandler(), $this->getOptions()->getForceDefaultSortSearches())) {
            $this->setSort($this->getOptions()->getDefaultSortByHandler($this->query->getHandler()));
        } else {
            parent::initSort($request);
        }
    }

    protected function handleQuery(\VuFindSearch\Query\Query $query) {
        if ($query->getHandler() == \IxTheo\Search\Backend\Solr\QueryBuilder::BIBLE_RANGE_HANDLER && $this->getTranslatorLocale() != 'de') {
            $queryString = strtr($query->getString(), ",", ":");
            $query->setString($queryString);
        }
    }

    protected function handleGroup(\VuFindSearch\Query\QueryGroup $group) {
        foreach ($group->getQueries() as $groupOrQuery) {
            $this->handleGroupOrQuery($groupOrQuery);
        }
    }


    protected function handleGroupOrQuery($groupOrQuery) {
        if ($groupOrQuery instanceof \VuFindSearch\Query\Query) {
            $this->handleQuery($groupOrQuery);
        } elseif ($groupOrQuery instanceof \VuFindSearch\Query\QueryGroup) {
            $this->handleGroup($groupOrQuery);
        }
    }


    public function getDisplayQuery()
    {
        // Rewrite English style bible searches in the English interface
        $this->handleGroupOrQuery($this->query);
        return parent::getDisplayQuery();
    }

}
