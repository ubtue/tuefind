<?php

namespace TueFind\Db\Service;

use TueFind\Db\Entity\UserAuthorityHistoryEntityInterface;
use TueFind\Db\Entity\UserEntityInterface;
use VuFind\Db\Service\AbstractDbService;

class UserAuthorityHistoryService extends AbstractDbService implements UserAuthorityHistoryServiceInterface
{
    protected function createEntity(): UserAuthorityHistoryEntityInterface
    {
        return $this->entityPluginManager->get(UserAuthorityHistoryEntityInterface::class);
    }

    public function getLatestRequestByUser(UserEntityInterface $user): ?UserAuthorityHistoryEntityInterface
    {
        $dql = 'SELECT uah '
            . 'FROM ' . UserAuthorityHistoryEntityInterface::class . ' uah '
            . 'WHERE uah.user = :user '
            . 'ORDER BY uah.requestUserDate DESC ';

        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setMaxResults(1);
        return $query->getOneOrNullResult();
    }

    public function getAll(): array
    {
        $dql = 'SELECT uah '
            . 'FROM ' . UserAuthorityHistoryEntityInterface::class . ' uah '
            . 'WHERE uah.processAdminDate IS NOT NULL '
            . 'ORDER BY uah.requestUserDate DESC ';

        $query = $this->entityManager->createQuery($dql);
        return $query->getResult();
    }

    public function addRequest(UserEntityInterface $user, $authorityControlNumber): UserAuthorityHistoryEntityInterface
    {
        $entity = $this->createEntity();
        $entity->setUser($user);
        $entity->setAuthorityControlNumber($authorityControlNumber);
        $entity->setAccessState('requested');
        $this->persistEntity($entity);
        return $entity;
    }

    public function updateHistoryEntry(UserAuthorityHistoryEntityInterface $historyEntry, UserEntityInterface $admin, $accessState)
    {
        $historyEntry->setAdmin($admin);
        $historyEntry->setAccessState($accessState);
        $historyEntry->setProcessAdminDate(new \DateTime());
        $this->persistEntity($historyEntry);
    }
}
