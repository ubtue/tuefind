<?php

namespace TueFind\Db\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use VuFind\Db\Entity\EntityInterface;

interface CmsPagesEntityInterface extends EntityInterface
{
    public function getId(): ?int;

    public function getPageSystemId(): ?string;
    public function setPageSystemId(string $pageSystemId): bool;

    public function getCreateDate(): ?DateTime;
    public function setCreateDate(DateTime $createDate): static;

    public function getChangeDate(): ?DateTime;
    public function setChangeDate(DateTime $changeDate): static;
    
}
