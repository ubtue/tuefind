<?php

namespace IxTheo\Db\Table;

use Laminas\Db\Sql\Select;

class UserAuthority extends \TueFind\Db\Table\UserAuthority {
    public function getAll()
    {
        $select = $this->getSql()->select();
        $select->join('user', 'tuefind_user_authorities.user_id = user.id', Select::SQL_STAR, Select::JOIN_LEFT);
        $select->order('username ASC, authority_id ASC');
        $select->where('user.ixtheo_user_type="' . \IxTheo\Utility::getUserTypeFromUsedEnvironment() . '"');
        return $this->selectWith($select);
    }
}
