<?php

namespace IxTheo\Db\Service;

use Laminas\Db\Sql\Select;

class PublicationService extends \TueFind\Db\Service\PublicationService implements PublicationServiceInterface
{
    public function getAll()
    {
        $select = $this->getSql()->select();
        $select->join('user', 'tuefind_publications.user_id = user.id', Select::SQL_STAR, Select::JOIN_LEFT);
        $select->where('user.ixtheo_user_type = "' . \IxTheo\Utility::getUserTypeFromUsedEnvironment() . '"');
        $select->order('publication_datetime DESC');
        return $this->selectWith($select);
    }
}
