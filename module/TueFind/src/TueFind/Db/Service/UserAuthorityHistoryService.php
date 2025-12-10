<?php

namespace TueFind\Db\Service;

use TueFind\Db\Row\UserAuthorityHistory as UserAuthorityHistoryRow;
use TueFind\Db\Entity\UserAuthorityHistoryEntityInterface;
use VuFind\Db\Service\AbstractDbService;

class UserAuthorityHistoryService extends AbstractDbService implements UserAuthorityHistoryServiceInterface
{

    public function getLatestRequestByUserId($requestUserId): ?UserAuthorityHistoryRow
    {
        $select = $this->getSql()->select();
        $select->where('user_id=' . $requestUserId);
        $select->order('request_user_date DESC');
        $resultSet = $this->selectWith($select);
        foreach ($resultSet as $entry) {
            return $entry;
        }
        return null;
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

    public function addUserRequest($userId, $authorityId)
    {
        $this->insert(['user_id' => $userId, 'authority_id' => $authorityId]);
    }
}
