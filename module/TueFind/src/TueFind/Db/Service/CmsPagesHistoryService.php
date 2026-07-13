<?php

namespace TueFind\Db\Service;

use DateTime;
use VuFind\Db\Service\AbstractDbService;
use TueFind\Db\Entity\CmsPagesHistory;
use TueFind\Db\Entity\CmsPagesHistoryEntityInterface;
use TueFind\Db\Entity\CmsPages;
use TueFind\Db\Entity\User;

class CmsPagesHistoryService extends AbstractDbService implements  CmsPagesHistoryServiceInterface
{

    public function getByID(int $id): ?CmsPagesHistoryEntityInterface
    {
        return $this->entityManager->find(CmsPagesHistoryEntityInterface::class, $id);
    }

    public function getAllBySubsystemId(int $subsystemId): array
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('ch')
            ->from(CmsPagesHistoryEntityInterface::class, 'ch')
            ->leftJoin('ch.cmsPage', 'cms')
            ->leftJoin('cms.subSystem', 'ss')
            ->where('ss.id = :subsystemId')
            ->setParameter('subsystemId', $subsystemId)
            ->orderBy('ch.id', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function getAllBySubsystemName(string $subsystemName): array
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('ch')
            ->from(CmsPagesHistoryEntityInterface::class, 'ch')
            ->leftJoin('ch.cmsPage', 'cms')
            ->leftJoin('cms.subSystem', 'ss')
            ->where('ss.subSystem = :subsystemName')
            ->setParameter('subsystemName', $subsystemName)
            ->orderBy('ch.id', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function getByPageID(int $cmsPageId): array
    {

        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('ch', 'cms', 'user')
            ->from(CmsPagesHistoryEntityInterface::class, 'ch')
            ->leftJoin('ch.cmsPage', 'cms')
            ->leftJoin('ch.user', 'user')
            ->where('cms.id = :cmsId')
            ->setParameter('cmsId', $cmsPageId)
            ->orderBy('ch.id', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function add(int $cmsPageId, User $user): CmsPagesHistory
    {
        $cmsHistory = new CmsPagesHistory();
        $cmsHistory->setCmsPage($this->entityManager->find(CmsPages::class, $cmsPageId));
        $cmsHistory->setUser($user);
        $cmsHistory->setCreated(new DateTime());

        try {
            $this->entityManager->persist($cmsHistory);
            $this->entityManager->flush();
            return $cmsHistory;
        } catch (\Exception $e) {
            // Log the exception or handle it as needed
            return $cmsHistory; // Return the history object even if it failed to save, or consider throwing an exception
        }
    }
}
