<?php

namespace TueFind\Db\Service;

use TueFind\Db\Entity\RssSubscriptionEntityInterface;
use TueFind\Db\Entity\RssFeedEntityInterface;
use TueFind\Db\Entity\UserEntityInterface;

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

    public function addSubscription(UserEntityInterface $user, RssFeedEntityInterface $feed): RssSubscriptionEntityInterface
    {
        $subscription = $this->entityPluginManager->get(RssSubscriptionEntityInterface::class);
        $subscription->setUser($user);
        $subscription->setRssFeed($feed);
        $this->persistEntity($subscription);
        return $subscription;
    }

    public function getSubscription(UserEntityInterface $user, RssFeedEntityInterface $feed)
    {
        $dql = 'SELECT r '
            . 'FROM ' . RssSubscriptionEntityInterface::class . ' r '
            . 'WHERE r.user = :user '
            . 'AND r.rssFeed = :feed ';
        $query = $this->entityManager->createQuery($dql);
        $query->setParameters(['user' => $user, 'feed' => $feed]);
        return $query->getSingleResult();
    }

    public function removeSubscription(UserEntityInterface $user, RssFeedEntityInterface $feed)
    {
        $subscription = $this->getSubscription($user, $feed);
        $this->deleteEntity($subscription);
    }
}
