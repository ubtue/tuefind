<?php
namespace TueFind\Controller;

use Laminas\ServiceManager\ServiceLocatorInterface;

class Search2recordController extends \VuFind\Controller\AbstractRecord
{
    protected function createViewModel($params = null)
    {
        $view = parent::createViewModel($params);
        $this->layout()->searchClassId = $view->searchClassId = $view->driver->getSearchBackendIdentifier();
        $view->driver = $this->loadRecord();
        $view->user = $this->getUser();
        return $view;
   }
}
