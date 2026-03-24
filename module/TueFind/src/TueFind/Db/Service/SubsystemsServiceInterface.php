<?php

namespace TueFind\Db\Service;

use VuFind\Db\Service\DbServiceInterface;
use TueFind\Db\Entity\Subsystems;
use TueFind\Db\Entity\SubsystemsEntityInterface;


interface SubsystemsServiceInterface extends DbServiceInterface
{
    public function getByID(int $id): ?SubsystemsEntityInterface;

    public function getAll(): array;

}
