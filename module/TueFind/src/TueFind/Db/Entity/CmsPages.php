<?php

namespace TueFind\Db\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use VuFind\Db\Entity\EntityInterface;
use TueFind\Db\Entity\CmsPagesTranslation;
use TueFind\Db\Entity\CmsHistory;
use Doctrine\Common\Collections\ArrayCollection;



#[ORM\Entity]
#[ORM\Table(name: 'cms_pages')]
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

    #[ORM\Column(name: 'subsystem', type: 'string', length: 255, nullable: false)]
    protected string $subSystem;
    
    #[ORM\Column(name: 'page_system_id', type: 'string', length: 255, nullable: false)]
    protected string $pageSystemId;
    
    #[ORM\Column(name: 'create_date', type: 'datetime', length: 255, nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    protected DateTime $createDate;

    #[ORM\Column(name: 'change_date', type: 'datetime', length: 255, nullable: false)]
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
        targetEntity: CmsHistory::class
    )]
    private Collection $history;

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getSubSystem(): ?string
    {
        return $this->subSystem ?? null;
    }

    public function setSubSystem(string $subSystem): bool
    {
        $this->subSystem = $subSystem;
        return true;
    }

    public function getPageSystemId(): ?string
    {
        return $this->pageSystemId ?? null;
    }

    public function setPageSystemId(string $pageSystemId): bool
    {
        $this->pageSystemId = $pageSystemId;
        return true;
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
