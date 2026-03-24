<?php

namespace IxTheo\Db\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use TueFind\Db\Entity\CmsPages;
use TueFind\Db\Entity\CmsPagesHistory;
use IxTheo\Db\Entity\UserEntityInterface;

#[ORM\Entity]
class User extends \TueFind\Db\Entity\User implements UserEntityInterface
{
    // The DB type is ENUM, but since this is not supported via Doctrine we map to string
    #[ORM\Column(name: 'ixtheo_user_type', type: 'string', nullable: false)]
    protected string $ixtheoUserType;

    #[ORM\Column(name: 'ixtheo_appellation', type: 'string', nullable: true, options: ['lengths' => [64]])]
    protected ?string $ixtheoAppellation = null;

    #[ORM\Column(name: 'ixtheo_title', type: 'string', nullable: true, options: ['lengths' => [64]])]
    protected ?string $ixtheoTitle = null;

    #[ORM\Column(name: 'ixtheo_can_use_tad', type: 'boolean', nullable: false, options: ['default' => false])]
    protected bool $ixtheoCanUseTad = false;

    #[ORM\Column(name: 'ixtheo_journal_subscription_format', type: 'string', nullable: true, options: ['lengths' => [64]])]
    protected ?string $ixtheoJournalSubscriptionFormat = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: CmsPagesHistory::class)]
    private Collection $cmsHistories;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: CmsPages::class)]
    private Collection $cmsPages;

    #[ORM\ManyToMany(mappedBy: 'adminUser', targetEntity: CmsPages::class)]
    private Collection $adminCmsPages;

    public function __construct()
    {
        parent::__construct();
        $this->cmsHistories = new ArrayCollection();
        $this->cmsPages = new ArrayCollection();
    }

    public function getCmsPages(): Collection
    {
        return $this->cmsPages;
    }

    public function cmsPagesHistory(): Collection
    {
        return $this->cmsHistories;
    }

    public function getUserType(): string
    {
        return $this->ixtheoUserType;
    }

    public function setUserType(string $userType): static
    {
        $this->ixtheoUserType = $userType;
        return $this;
    }

    public function getAppellation(): ?string
    {
        return $this->ixtheoAppellation;
    }

    public function setAppellation(?string $appellation): static
    {
        $this->ixtheoAppellation = $appellation;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->ixtheoTitle;
    }

    public function setTitle(?string $title): static
    {
        $this->ixtheoTitle = $title;
        return $this;
    }

    public function getCanUseTAD(): bool
    {
        return $this->ixtheoCanUseTad;
    }

    public function getJournalSubscriptionFormat(): ?string
    {
        return $this->ixtheoJournalSubscriptionFormat;
    }

    public function setEmailVerified(?DateTime $dateTime): static
    {
        $result = parent::setEmailVerified($dateTime);
        exec('/usr/local/bin/set_tad_access_flag.sh ' . $this->id);
        return $result;
    }
}
