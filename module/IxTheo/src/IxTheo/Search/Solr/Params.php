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

    public function getDisplayQuery()
    {
        // Rewrite English style bible searches in the English interface
        $handler = $this->query instanceof \VuFindSearch\Query\Query ? $this->query->getHandler() :
                   ($this->query instanceof \VuFindSearch\Query\QueryGroup ? $this->query->getReducedHandler() : "");
        if ($handler == \IxTheo\Search\Backend\Solr\QueryBuilder::BIBLE_RANGE_HANDLER &&
            $this->getTranslatorLocale() != 'de') {
               $queryString = strtr($this->query->getString(), ",", ":");
               $this->query->setString($queryString);
        }
        return parent::getDisplayQuery();
    }
}
