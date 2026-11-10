<?php

namespace TueFind\Db\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use VuFind\Db\Entity\EntityInterface;

interface UserEntityInterface extends EntityInterface
{
    public function isLicenseAccessLocked(): bool;

    public function getInstitution(): ?string;

    public function setInstitution(string $institution): static;

    public function getCountry(): ?string;

    public function setCountry(string $country): static;

    public function getRssFeedSendEmails(): bool;

    public function setRssFeedSendEmails(bool $value): static;

    public function getRssFeedLastNotification(): ?DateTime;

    public function setRssFeedLastNotification(DateTime $dateTime): static;

    public function getTueFindRights(): array;

    public function setTueFindRights(array|string|null $rights): static;

    public function cmsPagesHistory(): Collection;

    public function getCmsPages(): Collection;
}
