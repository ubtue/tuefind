<?php

namespace TueFind\Db\Service;

use TueFind\Db\Entity\UserAuthorityEntityInterface;
use TueFind\Db\Entity\UserEntityInterface;
use VuFind\Db\Service\AbstractDbService;

class UserAuthorityService extends AbstractDbService implements UserAuthorityServiceInterface
{
    protected function createEntity(): UserAuthorityEntityInterface
    {
        return $this->entityPluginManager->get(UserAuthorityEntityInterface::class);
    }

    public function getAll(): array
    {
        $dql = 'SELECT ua '
            . 'FROM ' . UserAuthorityEntityInterface::class . ' ua '
            . 'JOIN ua.user u '
            . 'ORDER BY u.username ASC, ua.authorityControlNumber ASC ';

        $query = $this->entityManager->createQuery($dql);
        return $query->getResult();
    }

    public function hasGrantedAuthorityRight(UserEntityInterface $user, string $authorityControlNumber): bool
    {
        $dql = 'SELECT ua '
            . 'FROM ' . UserAuthorityEntityInterface::class . ' ua '
            . 'WHERE ua.user = :user '
            . 'AND ua.authorityControlNumber = :authorityControlNumber '
            . 'AND ua.accessState = :accessState ';

        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('authorityControlNumber', $authorityControlNumber);
        $query->setParameter('accessState', 'granted');
        return ($query->getOneOrNullResult() != null);
    }

    public function getAllByUserAccessState(UserEntityInterface $user, $accessState=null): array
    {
        $dql = 'SELECT ua '
            . 'FROM ' . UserAuthorityEntityInterface::class . ' ua '
            . 'WHERE ua.user = :user ';

        $parameters = [
            'user' => $user,
        ];

        if (isset($accessState)) {
            $dql .= 'AND ua.accessState = :accessState ';
            $parameters['accessState'] = $accessState;
        }

        $query = $this->entityManager->createQuery($dql);
        $query->setParameters($parameters);
        $results = $query->getResult();
        return $results;
    }

    public function getByUser(UserEntityInterface $user): array
    {
        $dql = 'SELECT ua '
            . 'FROM ' . UserAuthorityEntityInterface::class . ' ua '
            . 'WHERE ua.user = :user ';

        $parameters = [
            'user' => $user,
        ];

        $query = $this->entityManager->createQuery($dql);
        $query->setParameters($parameters);
        return $query->getResult();
    }

    public function getByAuthorityControlNumber($authorityControlNumber): ?UserAuthorityEntityInterface
    {
        $dql = 'SELECT ua '
            . 'FROM ' . UserAuthorityEntityInterface::class . ' ua '
            . 'WHERE ua.authorityControlNumber = :authorityControlNumber ';

        $query = $this->entityManager->createQuery($dql);
        $query->setParameters(['authorityControlNumber' => $authorityControlNumber]);
        return $query->getOneOrNullResult();
    }

    public function getByUserAndAuthorityId(UserEntityInterface $user, $authorityControlNumber): ?UserAuthorityEntityInterface
    {
        $dql = 'SELECT ua '
            . 'FROM ' . UserAuthorityEntityInterface::class . ' ua '
            . 'WHERE ua.authorityControlNumber = :authorityControlNumber '
            . 'AND ua.user = :user ';

        $query = $this->entityManager->createQuery($dql);
        $query->setParameters(['authorityControlNumber' => $authorityControlNumber,
                               'user' => $user]
        );
        return $query->getOneOrNullResult();
    }

    public function addRequest(UserEntityInterface $user, $authorityControlNumber): UserAuthorityEntityInterface
    {
        $entity = $this->createEntity();
        $entity->setUser($user);
        $entity->setAuthorityControlNumber($authorityControlNumber);
        $entity->setAccessState('requested');
        $this->persistEntity($entity);
        return $entity;
    }

    public function updateAccessState(UserAuthorityEntityInterface $entity, $accessState)
    {
        $entity->setAccessState($accessState);
        $entity->setGrantedDateTime(new \DateTime());
        $this->persistEntity($entity);
    }
}
