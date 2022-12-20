<?php

namespace TueFind\Controller;

/**
 * This Controller cannot be named "AdminController" because it would conflict
 * with VuFindAdmin\Controller\AdminController, which is for
 * Backend administration, so we call this one AdminFrontendController instead.
 */
class AdminFrontendController extends \VuFind\Controller\AbstractBase {
    protected function forceAdminLogin()
    {
        $user = $this->getUser();
        if ($user == false) {
            throw new \Exception("You must be logged in first");
        }

        if ($user->tuefind_rights == null)
            throw new \Exception("This user has no admin rights!");
    }

    public function processUserAuthorityRequestAction()
    {
        try {
            $this->forceAdminLogin();
        } catch (\Exception $e) {
            return $this->forceLogin($e->getMessage());
        }

        $userId = $this->params()->fromRoute('user_id');
        $authorityId = $this->params()->fromRoute('authority_id');
        $entry = $this->getTable('user_authority')->getByUserIdAndAuthorityId($userId, $authorityId);
        $requestUser = $this->getTable('user')->getByID($userId);
        $action = $this->params()->fromPost('action');
        $accessInfo = "grant";
        if ($action != '') {
            if ($action == 'grant') {
                $entry->updateAccessState('granted');
            } elseif ($action == 'decline') {
                $accessInfo = "decline";
                $entry->delete();
            }
            // receivers
            $receivers = new \Laminas\Mail\AddressList();
            $receivers->add($requestUser->email);

            $config = $this->getConfig();
            $mailer = $this->serviceLocator->get(\VuFind\Mailer\Mailer::class);
            $receiverCount = count($receivers);
            if ($receiverCount == 0) {
                $receivers = $config->Site->email;
            } else {
                $mailer->setMaxRecipients($receiverCount);
            }

            // send mail
            $authority = $this->serviceLocator->get(\VuFind\Record\Loader::class)->load($authorityId, 'SolrAuth');
            $authorityName = $this->serviceLocator->get('ViewHelperManager')->get('authority')->getName($authority);

            // body
            $renderer = $this->getViewRenderer();
            $message = $renderer->render('Email/authority-request-access-'.$accessInfo.'.phtml');

            $mailer->send($receivers, $config->Site->email_from, $this->translate('authority_request_access_email_subject'), $message);
        }

        return $this->createViewModel(['action' => $action]);
    }

    public function showAdminsAction()
    {
        try {
            $this->forceAdminLogin();
        } catch (\Exception $e) {
            return $this->forceLogin($e->getMessage());
        }

        return $this->createViewModel(['admins' => $this->getTable('user')->getAdmins()]);
    }

    public function showUserAuthoritiesAction()
    {
        try {
            $this->forceAdminLogin();
        } catch (\Exception $e) {
            return $this->forceLogin($e->getMessage());
        }

        return $this->createViewModel(['users' => $this->getTable('user_authority')->getAll()]);
    }
}
