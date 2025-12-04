<?php

namespace TueFind\Db\Entity;

class UserAuthority implements UserAuthorityEntityInterface
{
    public function __construct($adapter)
    {
        parent::__construct('id', 'tuefind_user_authorities', $adapter);
    }

    public function updateAccessState($accessState)
    {
        $this->access_state = $accessState;
        $this->granted_datetime = date('Y-m-d H:i:s');
        $this->save();
    }
}
