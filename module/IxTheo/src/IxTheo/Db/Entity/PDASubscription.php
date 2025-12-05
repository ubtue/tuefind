<?php

namespace IxTheo\Db\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
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
