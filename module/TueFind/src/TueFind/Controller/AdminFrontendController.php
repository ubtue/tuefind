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
        $requestUserLanguage = $requestUser->last_language;
        $adminUser = $this->getUser();
        $userAuthorityHistoryTable = $this->getTable('user_authority_history')->getLatestRequestByUserId($userId);
        $action = $this->params()->fromPost('action');
        $accessInfo = "grant";
        if ($action != '') {
            if ($action == 'grant') {
                $entry->updateAccessState('granted');
                $userAuthorityHistoryTable->updateUserAuthorityHistory($adminUser->id, 'granted');
            } elseif ($action == 'decline') {
                $accessInfo = "decline";
                $userAuthorityHistoryTable->updateUserAuthorityHistory($adminUser->id, $accessInfo);
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
            $emailPathTemplate = $this->getEmailTemplatePath($requestUserLanguage, $accessInfo);
            
            // body
            $renderer = $this->getViewRenderer();
            $message = $renderer->render($emailPathTemplate);

            $mailer->send($receivers, $config->Site->email_from, $this->translate('authority_access_email_subject_'.$accessInfo), $message);
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

    public function showUserPublicationsAction()
    {
        try {
            $this->forceAdminLogin();
        } catch (\Exception $e) {
            return $this->forceLogin($e->getMessage());
        }
        return $this->createViewModel(['publications' => $this->getTable('publication')->getAll()]);
    }

    //generate a path for email templates which is not related to the current user, since VuFind does not yet have such functionality
    protected function getEmailTemplatePath(string $requestUserLanguage, string $accessInfo): string
    {
        $emailPathTemplate = 'Email/'.$requestUserLanguage.'/authority-request-access-'.$accessInfo.'.phtml';
        $fullEmailPathTemplate =  $_SERVER['VUFIND_HOME'].'/themes/tuefind/templates/'.$emailPathTemplate;

        if (!file_exists($fullEmailPathTemplate)) {
            $config = $this->serviceLocator->get(\VuFind\Config\PluginManager::class)->get('config');
            $defaultEmailLanguage = $config->Site->language;
            $emailPathTemplate = 'Email/'.$defaultEmailLanguage.'/authority-request-access-'.$accessInfo.'.phtml';
        }

        return $emailPathTemplate;
    }

    public function showUserAuthorityHistoryAction()
    {
        $this->forceAdminLogin();

        return $this->createViewModel(['user_authority_history_datas' => $this->getTable('user_authority_history')->getAll()]);
    }

    public function showUserPublicationStatisticsAction() {

        $this->forceAdminLogin();

        $publications = $this->getTable('publication')->getStatistick();

        return $this->createViewModel(['publications' => $publications]);

    }

}
