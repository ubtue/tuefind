<?php

namespace TueFind\Db\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use VuFind\Db\Feature\DateTimeTrait;

#[ORM\Table(name: 'tuefind_publications')]
#[ORM\UniqueConstraint(name: 'publication_control_number', columns: ['control_number'])]
#[ORM\UniqueConstraint(name: 'publication_external_document_id', columns: ['external_document_id'])]
#[ORM\UniqueConstraint(name: 'publication_external_document_guid', columns: ['external_document_guid'])]
#[ORM\UniqueConstraint(name: 'publication_doi', columns: ['publication_doi'])]
#[ORM\Index(name: 'user_id', columns: ['user_id'])]
#[ORM\Entity]
class Publication implements PublicationEntityInterface
{
    use DateTimeTrait;

    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: UserEntityInterface::class)]
    protected UserEntityInterface $user;

    #[ORM\Column(name: 'control_number', type: 'string', length: 255, nullable: false)]
    protected string $controlNumber;

    #[ORM\Column(name: 'external_document_id', type: 'string', length: 255, nullable: false)]
    protected string $externalDocumentId;

    #[ORM\Column(name: 'external_document_guid', type: 'string', length: 255, nullable: true)]
    protected string $externalDocumentGuid;

    #[ORM\Column(name: 'doi', type: 'string', length: 255, nullable: true)]
    protected string $doi;

    #[ORM\Column(name: 'doi_notification_datetime', type: 'datetime', nullable: true)]
    protected DateTime $doiNotificationDateTime;

    // Unfortunately Doctrine does not support Date, it forces DateTime and sets time to 00:00:00
    #[ORM\Column(name: 'terms_date', type: 'date', nullable: true)]
    protected DateTime $termsDate;

    #[ORM\Column(name: 'publication_datetime', type: 'datetime', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    protected DateTime $publicationDateTime;

    /**
     * Get identifier (returns null for an uninitialized or non-persisted object).
     *
     * @return ?int
     */
    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getUser(): UserEntityInterface
    {
        return $this->user;
    }

    public function setUser(?UserEntityInterface $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getControlNumber(): ?string
    {
        return $this->controlNumber;
    }

    public function setControlNumber(string $controlNumber): static
    {
        $this->controlNumber = $controlNumber;
        return $this;
    }

    public function getExternalDocumentId(): ?string
    {
        return $this->externalDocumentId;
    }

    public function setExternalDocumentId(string $externalDocumentId): static
    {
        $this->externalDocumentId = $externalDocumentId;
        return $this;
    }

    public function getExternalDocumentGuid(): ?string
    {
        return $this->externalDocumentGuid;
    }

    public function setExternalDocumentGuid(string $externalDocumentGuid): static
    {
        $this->externalDocumentGuid = $externalDocumentGuid;
        return $this;
    }

    public function getDoi(): ?string
    {
        return $this->doi;
    }

    public function setDoi(string $doi): static
    {
        $this->doi = $doi;
        return $this;
    }

    public function getDoiNotificationDateTime(): ?DateTime
    {
        return $this->doiNotificationDateTime;
    }

    public function setDoiNotificationDateTime(DateTime $doiNotificationDatetime): static
    {
        $this->doiNotificationDateTime = $doiNotificationDatetime;
        return $this;
    }

    public function getTermsDate(): ?DateTime
    {
        return $this->termsDate;
    }

    public function setTermsDate(DateTime $termsDate): static
    {
        $this->termsDate = $termsDate;
        return $this;
    }

    public function getPublicationDateTime(): ?DateTime
    {
        return $this->publicationDateTime;
    }

    public function setPublicationDateTime(DateTime $publicationDatetime): static
    {
        $this->publicationDateTime = $publicationDatetime;
        return $this;
    }
}
