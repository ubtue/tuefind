<?php

namespace TueFind\Db\Service;

use TueFind\Db\Entity\Subsystems;
use TueFind\Db\Entity\SubsystemsEntityInterface;
use VuFind\Db\Service\AbstractDbService;

class SubsystemsService extends AbstractDbService implements SubsystemsServiceInterface
{
    public function getByID(int $id): SubsystemsEntityInterface
    {
        return $this->entityManager->find(SubsystemsEntityInterface::class, $id);
    }

    public function getByName(string $name): SubsystemsEntityInterface
    {
        $dql = 'SELECT s '
            . 'FROM ' . SubsystemsEntityInterface::class . ' s '
            . 'WHERE s.subSystem = :name ';

        $parameters = [
            'name' => $name,
        ];

        $query = $this->entityManager->createQuery($dql);
        $query->setParameters($parameters);
        return $query->getSingleResult();
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
