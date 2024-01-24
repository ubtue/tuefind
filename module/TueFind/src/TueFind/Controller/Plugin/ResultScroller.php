<?php

/**
 * Class for managing "next" and "previous" navigation within result sets.
 * Starting from VuFind 9.1, the original record navigation with SearchMemory isn't compatible with TueFind and its sub-systems.
 * Therefore, this plugin is needed. 
 * This plugin removes the use of SearchMemory and restores the old-style navigation model. However, it follows the VuFind 9.1 class's structures.
 * 
 * @category TueFind
 * @package Controller_Plugins
 * @author Steven Lolong <steven.lolong@uni-tuebingen.de>
 */

namespace TueFind\Controller\Plugin;

use Laminas\Session\Container as SessionContainer;
use VuFind\Search\Results\PluginManager as ResultsManager;
use VuFind\RecordDriver\AbstractBase as BaseRecord;
use VuFind\Search\Base\Results;
use VuFind\Controller\Plugin\ResultScroller as ResultScrollerVuFind;

class ResultScroller extends ResultScrollerVuFind
{
  /**
  * Constructor. Create a new search result scroller.
  *
  * @param SessionContainer $session Session container
  * @param ResultsManager   $rm      Results manager
  * @param bool             $enabled Is the scroller enabled?
  */
  public function __construct(
      SessionContainer $session,
      ResultsManager $rm,
      $enabled = true
  ) {
      $this->enabled = $enabled;
      $this->data = $session;
      $this->resultsManager = $rm;
  }

  /**
  * @param \VuFind\Search\Base\Results
  * @return bool
  */
  public function init($searchObject)
  {
      // Do nothing if disabled or search is empty:
      if (!$this->enabled || $searchObject->getResultTotal() === 0) {
          return false;
      }

      // Save the details of this search in the session
      $this->addData($searchObject);
      return (bool)$this->data->currIds;
  }

  /**
   * Add for a search
   *
   * @param VuFind\Search\Base\Results $searchObject Search object
   *
   * @return void
   */
  protected function addData(Results $searchObject): void
  {
      
      $this->data->searchId = $searchObject->getSearchId();
      $this->data->page = $searchObject->getParams()->getPage();
      $this->data->limit = $searchObject->getParams()->getLimit();
      $this->data->sort = $searchObject->getParams()->getSort();
      $this->data->total = $searchObject->getResultTotal();
      $this->data->firstlast = $searchObject->getOptions()
          ->supportsFirstLastNavigation();

      // save the IDs of records on the current page to the session
      // so we can "slide" from one record to the next/previous records
      // spanning 2 consecutive pages
      $this->data->currIds = $this->fetchPage($searchObject);

      // clear the previous/next page
      unset($this->data->prevIds);
      unset($this->data->nextIds);
      unset($this->data->firstId);
      unset($this->data->lastId);
  }

  /**
  * Get the previous/next record in the last search
  * result set relative to the current one, also return
  * the position of the current record in the result set.
  * Return array('previousRecord'=>previd, 'nextRecord'=>nextid,
  * 'currentPosition'=>number, 'resultTotal'=>number).
  *
  * @param BaseRecord $driver Driver for the record
  * currently being displayed
  *
  * @return array
  */
  public function getScrollData($driver)
  {
      $retVal = [
          'firstRecord' => null, 'lastRecord' => null,
          'previousRecord' => null, 'nextRecord' => null,
          'currentPosition' => null, 'resultTotal' => null
      ];

      // Do nothing if disabled or data missing:
      if ($this->enabled
          && isset($this->data->currIds) && isset($this->data->searchId)
          && ($lastSearch = $this->restoreLastSearch())
      ) {
          $retVal = $this->buildScrollDataArray($retVal, $driver, $lastSearch);
      }
      return $retVal;
  }

