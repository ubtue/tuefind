<?php

namespace TueFind\Db\Service;

use TueFind\Db\Entity\RssSubscriptionEntityInterface;

class RssSubscriptionService extends RssBaseService
{

    public function getSubscriptionsForUserSortedByName($userId): array
    {
        $dql = 'SELECT S FROM ' . RssSubscriptionEntityInterface::class . ' S ';
        $dql .= 'LEFT JOIN S.rssFeed R ';
        $dql .= 'WHERE S.user = :userId ';
        $dql .= 'ORDER BY R.feedName ASC ';

        $query = $this->entityManager->createQuery($dql);
        $query->setParameters(['userId' => $userId]);

        return $query->getResult();
    }

    public function addSubscription($userId, $feedId)
    {
        $this->insert(['user_id' => $userId, 'rss_feeds_id' => $feedId]);
    }

    public function removeSubscription($userId, $feedId)
    {
        $delete = $this->getSql()->delete();
        $delete->where(['user_id' => $userId, 'rss_feeds_id' => $feedId]);
        $this->deleteWith($delete);
    }

}
