<?php

namespace TueFind\Db\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'tuefind_user_authorities')]
#[ORM\UniqueConstraint(name: 'user_authority', columns: ['user_id', 'authority_id'])]
#[ORM\Entity]
class UserAuthority implements UserAuthorityEntityInterface
{
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: UserEntityInterface::class)]
    protected ?UserEntityInterface $user = null;

    #[ORM\Column(name: 'authority_id', type: 'string', length: 255, nullable: false)]
    protected string $authorityControlNumber = '';

    // TODO: Change to Type?
    #[ORM\Column(name: 'access_state', type: 'string', length: 255, nullable: true)]
    protected string $accessState;

    #[ORM\Column(name: 'requested_datetime', type: 'datetime', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    protected DateTime $requestedDatetime;

    #[ORM\Column(name: 'granted_datetime', type: 'datetime', nullable: true)]
    protected ?DateTime $grantedDatetime = null;

    public function __construct()
    {
        $this->requestedDatetime = new DateTime();
    }

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

    public function getRequestedDatetime(): DateTime
    {
        return $this->requestedDatetime;
    }

    public function getGrantedDatetime(): ?DateTime
    {
        return $this->grantedDatetime;
    }

    public function setGrantedDatetime(DateTime $grantedDatetime): static
    {
        $this->grantedDatetime = $grantedDatetime;
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
}
