<?php

namespace KrimDok\Db\Service;

use TueFind\Db\Entity\UserEntityInterface;

class UserService extends \TueFind\Db\Service\UserService implements UserServiceInterface
{
    public function getLikeEmail(string $searchQuery, string $userType, int $limit = 20): array
    {
        $dql = 'SELECT U '
            . 'FROM ' . UserEntityInterface::class . ' U '
            . 'WHERE U.email LIKE :email '
            . 'ORDER BY U.email ASC';

        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('email', '%' . trim($searchQuery) . '%');
        $query->setMaxResults($limit);

        return $query->getResult();
    }
}
