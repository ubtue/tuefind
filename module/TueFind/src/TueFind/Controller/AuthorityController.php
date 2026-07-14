<?php

namespace TueFind\Controller;

class AuthorityController extends \VuFind\Controller\AuthorityController
{
    /**
     * This action needs to be overwritten because it is meant to be a redirect
     * to the recordAction under certain circumstances (especially tabs).
     * Therefore we need to make sure that additional parameters
     * like GND number or the active tab will not be lost.
     */
    public function homeAction()
    {
        $tab = $this->params()->fromRoute('tab', false);

        // If we came in with a record ID, forward to the record action:
        if ($id = $this->params()->fromRoute('id', false)) {
            $this->getRequest()->getQuery()->set('id', $id);
            $this->getRequest()->getQuery()->set('tab', $tab);
            return $this->forwardTo('Authority', 'Record');
        } elseif ($gndNumber = $this->params()->fromQuery('gnd')) {
            $this->getRequest()->getQuery()->set('gnd', $gndNumber);
            $this->getRequest()->getQuery()->set('tab', $tab);
            return $this->forwardTo('Authority', 'Record');
        }

        // Default behavior:
        return parent::homeAction();
    }
}
