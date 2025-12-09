<?php

namespace TueFind\Db\Service;

use VuFind\Db\Service\AbstractDbService;

class RedirectService extends AbstractDbService implements RedirectServiceInterface
{
    /**
     * Insert an URL with an optional group.
     * Timestamp will be added automatically, for later statistical analysis.
     *
     * @param string $url   The redirect target
     * @param string $group A group which might be use for later statistics
     */
    public function insertUrl(string $url, string $group=null) {
        $this->insert(['url' => $url, 'group_name' => $group]);
    }
}
