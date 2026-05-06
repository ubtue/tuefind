<?php

namespace TueFind\Controller;

use Laminas\View\Model\ViewModel;
use TueFind\Db\Entity\CmsPagesTranslationEntityInterface;
use VuFind\I18n\Locale\LocaleSettings;

class ContentController extends \VuFind\Controller\ContentController
{

    public function contentAction()
    {
        $pathPrefix = 'templates/content/'; // override path prefix to always use content, since the cms page content is determined by the database entry, not the template name
        $page = $this->params()->fromRoute('page');
        $this->setTranslator($this->serviceLocator->get(\Laminas\Mvc\I18n\Translator::class));
        $localeSettings = $this->serviceLocator->get(LocaleSettings::class);
        $language = $localeSettings->getUserLocale();
        $defaultLanguage = $localeSettings->getDefaultLocale();
        $subSystem = $this->serviceLocator->get('ViewHelperManager')->get('tuefind')->getTueFindInstance();

        $cmsPage = $this->getDbService(\TueFind\Db\Service\CmsPagesServiceInterface::class)->getByPageSystemId($page, $subSystem);
        $cmsPageTranslation = $cmsPage->getTranslation($language);
        if ($cmsPageTranslation == null) {
            $cmsPageTranslation = $cmsPage->getTranslation($defaultLanguage);
        }

        // Path regex should prevent dots, but double-check to make sure:
        if (str_contains($page, '..')) {
            return $this->notFoundAction();
        }
        // Find last slash and add preceding part to path if found:
        if (false !== ($p = strrpos($page, '/'))) {
            $subPath = substr($page, 0, $p + 1);
            $pathPrefix .= $subPath;
            // Ensure the path prefix does not contain extra slashes:
            if (str_ends_with($pathPrefix, '//')) {
                return $this->notFoundAction();
            }
            $page = substr($page, $p + 1);
        }
        $pageLocator = $this->getService(\VuFind\Content\PageLocator::class);
        // If a CMS page is found, override path prefix and page to ensure the correct template is used.
        // The template name is not determined by the URL, but by the database entry for the CMS page.
        if ($cmsPage) {
            $pathPrefix = 'templates/content/cmspage/';
            $page = 'main';
        }

        $data = $pageLocator->determineTemplateAndRenderer($pathPrefix, $page);

        if ($cmsPageTranslation && isset($data)) {
            $data['cmspage'] = $cmsPage;
            $data['renderer'] = 'CmsPage';
            return $this->getViewForCmsPage($data['page'], $data['relativePath'], $data['path'], $cmsPageTranslation);
        }

        $method = isset($data) ? 'getViewFor' . ucwords($data['renderer']) : false;

        // This was not possible using apache settings, so unfortunately we need to hardcode it here.
        // Some servers use a different default referrer policy, but we need to reset it on certain pages
        // else e.g. there will be problems using OpenStreetMap.
        $this->getResponse()->getHeaders()->addHeaderLine('Referrer-Policy', 'origin-when-cross-origin');

        return $method && is_callable([$this, $method])
            ? $this->$method($data['page'], $data['relativePath'], $data['path'])
            : $this->notFoundAction();

    }

    protected function getViewForCmsPage(string $page, string $relPath, string $path, CmsPagesTranslationEntityInterface $cmsPagesTranslation): ViewModel
    {
        // Convert relative path to a relative page name:
        $relPage = $relPath;
        if (str_starts_with($relPage, 'content/')) {
            $relPage = substr($relPage, 8);
        }
        if (str_ends_with($relPage, '.phtml')) {
            $relPage = substr($relPage, 0, -6);
        }
        // Prevent circular inclusion:
        if ('content' === $relPage) {
            return $this->notFoundAction();
        }
        $view = $this->createViewModel([
            'page' => $relPage,
            'cmsPagesTranslation' => $cmsPagesTranslation,
        ]);
        $view->setTemplate('content/cmspage/main');
        return $view;
    }


}
