<?php

namespace IxTheo\Db\Entity;

use DateTime;
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
