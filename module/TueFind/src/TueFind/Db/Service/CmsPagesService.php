<?php

namespace TueFind\Db\Service;

use DateTime;
use TueFind\Db\Entity\CmsPages;
use TueFind\Db\Entity\CmsPagesEntityInterface;
use TueFind\Db\Entity\CmsPagesTranslation;
use VuFind\Db\Service\AbstractDbService;
use Doctrine\ORM\Query\Expr\Join;

use function intval;


class CmsPagesService extends AbstractDbService implements  CmsPagesServiceInterface
{
    
    public function getById(int $id): ?CmsPagesEntityInterface
    {
        return $this->entityManager->find(CmsPagesEntityInterface::class, $id);
    }

    public function getCmsPages(): array {
        $dql = 'SELECT cp '
            . 'FROM ' . CmsPages::class . ' cp '
            . 'ORDER BY cp.id DESC';
        $query = $this->entityManager->createQuery($dql);
        
        return  $query->getArrayResult();;
    }

    public function getCMSPageByID(int $cmsPageId): ?array
    {
        $dql = '
            SELECT cp, cpt
            FROM ' . CmsPages::class . ' cp
            LEFT JOIN cp.cmsPagesTranslations cpt
            WHERE cp.id = :id
        ';

        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('id', $cmsPageId);

        $result = $query->getArrayResult();

        if (empty($result)) {
            return null;
        }

        // базовая страница
        $page = $result[0];
        $page['translations'] = [];

        foreach ($result as $row) {
            if (!empty($row['cmsPagesTranslations'])) {
                foreach ($row['cmsPagesTranslations'] as $tr) {
                    $page['translations'][] = [
                        'language' => $tr['language'],
                        'title'    => $tr['title'],
                        'content'  => $tr['content'],
                    ];
                }
            }
        }

        return $page;
    }

    public function getCMSPageByPageSystemId(string $pageSystemId,string $language): ?array
    {
        $dql = '
            SELECT cp, cpt
            FROM ' . CmsPages::class . ' cp
            LEFT JOIN cp.cmsPagesTranslations cpt
            WHERE cp.pageSystemId = :pageSystemId 
            AND cpt.language = :language
        ';

        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('pageSystemId', $pageSystemId);
        $query->setParameter('language', $language);

        $result = $query->getArrayResult();

        if (empty($result)) {
            return null;
        }

        // базовая страница
        $page = $result[0];
        $page['translations'] = [];

        foreach ($result as $row) {
            if (!empty($row['cmsPagesTranslations'])) {
                $page['translations'] = [
                    'language' => $row['cmsPagesTranslations'][0]['language'],
                    'title'    => $row['cmsPagesTranslations'][0]['title'],
                    'content'  => $row['cmsPagesTranslations'][0]['content'],
                ];
            }
        }

        return $page;
    }

    public function addCMSPage(string $subSystem, string $pageSystemId, DateTime $createdDate, DateTime $changeDate): int
    {   
        $cmsPage = new CmsPages();
        $cmsPage->setSubsystem($subSystem);
        $cmsPage->setPageSystemId($pageSystemId);
        $cmsPage->setCreateDate($createdDate);
        $cmsPage->setChangeDate($changeDate);
        $this->entityManager->persist($cmsPage);
        $this->entityManager->flush();
        return $cmsPage->getId();
    }

    public function updateCMSPage(int $cmsPageId, string $subSystem,  string $pageSystemId): bool
    {
        // Validate input (adapt to your validation utilities)
        if (empty($cmsPageId) || empty($subSystem) || empty($pageSystemId)) {
            throw new \InvalidArgumentException('Invalid arguments for updateCMSPage');
        }

        // Load page (adapt to your table/service)
        $page = $this->entityManager->find(CmsPages::class, $cmsPageId);
        if (!$page) {
            throw new \RuntimeException("CMS page not found: $cmsPageId");
        }

        // Apply changes and persist (adapt to your persistence API)
        $page->setSubsystem($subSystem);
        $page->setPageSystemId($pageSystemId);
        
        $this->entityManager->persist($page);
        $this->entityManager->flush();

        return true;
    }

    public function deleteCMSPage(int $id): void
    {

        $CMSPage = $this->getById($id);

        if ($CMSPage === null) {
            throw new \Exception('Content page not found');
        }

        //$translations = $CMSPage->getTranslations();
        //foreach ($translations as $translation) {
        //    $this->deleteEntity($translation);
        //}

        $this->deleteEntity($CMSPage);
    }
}
