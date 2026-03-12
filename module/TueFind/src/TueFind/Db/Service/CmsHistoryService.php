<?php

namespace TueFind\Db\Service;

use DateTime;
use TueFind\Db\Entity\CmsHistory;
use TueFind\Db\Entity\CmsHistoryEntityInterface;
use VuFind\Db\Service\AbstractDbService;
use Doctrine\ORM\Query\Expr\Join;
use TueFind\Db\Entity\CmsPages;
use TueFind\Db\Entity\User;

use function intval;


class CmsHistoryService extends AbstractDbService implements  CmsHistoryServiceInterface
{
    
    public function getById(int $id): ?CmsHistoryEntityInterface
    {
        return $this->entityManager->find(CmsHistoryEntityInterface::class, $id);
    }

    public function getCmsHistory(): array {
       $dql = 'SELECT ch, cms, user '
        . 'FROM ' . CmsHistory::class . ' ch '
        . 'LEFT JOIN ch.cmsPage cms '
        . 'LEFT JOIN ch.user user '
        . 'ORDER BY ch.id DESC';
        $query = $this->entityManager->createQuery($dql);
        return $query->getArrayResult();
    }

    public function getCmsHistoryByPageId(int $cms_page_id): array {
        $dql = 'SELECT ch, cms, user '
         . 'FROM ' . CmsHistory::class . ' ch '
         . 'LEFT JOIN ch.cmsPage cms '
         . 'LEFT JOIN ch.user user '
         . 'WHERE ch.cmsId = :cms_page_id '
         . 'ORDER BY ch.id DESC';
         $query = $this->entityManager->createQuery($dql);
         $query->setParameter('cms_page_id', $cms_page_id);
         return $query->getArrayResult();
     }

     public function addCMSPageHistory(int $cmsPageId, User $user): bool
     {
         $cmsHistory = new CmsHistory();
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
