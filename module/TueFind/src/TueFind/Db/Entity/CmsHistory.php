<?php

namespace TueFind\Db\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use VuFind\Db\Entity\EntityInterface;
use TueFind\Db\Entity\CmsPages;
use TueFind\Db\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use TueFind\Db\Entity\CmsHistoryEntityInterface;


#[ORM\Entity]
#[ORM\Table(name: 'cms_history')]
class CmsHistory implements CmsHistoryEntityInterface
{

    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\Column(name: 'user_id', type: 'integer', nullable: false)]
    protected int $userId;

    #[ORM\Column(name: 'cms_id', type: 'integer', nullable: false)]
    protected int $cmsId;
    
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

    public function setUserID(int $userId): bool
    {
        $this->userId = $userId;
        return true;
    }

    public function getCmsPage(): ?CmsPages
    {
        return $this->cmsPage;
    }

    public function setCmsPage(?CmsPages $cmsPage): self
    {
        $this->cmsPage = $cmsPage;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
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