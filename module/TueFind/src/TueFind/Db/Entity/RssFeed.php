<?php

namespace TueFind\Db\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'feed_name_constraint', columns: ['feed_name'])]
#[ORM\UniqueConstraint(name: 'feed_url_constraint', columns: ['feed_url'])]
#[ORM\UniqueConstraint(name: 'website_url_constraint', columns: ['website_url'])]
#[ORM\Index(name: 'type_index', columns: ['type'])]
#[ORM\Table(name: 'tuefind_rss_feeds')]
class RssFeed implements RssFeedEntityInterface
{
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\Column(name: 'feed_name', type: 'string', length: 200, nullable: false)]
    protected string $feedName;

    //#[ORM\Column(name: 'subsystem_types', type: 'set', length: 255, nullable: false)]
    protected array $subsystemTypes;

    #[ORM\Column(name: 'type', type: 'string', length: 255, nullable: false)]
    protected string $type;

    #[ORM\Column(name: 'feed_url', type: 'string', length: 1000, nullable: false)]
    protected string $feedUrl;

    #[ORM\Column(name: 'website_url', type: 'string', length: 1000, nullable: false)]
    protected string $websiteUrl;

    #[ORM\Column(name: 'title_suppression_regex', type: 'string', length: 200, nullable: true)]
    protected ?string $titleSuppressionRegex = null;

    #[ORM\Column(name: 'descriptions_and_substitutions', type: 'string', length: 1000, nullable: true)]
    protected ?string $descriptionsAndSubstitutitons = null;

    #[ORM\Column(name: 'strptime_format', type: 'string', length: 50, nullable: true)]
    protected ?string $strptimeFormat = null;

    #[ORM\Column(name: 'downloader_time_limit', type: 'integer', nullable: false, options: ['default' => 30])]
    protected int $downloaderTimeLimit = 30;

    #[ORM\Column(name: 'active', type: 'boolean', nullable: false, options: ['default' => true])]
    protected bool $active = true;

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getFeedName(): string
    {
        return $this->feedName;
    }

    public function getFeedUrl(): string
    {
        return $this->feedUrl;
    }

    public function getWebsiteUrl(): string
    {
        return $this->websiteUrl;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSubsystemTypes(): array
    {
        return $this->subsystemTypes;
    }

    public function getDownloaderTimeLimit(): int
    {
        return $this->downloaderTimeLimit;
    }

    public function getDescriptionsAndSubstitutions(): ?string
    {
        return $this->descriptionsAndSubstitutitons;
    }

    public function getStrptimeFormat(): ?string
    {
        return $this->strptimeFormat;
    }

    public function getTitleSuppressionRegex(): ?string
    {
        return $this->titleSuppressionRegex;
    }

    public function getActive(): bool
    {
        return $this->active;
    }
}
