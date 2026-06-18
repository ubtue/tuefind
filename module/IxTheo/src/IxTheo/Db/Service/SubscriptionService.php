<?php

namespace IxTheo\Db\Service;

use IxTheo\Db\Entity\SubscriptionEntityInterface;
use IxTheo\Db\Entity\UserEntityInterface;

use function in_array;

class SubscriptionService extends \VuFind\Db\Service\AbstractDbService implements SubscriptionServiceInterface
{
    public function createEntity(): SubscriptionEntityInterface
    {
        return $this->entityPluginManager->get(SubscriptionEntityInterface::class);
    }

    public function findExisting(UserEntityInterface $user, string $journalControlNumberOrBundleName): ?SubscriptionEntityInterface
    {
        $dql = 'SELECT S FROM ' . SubscriptionEntityInterface::class . ' S '
             . 'WHERE S.user = :userId AND S.journalControlNumberOrBundleName = :journalControlNumberOrBundleName';

        $query = $this->entityManager->createQuery($dql);
        $query->setParameters([
            'userId' => $user->getId(),
            'journalControlNumberOrBundleName' => $journalControlNumberOrBundleName,
        ]);
        return $query->getOneOrNullResult();
    }

    public function subscribe(UserEntityInterface $user, string $journalControlNumberOrBundleName): SubscriptionEntityInterface
    {
        $subscription = $this->createEntity();
        $subscription->setUser($user);
        $subscription->setJournalControlNumberOrBundleName($journalControlNumberOrBundleName);
        $this->persistEntity($subscription);
        return $subscription;
    }

    public function unsubscribe(UserEntityInterface $user, $recordId)
    {
        $dql = 'DELETE FROM ' . SubscriptionEntityInterface::class . ' s '
        . ' WHERE s.user = :userId AND s.journalControlNumberOrBundleName = :recordId';

        $query = $this->entityManager->createQuery($dql);
        $query->setParameters([
            'userId' => $user->getId(),
            'recordId' => $recordId,
        ]);
        $query->execute();
    }

    public function getByUser(UserEntityInterface $user, $sort = null, $start = null, $limit = null): array
    {
        $dql = 'SELECT S FROM ' . SubscriptionEntityInterface::class . ' S ';
        $dql .= 'WHERE S.user = :userId ';

        $parameters = ['userId' => $user->getId()];

        // sorting deprecated, sorting is done on php side
        // (fields like "title" are no longer stored in mysql,
        // else we have updating problem e.g. if title is changed in original data)
        if (isset($sort) && in_array('sort', ['journal_title'])) {
            //$dql .= ' ORDER BY ' . $sort;
        }

        $query = $this->entityManager->createQuery($dql);
        $query->setParameters($parameters);

        if (isset($start)) {
            $query->setFirstResult($start);
        }

        if (isset($limit)) {
            $query->setMaxResults($limit);
        }

        return $query->getResult();
    }

    /**
     * Apply a sort parameter to a query on the resource table.
     *
     * @param \Laminas\Db\Sql\Select $query Query to modify
     * @param string                 $sort  Field to use for sorting (may include 'desc'
     * qualifier)
     *
     * @return void
     */
    public static function applySort($query, $sort)
    {
        // Apply sorting, if necessary:
        $legalSorts = [
            // deprecated, sorting is done on php side
            // (fields like "title" are no longer stored in mysql,
            // else we have updating problem e.g. if title is changed in original data)
            'journal_title',
        ];
        if (!empty($sort) && in_array(strtolower($sort), $legalSorts)) {
            $query->order([$sort]);
        }
    }
}
