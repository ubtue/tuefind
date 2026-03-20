<?php

namespace TueFind\Db\Entity;

use VuFind\Db\Entity\EntityInterface;

interface SubsystemsEntityInterface extends EntityInterface
{
    public function getId(): ?int;

    public function getSubsystem(): ?string;

    public function setSubsystem(string $subSystem): static;
    /*
    public function getCmsPage(): ?CmsPages;

    public function setCmsPage(?CmsPages $cmsPage): static;
    */
}
