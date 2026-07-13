<?php

namespace TueFind\Db\Service;

use TueFind\Db\Entity\UserEntityInterface;

class UserService extends \VuFind\Db\Service\UserService implements UserServiceInterface
{
    public function getByRight($right): array
    {
        $select = $this->getSql()->select();
        $select->where('FIND_IN_SET("' . $right . '", tuefind_rights) > 0');
        $select->order('username ASC');
        return $this->selectWith($select);
    }

    public function getByUuid($uuid): ?UserEntityInterface
    {
        $dql = 'SELECT U '
            . 'FROM ' . UserEntityInterface::class . ' U '
            . 'WHERE U.tuefindUuid = :uuid';
        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('uuid', $uuid);
        return $query->getOneOrNullResult();
    }

    public function getAdmins(): array
    {
        $dql = 'SELECT U '
            . 'FROM ' . UserEntityInterface::class . ' U '
            . 'WHERE U.tuefindRights IS NOT NULL '
            . 'ORDER BY U.username ASC';

        $query = $this->entityManager->createQuery($dql);
        return $query->getResult();
    }
}
