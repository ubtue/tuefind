<?php

namespace TueFind\Db\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use TueFind\Db\Entity\CmsPages;

#[ORM\Entity]
#[ORM\Table(name: 'tuefind_subsystems')]
class Subsystems implements SubsystemsEntityInterface
{   
    public function __construct()
    {
        $this->cmsPages = new ArrayCollection();
    }

    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;
    
    #[ORM\Column(name: 'subsystem', type: 'string', length: 50, nullable: false)]
    protected string $subSystem;
    
    #[ORM\OneToMany(mappedBy: 'subSystem', targetEntity: CmsPages::class)]
    private Collection $cmsPages;
    
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

}