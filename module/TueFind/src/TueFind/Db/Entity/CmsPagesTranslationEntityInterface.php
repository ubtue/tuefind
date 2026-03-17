<?php

namespace TueFind\Db\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use VuFind\Db\Entity\EntityInterface;
use TueFind\Db\Entity\CmsPages;

interface CmsPagesTranslationEntityInterface extends EntityInterface
{
    public function getId(): ?int;

    public function getCmsPage(): CmsPages;
   
    public function setCmsPage(CmsPages $cmsPage): static;

    public function getLanguage(): ?string;
    public function setLanguage(string $language): bool;

    public function getTitle(): ?string;
    public function setTitle(string $title): bool;

    public function getContent(): ?string;
    public function setContent(string $content): bool;
    
}
