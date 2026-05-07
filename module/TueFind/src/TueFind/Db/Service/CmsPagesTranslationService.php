<?php

namespace TueFind\Db\Service;

use VuFind\Db\Service\AbstractDbService;
use TueFind\Db\Entity\CmsPages;
use TueFind\Db\Entity\CmsPagesTranslation;
use TueFind\Db\Entity\CmsPagesTranslationEntityInterface;

class CmsPagesTranslationService extends AbstractDbService implements  CmsPagesTranslationServiceInterface
{

    public function getByCMSID(int $cmsPageId): ?array
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

    public function add(
        int $cmsPageId,
        string $language,
        string $title,
        string $content
    ): CmsPagesTranslationEntityInterface {
        $cmsPageTranslation = new CmsPagesTranslation();

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

        return $cmsPageTranslation;
    }

    public function save(CmsPagesTranslationEntityInterface $cmsPageTranslation)
    {
        $this->entityManager->persist($cmsPageTranslation);
        $this->entityManager->flush();
    }

    public function delete(int $cmsPageId, string $language=null): void
    {
        $translations = $this->entityManager->getRepository(CmsPagesTranslationEntityInterface::class)
                     ->findBy(['cmsPagesId' => $cmsPageId]);
        foreach ($translations as $t) {
            if ($language == null || $language == $t->getLanguage()) {
                $this->entityManager->remove($t);
            }
        }
        $this->entityManager->flush();
    }
}
