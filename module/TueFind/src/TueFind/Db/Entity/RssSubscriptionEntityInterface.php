<?php

namespace TueFind\Db\Entity;

use VuFind\Db\Entity\EntityInterface;

interface RssSubscriptionEntityInterface extends EntityInterface
{
    public function getId(): ?int;
    public function getRssFeed(): RssFeedEntityInterface;
    public function getUser(): UserEntityInterface;
}
