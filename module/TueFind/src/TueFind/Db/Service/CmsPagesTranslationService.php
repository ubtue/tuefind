<?php

namespace TueFind\Db\Service;

use DateTime;
use TueFind\Db\Entity\CmsPages;
use TueFind\Db\Entity\CmsPagesTranslation;
use VuFind\Db\Service\AbstractDbService;
use Doctrine\ORM\Query\Expr\Join;
use TueFind\Db\Entity\CmsPagesTranslation as EntityCmsPagesTranslation;

use function intval;


class CmsPagesTranslationService extends AbstractDbService implements  CmsPagesTranslationServiceInterface
{
    
    public function getCMSPageTranslationByCMSId(int $cmsPageId): ?array
    {
        $dql = '
            SELECT cpt
            FROM ' . CmsPagesTranslation::class . ' cpt
            WHERE cpt.cmsPages = :page
        ';

        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('page', $cmsPageId);

        $result = $query->getOneOrNullResult();

        return $result;
       
    }

    public function addCMSPageTranslation(
        int $cmsPageId,
        string $language,
        string $title,
        string $content
    ): bool {
        $cmsPageTranslation = new CmsPagesTranslation();

        // 🔑 ВАЖНО: устанавливаем ENTITY, а не ID
        $cmsPage = $this->entityManager->getReference(
            CmsPages::class,
            $cmsPageId
        );

        $cmsPageTranslation->setCmsPage($cmsPage);
        $cmsPageTranslation->setLanguage($language);
        $cmsPageTranslation->setTitle($title);
        $cmsPageTranslation->setContent($content);

        $this->entityManager->persist($cmsPageTranslation);
        $this->entityManager->flush();

        return true;
    }
    
    /*
    public function deleteCMSPageTranslation(int $cmsPageId): bool

    {
        return $this->entityManager->transactional(function ($em) use ($cmsPageId) {
            $dql = '
                DELETE FROM ' . CmsPagesTranslation::class . ' cpt
                WHERE cpt.cmsPages = :page
            ';

            $pageRef = $em->getReference(CmsPages::class, $cmsPageId);

            $query = $em->createQuery($dql);
            $query->setParameter('page', $pageRef);

            return $query->execute() > 0;
        });
    }
        */
    public function deleteCMSPageTranslation(int $cmsPageId): void
    {
        $translations = $this->entityManager->getRepository(CmsPagesTranslation::class)
                     ->findBy(['cmsPagesId' => $cmsPageId]);
        foreach ($translations as $t) {
            $this->entityManager->remove($t);
        }
        $this->entityManager->flush();
    }
}
