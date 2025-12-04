<?php

namespace IxTheo\Db\Entity;

class User extends \TueFind\Db\Entity\User implements UserEntityInterface
{
    public function saveEmailVerified($datetime=null) {
        $result = parent::saveEmailVerified($datetime);
        exec('/usr/local/bin/set_tad_access_flag.sh ' . $this->id);
        return $result;
    }
}
