<?php

namespace TueFind\Db\Row;

class AdminHistory extends \VuFind\Db\Row\RowGateway
{
    public function __construct($adapter)
    {
        parent::__construct('id', 'tuefind_admin_history', $adapter);
    }

    public function updateAdminHistory($adminId, $access)
    {
        $this->admin_id = $adminId;
        $this->access_type = $access;
        $this->admin_request_date = date('Y-m-d H:i:s');
        $this->save();
    }
}
