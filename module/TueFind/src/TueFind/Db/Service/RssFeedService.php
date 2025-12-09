<?php

namespace TueFind\Db\Service;

use TueFind\Db\Entity\RssFeedEntityInterface;
use Laminas\Db\Sql\Predicate\PredicateSet;

class RssFeedService extends RssBaseService implements RssFeedServiceInterface
{

    public function getFeedsSortedByName(): array
    {
        $dql = 'SELECT R '
            . 'FROM ' . RssFeedEntityInterface::class . ' R '
            . 'WHERE R.active = 1 '
            . 'AND R.subsystemTypes LIKE :subsystem_type '
            . 'ORDER BY R.feedName ASC ';

        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('subsystem_type', '%' . $this->instance . '%');
        return $query->getResult();
    }

    public function hasUrl($url)
    {
        $select = $this->getSql()->select();
        $select->where(['website_url' => $url, 'feed_url' => $url], PredicateSet::OP_OR);
        $rows = $this->selectWith($select);
        return (count($rows) > 0);
    }
}
