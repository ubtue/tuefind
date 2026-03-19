<?php

namespace TueFind\Db\Service;

use DateTime;
use VuFind\Db\Service\DbServiceInterface;
use TueFind\Db\Entity\CmsPages;
use TueFind\Db\Entity\CmsPagesEntityInterface;

interface CmsPagesServiceInterface extends DbServiceInterface
{
    public function getByID(int $id): ?CmsPagesEntityInterface;

    public function getAll(): array;

    public function getByIDFull(int $cmsPageId): ?array;

    public function getByPageSystemID(string $pageSystemId, string $subSystem, string $language): ?array;

    public function add(string $pageSystemId, DateTime $dateCreated, DateTime $dateModified): int;

    public function update(
        int $cmsPageId,
        DateTime $dateModified
    ): CmsPages;

    public function delete(int $cmsPageId): void;
}
