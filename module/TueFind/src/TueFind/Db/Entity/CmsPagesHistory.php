<?php

namespace TueFind\Db\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tuefind_cms_pages_history')]
class CmsPagesHistory implements CmsPagesHistoryEntityInterface
{
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\Column(name: 'comment', type: 'text', nullable: true)]
    protected ?string $comment = null;

    #[ORM\ManyToOne(targetEntity: Subsystems::class)]
    #[ORM\JoinColumn(name: 'subsystem_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    protected ?Subsystems $subSystem = null;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    protected DateTime $created;

    #[ORM\ManyToOne(targetEntity: CmsPages::class, inversedBy: 'history')]
    #[ORM\JoinColumn(name: 'cms_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?CmsPages $cmsPage = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'cmsHistories')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    public function __construct()
    {
        $this->created = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getUserID(): ?int
    {
        return $this->user?->getId();
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getSubSystem(): ?Subsystems
    {
        return $this->subSystem;
    }

    public function setSubSystem(?Subsystems $subSystem): static
    {
        $this->subSystem = $subSystem;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;
        return $this;
    }

    public function getCreated(): ?DateTime
    {
        return $this->created;
    }

    public function setCreated(DateTime $created): static
    {
        $this->created = $created;
        return $this;
    }
}
