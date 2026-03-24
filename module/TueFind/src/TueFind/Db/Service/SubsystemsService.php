<?php

namespace TueFind\Db\Service;

use VuFind\Db\Service\AbstractDbService;
use TueFind\Db\Entity\CmsPages;
use TueFind\Db\Entity\Subsystems;
use TueFind\Db\Entity\SubsystemsEntityInterface;

class SubsystemsService extends AbstractDbService implements SubsystemsServiceInterface
{
    
    public function getByID(int $id): ?SubsystemsEntityInterface
    {
        return $this->entityManager->find(SubsystemsEntityInterface::class, $id);
    }

    public function getAll(): array
    {
        $dql = 'SELECT s
                FROM ' . Subsystems::class . ' s
                ORDER BY s.id DESC';
        $query = $this->entityManager->createQuery($dql);
        
        return  $query->getArrayResult();
    }
}
