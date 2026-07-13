<?php

namespace IxTheo\Db\Entity;

use DateTime;
use VuFind\Db\Entity\EntityInterface;

interface SubscriptionEntityInterface extends EntityInterface
{
    public function getId(): ?int;

    public function getUser(): UserEntityInterface;
    public function setUser(UserEntityInterface $user): static;

    public function getJournalControlNumberOrBundleName(): string;
    public function setJournalControlNumberOrBundleName(string $mixed): static;

    public function getMaxLastModificationTime(): DateTime;
    public function setMaxLastModificationTime(DateTime $dateTime): static;
}
