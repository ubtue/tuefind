<?php
namespace IxTheo\Db\Entity;

class PDASubscription implements PDASubscriptionEntityInterface
{
    /**
     * Constructor
     *
     * @param \Laminas\Db\Adapter\Adapter $adapter Database adapter
     */
    public function __construct($adapter)
    {
        parent::__construct('id', 'ixtheo_pda_subscriptions', $adapter);
    }
}
