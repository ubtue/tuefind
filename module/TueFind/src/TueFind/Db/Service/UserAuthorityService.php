<?php

namespace TueFind\Db\Service;

use TueFind\Db\Entity\UserAuthorityEntityInterface;
use TueFind\Db\Entity\UserEntityInterface;
use VuFind\Db\Service\AbstractDbService;

class UserAuthorityService extends AbstractDbService implements UserAuthorityHistoryServiceInterface
{

    public function getAll()
    {
        $dql = 'SELECT ua '
            . 'FROM ' . UserAuthorityEntityInterface::class . ' ua '
            . 'JOIN ua.user u '
            . 'ORDER BY u.username ASC, ua.authorityControlNumber ASC ';

        $query = $this->entityManager->createQuery($dql);
        return $query->getResult();
    }

    public function hasGrantedAuthorityRight($userId, $authorityIds): bool
    {
        $select = $this->getSql()->select();
        $where = new \Laminas\Db\Sql\Where();
        $where->in("authority_id", $authorityIds);
        $where->equalTo('user_id', $userId);
        $where->equalTo('access_state', 'granted');
        $select->where($where);

        $rows = $this->selectWith($select);
        return count($rows) > 0;
    }

    public function getByUserId(UserEntityInterface|int $userOrId, $accessState=null): array
    {
        $dql = 'SELECT ua '
            . 'FROM ' . UserAuthorityEntityInterface::class . ' ua '
            . 'WHERE ua.user = :user ';

        $parameters = [
            'user' => $this->getDoctrineReference(UserEntityInterface::class, $userOrId),
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

    public function getByUserIdCurrent($userId): ?UserAuthorityEntityInterface
    {
        return $this->select(['user_id' => $userId])->current();
    }

    public function getByAuthorityControlNumber($authorityControlNumber): ?UserAuthorityEntityInterface
    {
        $dql = 'SELECT UA '
            . 'FROM ' . UserAuthorityEntityInterface::class . ' UA '
            . 'WHERE UA.authorityControlNumber = :authorityControlNumber ';

        $query = $this->entityManager->createQuery($dql);
        $query->setParameters(['authorityControlNumber' => $authorityControlNumber]);
        return $query->getOneOrNullResult();
    }

    public function getByUserIdAndAuthorityId($userId, $authorityId): ?UserAuthorityEntityInterface
    {
        return $this->select(['user_id' => $userId, 'authority_id' => $authorityId])->current();
    }

    public function addRequest($userId, $authorityId)
    {
        $this->insert(['user_id' => $userId, 'authority_id' => $authorityId, 'access_state' => 'requested']);
    }
}
