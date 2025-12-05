<?php

namespace TueFind\Db\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class UserAuthorityHistory implements UserAuthorityHistoryEntityInterface
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
