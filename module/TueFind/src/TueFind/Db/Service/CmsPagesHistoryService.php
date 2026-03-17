<?php

namespace TueFind\Db\Service;

use DateTime;
use TueFind\Db\Entity\CmsPagesHistory;
use TueFind\Db\Entity\CmsPagesHistoryEntityInterface;
use VuFind\Db\Service\AbstractDbService;
use Doctrine\ORM\Query\Expr\Join;
use TueFind\Db\Entity\CmsPages;
use TueFind\Db\Entity\User;

class CmsPagesHistoryService extends AbstractDbService implements  CmsPagesHistoryServiceInterface
{
    
    public function getById(int $id): ?CmsPagesHistoryEntityInterface
    {
        return $this->entityManager->find(CmsPagesHistoryEntityInterface::class, $id);
    }

    public function getCmsHistory(): array {

        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('ch', 'cms', 'user', 'subsystem')
            ->from(CmsPagesHistory::class, 'ch')
            ->leftJoin('ch.cmsPage', 'cms')
            ->leftJoin('ch.user', 'user')
            ->leftJoin('cms.subsystem', 'subsystem')
            ->orderBy('ch.id', 'DESC');

        return $qb->getQuery()->getArrayResult();
    }

    public function getCmsHistoryByPageId(int $cmsPageId): array {
        
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('ch', 'cms', 'user')
            ->from(CmsPagesHistory::class, 'ch')
            ->leftJoin('ch.cmsPage', 'cms')
            ->leftJoin('ch.user', 'user')
            ->where('cms.id = :cmsId')
            ->setParameter('cmsId', $cmsPageId)
            ->orderBy('ch.id', 'DESC');

        return $qb->getQuery()->getArrayResult();
     }

     public function addCMSPageHistory(int $cmsPageId, User $user): bool
     {
         $cmsHistory = new CmsPagesHistory();
         $cmsHistory->setCmsPage($this->entityManager->find(CmsPages::class, $cmsPageId));
         $cmsHistory->setUser($user);
         $cmsHistory->setCreated(new DateTime());
 
         try {
             $this->entityManager->persist($cmsHistory);
             $this->entityManager->flush();
             return true;
         } catch (\Exception $e) {
             // Log the exception or handle it as needed
             return false;
         }
     }
}
