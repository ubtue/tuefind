<?php

namespace TueFind\Db\Service;

use Laminas\Db\Sql\Select;
use VuFind\Db\Service\AbstractDbService;
use TueFind\Db\Entity\PublicationEntityInterface;

class PublicationService extends AbstractDbService implements PublicationServiceInterface
{

    public function getAll()
    {
        $select = $this->getSql()->select();
        $select->join('user', 'tuefind_publications.user_id = user.id', Select::SQL_STAR, Select::JOIN_LEFT);
        $select->order('publication_datetime DESC');
        return $this->selectWith($select);
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

    public function getByControlNumber($controlNumber): PublicationEntityInterface
    {
        return $this->select(['control_number' => $controlNumber])->current();
    }

    public function addPublication(int $userId, string $controlNumber, string $externalDocumentId, string $externalDocumentGuid, string $termsDate): bool
    {
        $this->insert(['user_id' => $userId, 'control_number' => $controlNumber, 'external_document_id' => $externalDocumentId, 'external_document_guid' => $externalDocumentGuid, 'terms_date' => $termsDate]);
        return true;
    }

    public function getStatistics()
    {
        $select = $this->getSql()->select();
        $select->columns([
            'publication_count' => new \Laminas\Db\Sql\Expression("COUNT(*)"),
            'year' => new \Laminas\Db\Sql\Expression("YEAR(publication_datetime)"),
        ]);
        $select->join('user', 'tuefind_publications.user_id = user.id', null, Select::JOIN_LEFT);
        $select->where('user.ixtheo_user_type = "' . \IxTheo\Utility::getUserTypeFromUsedEnvironment() . '"');
        $select->group(new \Laminas\Db\Sql\Expression("year"));
        $select->order('year DESC');
        return $this->selectWith($select);

    }
}
