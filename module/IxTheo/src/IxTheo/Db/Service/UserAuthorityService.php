<?php

namespace IxTheo\Db\Service;

use TueFind\Db\Entity\UserAuthorityEntityInterface;

class UserAuthorityService extends \TueFind\Db\Service\UserAuthorityService implements UserAuthorityServiceInterface
{
    public function getAll(): array
    {
        $dql = 'SELECT ua '
            . 'FROM ' . UserAuthorityEntityInterface::class . ' ua '
            . 'JOIN ua.user u '
            . 'WHERE u.ixtheoUserType= :userType '
            . 'ORDER BY u.username ASC, ua.authorityControlNumber ASC ';

        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('userType', \IxTheo\Utility::getUserTypeFromUsedEnvironment());
        return $query->getResult();
    }

    public function getByAuthorityControlNumber($authorityControlNumber): ?UserAuthorityEntityInterface
    {
        $dql = 'SELECT ua '
            . 'FROM ' . UserAuthorityEntityInterface::class . ' ua '
            . 'JOIN ua.user u '
            . 'WHERE ua.authorityControlNumber = :authorityControlNumber '
            . 'AND u.ixtheoUserType= :userType';

        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('authorityControlNumber', $authorityControlNumber);
        $query->setParameter('userType', \IxTheo\Utility::getUserTypeFromUsedEnvironment());
        return $query->getOneOrNullResult();
    }
}
