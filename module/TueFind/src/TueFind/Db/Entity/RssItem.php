<?php

namespace TueFind\Db\Entity;

class RssItem implements RssItemEntityInterface
{
    public function __construct(\Laminas\Db\Adapter\Adapter $adapter)
    {
        parent::__construct('id', 'tuefind_rss_items', $adapter);
    }
}
