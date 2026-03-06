<?php

namespace TueFind\Db\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tuefind_redirect')]
class Redirect implements RedirectEntityInterface
{   
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'id', type: 'integer', options: ['unsigned' => true])]
    protected int $id;

    #[ORM\Column(name: 'url', type: 'string', length: 1000, nullable: false)]
    protected string $url;

    #[ORM\Column(name: 'group_name', type: 'string', length: 1000, nullable: true)]
    protected string $groupName = '';

    #[ORM\Column(name: 'timestamp', type: 'datetime', nullable: false)]
    protected \DateTime $timestamp;

    public function __construct(){
        $this->timestamp = new \DateTime();
    }
     public function getId(): ?int
    {
        return $this->id ?? null;
    }
    
    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;
        return $this;
    }

    public function getGroupName(): ?string
    {
        return $this->groupName;
    }

    public function setGroupName(?string $groupName): static
    {
        $this->groupName = $groupName;
        return $this;
    }

    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTime $timestamp): void
    {
        $this->timestamp = $timestamp;
    }
}