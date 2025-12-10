<?php

namespace TueFind\Db\Entity;

use VuFind\Db\Entity\EntityInterface;

interface RssSubscriptionEntityInterface extends EntityInterface
{
    public function getId(): ?int;

    public function getRssFeed(): RssFeedEntityInterface;
    public function setRssFeed(RssFeedEntityInterface $rssFeed): static;

    public function getUser(): UserEntityInterface;
    public function setUser(UserEntityInterface $user): static;
}
