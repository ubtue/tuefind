<?php

namespace TueFind\Db\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use VuFind\Db\Entity\EntityInterface;
use TueFind\Db\Entity\CmsPages;
use TueFind\Db\Entity\User;

interface CmshistoryEntityInterface extends EntityInterface
{
    public function getId(): ?int;

    public function getUserID(): ?int;

    public function setUserID(int $userId): bool;

    public function getCmsPage(): ?CmsPages;

    public function setCmsPage(?CmsPages $cmsPage): self;

    public function getUser(): ?User;

    public function setUser(?User $user): self;

    public function getCreated(): ?DateTime;

    public function setCreated(DateTime $created): static;
    
}
