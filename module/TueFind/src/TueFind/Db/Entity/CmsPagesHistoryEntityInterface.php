<?php

namespace TueFind\Db\Entity;

use DateTime;
use VuFind\Db\Entity\EntityInterface;

interface CmsPagesHistoryEntityInterface extends EntityInterface
{
    public function getId(): ?int;

    public function getUserID(): ?int;

    public function getCmsPage(): ?CmsPages;

    public function setCmsPage(?CmsPages $cmsPage): static;

    public function getUser(): ?User;

    public function setUser(?User $user): static;

    public function getSubSystem(): Subsystems;

    public function setSubSystem(Subsystems $subSystem): static;

    public function getComment(): ?string;

    public function setComment(?string $comment): static;

    public function getCreated(): ?DateTime;

    public function setCreated(DateTime $created): static;
}
