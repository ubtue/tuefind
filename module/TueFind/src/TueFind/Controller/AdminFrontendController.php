<?php

namespace TueFind\Controller;

use function count;

/**
 * This Controller cannot be named "AdminController" because it would conflict
 * with VuFindAdmin\Controller\AdminController, which is for
 * Backend administration, so we call this one AdminFrontendController instead.
 */
class AdminFrontendController extends \VuFind\Controller\AbstractBase
{
    use Feature\CmsTrait;

    protected function forceAdminLogin()
    {
        $user = $this->getUser();
        if ($user == false) {
            throw new \Exception('You must be logged in first');
        }

        if ($user->getTueFindRights() == []) {
            throw new \Exception('This user has no admin rights!');
        }
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

            $mailer->send($receivers, $config->Site->email, $this->translate('authority_access_email_subject_' . $accessInfo), $message);
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
        $emailPathTemplate = 'Email/' . $requestUserLanguage . '/authority-request-access-' . $accessInfo . '.phtml';
        $fullEmailPathTemplate =  $_SERVER['VUFIND_HOME'] . '/themes/tuefind/templates/' . $emailPathTemplate;

        if (!file_exists($fullEmailPathTemplate)) {
            $config = $this->serviceLocator->get(\VuFind\Config\PluginManager::class)->get('config');
            $defaultEmailLanguage = $config->Site->language;
            $emailPathTemplate = 'Email/' . $defaultEmailLanguage . '/authority-request-access-' . $accessInfo . '.phtml';
        }

        return $emailPathTemplate;
    }

    public function showUserAuthorityHistoryAction()
    {
        $this->forceAdminLogin();

        return $this->createViewModel(['user_authority_history_datas' => $this->getDbService(\TueFind\Db\Service\UserAuthorityHistoryServiceInterface::class)->getAll()]);
    }

    public function showUserPublicationStatisticsAction()
    {
        $this->forceAdminLogin();

        return $this->createViewModel(['publications' => $this->getDbService(\TueFind\Db\Service\PublicationServiceInterface::class)->getStatistics()]);
    }

    public function assetAction()
    {
        $relativePath = $this->params()->fromRoute('relative_path');

        $config = $this->serviceLocator->get(\VuFind\Config\PluginManager::class)->get('tuefind');

        $allowedBase = $config->CMS->repository_path;

        $fullPath = $allowedBase . $relativePath;

        $realFullPath = realpath($fullPath);
        $realAllowedBase = realpath($allowedBase);

        if (
            empty($fullPath) ||
            !$realFullPath ||
            !$realAllowedBase ||
            !str_starts_with($realFullPath, $realAllowedBase) ||
            !is_file($realFullPath) ||
            !file_exists($realFullPath)
        ) {
            $response = $this->getResponse();
            $response->setStatusCode(404);
            return $response;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $realFullPath);
        finfo_close($finfo);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($realFullPath));
        header('Cache-Control: public, max-age=86400');

        readfile($realFullPath);
        exit;
    }
}
