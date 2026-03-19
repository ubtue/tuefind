<?php

namespace TueFind\Db\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use TueFind\Db\Entity\CmsPages;
use TueFind\Db\Entity\User;

#[ORM\Entity]
#[ORM\Table(name: 'cms_pages_history')]
class CmsPagesHistory implements CmsPagesHistoryEntityInterface
{

    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\Column(name: 'user_id', type: 'integer', nullable: false)]
    protected int $userId;
    
    #[ORM\Column(name: 'created', type: 'datetime', length: 255, nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    protected DateTime $created;

    #[ORM\ManyToOne(targetEntity: CmsPages::class, inversedBy: 'history')]
    #[ORM\JoinColumn(name: 'cms_id', referencedColumnName: 'id', nullable: false)]
    private ?CmsPages $cmsPage = null;
    
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'cmsHistories')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getUserID(): ?int
    {
        return $this->userId ?? null;
    }

    public function setUserID(int $userId): static
    {
        $this->userId = $userId;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
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