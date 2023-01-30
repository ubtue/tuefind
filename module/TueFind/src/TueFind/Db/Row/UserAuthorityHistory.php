<?php

namespace TueFind\Db\Row;

class UserAuthorityHistory extends \VuFind\Db\Row\RowGateway
{
    public function __construct($adapter)
    {
        parent::__construct('id', 'tuefind_user_authorities_history', $adapter);
    }

    public function updateUserAuthorityHistory($adminId, $access)
    {
        $this->admin_id = $adminId;
        $this->access_state = $access;
        $this->process_admin_date = date('Y-m-d H:i:s');
        $this->save();
    }
}
