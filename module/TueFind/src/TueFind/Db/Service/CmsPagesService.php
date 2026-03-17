<?php

namespace TueFind\Db\Service;

use DateTime;
use TueFind\Db\Entity\CmsPages;
use TueFind\Db\Entity\CmsPagesEntityInterface;
use TueFind\Db\Entity\CmsPagesTranslation;
use VuFind\Db\Service\AbstractDbService;
use Doctrine\ORM\Query\Expr\Join;
use TueFind\Db\Entity\CmsPagesSubsystem;

use function intval;


class CmsPagesService extends AbstractDbService implements  CmsPagesServiceInterface
{
    
    public function getById(int $id): ?CmsPagesEntityInterface
    {
        return $this->entityManager->find(CmsPagesEntityInterface::class, $id);
    }

    public function getCmsPages(): array {
        $dql = 'SELECT cp, cpt '
            . 'FROM ' . CmsPages::class . ' cp '
            . 'LEFT JOIN cp.subsystem cpt '
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
            ORDER BY cpt.id ASC
        ';

        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('id', $cmsPageId);

        $result = $query->getArrayResult();

        if (empty($result)) {
            return null;
        }

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

    public function getCMSPageByPageSystemId(string $pageSystemId, string $subSystem, string $language): ?array
    {

        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('cms', 'subsystem', 'cpt')
            ->from(CmsPages::class, 'cms')
            ->leftJoin('cms.cmsPagesTranslations', 'cpt', Join::WITH, 'cpt.language = :language')
            ->leftJoin('cms.subsystem', 'subsystem')
            ->where('cms.pageSystemId = :pageSystemId')
            ->andWhere('subsystem.subsystem = :subSystem')
            ->setParameter('language', $language)
            ->setParameter('pageSystemId', $pageSystemId)
            ->setParameter('subSystem', $subSystem);

        $result = $qb->getQuery()->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        if (empty($result) || empty($result['cmsPagesTranslations'][0]['title'])) {
            return null;
        }

        return $result;
    }

    public function addCMSPage(string $pageSystemId, DateTime $createdDate, DateTime $changeDate): int
    {   
        $cmsPage = new CmsPages();
        $cmsPage->setPageSystemId($pageSystemId);
        $cmsPage->setCreateDate($createdDate);
        $cmsPage->setChangeDate($changeDate);
        $this->entityManager->persist($cmsPage);
        $this->entityManager->flush();
        return $cmsPage->getId();
    }

    public function updateCMSPage(int $cmsPageId, DateTime $dateModified): bool
    {

        // Load page (adapt to your table/service)
        $page = $this->entityManager->find(CmsPages::class, $cmsPageId);
        if (!$page) {
            throw new \RuntimeException("CMS page not found: $cmsPageId");
        }
        $page->setChangeDate($dateModified);
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

        $this->deleteEntity($CMSPage);
    }
}
