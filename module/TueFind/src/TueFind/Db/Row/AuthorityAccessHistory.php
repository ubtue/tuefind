<?php

namespace TueFind\Db\Row;

class AuthorityAccessHistory extends \VuFind\Db\Row\RowGateway
{
    public function __construct($adapter)
    {
        parent::__construct('id', 'tuefind_authority_access_history', $adapter);
    }

    public function updateAuthorityAccessHistory($adminId, $access)
    {
        $this->admin_id = $adminId;
        $this->access_type = $access;
        $this->admin_request_date = date('Y-m-d H:i:s');
        $this->save();
    }
}
