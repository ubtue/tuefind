<?php

namespace TueFind\Db\Service;

use DateTime;

use VuFind\Db\Service\AbstractDbService;
use TueFind\Db\Entity\CmsPages;
use TueFind\Db\Entity\CmsPagesEntityInterface;
use TueFind\Db\Service\CmsPagesServiceInterface;
use TueFind\Db\Entity\Subsystems;

class CmsPagesService extends AbstractDbService implements  CmsPagesServiceInterface
{
    public function getByID(int $id): ?CmsPagesEntityInterface
    {
        return $this->entityManager->find(CmsPagesEntityInterface::class, $id);
    }

    public function getAll(): array {

        $dql = 'SELECT cp, s
                FROM ' . CmsPages::class . ' cp
                LEFT JOIN cp.subSystem s
                ORDER BY cp.id DESC';
        $query = $this->entityManager->createQuery($dql);

        return  $query->getArrayResult();
    }

    public function getByPageSystemID(string $pageSystemId, string $subSystem): ?CmsPagesEntityInterface
    {

        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('cms', 'subSystem')
            ->from(CmsPages::class, 'cms')
            ->leftJoin('cms.subSystem', 'subSystem')
            ->where('cms.pageSystemId = :pageSystemId')
            ->andWhere('subSystem.subSystem = :subSystem')
            ->setParameter('pageSystemId', $pageSystemId)
            ->setParameter('subSystem', $subSystem);

        $result = $qb->getQuery()->getOneOrNullResult();
        return $result;
    }

    public function add(int $subSystemId, string $pageSystemId, DateTime $createdDate, DateTime $changeDate): int
    {
        $cmsPage = new CmsPages();

        $subSystem = $this->entityManager->find(Subsystems::class, $subSystemId);
        if (!$subSystem) {
            throw new \RuntimeException("Subsystem not found");
        }

        $cmsPage->setSubSystem($subSystem);
        $cmsPage->setPageSystemId($pageSystemId);
        $cmsPage->setCreateDate($createdDate);
        $cmsPage->setChangeDate($changeDate);

        $this->entityManager->persist($cmsPage);
        $this->entityManager->flush();

        return $cmsPage->getId();
    }

    public function update(int $cmsPageId, DateTime $dateModified): CmsPages
    {

        // Load page (adapt to your table/service)
        $page = $this->entityManager->find(CmsPages::class, $cmsPageId);
        if (!$page) {
            throw new \RuntimeException("CMS page not found: $cmsPageId");
        }
        $page->setChangeDate($dateModified);
        $this->entityManager->persist($page);
        $this->entityManager->flush();

        return $page;
    }

    public function delete(int $id): void
    {

        $CMSPage = $this->getById($id);

        if ($CMSPage === null) {
            throw new \Exception('Content page not found');
        }

        $this->deleteEntity($CMSPage);
    }
}
