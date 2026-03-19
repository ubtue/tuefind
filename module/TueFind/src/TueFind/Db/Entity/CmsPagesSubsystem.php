<?php

namespace TueFind\Db\Entity;

use Doctrine\ORM\Mapping as ORM;
use TueFind\Db\Entity\CmsPages;

#[ORM\Entity]
#[ORM\Table(name: 'cms_pages_subsystem')]
class CmsPagesSubsystem implements CmsPagesSubsystemEntityInterface
{

    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;
    
    #[ORM\Column(name: 'subsystem', type: 'string', length: 50, nullable: false)]
    protected string $subSystem;

    #[ORM\OneToOne(targetEntity: CmsPages::class, inversedBy: 'subSystems')]
    #[ORM\JoinColumn(name: 'cms_id', referencedColumnName: 'id', nullable: false)]
    private ?CmsPages $cmsPage = null;

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getSubsystem(): ?string
    {
        return $this->subSystem ?? null;
    }

    public function setSubsystem(string $subSystem): static
    {
        $this->subSystem = $subSystem;
        return $this;
    }

    public function getCmsPage(): ?CmsPages
    {
        return $this->cmsPage;
    }

    public function setCmsPage(?CmsPages $cmsPage): static
    {
        $this->cmsPage = $cmsPage;
        return $this;
    }

}