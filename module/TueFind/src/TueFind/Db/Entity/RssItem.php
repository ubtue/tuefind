<?php

namespace TueFind\Db\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tuefind_rss_items')]
class RssItem implements RssItemEntityInterface
{
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\JoinColumn(name: 'rss_feeds_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: RssFeedEntityInterface::class)]
    protected RssFeedEntityInterface $rssFeed;

    #[ORM\Column(name: 'item_id', type: 'string', length: 768, nullable: false)]
    protected string $itemId;

    #[ORM\Column(name: 'item_url', type: 'string', length: 1000, nullable: false)]
    protected string $itemUrl;

    #[ORM\Column(name: 'item_title', type: 'string', length: 1000, nullable: false)]
    protected string $itemTitle;

    #[ORM\Column(name: 'item_description', type: 'text', length: 16777215, nullable: false)]
    protected string $itemDescription;

    #[ORM\Column(name: 'pub_date', type: 'datetime', nullable: false)]
    protected DateTime $publicationDateTime;

    #[ORM\Column(name: 'insertion_time', type: 'datetime', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    protected DateTime $insertionDateTime;

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getRssFeed(): RssFeedEntityInterface
    {
        return $this->rssFeed;
    }

    public function getItemId(): string
    {
        return $this->itemId;
    }

    public function getItemUrl(): string
    {
        return $this->itemUrl;
    }

    public function getItemTitle(): string
    {
        return $this->itemTitle;
    }

    public function getItemDescription(): string
    {
        return $this->itemDescription;
    }

    public function getPublicationDateTime(): DateTime
    {
        return $this->publicationDateTime;
    }

    public function getInsertionDateTime(): DateTime
    {
        return $this->insertionDateTime;
    }
}
