<?php

namespace TueFind\Db\Service;

use Doctrine\ORM\Query\ResultSetMapping;
use VuFind\Db\Service\AbstractDbService;
use TueFind\Db\Entity\PublicationEntityInterface;
use TueFind\Db\Entity\UserEntityInterface;


class PublicationService extends AbstractDbService implements PublicationServiceInterface
{
    protected function createEntity(): PublicationEntityInterface
    {
        return $this->entityPluginManager->get(PublicationEntityInterface::class);
    }

    public function createPublication(UserEntityInterface $user, string $controlNumber, string $externalDocumentId, string $externalDocumentGuid, string $termsDate): PublicationServiceInterface
    {
        $publication = $this->createEntity();
        $publication->setUser($user);
        $publication->setControlNumber($controlNumber);
        $publication->setExternalDocumentId($externalDocumentId);
        $publication->setExternalDocumentGuid($externalDocumentGuid);
        $publication->setTermsDate($termsDate);
        $this->entityManager->persist($publication);
        return $publication;
    }

    public function getAll()
    {
        $dql = 'SELECT p '
            . 'FROM ' . PublicationEntityInterface::class . ' p '
            . 'ORDER BY p.publicationDateTime DESC ';

        $query = $this->entityManager->createQuery($dql);
        return $query->getResult();
    }

    public function getByUserId($userId): array
    {
        $dql = 'SELECT P '
            . 'FROM ' . PublicationEntityInterface::class . ' P '
            . 'WHERE P.user = :userId';
        $query = $this->entityManager->createQuery($dql);
        $query->setParameters(['userId' => $userId]);
        return $query->getResult();
    }

    public function getByControlNumber($controlNumber): ?PublicationEntityInterface
    {
        $dql = 'SELECT P '
            . 'FROM ' . PublicationEntityInterface::class . ' P '
            . 'WHERE P.controlNumber = :controlNumber';
        $query = $this->entityManager->createQuery($dql);
        $query->setParameters(['controlNumber' => $controlNumber]);
        return $query->getOneOrNullResult();
    }

    public function getStatistics()
    {
        $dql = 'SELECT COUNT(*) AS count, YEAR(publication_datetime) AS year '
             . 'FROM tuefind_publications '
             . 'GROUP BY year '
             . 'ORDER BY year DESC ';

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('count', 'count');
        $rsm->addScalarResult('year', 'year');

        $query = $this->entityManager->createNativeQuery($dql, $rsm);
        return $query->getResult();
    }
}
