<?php

namespace TueFind\Db\Service;

use Doctrine\ORM\EntityManagerInterface;
use VuFind\Db\Service\AbstractDbService;

abstract class RssBaseService extends AbstractDbService implements RssBaseServiceInterface
{
    protected string $instance;

    public function setInstance(string $instance): void
    {
        $this->instance = $instance;
    }

    protected function getInstance(): string
    {
        return $this->instance;
    }
}
