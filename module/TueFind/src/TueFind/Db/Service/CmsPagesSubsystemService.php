<?php

namespace TueFind\Db\Service;

use VuFind\Db\Service\AbstractDbService;
use TueFind\Db\Entity\CmsPages;
use TueFind\Db\Entity\CmsPagesSubsystem;
use TueFind\Db\Entity\CmsPagesSubsystemEntityInterface;

class CmsPagesSubsystemService extends AbstractDbService implements  CmsPagesSubsystemServiceInterface
{
    
    public function getByID(int $id): ?CmsPagesSubsystemEntityInterface
    {
        return $this->entityManager->find(CmsPagesSubsystemEntityInterface::class, $id);
    }

    public function add(int $cmsPageId, string $subSystem): CmsPagesSubsystem
    {
        $cmsPagesSubsystem = new CmsPagesSubsystem();
        $cmsPagesSubsystem->setCmsPage($this->entityManager->find(CmsPages::class, $cmsPageId));
        $cmsPagesSubsystem->setSubsystem($subSystem);

        try {
            $this->entityManager->persist($cmsPagesSubsystem);
            $this->entityManager->flush();
            return $cmsPagesSubsystem;
        } catch (\Exception $e) {
            // Log the exception or handle it as needed
            return $cmsPagesSubsystem; // Return the object even if it wasn't persisted, or handle as needed
        }
    }
}
