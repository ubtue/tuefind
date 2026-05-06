<?php

namespace TueFind\Db\Service;

use VuFind\Db\Service\DbServiceInterface;
use TueFind\Db\Entity\CmsPagesTranslationEntityInterface;

interface CmsPagesTranslationServiceInterface extends DbServiceInterface
{
    public function getByCMSID(int $cmsPageId): ?array;

    public function add(
        int $cmsPageId,
        string $language,
        string $title,
        string $content
    ): CmsPagesTranslationEntityInterface;

    public function save(CmsPagesTranslationEntityInterface $cmsPageTranslation);

    public function delete(int $cmsPageId, string $language=null): void;
}
