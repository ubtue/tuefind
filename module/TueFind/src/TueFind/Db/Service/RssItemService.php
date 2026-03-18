<?php

namespace TueFind\Db\Service;

use TueFind\Db\Entity\RssItem;
use TueFind\Db\Entity\RssSubscription;

class RssItemService extends RssBaseService implements RssItemServiceInterface
{

    public function getItemsSortedByPubDate() : array{
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('ri', 'rf')
            ->from(RssItem::class, 'ri')
            ->leftJoin('ri.rssFeed', 'rf') // связь ManyToOne
            ->where('rf.subsystemTypes LIKE :instance')
            ->andWhere('rf.active = 1')
            ->setParameter('instance', '%' . $this->instance . '%')
            ->orderBy('ri.publicationDateTime', 'DESC');

        return $qb->getQuery()->getArrayResult();
    }

    public function getItemsForUserSortedByPubDate($userId){
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('ri', 'rf', 'rs')
            ->from(RssItem::class, 'ri')
            ->leftJoin('ri.rssFeed', 'rf')
            ->leftJoin(RssSubscription::class, 'rs', 'WITH', 'ri.rssFeed = rs.rssFeed')
            ->where('rs.user = :userId')
            ->andWhere('rf.active = 1')
            ->setParameter('userId', $userId)
            ->orderBy('ri.publicationDateTime', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function hasUrl($url) : bool
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('COUNT(ri.id)')
            ->from(RssItem::class, 'ri')
            ->where('ri.itemUrl = :url')
            ->setParameter('url', $url);

        return (bool) $qb->getQuery()->getSingleScalarResult();
    }
}