  /**
   * Build and return the scroll data array
   *
   * @param array      $retVal     Return values (in progress)
   * @param BaseRecord $driver     Driver for the record currently being displayed
   * @param VuFind\Search\Base\Results    $lastSearch Representation of last search
   *
   * @return array
   */
  protected function buildScrollDataArray(
    array $retVal,
    BaseRecord $driver,
    Results $lastSearch
  ): array {
    // Make sure expected data elements are populated:
    if (!isset($this->data->prevIds)) {
      $this->data->prevIds = null;
    }
    if (!isset($this->data->nextIds)) {
        $this->data->nextIds = null;
    }
    
    // Store total result set size:
    $retVal['resultTotal'] = $this->data->total ?? 0;

    // Set first and last record IDs
    if ($this->data->firstlast) {
      $retVal['firstRecord'] = $this->getFirstRecordId($lastSearch);
      $retVal['lastRecord'] = $this->getLastRecordId($lastSearch);
    }

    // build a full ID string using the driver:
    $id = $driver->getSourceIdentifier() . '|' . $driver->getUniqueId();

    // find where this record is in the current result page
    $pos = is_array($this->data->currIds)
        ? array_search($id, $this->data->currIds)
        : false;
    if ($pos !== false) {
        // OK, found this record in the current result page
        // calculate its position relative to the result set
        $retVal['currentPosition']
            = ($this->data->page - 1) * $this->data->limit + $pos + 1;

        // count how many records in the current result page
        $count = count($this->data->currIds);
        if ($pos > 0 && $pos < $count - 1) {
            // the current record is somewhere in the middle of the current
            // page, ie: not first or last
            return $this->scrollOnCurrentPage($retVal, $pos);
        } elseif ($pos == 0) {
            // this record is first record on the current page
            return $this
                ->fetchPreviousPage($retVal, $lastSearch, $pos, $count);
        } elseif ($pos == $count - 1) {
            // this record is last record on the current page
            return $this->fetchNextPage($retVal, $lastSearch, $pos);
        }
    } else {
        // the current record is not on the current page
        // if there is something on the previous page
        if (!empty($this->data->prevIds)) {
            // check if current record is on the previous page
            $pos = is_array($this->data->prevIds)
                ? array_search($id, $this->data->prevIds) : false;
            if ($pos !== false) {
                return $this
                    ->scrollToPreviousPage($retVal, $lastSearch, $pos);
            }
        }
        // if there is something on the next page
        if (!empty($this->data->nextIds)) {
            // check if current record is on the next page
            $pos = is_array($this->data->nextIds)
                ? array_search($id, $this->data->nextIds) : false;
            if ($pos !== false) {
                return $this->scrollToNextPage($retVal, $lastSearch, $pos);
            }
        }
        if ($this->data->firstlast) {
            if ($id == $retVal['firstRecord']) {
                return $this->scrollToFirstRecord($retVal, $lastSearch);
            }
            if ($id == $retVal['lastRecord']) {
                return $this->scrollToLastRecord($retVal, $lastSearch);
            }
        }
    }
    return $retVal;
  }


  /**
  * Restore the last saved search.
  *
  * @return VuFind\Search\Base\Results
  */
  protected function restoreLastSearch()
  {
      if (isset($this->data->searchId)) {
          $searchTable = $this->getController()->getTable('Search');
          $row = $searchTable->getRowById($this->data->searchId, false);
          if (!empty($row)) {
              $minSO = $row->getSearchObject();
              $search = $minSO->deminify($this->resultsManager);
              // The saved search does not remember its original limit or sort;
              // we should reapply them from the session data:
              $search->getParams()->setLimit($this->data->limit);
              $search->getParams()->setSort($this->data->sort);
              return $search;
          }
      }
      return null;
  }

      /**
   * Update the remembered "last search" in the session.
   *
   * @param VuFind\Search\Base\Results $search Search object to remember.
   *
   * @return void
   */
  protected function rememberSearch($search)
  {
      $baseUrl = $this->getController()->url()->fromRoute(
          $search->getOptions()->getSearchAction()
      );
      $this->getController()->getSearchMemory()->rememberSearch(
          $baseUrl . $search->getUrlQuery()->getParams(false)
      );
  }
}

