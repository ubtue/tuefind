<?php

namespace IxTheo\Db\Service;

use Laminas\Db\Sql\Select;

class UserAuthorityHistoryService extends \TueFind\Db\Service\UserAuthorityHistory implements UserAuthorityHistoryServiceInterface {
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

        // IxTheo: make sure we only return rows for the current users
        $select->where('admin.ixtheo_user_type = "' . \IxTheo\Utility::getUserTypeFromUsedEnvironment() . '"');

        $select->order('request_user_date DESC');
        return $this->selectWith($select);
    }
}
