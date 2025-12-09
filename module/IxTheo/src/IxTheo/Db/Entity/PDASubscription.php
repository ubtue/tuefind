<?php

namespace IxTheo\Db\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: '`ixtheo_pda_subscriptions`')]
class PDASubscription implements PDASubscriptionEntityInterface
{
    // Careful!! This needs to be changed to an auto incemrent id, right now it references the user table!
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\Column(name: 'book_title', type: 'string', nullable: false, options: ['lengths' => [256]])]
    protected string $bookTitle;

    #[ORM\Column(name: 'book_author', type: 'string', nullable: false, options: ['lengths' => [256]])]
    protected string $bookAuthor;

    #[ORM\Column(name: 'book_year', type: 'string', nullable: false, options: ['lengths' => [32]])]
    protected string $bookYear;

    #[ORM\Column(name: 'book_ppn', type: 'string', nullable: false, options: ['lengths' => [10]])]
    protected string $bookPpn;

    #[ORM\Column(name: 'book_isbn', type: 'string', nullable: false, options: ['lengths' => [13]])]
    protected string $bookIsbn;

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getBookTitle(): string
    {
        return $this->bookTitle;
    }

    public function setBookTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getBookAuthor(): string
    {
        return $this->bookAuthor;
    }

    public function setBookAuthor(string $author): static
    {
        $this->author = $author;
        return $this;
    }

    public function getBookYear(): string
    {
        return $this->bookYear;
    }

    public function setBookYear(string $year): static
    {
        $this->year = $year;
        return $this;
    }

    public function getBookPpn(): string
    {
        return $this->bookPpn;
    }

    public function setBookPpn(string $ppn): static
    {
        $this->ppn = $ppn;
        return $this;
    }

    public function getBookIsbn(): string
    {
        return $this->bookIsbn;
    }

    public function setBookIsbn(string $isbn): static
    {
        $this->isbn = $isbn;
        return $this;
    }
}
