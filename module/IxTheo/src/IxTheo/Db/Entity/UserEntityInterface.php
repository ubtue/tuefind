<?php

namespace IxTheo\Db\Entity;

interface UserEntityInterface extends \TueFind\Db\Entity\UserEntityInterface {
    public function getUserType(): string;
    public function setUserType(string $type): static;

    public function getAppellation(): ?string;
    public function setAppellation(?string $appellation): static;

    public function getTitle(): ?string;
    public function setTitle(?string $title): static;

    public function getCanUseTAD(): bool;
    public function getJournalSubscriptionFormat(): ?string;
}
