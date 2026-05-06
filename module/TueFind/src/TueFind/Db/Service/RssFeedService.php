<?php

namespace TueFind\Db\Service;

use TueFind\Db\Entity\RssFeedEntityInterface;

class RssFeedService extends RssBaseService implements RssFeedServiceInterface
{

    public function getFeedsSortedByName(): array
    {
         $qb = $this->entityManager->createQueryBuilder();
            $qb->select('rf')
                ->from(RssFeedEntityInterface::class, 'rf')
                ->where('rf.active = 1')
                ->andWhere('rf.subsystemTypes LIKE :instance')
                ->setParameter('instance', '%' . $this->instance . '%')
                ->orderBy('rf.feedName', 'ASC');
        return $qb->getQuery()->getResult();
    }

    public function hasUrl($url): bool
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(rf.id)')
            ->from(RssFeedEntityInterface::class, 'rf')
            ->where('rf.websiteUrl = :url')
            ->orWhere('rf.feedUrl = :url')
            ->setParameter('url', $url);
        return (bool) $qb->getQuery()->getSingleScalarResult();
    }
}
