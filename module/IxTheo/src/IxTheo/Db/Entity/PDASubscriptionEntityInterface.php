<?php

namespace IxTheo\Db\Entity;

use VuFind\Db\Entity\EntityInterface;

interface PDASubscriptionEntityInterface extends EntityInterface
{
    public function getId(): ?int;

    public function getBookTitle(): string;
    public function setBookTitle(string $title): static;

    public function getBookAuthor(): string;
    public function setBookAuthor(string $author): static;

    public function getBookYear(): string;
    public function setBookYear(string $year): static;

    public function getBookPpn(): string;
    public function setBookPpn(string $ppn): static;

    public function getBookIsbn(): string;
    public function setBookIsbn(string $isbn): static;
}
