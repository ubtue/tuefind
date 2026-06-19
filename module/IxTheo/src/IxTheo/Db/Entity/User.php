<?php

namespace IxTheo\Db\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Subscription::class)]
    protected Collection $ixtheoJournalSubscriptions;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PDASubscription::class)]
    protected Collection $ixtheoPDASubscriptions;

    public function __construct()
    {
        parent::__construct();
        $this->ixtheoJournalSubscriptions = new ArrayCollection();
        $this->ixtheoPDASubscriptions = new ArrayCollection();
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

    public function getJournalSubscriptions(): Collection
    {
        return $this->ixtheoJournalSubscriptions;
    }

    public function getPDASubscriptions(): Collection
    {
        return $this->ixtheoPDASubscriptions;
    }

    public function setEmailVerified(?DateTime $dateTime): static
    {
        $result = parent::setEmailVerified($dateTime);
        exec(\TueFind\Utility::BIN_DIR . '/set_tad_access_flag.sh ' . $this->id);
        return $result;
    }
}
