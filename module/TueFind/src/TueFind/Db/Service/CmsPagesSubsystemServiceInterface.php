<?php

namespace TueFind\Db\Service;

use DateTime;
use TueFind\Db\Entity\CmsPagesSubsystem;
use TueFind\Db\Entity\CmsPagesSubsystemEntityInterface;
use VuFind\Db\Service\DbServiceInterface;
use TueFind\Db\Entity\CmsPages;

interface CmsPagesSubsystemServiceInterface extends DbServiceInterface
{
    public function getById(int $id): ?CmsPagesSubsystemEntityInterface;

    public function addCMSPageSubsystem(int $cmsPageId, string $subsystem): bool;

}
