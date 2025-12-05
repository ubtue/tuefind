<?php

namespace TueFind\Db\Service;

use VuFind\Db\Row\RowGateway;
use VuFind\Db\Table\PluginManager;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Predicate\PredicateSet;

class RssFeedService extends RssBaseService implements RssFeedServiceInterface
{
    public function __construct(Adapter $adapter, PluginManager $tm, $cfg,
        RowGateway $rowObj = null, $table = 'tuefind_rss_feeds'
    ) {
        parent::__construct($adapter, $tm, $cfg, $rowObj, $table);
    }

    public function getFeedsSortedByName()
    {
        $select = $this->getSql()->select();
        $select->where->like('subsystem_types', '%' . $this->instance . '%');
        $select->where(['active'=>'1']);
        $select->order('feed_name ASC');
        return $this->selectWith($select);
    }

    public function hasUrl($url)
    {
        $select = $this->getSql()->select();
        $select->where(['website_url' => $url, 'feed_url' => $url], PredicateSet::OP_OR);
        $rows = $this->selectWith($select);
        return (count($rows) > 0);
    }
}
