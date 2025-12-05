<?php

namespace TueFind\Db\Service;

class RssBaseService implements RssBaseServiceInterface
{
    use \VuFind\Db\Table\DbTableAwareTrait;

    protected $instance;

    public function setInstance($instance)
    {
        $this->instance = $instance;
    }
}
