<?php

namespace TueFind\Db\Service;

use TueFind\Db\Entity\CmsPagesSubsystem;
use TueFind\Db\Entity\CmsPagesSubsystemEntityInterface;
use VuFind\Db\Service\AbstractDbService;
use TueFind\Db\Entity\CmsPages;

class CmsPagesSubsystemService extends AbstractDbService implements  CmsPagesSubsystemServiceInterface
{
    
    public function getById(int $id): ?CmsPagesSubsystemEntityInterface
    {
        return $this->entityManager->find(CmsPagesSubsystemEntityInterface::class, $id);
    }

    public function addCMSPageSubsystem(int $cmsPageId, string $subsystem): bool
    {
        $cmsPagesSubsystem = new CmsPagesSubsystem();
        $cmsPagesSubsystem->setCmsPage($this->entityManager->find(CmsPages::class, $cmsPageId));
        $cmsPagesSubsystem->setSubsystem($subsystem);

        try {
            $this->entityManager->persist($cmsPagesSubsystem);
            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            // Log the exception or handle it as needed
            return false;
        }
    }
}
