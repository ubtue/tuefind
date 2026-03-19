<?php

namespace TueFind\Db\Service;

use VuFind\Db\Service\DbServiceInterface;
use TueFind\Db\Entity\CmsPagesTranslation;

interface CmsPagesTranslationServiceInterface extends DbServiceInterface
{
    public function getByCMSID(int $cmsPageId): ?array;

    public function add(
        int $cmsPageId,
        string $language,
        string $title,
        string $content
    ): CmsPagesTranslation;

    public function delete(int $cmsPageId): void;
}
