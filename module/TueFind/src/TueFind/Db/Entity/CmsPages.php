<?php

namespace TueFind\Db\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tuefind_cms_pages')]
class CmsPages implements CmsPagesEntityInterface
{
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\Column(name: 'page_system_id', type: 'string', length: 255, nullable: false)]
    protected string $pageSystemId;

    #[ORM\Column(name: 'custom_js', type: Types::TEXT, nullable: true)]
    protected ?string $customJs = null;

    #[ORM\Column(name: 'custom_css', type: Types::TEXT, nullable: true)]
    protected ?string $customCss = null;

    #[ORM\Column(name: 'created', type: 'datetime', length: 255, nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    protected DateTime $createDate;

    #[ORM\Column(name: 'changed', type: 'datetime', length: 255, nullable: false)]
    protected DateTime $changeDate;

    #[ORM\OneToMany(
        mappedBy: 'cmsPage',
        targetEntity: User::class
    )]
    protected Collection $users;

    #[ORM\OneToMany(
        mappedBy: 'cmsPage',
        targetEntity: CmsPagesTranslation::class
    )]
    protected Collection $cmsPagesTranslations;

    #[ORM\OneToMany(
        mappedBy: 'cmsPage',
        targetEntity: CmsPagesHistory::class
    )]
    protected Collection $history;

    #[ORM\ManyToOne(targetEntity: Subsystems::class)]
    #[ORM\JoinColumn(name: 'subsystem_id', referencedColumnName: 'id', nullable: false)]
    protected Subsystems $subSystem;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->cmsPagesTranslations = new ArrayCollection();
        $this->history = new ArrayCollection();
        $this->createDate = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getSubSystem(): Subsystems
    {
        return $this->subSystem;
    }

    public function setSubSystem(Subsystems $subSystem): static
    {
        $this->subSystem = $subSystem;
        return $this;
    }

    public function getPageSystemId(): ?string
    {
        return $this->pageSystemId ?? null;
    }

    public function setPageSystemId(string $pageSystemId): static
    {
        $this->pageSystemId = $pageSystemId;
        return $this;
    }

    public function getCustomJs(): ?string
    {
        return $this->customJs ?? null;
    }

    public function setCustomJs(?string $customJs): static
    {
        $this->customJs = $customJs;
        return $this;
    }

    public function getCustomCss(): ?string
    {
        return $this->customCss ?? null;
    }

    public function setCustomCss(?string $customCss): static
    {
        $this->customCss = $customCss;
        return $this;
    }

    public function getCreateDate(): ?DateTime
    {
        return $this->createDate;
    }

    public function setCreateDate(DateTime $createDate): static
    {
        $this->createDate = $createDate;
        return $this;
    }

    public function getChangeDate(): ?DateTime
    {
        return $this->changeDate;
    }

    public function setChangeDate(DateTime $changeDate): static
    {
        $this->changeDate = $changeDate;
        return $this;
    }

    public function getTranslations(): Collection
    {
        return $this->cmsPagesTranslations;
    }

    public function getTranslation(string $language): ?CmsPagesTranslation
    {
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLanguage() == $language) {
                return $translation;
            }
        }
        return null;
    }

    public function getHistory(): Collection
    {
        return $this->history;
    }
}
