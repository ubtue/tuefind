<?php

namespace TueFind\Db\Service;

use VuFind\Db\Service\DbServiceInterface;
use TueFind\Db\Entity\CmsPagesHistoryEntityInterface;
use TueFind\Db\Entity\User;
use TueFind\Db\Entity\CmsPagesHistory;

interface CmsPagesHistoryServiceInterface extends DbServiceInterface
{
    public function getByID(int $id): ?CmsPagesHistoryEntityInterface;

    public function getAll(): array;

    public function getByPageID(int $cmsPageId): array;

    public function add(int $cmsPageId, User $user): CmsPagesHistory;
}
