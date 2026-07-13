<?php

namespace IxTheo\Db\Service;

use TueFind\Db\Entity\PublicationEntityInterface;

class PublicationService extends \TueFind\Db\Service\PublicationService implements PublicationServiceInterface
{
    public function getAll(): array
    {
        $dql = 'SELECT p '
            . 'FROM ' . PublicationEntityInterface::class . ' p '
            . 'JOIN p.user u '
            . 'WHERE u.ixtheoUserType= :userType '
            . 'ORDER BY p.publicationDateTime DESC ';

        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('userType', \IxTheo\Utility::getUserTypeFromUsedEnvironment());
        return $query->getResult();
    }
}
