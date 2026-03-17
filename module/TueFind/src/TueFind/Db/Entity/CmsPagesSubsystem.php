<?php

namespace TueFind\Db\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use VuFind\Db\Entity\EntityInterface;
use TueFind\Db\Entity\CmsPages;
use Doctrine\Common\Collections\ArrayCollection;


#[ORM\Entity]
#[ORM\Table(name: 'cms_pages_subsystem')]
class CmsPagesSubsystem implements CmsPagesSubsystemEntityInterface
{

    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;
    
    #[ORM\Column(name: 'subsystem', type: 'string', length: 50, nullable: false)]
    protected string $subsystem;

    #[ORM\OneToOne(targetEntity: CmsPages::class, inversedBy: 'subsystems')]
    #[ORM\JoinColumn(name: 'cms_id', referencedColumnName: 'id', nullable: false)]
    private ?CmsPages $cmsPage = null;

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getSubsystem(): ?string
    {
        return $this->subsystem ?? null;
    }

    public function setSubsystem(string $subsystem): static
    {
        $this->subsystem = $subsystem;
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