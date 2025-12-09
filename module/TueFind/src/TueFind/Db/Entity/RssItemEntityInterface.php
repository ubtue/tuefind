<?php

namespace TueFind\Db\Entity;

use DateTime;
use VuFind\Db\Entity\EntityInterface;

interface RssItemEntityInterface extends EntityInterface
{
    public function getId(): ?int;

    public function getRssFeed(): RssFeedEntityInterface;

    public function getItemId(): string;

    public function getItemUrl(): string;

    public function getItemTitle(): string;

    public function getItemDescription(): string;

    public function getPublicationDateTime(): DateTime;

    public function getInsertionDateTime(): DateTime;
}
