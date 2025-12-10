<?php
namespace IxTheo\Db\Service;

use VuFind\Db\Service\AbstractDbService;
use IxTheo\Db\Entity\PDASubscriptionEntityInterface;

class PDASubscriptionService extends AbstractDbService implements PDASubscriptionServiceInterface
{
    use \VuFind\Db\Service\DbServiceAwareTrait;

    /**
     * Session container for last list information.
     *
     * @var \Laminas\Session\Container
     */
    protected $session;

    public function getNew($userId, $ppn, $title, $author, $year, $isbn)
    {
        $row = $this->createRow();
        $row->id = $userId;
        $row->book_title = $title ?: "";
        $row->book_author = $author ?: "";
        $row->book_year = $year ?: "";
        $row->book_ppn = $ppn ?: "";
        $row->book_isbn = $isbn ?: "";
        return $row;
    }

    public function findExisting($userId, $ppn)
    {
        return $this->select(['id' => $userId, 'book_ppn' => $ppn])->current();
    }

    public function subscribe($userId, $ppn, $title, $author, $year, $isbn)
    {
        $row = $this->getNew($userId, $ppn, $title, $author, $year, $isbn);
        $row->save();
        return $row->id;
    }

    public function unsubscribe($userId, $recordId)
    {
        return $this->delete(['id' => $userId, 'book_ppn' => $recordId]);
    }

    public function getAll($userId, $sort)
    {
        $dql = 'SELECT P '
            . 'FROM ' . PDASubscriptionEntityInterface::class . ' P '
            . 'WHERE P.user = :userId';

        $this->applySort($dql, $sort);
        $query = $this->entityManager->createQuery($dql);
        $query->setParameters(['userId' => $userId]);
        return $query->getResult();
    }

    public function get($userId, $sort, $start, $limit)
    {
        $dql = 'SELECT P '
            . 'FROM ' . PDASubscriptionEntityInterface::class . ' P '
            . 'WHERE P.user = :userId '
            . 'LIMIT ' . $limit
            . 'OFFSET ' . $start;

        $this->applySort($dql, $sort);
        $query = $this->entityManager->createQuery($dql);
        $query->setParameters(['userId' => $userId]);
        return $query->getResult();
    }

    /**
     * Apply a sort parameter to a query on the resource table.
     *
     * @return void
     */
    public static function applySort(string &$dql, string $sort)
    {
        // Apply sorting, if necessary:
        $legalSorts = [
            'book_title' => 'bookTitle',
            'book_title desc' => 'bookTitle desc',
            'book_author' => 'bookAuthor',
            'book_author desc' => 'bookAuthor desc',
            'book_year' => 'bookYear',
            'book_year desc' => 'bookYear desc',
        ];
        if (!empty($sort) && array_key_exists(strtolower($sort), $legalSorts)) {
            $dql .= ' ORDER BY P.' . $legalSorts[strtolower($sort)];
        }
    }
}
