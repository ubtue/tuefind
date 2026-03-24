<?php

namespace TueFind\Db\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use TueFind\Db\Entity\CmsPagesTranslation;
use TueFind\Db\Entity\CmsPagesHistory;
use TueFind\Db\Entity\Subsystems;

#[ORM\Entity]
#[ORM\Table(name: 'tuefind_cms_pages')]
class CmsPages implements CmsPagesEntityInterface
{
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->cmsPagesTranslations = new ArrayCollection();
        $this->history = new ArrayCollection();
    }

    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\Column(name: 'subsystem_id', type: 'integer', nullable: false)]
    protected int $subSystemId;

    #[ORM\Column(name: 'page_system_id', type: 'string', length: 255, nullable: false)]
    protected string $pageSystemId;
    
    #[ORM\Column(name: 'created', type: 'datetime', length: 255, nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    protected DateTime $createDate;

    #[ORM\Column(name: 'changed', type: 'datetime', length: 255, nullable: false)]
    protected DateTime $changeDate;

    #[ORM\OneToMany(
        mappedBy: 'cmsPage',
        targetEntity: User::class
    )]
    protected Collection $users;

    #[ORM\OneToMany(
        mappedBy: 'cmsPage',
        targetEntity: CmsPagesTranslation::class
    )]
    protected Collection $cmsPagesTranslations;

    #[ORM\OneToMany(
        mappedBy: 'cmsPage', 
        targetEntity: CmsPagesHistory::class
    )]
    private Collection $history;

    #[ORM\ManyToOne(targetEntity: Subsystems::class)]
    #[ORM\JoinColumn(name: 'subsystem_id', referencedColumnName: 'id', nullable: false)]
    private ?Subsystems $subSystem = null;

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getSubSystemId(): ?int
    {
        return $this->subSystemId ?? null;
    }

    public function setSubSystemId(int $subSystemId): int
    {
        $this->subSystemId = $subSystemId;
        return $subSystemId;
    }

    public function getPageSystemId(): ?string
    {
        return $this->pageSystemId ?? null;
    }

    public function setPageSystemId(string $pageSystemId): string
    {
        $this->pageSystemId = $pageSystemId;
        return $pageSystemId;
    }

    public function getCreateDate(): ?DateTime
    {
        return $this->createDate;
    }

    public function setCreateDate(DateTime $createDate): static
    {
        $this->createDate = $createDate;
        return $this;
    }

    public function getChangeDate(): ?DateTime
    {
        return $this->changeDate;
    }

    public function setChangeDate(DateTime $changeDate): static
    {
        $this->changeDate = $changeDate;
        return $this;
    }

}
