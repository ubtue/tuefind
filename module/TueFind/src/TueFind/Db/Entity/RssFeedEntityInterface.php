<?php

namespace TueFind\Db\Entity;

use VuFind\Db\Entity\EntityInterface;

interface RssFeedEntityInterface extends EntityInterface {
    public function getId(): ?int;

    public function getFeedName(): string;

    public function getFeedUrl(): string;

    public function getWebsiteUrl(): string;

    public function getTitleSuppressionRegex(): ?string;

    public function getDescriptionsAndSubstitutions(): ?string;

    public function getStrptimeFormat(): ?string;

    public function getDownloaderTimeLimit(): int;

    public function getSubsystemTypes(): array;

    public function getType(): string;

    public function getActive(): bool;

}
