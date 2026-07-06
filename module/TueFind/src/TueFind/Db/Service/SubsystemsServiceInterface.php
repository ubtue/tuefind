<?php

namespace TueFind\Db\Service;

use TueFind\Db\Entity\SubsystemsEntityInterface;
use VuFind\Db\Service\DbServiceInterface;

interface SubsystemsServiceInterface extends DbServiceInterface
{
    public function getByID(int $id): SubsystemsEntityInterface;

    public function getByName(string $name): SubsystemsEntityInterface;

    public function getAll(): array;
}
