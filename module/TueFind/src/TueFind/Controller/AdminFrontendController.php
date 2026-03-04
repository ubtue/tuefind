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

     public function showCMSMainAction() {
        $this->forceAdminLogin();

        $allCMS = ['allCMSPages' => $this->getDbService(\TueFind\Db\Service\CmsPagesServiceInterface::class)->getCmsPages()];

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

        $subsystem = $this->serviceLocator->get('ViewHelperManager')->get('tuefind')->getAllTueFindSubsystems();

        $action = $this->params()->fromPost('action');

        $page_content = $this->params()->fromPost('page_content');
        $page_title = $this->params()->fromPost('page_title');

        if ($action == 'publish') {

            $cms_page_id = $this->getDbService(\TueFind\Db\Service\CmsPagesServiceInterface::class)->addCMSPage(
                $this->params()->fromPost('sybsystem'),
                $this->params()->fromPost('page_system_id'),
                new \DateTime(),
                new \DateTime()
            );

            if (!$cms_page_id) {
                throw new \RuntimeException('CMS page was not created');
            }

            $iLang=0;
            foreach ($langs as $key=>$name) {
                $this->getDbService(\TueFind\Db\Service\CmsPagesTranslationServiceInterface::class)->addCMSPageTranslation(
                    $cms_page_id,
                    $key,
                    $page_title[$iLang],
                    $page_content[$iLang]
                );
                $iLang++;
            }

            $this->flashMessenger()->addMessage(['msg' => 'page created!', 'html' => true], 'success');
            $this->redirect()->toUrl('/AdminFrontend/ShowCMSMain');
        }

        $view = $this->createViewModel();
        $view->langs = $langs;
        $view->subsystem = $subsystem;
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
        $cms_page_id = $this->params()->fromQuery('cms_page_id');
        $page_content = $this->params()->fromPost('page_content');
        $page_title = $this->params()->fromPost('page_title');

        $cmsPage = $this->getDbService(\TueFind\Db\Service\CmsPagesServiceInterface::class)->getCMSPageByID($cms_page_id);

        $subsystem = $this->serviceLocator->get('ViewHelperManager')->get('tuefind')->getAllTueFindSubsystems();

        if ($action == 'update') {

            $result = $this->getDbService(\TueFind\Db\Service\CmsPagesServiceInterface::class)->updateCMSPage(
                $cms_page_id,
                $cmsPage['subSystem'],
                $cmsPage['pageSystemId']
            );
            $result = $this->getDbService(\TueFind\Db\Service\CmsPagesTranslationServiceInterface::class)->deleteCMSPageTranslation($cms_page_id);

            $iLang=0;
            foreach ($langs as $key=>$name) {
                $this->getDbService(\TueFind\Db\Service\CmsPagesTranslationServiceInterface::class)->addCMSPageTranslation(
                    $cms_page_id,
                    $key,
                    $page_title[$iLang],
                    $page_content[$iLang]
                );
                $iLang++;
            }

            $this->flashMessenger()->addMessage(['msg' => 'page updated!', 'html' => true], 'success');

            $cmsPage = $this->getDbService(\TueFind\Db\Service\CmsPagesServiceInterface::class)->getCMSPageByID($cms_page_id);

            $user = $this->getUser();

            $this->getDbService(\TueFind\Db\Service\CmsHistoryServiceInterface::class)->addCMSPageHistory($cms_page_id, $user);
        }

        $view = $this->createViewModel();
        $view->langs = $langs;
        $view->cmsPage = $cmsPage;
        $view->subsystem = $subsystem;
        return $view;
    }

    public function deleteCMSPageAction()
    {
        $user = $this->getUser();
        if ($user == false) {
            return $this->forceLogin();
        }

        $cms_page_id = $this->params()->fromQuery('cms_page_id');

        $this->getDbService(\TueFind\Db\Service\CmsPagesServiceInterface::class)->deleteCMSPage($cms_page_id);
        $this->getDbService(\TueFind\Db\Service\CmsPagesTranslationServiceInterface::class)->deleteCMSPageTranslation($cms_page_id);

        $this->flashMessenger()->addMessage(['msg' => 'page deleted!', 'html' => true], 'success');

        return $this->redirect()->toUrl('/AdminFrontend/ShowCMSMain');

    }

    public function showAllCMSHistoryAction() {
        $this->forceAdminLogin();
        $user = $this->getUser();
        $CMSPagesHistory = ['CMSPagesHistory' => $this->getDbService(\TueFind\Db\Service\CmsHistoryServiceInterface::class)->getCmsHistory()];
        return $this->createViewModel($CMSPagesHistory);
    }

    public function showCMSHistoryAction() {
        $this->forceAdminLogin();

        $cms_page_id = $this->params()->fromQuery('cms_page_id');

        $CMSPages =  $this->getDbService(\TueFind\Db\Service\CmsPagesServiceInterface::class)->getCMSPageByID($cms_page_id);

        $CMSPagesHistory = $this->getDbService(\TueFind\Db\Service\CmsHistoryServiceInterface::class)->getCmsHistoryByPageId($cms_page_id);

        return $this->createViewModel([
            'CMSPage' => $CMSPages,
            'CMSPagesHistory' => $CMSPagesHistory
        ]);
    }

}
