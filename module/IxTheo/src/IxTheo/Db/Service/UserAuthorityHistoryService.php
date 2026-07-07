<?php

namespace IxTheo\Db\Service;

use TueFind\Db\Entity\UserAuthorityHistoryEntityInterface;

class UserAuthorityHistoryService extends \TueFind\Db\Service\UserAuthorityHistoryService implements UserAuthorityHistoryServiceInterface
{
    public function getAll(): array
    {
        $dql = 'SELECT uah '
            . 'FROM ' . UserAuthorityHistoryEntityInterface::class . ' uah '
            . 'JOIN uah.user u '
            . 'WHERE uah.processAdminDate IS NOT NULL '
            . 'AND u.ixtheoUserType= :userType'
            . 'ORDER BY uah.requestUserDate DESC ';

        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('userType', \IxTheo\Utility::getUserTypeFromUsedEnvironment());
        return $query->getResult();
    }
}
