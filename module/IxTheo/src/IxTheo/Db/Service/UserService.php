<?php

namespace IxTheo\Db\Service;

use IxTheo\Db\Entity\UserEntityInterface;

class UserService extends \TueFind\Db\Service\UserService implements UserServiceInterface
{
    public function createEntity(): \VuFind\Db\Entity\UserEntityInterface
    {
        $user = parent::createEntity();
        $user->setUserType(\IxTheo\Utility::getUserTypeFromUsedEnvironment());
        return $user;
    }

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

            // TueFind: also check instance / user type
            $dql .= ' AND U.ixtheoUserType = :ixtheoUserType';
            $parameters['ixtheoUserType'] = \IxTheo\Utility::getUserTypeFromUsedEnvironment();

            $query = $this->entityManager->createQuery($dql);
            $query->setParameters($parameters);
            return $query->getOneOrNullResult();
        }
        throw new \InvalidArgumentException('Field name must be ' . implode(', ', array_keys($legalFieldMap)));
    }

    public function getAdmins()
    {
        $dql = 'SELECT U '
            . 'FROM ' . UserEntityInterface::class . ' U '
            . 'WHERE U.tuefindRights IS NOT NULL '
            . 'AND U.ixtheoUserType = :ixtheoUserType '
            . 'ORDER BY U.username ASC';

        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('ixtheoUserType', \IxTheo\Utility::getUserTypeFromUsedEnvironment());
        return $query->getResult();
    }

    public function getByRight($right)
    {
        $dql = 'SELECT U '
            . 'FROM ' . UserEntityInterface::class . ' U '
            . 'WHERE FIND_IN_SET(:right, tuefind_rights) > 0'
            . 'AND U.ixtheoUserType = :ixtheoUserType '
            . 'ORDER BY U.username ASC';

        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('ixtheoUserType', \IxTheo\Utility::getUserTypeFromUsedEnvironment());
        $query->setParameter('right', $right);
        return $query->getResult();
    }
}
