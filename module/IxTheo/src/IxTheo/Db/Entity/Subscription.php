<?php

namespace IxTheo\Db\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: '`ixtheo_journal_subscriptions`')]
#[ORM\UniqueConstraint(name: 'user_subscription', columns: ['user_id', 'journal_control_number_or_bundle_name'])]
#[ORM\Entity]
class Subscription implements SubscriptionEntityInterface
{
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: UserEntityInterface::class)]
    protected UserEntityInterface $user;

    #[ORM\Column(name: 'journal_control_number_or_bundle_name', type: 'string', length: 256, nullable: false)]
    protected string $journalControlNumberOrBundleName;

    #[ORM\Column(name: 'max_last_modification_time', type: 'datetime', nullable: false)]
    protected DateTime $maxLastModificationTime;

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getUser(): UserEntityInterface
    {
        return $this->user;
    }

    public function setUser(UserEntityInterface $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getJournalControlNumberOrBundleName(): string
    {
        return $this->journalControlNumberOrBundleName;
    }

    public function setJournalControlNumberOrBundleName(string $mixed): static
    {
        $this->journalControlNumberOrBundleName = $mixed;
        return $this;
    }

    public function getMaxLastModificationTime(): DateTime
    {
        return $this->maxLastModificationTime;
    }

    public function setMaxLastModificationTime(DateTime $dateTime): static
    {
        $this->maxLastModificationTime = $dateTime;
        return $this;
    }
}
