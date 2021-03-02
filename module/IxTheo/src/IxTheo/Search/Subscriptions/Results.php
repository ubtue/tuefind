<?php
namespace IxTheo\Search\Subscriptions;


use LmcRbacMvc\Service\AuthorizationServiceAwareInterface;
use LmcRbacMvc\Service\AuthorizationServiceAwareTrait;
use VuFind\Exception\ListPermission as ListPermissionException;
use VuFind\Search\Base\Results as BaseResults;
use IxTheo\Db\Table\Subscription as SubscriptionTable;


class Results extends BaseResults
    implements AuthorizationServiceAwareInterface
{
    use AuthorizationServiceAwareTrait;

    /**
     * Object if user is logged in, false otherwise.
     *
     * @var \VuFind\Db\Row\User|bool
     */
    protected $user = null;

    /**
     * Active user list (false if none).
     *
     * @var \VuFind\Db\Row\UserList|bool
     */
    protected $list = false;

    /**
     *
     * @var \IxTheo\Db\Table\Subscription
     */
    protected $subscriptionTable = null;

    /**
     * Returns the stored list of facets for the last search
     *
     * @param array $filter Array of field => on-screen description listing
     * all of the desired facet fields; set to null to get all configured values.
     *
     * @return array        Facets data arrays
     */
    public function getFacetList($filter = null)
    {
        return [];
    }

    /**
     * Support method for performAndProcessSearch -- perform a search based on the
     * parameters passed to the object.
     *
     * @throws ListPermissionException
     */
    protected function performSearch()
    {
        $auth = $this->getAuthorizationService();
        $this->user = $auth ? $auth->getIdentity() : false;
        if (!$this->user) {
            throw new ListPermissionException('Cannot retrieve subscriptions without logged in user.');
        }
        $list = $this->getListObject();
        if (is_null($list)) {
            throw new ListPermissionException('Cannot retrieve subscriptions without logged in user.');
        }

        $this->resultTotal = count($list->toArray());

        // Apply offset and limit if necessary!
        $limit = $this->getParams()->getLimit();
        if ($this->resultTotal > $limit) {
            $list = $this->subscriptionTable->get($this->user->id, $this->getParams()->getSort(), $this->getStartRecord() - 1, $limit);
        }

        // Retrieve record drivers for the selected items.
        $recordsToRequest = [];
        foreach ($list as $row) {
            $recordsToRequest[] = [
                'id' => $row->journal_control_number_or_bundle_name,
                'source' => 'Solr'
            ];
        }

        $this->recordLoader->setCacheContext("Subscription");
        $this->results = $this->recordLoader->loadBatch($recordsToRequest);
    }

    /**
     * Get the list object associated with the current search (null if no list
     * selected).
     *
     * @return \VuFind\Db\Row\UserList|null
     */
    public function getListObject()
    {
        // If we haven't previously tried to load a list, do it now:
        if ($this->list === false) {
            $this->list = $this->subscriptionTable->getAll($this->user->id, $this->getParams()->getSort());
        }
        return $this->list;
    }

    /**
     * Get Results, sorted on PHP side
     * (by title, which is not stored in MySQL due to redundancy issues)
     *
     * "Missing" records will be hidden
     * (e.g. if a user has subscribed a record in IxTheo and opens "MyResearch" in RelBib,
     * it can't be displayed there, cause it's not part of the index)
     *
     * @return array
     */
    public function getResultsSorted()
    {
        $results = $this->getResults();
        $results_sorted = [];

        foreach ($results as $i => $result) {
            if (!($result instanceof \VuFind\RecordDriver\Missing)) {
                $ppn = $result->getRecordId();
                $title = $result->getTitle();
                $results_sorted[$title . '#' . $ppn] = $result;
            }
        }
        ksort($results_sorted, SORT_LOCALE_STRING);
        return $results_sorted;
    }

    public function setSubscriptionTable(SubscriptionTable $subscriptionTable) {
        $this->subscriptionTable = $subscriptionTable;
    }
}
