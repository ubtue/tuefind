<?php

namespace TueFind\Db\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use VuFind\Db\Entity\EntityInterface;
use TueFind\Db\Entity\CmsPages;


#[ORM\Entity]
#[ORM\Table(name: 'cms_pages_translation')]
class CmsPagesTranslation implements CmsPagesTranslationEntityInterface
{

    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\Column(name: 'cms_pages_id', type: 'integer', nullable: false)]
    protected int $cmsPagesId;

    #[ORM\Column(name: 'language', type: 'string', length: 50, nullable: true)]
    protected string $language;

    #[ORM\Column(name: 'title', type: 'string', length: 255, nullable: true)]
    protected string $title;

    #[ORM\Column(name: 'content', type: 'text', length: 16777215, nullable: true)]
    protected string $content;

    #[ORM\ManyToOne(
        targetEntity: CmsPages::class,
        inversedBy: 'cmsPagesTranslations'
    )]
    #[ORM\JoinColumn(
        name: 'cms_pages_id',
        referencedColumnName: 'id',
        nullable: false
    )]
    protected CmsPages $cmsPage;

    public function getCmsPage(): CmsPages
    {
        return $this->cmsPage;
    }

    public function setCmsPage(CmsPages $cmsPage): static
    {
        $this->cmsPage = $cmsPage;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getLanguage(): ?string
    {
        return $this->language ?? null;
    }

    public function setLanguage(string $language): bool
    {
        $this->language = $language;
        return true;
    }

    public function getTitle(): ?string
    {
        return $this->title ?? null;
    }

    public function setTitle(string $title): bool
    {
        $this->title = $title;
        return true;
    }

    public function getContent(): ?string
    {
        return $this->content ?? null;
    }

    public function setContent(string $content): bool
    {
        $this->content = $content;
        return true;
    }

}