<?php

namespace TueFind\Db\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use VuFind\Db\Entity\EntityInterface;

interface CmsPagesEntityInterface extends EntityInterface
{
    public function getId(): ?int;

    public function getSubSystem(): Subsystems;

    public function setSubSystem(Subsystems $subSystem): static;

    public function getPageSystemId(): ?string;

    public function setPageSystemId(string $pageSystemId): static;

    public function getCustomJs(): ?string;

    public function setCustomJs(?string $customJs): static;

    public function getCustomCss(): ?string;

    public function setCustomCss(?string $customCss): static;

    public function getCreateDate(): ?DateTime;

    public function setCreateDate(DateTime $createDate): static;

    public function getChangeDate(): ?DateTime;

    public function setChangeDate(DateTime $changeDate): static;

    public function getTranslations(): Collection;

    public function getTranslation(string $language): ?CmsPagesTranslation;

    public function getHistory(): Collection;
}
