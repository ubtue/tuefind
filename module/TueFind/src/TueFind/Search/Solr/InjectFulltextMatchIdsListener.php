<?php
namespace TueFind\Search\Solr;

use Laminas\EventManager\EventInterface;
use Laminas\EventManager\SharedEventManagerInterface;
use VuFindSearch\Backend\BackendInterface;
use VuFindSearch\Service;

class InjectFulltextMatchIdsListener
{

    /**
     * Backend.
     *
     * @var BackendInterface
     */
    protected $backend;

    /**
     * Is highlighting active?
     *
     * @var bool
     */
    protected $active = false;


    /**
     * Active Fulltext Type Filters
     *
     */
    protected $selected_fulltext_types;


    public function __construct(BackendInterface $backend)
    {
       $this->backend = $backend;
    }
   /**
     * Attach listener to shared event manager.
     *
     * @param SharedEventManagerInterface $manager Shared event manager
     *
     * @return void
     */
    public function attach(SharedEventManagerInterface $manager)
    {
        $manager->attach('VuFind\Search', Service::EVENT_PRE, [$this, 'onSearchPre']);
        $manager->attach('VuFind\Search', Service::EVENT_POST, [$this, 'onSearchPost']);
    }


    /**
     * GetSearchHandlerName
     * @return string
     */
    protected function getSearchHandlerName(EventInterface $event) {
        $query = $event->getParam('query');
        if ($query instanceof \VuFindSearch\Query\Query)
            return $query->getHandler();
        if ($query instanceof \VuFindSearch\Query\QueryGroup)
            return $query->getReducedHandler();
        return "";
    }


    protected function getFulltextFilterFromFulltextTypeFacet($backend, $params) {
        $filter_queries = $params->get('fq');
        if ($filter_queries == null)
            return "";
        $selected_fulltext_types = [];
        foreach ($filter_queries as $filter_query) {
            if (!preg_match('/fulltext_types:(.*)/', $filter_query))
                continue;
            $fulltext_type_facet_expression = $backend->getQueryBuilder()->getLuceneHelper()->extractSearchTerms($filter_query);
            $selected_fulltext_types = array_filter(explode('"', preg_replace('/(\s*(AND|OR)\s*)/', '', $fulltext_type_facet_expression)));
        }
        return $selected_fulltext_types;
   }


   /**
    * Set up highlighting parameters.
    *
    * @param EventInterface $event Event
    *
    * @return EventInterface
    */
    public function onSearchPre(EventInterface $event) {
        $command = $event->getParam('command');
        if ($command->getContext() != 'search') {
            return $event;
        }
        $backend = $command->getTargetIdentifier();
        if ($backend == $this->backend->getIdentifier()) {
            $params = $command->getSearchParameters();
            if ($params) {
                if ($backend == 'Search2') {
                    $this->active = true;
                    $this->backend->getQueryBuilder()->setIncludeFulltextSnippets(true);
                    // Pass filter from chosen fulltext_type facet
                    $this->selected_fulltext_types = $this->getFulltextFilterFromFulltextTypeFacet($backend, $params);
                    $this->backend->getQueryBuilder()->setSelectedFulltextTypes($this->selected_fulltext_types);
                }
            }
        }
        return $event;
    }


   /**
     * Inject highlighting results.
     *
     * @param EventInterface $event Event
     *
     * @return EventInterface
     */
    public function onSearchPost(EventInterface $event)
    {
        $command = $event->getParam('command');
        $backend = $command->getTargetIdentifier();
        if (!$this->active || $command->getContext() != 'search') {
            return $event;
        }

        // Inject highlighting details into record objects:
        if ($backend == $this->backend->getIdentifier()) {
            $result = $command->getResult();
            $params = $command->getSearchParameters();
            if ($backend == 'Search2' && $params->get('q')[0] && $params->get('q')[0] != '*:*') {
                foreach ($result->getRecords() as $record) {
                    $record->setHasFulltextMatch();
                    $record->setFulltextTypeFilters($this->selected_fulltext_types);
                }
            }
        }
    }
}
