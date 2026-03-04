<?php

namespace TueFind\Db\Service;

use DateTime;
use TueFind\Db\Entity\CmsHistory;
use TueFind\Db\Entity\CmsHistoryEntityInterface;
use VuFind\Db\Service\DbServiceInterface;
use TueFind\Db\Entity\CmsPages;
use TueFind\Db\Entity\User;

interface CmsHistoryServiceInterface extends DbServiceInterface
{
    public function getById(int $id): ?CmsHistoryEntityInterface;

    public function getCmsHistory(): array;

    public function getCmsHistoryByPageId(int $cms_page_id): array;

    public function addCMSPageHistory(int $cmsPageId, User $user): bool;
}
