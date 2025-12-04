<?php

namespace TueFind\Db\Entity;

class RssFeed implements RssFeedEntityInterface
{
    public function __construct(\Laminas\Db\Adapter\Adapter $adapter)
    {
        parent::__construct('id', 'tuefind_rss_feeds', $adapter);
    }
}
