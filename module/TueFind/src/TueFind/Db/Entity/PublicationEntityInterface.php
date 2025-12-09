<?php

namespace TueFind\Db\Entity;

use DateTime;
use VuFind\Db\Entity\EntityInterface;

interface PublicationEntityInterface extends EntityInterface
{

    public function getId(): ?int;

    public function getUser(): UserEntityInterface;
    public function setUser(UserEntityInterface $user): static;

    public function getControlNumber(): ?string;
    public function setControlNumber(string $controlNumber): static;

    public function getExternalDocumentId(): ?string;
    public function setExternalDocumentId(string $externalDocumentId): static;

    public function getExternalDocumentGuid(): ?string;
    public function setExternalDocumentGuid(string $externalDocumentGuid): static;

    public function getDoi(): ?string;
    public function setDoi(string $doi): static;

    public function getDoiNotificationDatetime(): ?DateTime;
    public function setDoiNotificationDatetime(DateTime $doiNotificationDatetime): static;

    public function getTermsDate(): ?DateTime;
    public function setTermsDate(DateTime $date): static;

    public function getPublicationDatetime(): ?DateTime;
    public function setPublicationDatetime(DateTime $publicationDatetime): static;
}
