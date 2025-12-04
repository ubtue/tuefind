<?php

namespace TueFind\Db\Entity;

class RssSubscription implements RssSubscriptionEntityInterface
{
    public function __construct(\Laminas\Db\Adapter\Adapter $adapter)
    {
        parent::__construct('id', 'tuefind_rss_subscriptions', $adapter);
    }
}
