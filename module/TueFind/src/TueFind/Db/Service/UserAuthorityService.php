<?php

namespace TueFind\Db\Service;

use Laminas\Db\ResultSet\ResultSetInterface as ResultSet;
use Laminas\Db\Sql\Select;
use TueFind\Db\Row\UserAuthority as UserAuthorityRow;
use TueFind\Db\Entity\UserAuthorityEntityInterface;
use TueFind\Db\Entity\UserEntityInterface;
use VuFind\Db\Service\AbstractDbService;

class UserAuthorityService extends AbstractDbService implements UserAuthorityHistoryServiceInterface
{

    public function getAll()
    {
        $select = $this->getSql()->select();
        $select->join('user', 'tuefind_user_authorities.user_id = user.id', Select::SQL_STAR, Select::JOIN_LEFT);
        $select->order('username ASC, authority_id ASC');
        return $this->selectWith($select);
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

    public function getByUser(UserEntityInterface $user, $accessState=null): ResultSet
    {
        $dql = 'SELECT UA '
            . 'FROM ' . UserAuthorityEntityInterface::class . ' UA '
            . 'WHERE UA.user_id = :userId ';

        $parameters = ['userId' => $user->getId()];
        if (isset($accessState)) {
            $dql .= 'AND UA.access_state = :accessState';
            $parameters['accessState'] = $accessState;
        }

        $query = $this->entityManager->createQuery($dql);
        $query->setParameters($parameters);
        return $query->getResult();



    }

    public function getByUserIdCurrent($userId): ?UserAuthorityRow
    {
        return $this->select(['user_id' => $userId])->current();
    }

    public function getByAuthorityId($authorityId): ?UserAuthorityRow
    {
        return $this->select(['authority_id' => $authorityId])->current();
    }

    public function getByUserIdAndAuthorityId($userId, $authorityId): ?UserAuthorityRow
    {
        return $this->select(['user_id' => $userId, 'authority_id' => $authorityId])->current();
    }

    public function addRequest($userId, $authorityId)
    {
        $this->insert(['user_id' => $userId, 'authority_id' => $authorityId, 'access_state' => 'requested']);
    }
}
