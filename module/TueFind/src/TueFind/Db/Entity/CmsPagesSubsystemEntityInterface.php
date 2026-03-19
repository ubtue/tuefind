<?php

namespace TueFind\Db\Entity;

use VuFind\Db\Entity\EntityInterface;
use TueFind\Db\Entity\CmsPages;

interface CmsPagesSubsystemEntityInterface extends EntityInterface
{
    public function getId(): ?int;

    public function getSubsystem(): ?string;

    public function setSubsystem(string $subSystem): static;

    public function getCmsPage(): ?CmsPages;

    public function setCmsPage(?CmsPages $cmsPage): static;
    
}
