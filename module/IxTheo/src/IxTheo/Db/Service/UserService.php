<?php

namespace IxTheo\Db\Service;

use IxTheo\Db\Entity\UserEntityInterface;

class UserService extends \TueFind\Db\Service\UserService implements UserServiceInterface
{

    // Similar to parent function, but we need to add the instance name when selecting
    public function getUserByField(string $fieldName, int|string|null $fieldValue): ?\VuFind\Db\Entity\UserEntityInterface
    {
        // Null ID lookups cannot possibly retrieve a value:
        if ($fieldName === 'id' && $fieldValue === null) {
            return null;
        }
        // Map expected incoming values (actual database columns) to legal values (Doctrine properties)
        $legalFieldMap = [
            'id' => 'id',
            'username' => 'username',
            'email' => 'email',
            'cat_id' => 'catId',
            'verify_hash' => 'verifyHash',
        ];
        // For now, only username lookups are case-insensitive:
        $caseInsensitive = $fieldName === 'username';
        if (isset($legalFieldMap[$fieldName])) {
            $where = $caseInsensitive
                ? 'LOWER(U.' . $legalFieldMap[$fieldName] . ') = LOWER(:fieldValue)'
                : 'U.' . $legalFieldMap[$fieldName] . ' = :fieldValue';
            $dql = 'SELECT U FROM ' . UserEntityInterface::class . ' U '
                . 'WHERE ' . $where;
            $parameters = compact('fieldValue');

            // TueFind: also check instance
            $dql .= ' AND U.ixtheoUserType = :ixtheoUserType';
            $parameters['ixtheoUserType'] = \IxTheo\Utility::getUserTypeFromUsedEnvironment();

            $query = $this->entityManager->createQuery($dql);
            $query->setParameters($parameters);
            return $query->getOneOrNullResult();
        }
        throw new \InvalidArgumentException('Field name must be id, username, email or cat_id');
    }

    public function canUseTAD($userId)
    {
        return $this->get($userId)->ixtheo_can_use_tad;
    }

    public function createRowForUsername($username)
    {
        $row = parent::createRowForUsername($username);
        $row->ixtheo_user_type = \IxTheo\Utility::getUserTypeFromUsedEnvironment();
        return $row;
    }

    public function getAdmins()
    {
        $select = $this->getSql()->select();
        $select->where('user.tuefind_rights IS NOT NULL AND user.ixtheo_user_type = "' . \IxTheo\Utility::getUserTypeFromUsedEnvironment() . '"');
        $select->order('user.username ASC');
        return $this->selectWith($select);
    }

    public function get($userId)
    {
        $select = $this->getSql()->select();
        $select->where("id=" . $userId);
        $rowset = $this->selectWith($select);
        return $rowset->current();
    }

    public function getByEmail($email)
    {
        $row = $this->select(['email' => $email, 'ixtheo_user_type' => \IxTheo\Utility::getUserTypeFromUsedEnvironment()])->current();
        return $row;
    }

    public function getByRight($right)
    {
        $select = $this->getSql()->select();
        $select->where('FIND_IN_SET("' . $right . '", tuefind_rights) > 0 AND ixtheo_user_type="' . \IxTheo\Utility::getUserTypeFromUsedEnvironment() . '"');
        $select->order('username ASC');
        return $this->selectWith($select);
    }

    public function getByUsername($username, $create = true)
    {
        $row = $this->select(['username' => $username, 'ixtheo_user_type' => \IxTheo\Utility::getUserTypeFromUsedEnvironment()])->current();
        return ($create && empty($row))
            ? $this->createRowForUsername($username) : $row;
    }

    public function getNew($userId)
    {
        $row = $this->createRow();
        $row->id = $userId;
        $row->ixtheo_user_type = \IxTheo\Utility::getUserTypeFromUsedEnvironment();
        return $row;
    }


}
