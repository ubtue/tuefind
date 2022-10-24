<?php

namespace IxTheo\Db\Row;

class User extends \TueFind\Db\Row\User
{
    public function saveEmailVerified($datetime=null) {
        $result = parent::saveEmailVerified($datetime);
        exec('/usr/local/bin/set_tad_access_flag.sh ' . $this->id);
        return $result;
    }
}
