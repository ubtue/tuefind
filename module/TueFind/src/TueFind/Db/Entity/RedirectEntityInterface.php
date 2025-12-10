<?php

namespace TueFind\Db\Entity;

use DateTime;
use VuFind\Db\Entity\EntityInterface;

interface RedirectEntityInterface extends EntityInterface
{
    public function getUrl(): string;
    public function setUrl(string $url): static;

    public function getGroupName(): ?string;
    public function setGroupName(?string $groupName): static;

    public function getTimestamp(): DateTime;
}
