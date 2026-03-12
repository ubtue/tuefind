<?php

namespace TueFind\Db\Service;

//use HebisTmp\Db\Entity\ContentPageInterface;
use VuFind\Db\Service\DbServiceInterface;

interface CmsPagesTranslationServiceInterface extends DbServiceInterface
{
    public function getCMSPageTranslationByCMSId(int $cmsPageId): ?array;

    public function addCMSPageTranslation(
        int $cmsPageId,
        string $language,
        string $title,
        string $content
    ): bool;

    public function deleteCMSPageTranslation(int $cmsPageId): void;
}
