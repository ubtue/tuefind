<?php

namespace TueFind\Db\Service;

use VuFind\Db\Service\AbstractDbService;

class RssBaseService extends AbstractDbService implements RssBaseServiceInterface
{
    use \VuFind\Db\Service\DbServiceAwareTrait;

    protected $instance;

    public function setInstance($instance)
    {
        $this->instance = $instance;
    }
}
