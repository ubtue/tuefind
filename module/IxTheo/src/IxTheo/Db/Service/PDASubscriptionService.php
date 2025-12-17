<?php
namespace IxTheo\Db\Service;

use VuFind\Db\Service\AbstractDbService;
use IxTheo\Db\Entity\PDASubscriptionEntityInterface;
use IxTheo\Db\Entity\UserEntityInterface;

class PDASubscriptionService extends AbstractDbService implements PDASubscriptionServiceInterface
{
    use \VuFind\Db\Service\DbServiceAwareTrait;

    public function createEntity(): PDASubscriptionEntityInterface
    {
        return $this->entityPluginManager->get(PDASubscriptionEntityInterface::class);
    }

    public function findExisting(UserEntityInterface $user, $ppn): ?PDASubscriptionEntityInterface
    {
        $dql = 'SELECT P '
            . 'FROM ' . PDASubscriptionEntityInterface::class . ' P '
            . 'WHERE P.user = :user '
            . 'AND P.bookPpn = :ppn ';

        $query = $this->entityManager->createQuery($dql);
        $query->setParameters(['user' => $user, 'ppn' => $ppn]);
        return $query->getOneOrNullResult();
    }

    public function subscribe(UserEntityInterface $user, $ppn, $title, $author, $year, $isbn): PDASubscriptionEntityInterface
    {
        $entity = $this->createEntity();
        $entity->setUser($user);
        $entity->setBookTitle($title ?: '');
        $entity->setBookAuthor($author ?: '');
        $entity->setBookYear($year ?: '');
        $entity->setBookPpn($ppn ?: '');
        $entity->setBookIsbn($isbn ?: '');
        $this->persistEntity($entity);
        return $entity;
    }

    public function unsubscribe(UserEntityInterface $user, $recordId): void
    {
        $entity = $this->findExisting($user, $recordId);
        $this->deleteEntity($entity);
    }

    public function getAll(UserEntityInterface $user, $sort): array
    {
        $dql = 'SELECT P '
            . 'FROM ' . PDASubscriptionEntityInterface::class . ' P '
            . 'WHERE P.user = :user';

        $this->applySort($dql, $sort);
        $query = $this->entityManager->createQuery($dql);
        $query->setParameters(['user' => $user]);
        return $query->getResult();
    }

    public function get(UserEntityInterface $user, $sort, $start, $limit)
    {
        $dql = 'SELECT P '
            . 'FROM ' . PDASubscriptionEntityInterface::class . ' P '
            . 'WHERE P.user = :user '
            . 'LIMIT ' . $limit
            . 'OFFSET ' . $start;

        $this->applySort($dql, $sort);
        $query = $this->entityManager->createQuery($dql);
        $query->setParameters(['userId' => $user]);
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
