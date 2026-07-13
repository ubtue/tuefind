<?php

namespace TueFind\Db\Entity;

use DateTime;
use VuFind\Db\Entity\EntityInterface;

interface UserAuthorityHistoryEntityInterface extends EntityInterface
{
    public function getId(): ?int;

    public function getAuthorityControlNumber(): string;
    public function setAuthorityControlNumber(string $authorityControlNumber): static;

    public function getUser(): UserEntityInterface;
    public function setUser(UserEntityInterface $user): static;

    public function getAdmin(): ?UserEntityInterface;
    public function setAdmin(?UserEntityInterface $user): static;

    public function getAccessState(): string;
    public function setAccessState(string $accessState): static;

    public function getRequestUserDate(): DateTime;

    public function getProcessAdminDate(): ?DateTime;
    public function setProcessAdminDate(DateTime $dateTime): static;
}
