<?php

namespace TueFind\Db\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Index(name: 'admin_id', columns: ['admin_id'])]
#[ORM\Index(name: 'user_id', columns: ['user_id'])]
#[ORM\Table(name: 'tuefind_user_authorities_history')]
class UserAuthorityHistory implements UserAuthorityHistoryEntityInterface
{
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\Column(name: 'authority_id', type: 'string', length: 255, nullable: false)]
    protected string $authorityControlNumber;

    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: UserEntityInterface::class)]
    protected UserEntityInterface $user;

    #[ORM\JoinColumn(name: 'admin_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: UserEntityInterface::class)]
    protected ?UserEntityInterface $admin = null;

    #[ORM\Column(name: 'access_state', type: 'string', length: 255, nullable: false)]
    protected string $accessState;

    #[ORM\Column(name: 'request_user_date', type: 'datetime', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    protected DateTime $requestUserDate;

    #[ORM\Column(name: 'process_admin_date', type: 'datetime', nullable: true)]
    protected ?DateTime $processAdminDate = null;

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getAuthorityControlNumber(): string
    {
        return $this->authorityControlNumber;
    }

    public function setAuthorityControlNumber(string $authorityControlNumber): static
    {
        $this->authorityControlNumber = $authorityControlNumber;
        return $this;
    }

    public function getUser(): UserEntityInterface
    {
        return $this->user;
    }

    public function setUser(UserEntityInterface $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getAdmin(): ?UserEntityInterface
    {
        return $this->admin;
    }

    public function setAdmin(UserEntityInterface $admin): static
    {
        $this->admin = $admin;
        return $this;
    }

    public function getRequestUserDate(): DateTime
    {
        return $this->requestUserDate;
    }

    public function getProcessAdminDate(): ?DateTime
    {
        return $this->processAdminDate;
    }

    public function setProcessAdminDate(DateTime $dateTime): static
    {
        $this->processAdminDate = $dateTime;
        return $this;
    }

    public function getAccessState(): string
    {
        return $this->accessState;
    }

    public function setAccessState(string $accessState): static
    {
        $this->accessState = $accessState;
        return $this;
    }

    public function updateUserAuthorityHistory($adminId, $access)
    {
        $this->admin_id = $adminId;
        $this->access_state = $access;
        $this->process_admin_date = date('Y-m-d H:i:s');
        $this->save();
    }
}
