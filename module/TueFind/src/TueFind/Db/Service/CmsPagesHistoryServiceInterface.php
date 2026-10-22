<?php

namespace TueFind\Db\Service;

use TueFind\Db\Entity\CmsPagesHistory;
use TueFind\Db\Entity\CmsPagesHistoryEntityInterface;
use TueFind\Db\Entity\Subsystems;
use TueFind\Db\Entity\User;
use VuFind\Db\Service\DbServiceInterface;

interface CmsPagesHistoryServiceInterface extends DbServiceInterface
{
    public function getByID(int $id): ?CmsPagesHistoryEntityInterface;

    public function getAllBySubsystem(Subsystems $subsystem): array;

    public function getByPageID(int $cmsPageId): array;

    public function add(?int $cmsPageId, User $user = null, Subsystems $subsystem, string $comment = ''): CmsPagesHistory;
}
