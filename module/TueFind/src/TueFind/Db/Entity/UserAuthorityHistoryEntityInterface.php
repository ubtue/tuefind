<?php

namespace TueFind\Db\Entity;

use DateTime;
use VuFind\Db\Entity\EntityInterface;

interface UserAuthorityHistoryEntityInterface extends EntityInterface
{
    public function getId(): ?int;

    public function getAuthorityControlNumber(): string;

    public function getUser(): UserEntityInterface;

    public function getAdmin(): ?UserEntityInterface;

    public function getAccessState(): string;

    public function getRequestUserDate(): DateTime;

    public function getProcessAdminDate(): ?DateTime;
}
