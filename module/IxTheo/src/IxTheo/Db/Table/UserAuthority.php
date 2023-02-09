<?php

namespace IxTheo\Db\Table;

use Laminas\Db\Sql\Select;
use IxTheo\Db\Row\UserAuthority as UserAuthorityRow;

class UserAuthority extends \TueFind\Db\Table\UserAuthority {
    public function getAll()
    {
        $select = $this->getSql()->select();
        $select->join('user', 'tuefind_user_authorities.user_id = user.id', Select::SQL_STAR, Select::JOIN_LEFT);
        $select->order('username ASC, authority_id ASC');
        $select->where('user.ixtheo_user_type="' . \IxTheo\Utility::getUserTypeFromUsedEnvironment() . '"');
        return $this->selectWith($select);
    }

    public function getByAuthorityId($authorityId): ?UserAuthorityRow
    {
        $select = $this->getSql()->select();
        $select->join('user', 'tuefind_user_authorities.user_id = user.id', Select::SQL_STAR, Select::JOIN_LEFT);
        $select->where('authority_id="' . $authorityId . '"');
        $select->where('user.ixtheo_user_type="' . \IxTheo\Utility::getUserTypeFromUsedEnvironment() . '"');

        $resultSet = $this->selectWith($select);
        foreach ($resultSet as $entry) {
            return $entry;
        }
        return null;
    }
}
