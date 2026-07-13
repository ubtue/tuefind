<?php

namespace TueFind\Db\Entity;

use DateTime;
use VuFind\Db\Entity\EntityInterface;

interface UserAuthorityEntityInterface extends EntityInterface
{
    public function getId(): ?int;

    public function getUser(): UserEntityInterface;
    public function setUser(UserEntityInterface $user): static;

    public function getAuthorityControlNumber(): string;
    public function setAuthorityControlNumber(string $authorityControlNumber): static;

    public function getAccessState(): string;
    public function setAccessState(string $accessState): static;

    public function getRequestedDatetime(): DateTime;

    public function getGrantedDatetime(): ?DateTime;
    public function setGrantedDatetime(DateTime $grantedDatetime): static;
}
