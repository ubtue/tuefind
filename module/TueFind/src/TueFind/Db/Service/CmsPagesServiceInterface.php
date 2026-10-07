<?php

namespace TueFind\Db\Service;

use DateTime;
use TueFind\Db\Entity\CmsPages;
use TueFind\Db\Entity\CmsPagesEntityInterface;
use VuFind\Db\Service\DbServiceInterface;

interface CmsPagesServiceInterface extends DbServiceInterface
{
    public function getByID(int $id): ?CmsPagesEntityInterface;

    public function getAll(): array;

    public function getByPageSystemID(string $pageSystemId, string $subSystem): ?CmsPagesEntityInterface;

    public function add(int $subSystemId, string $pageSystemId, string $customJs, string $customCss, DateTime $dateCreated, DateTime $dateModified): int;

    public function update(
        int $cmsPageId,
        string $customJs,
        string $customCss,
        DateTime $dateModified
    ): CmsPages;

    public function delete(int $cmsPageId): void;
}
