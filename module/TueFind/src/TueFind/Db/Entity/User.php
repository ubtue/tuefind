<?php

namespace TueFind\Db\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class User extends \VuFind\Db\Entity\User implements UserEntityInterface
{
    // This is set to nullable here, but will in fact be generated on insert via a MySQL Hook
    // That's also why no setter is provided
    #[ORM\Column(name: 'tuefind_uuid', type: 'string', nullable: true, options: ['lengths' => [32]])]
    protected ?string $tuefindUuid = null;

    #[ORM\Column(name: 'tuefind_license_access_locked', type: 'boolean', nullable: false, options: ['default' => false])]
    protected bool $tuefindLicenseAccessLocked = false;

    #[ORM\Column(name: 'tuefind_institution', type: 'string', nullable: true, options: ['lengths' => [255]])]
    protected ?string $tuefindInstitution = null;

    #[ORM\Column(name: 'tuefind_country', type: 'string', nullable: true, options: ['lengths' => [255]])]
    protected ?string $tuefindCountry = null;

    #[ORM\Column(name: 'tuefind_rss_feed_send_emails', type: 'boolean', nullable: false, options: ['default' => false])]
    protected bool $tuefindRssFeedSendEmails = false;

    #[ORM\Column(name: 'tuefind_rss_feed_last_notification', type: 'datetime', nullable: true)]
    protected ?DateTime $tuefindRssFeedLastNotification = null;

    // We need to find a solution for this, since the Type SET is not available in Doctrine
    //#[ORM\Column(name: 'tuefind_rss_feed_last_notification', type: 'set', nullable: true)]
    protected array $tuefindRights = [];

    public function getUuid(): string
    {
        return $this->tuefindUuid;
    }

    public function isLicenseAccessLocked(): bool
    {
        // No setter here, since this will only be set by an admin directly in the database if necessary
        return $this->tuefindLicenseAccessLocked;
    }

    public function getInstitution(): ?string
    {
        return $this->tuefindInstitution;
    }

    public function setInstitution($institution): static
    {
        $this->tuefindInstitution = $institution;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->tuefindCountry;
    }

    public function setCountry($tuefindCountry): static
    {
        $this->tuefindCountry = $tuefindCountry;
        return $this;
    }

    public function getRssFeedSendEmails(): bool
    {
        return $this->tuefindRssFeedSendEmails;
    }

    public function setRssFeedSendEmails(bool $value): static
    {
        $this->tuefindRssFeedSendEmails = $value;
        if ($value == true) {
            $this->setRssFeedLastNotification(new DateTime());
        }
        return $this;
    }

    public function getRssFeedLastNotification(): ?DateTime
    {
        return $this->tuefindRssFeedLastNotification;
    }

    public function setRssFeedLastNotification(DateTime $dateTime): static
    {
        $this->tuefindRssFeedLastNotification = $dateTime;
        return $this;
    }

    public function getRights(): array
    {
        return $this->tuefindRights;
    }
}
