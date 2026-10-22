<?php

namespace TueFind\Db\Service;

use DateTime;
use TueFind\Db\Entity\CmsPages;
use TueFind\Db\Entity\CmsPagesHistory;
use TueFind\Db\Entity\CmsPagesHistoryEntityInterface;
use TueFind\Db\Entity\Subsystems;
use TueFind\Db\Entity\User;
use VuFind\Db\Service\AbstractDbService;

class CmsPagesHistoryService extends AbstractDbService implements CmsPagesHistoryServiceInterface
{
    public function getByID(int $id): ?CmsPagesHistoryEntityInterface
    {
        return $this->entityManager->find(CmsPagesHistoryEntityInterface::class, $id);
    }

    public function getAllBySubsystem(Subsystems $subsystem): array
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('ch')
            ->from(CmsPagesHistory::class, 'ch')
            ->leftJoin('ch.cmsPage', 'cms')
            ->leftJoin('ch.user', 'u')
            ->where('ch.subSystem = :subsystem')
            ->setParameter('subsystem', $subsystem)
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

    public function add(?int $cmsPageId, User $user = null, Subsystems $subsystem, string $comment = ''): CmsPagesHistory
    {
        $cmsHistory = new CmsPagesHistory();
        if ($cmsPageId !== null) {
            $cmsHistory->setCmsPage($this->entityManager->find(CmsPages::class, $cmsPageId));
        }
        $cmsHistory->setUser($user);
        $cmsHistory->setSubSystem($subsystem);
        $cmsHistory->setComment($comment);
        $cmsHistory->setCreated(new DateTime());

        try {
            $this->entityManager->persist($cmsHistory);
            $this->entityManager->flush();
            return $cmsHistory;
        } catch (\Exception $e) {
            error_log('Failed to save CMS history: ' . $e->getMessage());
            throw $e;
        }
    }
}
