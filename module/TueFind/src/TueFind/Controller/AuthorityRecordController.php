<?php

namespace TueFind\Controller;

class AuthorityRecordController extends \VuFind\Controller\AuthorityRecordController
{
    public function homeAction()
    {
        $result = parent::homeAction();
        $result->user = $this->getUser();
        return $result;
    }
}
