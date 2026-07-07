<?php

namespace TueFind\Db\Service;

use DateTime;
use TueFind\Db\Entity\CmsPages;
use TueFind\Db\Entity\CmsPagesEntityInterface;
use TueFind\Db\Entity\Subsystems;
use VuFind\Db\Service\AbstractDbService;

class CmsPagesService extends AbstractDbService implements CmsPagesServiceInterface
{
    public function getByID(int $id): ?CmsPagesEntityInterface
    {
        return $this->entityManager->find(CmsPagesEntityInterface::class, $id);
    }

    public function getAll(): array
    {

        $dql = 'SELECT cp, s
                FROM ' . CmsPages::class . ' cp
                LEFT JOIN cp.subSystem s
                ORDER BY cp.id DESC';
        $query = $this->entityManager->createQuery($dql);

        return  $query->getArrayResult();
    }

    public function getByPageSystemID(string $pageSystemId, string $subSystem): ?CmsPagesEntityInterface
    {

        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('cms', 'subSystem')
            ->from(CmsPages::class, 'cms')
            ->leftJoin('cms.subSystem', 'subSystem')
            ->where('cms.pageSystemId = :pageSystemId')
            ->andWhere('subSystem.subSystem = :subSystem')
            ->setParameter('pageSystemId', $pageSystemId)
            ->setParameter('subSystem', $subSystem);

        $result = $qb->getQuery()->getOneOrNullResult();
        return $result;
    }

    public function add(int $subSystemId, string $pageSystemId, DateTime $createdDate, DateTime $changeDate): int
    {
        $cmsPage = new CmsPages();

        $subSystem = $this->entityManager->find(Subsystems::class, $subSystemId);
        if (!$subSystem) {
            throw new \RuntimeException('Subsystem not found');
        }

        $cmsPage->setSubSystem($subSystem);
        $cmsPage->setPageSystemId($pageSystemId);
        $cmsPage->setCreateDate($createdDate);
        $cmsPage->setChangeDate($changeDate);

        $this->entityManager->persist($cmsPage);
        $this->entityManager->flush();

        return $cmsPage->getId();
    }

    public function update(int $cmsPageId, DateTime $dateModified): CmsPages
    {

        // Load page (adapt to your table/service)
        $page = $this->entityManager->find(CmsPages::class, $cmsPageId);
        if (!$page) {
            throw new \RuntimeException("CMS page not found: $cmsPageId");
        }
        $page->setChangeDate($dateModified);
        $this->entityManager->persist($page);
        $this->entityManager->flush();

        return $page;
    }

    public function delete(int $id): void
    {

        $CMSPage = $this->getById($id);

        if ($CMSPage === null) {
            throw new \Exception('Content page not found');
        }

        $this->deleteEntity($CMSPage);
    }

    public function getByPageSystemIDWithoutTranslations(string $pageSystemId, string $subSystem): ?CmsPagesEntityInterface
    {
        // redirect to getByPageSystemID, as translations are not loaded by default and this is only used for syncing, where we do not need the translations
        return $this->getByPageSystemID($pageSystemId, $subSystem);
    }

    /**
     * clearTranslations: Directly deletes all translations for a given page ID using raw SQL.
     *
     * @param int $pageId The ID of the page whose translations should be deleted.
     */
    public function forceDeleteTranslations(int $pageId): void
    {
        $this->entityManager->getConnection()->executeStatement(
            'DELETE FROM tuefind_cms_pages_translation WHERE cms_pages_id = :pageId',
            ['pageId' => $pageId]
        );
    }

    /**
     * forceAddTranslation: Directly inserts a new translation for a given page ID using raw SQL.
     *
     * @param int    $pageId  The ID of the page for which to add a translation.
     * @param string $lang    The language of the translation.
     * @param string $title   The title of the translation.
     * @param string $content The content of the translation.
     */
    public function forceAddTranslation(int $pageId, string $lang, string $title, string $content): void
    {
        $this->entityManager->getConnection()->executeStatement(
            'INSERT INTO tuefind_cms_pages_translation (cms_pages_id, language, title, content)
            VALUES (:pageId, :lang, :title, :content)',
            [
                'pageId'  => $pageId,
                'lang'    => $lang,
                'title'   => $title,
                'content' => $content,
            ]
        );
    }

    public function flushEntityManager(): void
    {
        // 1. Standard Doctrine flush
        $this->entityManager->flush();

        // 2. Hard commit at the database connection level
        $connection = $this->entityManager->getConnection();

        // If Doctrine or the framework opened a transaction, forcefully close it with a commit
        if ($connection->isTransactionActive()) {
            $connection->commit();
        }

        // 3. Clear the memory cache
        $this->entityManager->clear();
    }
}
