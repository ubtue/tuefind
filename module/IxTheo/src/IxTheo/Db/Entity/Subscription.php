<?php
namespace IxTheo\Db\Entity;

class Subscription implements SubscriptionEntityInterface
{
    /**
     * Constructor
     *
     * @param \Laminas\Db\Adapter\Adapter $adapter Database adapter
     */
    public function __construct($adapter)
    {
        parent::__construct('user_id', 'ixtheo_journal_subscriptions', $adapter);
    }
}
