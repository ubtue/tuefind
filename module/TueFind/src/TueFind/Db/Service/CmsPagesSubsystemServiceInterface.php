<?php

namespace TueFind\Db\Service;

use VuFind\Db\Service\DbServiceInterface;
use TueFind\Db\Entity\CmsPagesSubsystem;
use TueFind\Db\Entity\CmsPagesSubsystemEntityInterface;

interface CmsPagesSubsystemServiceInterface extends DbServiceInterface
{
    public function getByID(int $id): ?CmsPagesSubsystemEntityInterface;

    public function add(int $cmsPageId, string $subSystem): CmsPagesSubsystem;

}
