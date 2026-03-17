<?php

namespace TueFind\Db\Service;

use DateTime;
use TueFind\Db\Entity\CmsPages;
use TueFind\Db\Entity\CmsPagesEntityInterface;
use VuFind\Db\Service\DbServiceInterface;

interface CmsPagesServiceInterface extends DbServiceInterface
{
    public function getById(int $id): ?CmsPagesEntityInterface;

    public function getCmsPages(): array;

    public function getCMSPageByID(int $cmsPageId): ?array;

    public function getCMSPageByPageSystemId(string $pageSystemId, string $subSystem, string $language): ?array;

    public function addCMSPage(string $pageSystemId, DateTime $dateCreated, DateTime $dateModified): int;

    public function updateCMSPage(
        int $cmsPageId,
        DateTime $dateModified
    ): bool;

    public function deleteCMSPage(int $cmsPageId): void;
}
