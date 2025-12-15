<?php

namespace TueFind\Db\Service;

use Doctrine\ORM\Query\ResultSetMapping;
use VuFind\Db\Service\AbstractDbService;
use TueFind\Db\Entity\PublicationEntityInterface;


class PublicationService extends AbstractDbService implements PublicationServiceInterface
{

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

    public function addPublication(int $userId, string $controlNumber, string $externalDocumentId, string $externalDocumentGuid, string $termsDate): bool
    {
        $this->insert(['user_id' => $userId, 'control_number' => $controlNumber, 'external_document_id' => $externalDocumentId, 'external_document_guid' => $externalDocumentGuid, 'terms_date' => $termsDate]);
        return true;
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
