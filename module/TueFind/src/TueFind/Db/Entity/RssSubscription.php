<?php

namespace TueFind\Db\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tuefind_rss_subscriptions')]
class RssSubscription implements RssSubscriptionEntityInterface
{
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: UserEntityInterface::class)]
    protected UserEntityInterface $user;

    #[ORM\JoinColumn(name: 'rss_feeds_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: RssFeedEntityInterface::class)]
    protected RssFeedEntityInterface $rssFeed;

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

    public function getRssFeed(): RssFeedEntityInterface
    {
        return $this->rssFeed;
    }

    public function setRssFeed(RssFeedEntityInterface $rssFeed): static
    {
        $this->rssFeed = $rssFeed;
        return $this;
    }
}
