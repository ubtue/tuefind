<?php

namespace TueFind\Db\Service;

use DateTime;
use TueFind\Db\Entity\CmsPagesHistory;
use TueFind\Db\Entity\CmsPagesHistoryEntityInterface;
use VuFind\Db\Service\DbServiceInterface;
use TueFind\Db\Entity\CmsPages;
use TueFind\Db\Entity\User;

interface CmsPagesHistoryServiceInterface extends DbServiceInterface
{
    public function getById(int $id): ?CmsPagesHistoryEntityInterface;

    public function getCmsHistory(): array;

    public function getCmsHistoryByPageId(int $cms_page_id): array;

    public function addCMSPageHistory(int $cmsPageId, User $user): bool;
}
