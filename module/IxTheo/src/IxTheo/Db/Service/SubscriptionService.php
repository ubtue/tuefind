<?php
namespace IxTheo\Db\Service;

use IxTheo\Db\Entity\SubscriptionEntityInterface;
use IxTheo\Db\Entity\UserEntityInterface;

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
            'journalControlNumberOrBundleName' => $journalControlNumberOrBundleName
        ]);
        return $query->getOneOrNullResult();
    }

    public function subscribe(UserEntityInterface $user, string $journalControlNumberOrBundleName): SubscriptionEntityInterface
    {
        $subscription = $this->createEntity();
        $subscription->setUser($user);
        $subscription->setJournalControlNumberOrBundleName($journalControlNumberOrBundleName);
        $this->entityManager->persist($subscription);
        return $subscription;
    }

    public function unsubscribe(UserEntityInterface $user, $recordId)
    {
        return $this->delete(['user_id' => $user, 'journal_control_number_or_bundle_name' => $recordId]);
    }

    public function getAll(UserEntityInterface $user, $sort)
    {
        $dql = 'SELECT S FROM ' . SubscriptionEntityInterface::class . ' S ';
        $dql .= 'WHERE S.user = :userId ';

        //deprecated, see "applySort":
        //$dql .= 'ORDER BY journal_control_number_or_bundle_name ' . $sort;

        $query = $this->entityManager->createQuery($dql);
        $query->setParameters(['userId' => $user->getId()]);

        return $query->getResult();
    }

    public function get(UserEntityInterface $user, $sort, $start, $limit): array
    {
        $select = $this->getSql()->select()->where(['user_id' => $user->getId()])->offset($start)->limit($limit);
        $this->applySort($select, $sort);
        return $this->selectWith($select);
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
            'journal_title'
        ];
        if (!empty($sort) && in_array(strtolower($sort), $legalSorts)) {
            $query->order([$sort]);
        }
    }
}
