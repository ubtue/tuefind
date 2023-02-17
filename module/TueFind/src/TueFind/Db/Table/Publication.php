<?php

namespace TueFind\Db\Table;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\ResultSet\ResultSetInterface as ResultSet;
use Laminas\Db\Sql\Select;
use VuFind\Db\Row\RowGateway;
use VuFind\Db\Table\PluginManager;

class Publication extends \VuFind\Db\Table\Gateway {

    public function __construct(Adapter $adapter, PluginManager $tm, $cfg,
        RowGateway $rowObj = null, $table = 'tuefind_publications'
    ) {
        parent::__construct($adapter, $tm, $cfg, $rowObj, $table);
    }

    public function getAll()
    {
        $select = $this->getSql()->select();
        $select->join('user', 'tuefind_publications.user_id = user.id', Select::SQL_STAR, Select::JOIN_LEFT);
        $select->order('publication_datetime DESC');
        return $this->selectWith($select);
    }

    public function getByUserId($userId): ResultSet
    {
        return $this->select(['user_id' => $userId]);
    }

    public function getByControlNumber($controlNumber)
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
        $select->group(new \Laminas\Db\Sql\Expression("YEAR(publication_datetime)"));
        return $this->selectWith($select);
    }
}
