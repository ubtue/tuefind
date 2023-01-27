<?php

namespace TueFind\Db\Table;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\ResultSet\ResultSetInterface as ResultSet;
use Laminas\Db\Sql\Select;
use VuFind\Db\Row\RowGateway;
use VuFind\Db\Table\PluginManager;
use TueFind\Db\Row\AdminHistory as AdminHistoryRow;

class AdminHistory extends \VuFind\Db\Table\Gateway {

    public function __construct(Adapter $adapter, PluginManager $tm, $cfg,
        RowGateway $rowObj = null, $table = 'tuefind_admin_history'
    ) {
        parent::__construct($adapter, $tm, $cfg, $rowObj, $table);
    }

    public function getByRequestUserId($requestUserId): ?AdminHistoryRow
    {
        return $this->select(['request_user_id' => $requestUserId])->current();
    }

    public function getAll()
    {
        $select = $this->getSql()->select();
        $select->join(['admin'=>'user'], 'tuefind_admin_history.admin_id = admin.id', [
            'admin_username'=>'username',
            'admin_firstname'=>'firstname',
            'admin_lastname'=>'lastname'
        ], Select::JOIN_LEFT);
        $select->join(['request_user'=>'user'], 'tuefind_admin_history.request_user_id = request_user.id', [
            'request_user_firstname'=>'firstname',
            'request_user_lastname'=>'lastname'
        ], Select::JOIN_LEFT);
        $select->where->isNotNull('admin_request_date');
        $select->order('request_user_date DESC');
        return $this->selectWith($select);
    }

    public function addUserRequest($userId, $authorityId) 
    {
        $this->insert(['request_user_id' => $userId, 'authority_id' => $authorityId]);
    }
}
