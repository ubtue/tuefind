<?php

namespace TueFind\Db\Service;

use Laminas\Db\Sql\Select;
use TueFind\Db\Row\UserAuthorityHistory as UserAuthorityHistoryRow;
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

    public function getAll()
    {
        $select = $this->getSql()->select();
        $select->join(['admin'=>'user'], 'tuefind_user_authorities_history.admin_id = admin.id', [
            'admin_username'=>'username',
            'admin_firstname'=>'firstname',
            'admin_lastname'=>'lastname'
        ], Select::JOIN_LEFT);
        $select->join(['request_user'=>'user'], 'tuefind_user_authorities_history.user_id = request_user.id', [
            'request_user_firstname'=>'firstname',
            'request_user_lastname'=>'lastname'
        ], Select::JOIN_LEFT);
        $select->where->isNotNull('process_admin_date');
        $select->order('request_user_date DESC');
        return $this->selectWith($select);
    }

    public function addUserRequest($userId, $authorityId)
    {
        $this->insert(['user_id' => $userId, 'authority_id' => $authorityId]);
    }
}
