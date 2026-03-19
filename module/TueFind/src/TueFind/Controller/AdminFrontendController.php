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

        if ($user->getTueFindRights() == [])
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
        $requestUser = $this->getDbService(\TueFind\Db\Service\UserServiceInterface::class)->getEntityByID(\TueFind\Db\Entity\UserEntityInterface::class, $userId);
        $authorityId = $this->params()->fromRoute('authority_id');
        $userAuthorityService = $this->getDbService(\TueFind\Db\Service\UserAuthorityServiceInterface::class);
        $entry = $userAuthorityService->getByUserAndAuthorityId($requestUser, $authorityId);
        $requestUserLanguage = $requestUser->getLastLanguage();
        $adminUser = $this->getUser();
        $userAuthorityHistoryService = $this->getDbService(\TueFind\Db\Service\UserAuthorityHistoryServiceInterface::class);
        $userAuthorityHistoryEntry = $userAuthorityHistoryService->getLatestRequestByUser($requestUser);
        $action = $this->params()->fromPost('action');
        $accessInfo = 'grant';
        if ($action != '') {
            if ($action == 'grant') {
                $userAuthorityHistoryService->updateHistoryEntry($userAuthorityHistoryEntry, $adminUser, 'granted');
                $userAuthorityService->updateAccessState($entry, 'granted');
            } elseif ($action == 'decline') {
                $accessInfo = 'declined';
                $userAuthorityHistoryService->updateHistoryEntry($userAuthorityHistoryEntry, $adminUser, $accessInfo);
                $userAuthorityService->deleteEntity($entry);
            }

            // receivers
            $receivers = [];
            $receivers[] = $requestUser->getEmail();

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

        return $this->createViewModel(['admins' => $this->getDbService(\TueFind\Db\Service\UserServiceInterface::class)->getAdmins()]);
    }

    public function showUserAuthoritiesAction()
    {
        try {
            $this->forceAdminLogin();
        } catch (\Exception $e) {
            return $this->forceLogin($e->getMessage());
        }

        return $this->createViewModel(['userAuthorities' => $this->getDbService(\TueFind\Db\Service\UserAuthorityServiceInterface::class)->getAll()]);
    }

    public function showUserPublicationsAction()
    {
        try {
            $this->forceAdminLogin();
        } catch (\Exception $e) {
            return $this->forceLogin($e->getMessage());
        }
        return $this->createViewModel(['publications' => $this->getDbService(\TueFind\Db\Service\PublicationServiceInterface::class)->getAll()]);
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

        return $this->createViewModel(['user_authority_history_datas' => $this->getDbService(\TueFind\Db\Service\UserAuthorityHistoryServiceInterface::class)->getAll()]);
    }

    public function showUserPublicationStatisticsAction() {
        $this->forceAdminLogin();

        return $this->createViewModel(['publications' => $this->getDbService(\TueFind\Db\Service\PublicationServiceInterface::class)->getStatistics()]);
    }

     public function CMSPagesAction() {
        $this->forceAdminLogin();

        $allCMS = ['allCMSPages' => $this->getDbService(\TueFind\Db\Service\CmsPagesServiceInterface::class)->getAll()];

        return $this->createViewModel($allCMS);
    }

    public function addCMSPageAction()
    {
        $user = $this->getUser();
        if ($user == false) {
            return $this->forceLogin();
        }

        $config = $this->serviceLocator->get(\VuFind\Config\PluginManager::class)->get('config');

        $langs = $config->Languages;

        $subSystem = $this->serviceLocator->get('ViewHelperManager')->get('tuefind')->getAllTueFindSubsystems();

        $action = $this->params()->fromPost('action');

        $pageContent = $this->params()->fromPost('page_content');
        $pageTitle = $this->params()->fromPost('page_title');

        //$user_type = $user->getUserType(); for now we do not have different user types, but in the future we might want to use this to determine if a user has access to certain subsystems or not

        if ($action == 'publish') {

            $cmsPageId = $this->getDbService(\TueFind\Db\Service\CmsPagesServiceInterface::class)->add(
                $this->params()->fromPost('page_system_id'),
                new \DateTime(),
                new \DateTime()
            );

            if (!$cmsPageId) {
                throw new \RuntimeException('CMS page was not created');
            }

            $this->getDbService(\TueFind\Db\Service\CmsPagesSubsystemServiceInterface::class)->add(
                $cmsPageId,
                $this->params()->fromPost('subsystem')
            );

            $iLang=0;
            foreach ($langs as $key=>$name) {
                $this->getDbService(\TueFind\Db\Service\CmsPagesTranslationServiceInterface::class)->add(
                    $cmsPageId,
                    $key,
                    $pageTitle[$iLang],
                    $pageContent[$iLang]
                );
                $iLang++;
            }

            $this->flashMessenger()->addMessage(['msg' => 'page created!', 'html' => true], 'success');
            $this->redirect()->toUrl('/AdminFrontend/CMSPages');
        }

        $view = $this->createViewModel();
        $view->langs = $langs;
        $view->subSystem = $subSystem;
        return $view;
    }

    public function updateCMSPageAction()
    {
        $user = $this->getUser();
        if ($user == false) {
            return $this->forceLogin();
        }

        $config = $this->serviceLocator->get(\VuFind\Config\PluginManager::class)->get('config');

        $langs = $config->Languages;

        $action = $this->params()->fromPost('action');
        $cmsPageId = $this->params()->fromQuery('cms_page_id');
        $pageContent = $this->params()->fromPost('page_content');
        $pageTitle = $this->params()->fromPost('page_title');

        $cmsPage = $this->getDbService(\TueFind\Db\Service\CmsPagesServiceInterface::class)->getByIDFull($cmsPageId);

        if ($action == 'update') {

            //for now we only update changeDate fild in cms_pages table, but in the future we might want to update other fields as well, for example pageSystemId if we want to allow that to be changed
            $this->getDbService(\TueFind\Db\Service\CmsPagesServiceInterface::class)->update($cmsPageId, new \DateTime());

            $result = $this->getDbService(\TueFind\Db\Service\CmsPagesTranslationServiceInterface::class)->delete($cmsPageId);

            $iLang=0;
            foreach ($langs as $key=>$name) {
                $this->getDbService(\TueFind\Db\Service\CmsPagesTranslationServiceInterface::class)->add(
                    $cmsPageId,
                    $key,
                    $pageTitle[$iLang],
                    $pageContent[$iLang]
                );
                $iLang++;
            }

            $this->flashMessenger()->addMessage(['msg' => 'page updated!', 'html' => true], 'success');

            $cmsPage = $this->getDbService(\TueFind\Db\Service\CmsPagesServiceInterface::class)->getByIDFull($cmsPageId);

            $user = $this->getUser();

            $this->getDbService(\TueFind\Db\Service\CmsPagesHistoryServiceInterface::class)->add($cmsPageId, $user);
        }

        $view = $this->createViewModel();
        $view->langs = $langs;
        $view->cmsPage = $cmsPage;
        return $view;
    }

    public function deleteCMSPageAction()
    {
        $user = $this->getUser();
        if ($user == false) {
            return $this->forceLogin();
        }

        $cmsPageId = $this->params()->fromQuery('cms_page_id');

        $this->getDbService(\TueFind\Db\Service\CmsPagesServiceInterface::class)->delete($cmsPageId);
        $this->getDbService(\TueFind\Db\Service\CmsPagesTranslationServiceInterface::class)->delete($cmsPageId);

        $this->flashMessenger()->addMessage(['msg' => 'page deleted!', 'html' => true], 'success');

        return $this->redirect()->toUrl('/AdminFrontend/CMSPages');

    }

    public function CmsPagesAllHistoryAction() {
        $this->forceAdminLogin();
        $user = $this->getUser();
        $CMSPagesHistory = ['CMSPagesHistory' => $this->getDbService(\TueFind\Db\Service\CmsPagesHistoryServiceInterface::class)->getAll()];

        return $this->createViewModel($CMSPagesHistory);
    }

    public function CmsPagesHistoryAction() {
        $this->forceAdminLogin();

        $cmsPageId = $this->params()->fromQuery('cms_page_id');

        $CMSPages =  $this->getDbService(\TueFind\Db\Service\CmsPagesServiceInterface::class)->getByIDFull($cmsPageId);

        $CMSPagesHistory = $this->getDbService(\TueFind\Db\Service\CmsPagesHistoryServiceInterface::class)->getByPageID($cmsPageId);

        return $this->createViewModel([
            'CMSPage' => $CMSPages,
            'CMSPagesHistory' => $CMSPagesHistory
        ]);
    }

}
