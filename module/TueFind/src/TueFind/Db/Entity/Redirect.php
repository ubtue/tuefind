<?php

namespace TueFind\Db\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tuefind_redirect')]
class Redirect implements RedirectEntityInterface
{
    #[ORM\Column(name: 'url', type: 'string', length: 1000, nullable: false)]
    protected string $url;

    #[ORM\Column(name: 'group_name', type: 'string', length: 1000, nullable: true)]
    protected string $groupName = null;

    #[ORM\Column(name: 'publication_datetime', type: 'datetime', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    protected DateTime $timestamp;

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

    public function getTimestamp(): DateTime
    {
        return $this->timestamp;
    }
}
