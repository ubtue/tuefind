<?php

namespace TueFind\Db\Entity;

use DateTime;
use VuFind\Db\Entity\EntityInterface;
use TueFind\Db\Entity\CmsPages;
use TueFind\Db\Entity\User;

interface CmsPagesHistoryEntityInterface extends EntityInterface
{
    public function getId(): ?int;

    public function getUserID(): ?int;

    public function setUserID(int $userId): static;

    public function getCmsPage(): ?CmsPages;

    public function setCmsPage(?CmsPages $cmsPage): static;

    public function getUser(): ?User;

    public function setUser(?User $user): static;

    public function getCreated(): ?DateTime;

    public function setCreated(DateTime $created): static;
    
}
