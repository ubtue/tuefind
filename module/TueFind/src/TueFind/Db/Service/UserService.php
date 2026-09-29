<?php

namespace TueFind\Db\Service;

use TueFind\Db\Entity\UserEntityInterface;

class UserService extends \VuFind\Db\Service\UserService implements UserServiceInterface
{
    public function getByRight($right): array
    {
        $dql = 'SELECT U '
            . 'FROM ' . UserEntityInterface::class . ' U '
            //. 'WHERE FIND_IN_SET(:right, tuefind_rights) > 0'
            //. 'WHERE :right MEMBER OF U.tuefindRights '
            . 'WHERE U.tuefindRights LIKE :right '
            . 'ORDER BY U.username ASC';

        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('right', '%' . $right . '%');
        return $query->getResult();
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
