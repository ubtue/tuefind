<?php

namespace TueFind\Controller\Feature;

/**
 * This has been part of the AdminFrontendController for a long time,
 * but due to the increasing amount of functions, we decided to move them
 * into a separate trait for better maintainability.
 */
trait CmsTrait
{
    public function CMSPagesAction()
    {
        $this->forceAdminLogin();

        $subsystem = $this->getDbService(\TueFind\Db\Service\SubsystemsServiceInterface::class)->getByName(\IxTheo\Utility::getUserTypeFromUsedEnvironment());
        $cmsPages = $subsystem->getCmsPages();

        $allCMS = ['cmsPages' => $cmsPages,
                  'cmsSync' => $this->serviceLocator->get(\TueFind\Service\CmsSync::class),
                  'user' => $this->getUser(),
        ];

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

        $action = $this->params()->fromPost('action');

        $pageContent = $this->params()->fromPost('page_content');
        $pageTitle = $this->params()->fromPost('page_title');
        if ($action == 'publish') {
            $subsystem = $this->getDbService(\TueFind\Db\Service\SubsystemsServiceInterface::class)->getByName(\IxTheo\Utility::getUserTypeFromUsedEnvironment());
            $cmsPageId = $this->getDbService(\TueFind\Db\Service\CmsPagesServiceInterface::class)->add(
                $subsystem->getId(),
                $this->params()->fromPost('page_system_id'),
                new \DateTime(),
                new \DateTime()
            );

            if (!$cmsPageId) {
                throw new \RuntimeException('CMS page was not created');
            }

            $iLang = 0;
            foreach ($langs as $key => $name) {
                if ($pageTitle[$iLang] != '') {
                    $this->getDbService(\TueFind\Db\Service\CmsPagesTranslationServiceInterface::class)->add(
                        $cmsPageId,
                        $key,
                        $pageTitle[$iLang],
                        $this->replaceSpecialCharsForSummernote($pageContent[$iLang])
                    );
                }
                $iLang++;
            }

            $this->flashMessenger()->addMessage(['msg' => 'page created!', 'html' => true], 'success');
            $this->redirect()->toUrl('/AdminFrontend/CMSPages');
        }

        $view = $this->createViewModel();
        $view->langs = $langs;
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
        $cmsPageId = $this->params()->fromRoute('cms_page_id');
        $pageContents = $this->params()->fromPost('page_content');
        $pageTitles = $this->params()->fromPost('page_title');

        $cmsPage = $this->getDbService(\TueFind\Db\Service\CmsPagesServiceInterface::class)->getByID($cmsPageId);

        if ($action == 'update') {
            $iLang = 0;
            foreach ($langs as $key => $name) {
                $pageTitle = $pageTitles[$iLang];
                $pageContent = $this->replaceSpecialCharsForSummernote($pageContents[$iLang]);
                $existingTranslation = $cmsPage->getTranslation($key);
                if ($existingTranslation == null && $pageTitle != '') {
                    // add
                    $this->getDbService(\TueFind\Db\Service\CmsPagesTranslationServiceInterface::class)->add(
                        $cmsPageId,
                        $key,
                        $pageTitle,
                        $pageContent
                    );
                } elseif ($existingTranslation != null) {
                    if ($pageTitle != '') {
                        // update
                        $this->getDbService(\TueFind\Db\Service\CmsPagesServiceInterface::class)->update($cmsPageId, new \DateTime());
                        $existingTranslation->setTitle($pageTitle);
                        $existingTranslation->setContent($pageContent);
                        $this->getDbService(\TueFind\Db\Service\CmsPagesTranslationServiceInterface::class)->save($existingTranslation);
                    } else {
                        // delete
                        $this->getDbService(\TueFind\Db\Service\CmsPagesTranslationServiceInterface::class)->delete($cmsPageId, $key);
                    }
                }
                $iLang++;
            }

            $this->flashMessenger()->addMessage(['msg' => 'page updated!', 'html' => true], 'success');

            $cmsPage = $this->getDbService(\TueFind\Db\Service\CmsPagesServiceInterface::class)->getByID($cmsPageId);

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

        $cmsPageId = $this->params()->fromRoute('cms_page_id');

        $this->getDbService(\TueFind\Db\Service\CmsPagesServiceInterface::class)->delete($cmsPageId);
        $this->getDbService(\TueFind\Db\Service\CmsPagesTranslationServiceInterface::class)->delete($cmsPageId);

        $this->flashMessenger()->addMessage(['msg' => 'page deleted!', 'html' => true], 'success');

        return $this->redirect()->toUrl('/AdminFrontend/CMSPages');
    }

    public function CmsPagesAllHistoryAction()
    {
        $this->forceAdminLogin();

        $CMSPagesHistory = ['CMSPagesHistory' => $this->getDbService(\TueFind\Db\Service\CmsPagesHistoryServiceInterface::class)->getAllBySubsystemName(\IxTheo\Utility::getUserTypeFromUsedEnvironment())];
        return $this->createViewModel($CMSPagesHistory);
    }

    public function CmsPagesHistoryAction()
    {
        $this->forceAdminLogin();

        $cmsPageId = $this->params()->fromRoute('cms_page_id');
        $CMSPages =  $this->getDbService(\TueFind\Db\Service\CmsPagesServiceInterface::class)->getByID($cmsPageId);
        return $this->createViewModel([
            'CMSPage' => $CMSPages,
        ]);
    }

    public function CmsPagesDocsAction()
    {
        $this->forceAdminLogin();

        $subSystem = $this->getDbService(\TueFind\Db\Service\SubsystemsServiceInterface::class)->getAll();
        //$user_type = $user->getUserType(); for now we do not have different user types, but in the future we might want to use this to determine if a user has access to certain subsystems or not

        return $this->createViewModel([
            'subSystem' => $subSystem,
        ]);
    }

    public function CmsPagesFilesAction()
    {
        $this->forceAdminLogin();

        $subSystem = $this->getDbService(\TueFind\Db\Service\SubsystemsServiceInterface::class)->getAll();
        //$user_type = $user->getUserType(); for now we do not have different user types, but in the future we might want to use this to determine if a user has access to certain subsystems or not

        return $this->createViewModel([
            'subSystem' => $subSystem,
        ]);
    }

    public function CmsPagesImagesAction()
    {
        $this->forceAdminLogin();

        $subSystem = $this->getDbService(\TueFind\Db\Service\SubsystemsServiceInterface::class)->getAll();
        //$user_type = $user->getUserType(); for now we do not have different user types, but in the future we might want to use this to determine if a user has access to certain subsystems or not

        return $this->createViewModel([
            'subSystem' => $subSystem,
        ]);
    }

    protected function replaceSpecialCharsForSummernote($content)
    {
        // Note: This must be in sync with changes done within the JS callback when switching out of the editor preview, see updatecmspage.phtml,
        //       as well as the command parser in the TueFind View helper.
        return preg_replace_callback('/\{\{[\s\S]*?\}\}/s', function ($matches) {
            // replace &gt; with > inside the matched content
            return str_replace('&gt;', '>', $matches[0]);
        }, $content);
    }
}
