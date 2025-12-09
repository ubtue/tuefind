<?php

namespace TueFind\Db\Service;

use TueFind\Db\Entity\UserEntityInterface;

class UserService extends \VuFind\Db\Service\UserService implements UserServiceInterface
{
    public function getByRight($right)
    {
        $select = $this->getSql()->select();
        $select->where('FIND_IN_SET("' . $right . '", tuefind_rights) > 0');
        $select->order('username ASC');
        return $this->selectWith($select);
    }

    /**
     * Retrieve a user object from the database based on ID.
     *
     * @param string $uuid Uuid.
     *
     * @return UserRow
     */
    public function getByUuid($uuid)
    {
        return $this->select(['tuefind_uuid' => $uuid])->current();
    }

    public function getByID($userID)
    {
        return $this->select(['id' => $userID])->current();
    }

    public function getAdmins()
    {
        $dql = 'SELECT U '
            . 'FROM ' . UserEntityInterface::class . ' U '
            . 'WHERE U.tuefindRights IS NOT NULL '
            . 'ORDER BY U.username ASC';

        $query = $this->entityManager->createQuery($dql);
        return $query->getResult();
    }
}
