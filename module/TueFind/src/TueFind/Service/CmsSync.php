<?php

namespace TueFind\Service;

use Gitonomy\Git\Repository;
use TueFind\Db\Entity\CmsPages;
use TueFind\Db\Service\CmsPagesTranslationServiceInterface;

use function count;
use function is_array;

class CmsSync
{
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';

    protected $config;

    protected $dbServiceManager;

    protected $cmsPagesService;

    protected $subsystem;

    protected CmsPagesTranslationServiceInterface $cmsPagesTranslationService;

    protected $repository;

    protected $authManager;

    public function __construct(\VuFind\Config\Config $config, string $subsystem, \VuFind\Db\Service\PluginManager $dbServiceManager, $authManager)
    {
        $this->dbServiceManager = $dbServiceManager;
        $this->cmsPagesService = $this->dbServiceManager->get(\TueFind\Db\Service\CmsPagesServiceInterface::class);
        $this->cmsPagesTranslationService = $this->dbServiceManager->get(\TueFind\Db\Service\CmsPagesTranslationServiceInterface::class);
        $this->config = $config;
        $this->subsystem = $subsystem;
        $this->authManager = $authManager;
    }

    /**
     * exportPagesToRepository: Exports all pages from the database to JSON files and pushes them to the remote repository.
     */
    public function exportPagesToRepository(): array
    {
        $pages = $this->cmsPagesService->getAll();

        if (count($pages) > 0) {
            foreach ($pages as $page) {
                // Safely extract ID: works whether $page is an object or an array
                $pageId = is_array($page) ? ($page['id'] ?? null) : $page->getId();

                if (!$pageId) {
                    continue; // On the off chance, skip corrupted data
                }

                // step 1: load the most recent object from the database by ID
                // to ensure translations are loaded bypassing the Doctrine cache,
                // first find the page itself through getByID
                $pageObject = $this->cmsPagesService->getByID($pageId);

                if (!$pageObject) {
                    continue;
                }

                // step 2: use the method by SystemID, which we checked last time —
                // it builds a QueryBuilder and updates the relationships (translations) in PHP memory
                $freshPage = $this->cmsPagesService->getByPageSystemID(
                    $pageObject->getPageSystemId(),
                    $this->subsystem
                );

                if (!$freshPage) {
                    continue;
                }

                // generate JSON and save it
                $pageArray = $this->pageToSharedArray($freshPage);
                $pagePath = $this->getPagePath($freshPage);
                $pageJson = json_encode($pageArray, JSON_PRETTY_PRINT);
                file_put_contents($pagePath, $pageJson);
                $this->gitAdd($pagePath);
            }

            return $this->pushRepository();
        }

        return [
            'success' => true,
            'message' => 'No pages found in database to export.',
        ];
    }

    /**
     * importPagesFromRepository: Scans JSON files in the repository and updates/adds translations to the database.
     *
     * @return array An array containing the success status and message of the operation.
     */
    public function importPagesFromRepository(): array
    {
        $jsonFiles = array_diff(scandir($this->getPagesPath()), ['.', '..']);
        $updatedPages = [];

        foreach ($jsonFiles as $jsonFile) {
            if (preg_match('"\.json$"', $jsonFile)) {
                $pageJson = file_get_contents($this->getPagesPath() . DIRECTORY_SEPARATOR . $jsonFile);
                $pageArray = json_decode($pageJson, true);
                $pageSystemId = preg_replace('"\.json$"', '', $jsonFile);

                $pageInDb = $this->cmsPagesService->getByPageSystemIDWithoutTranslations($pageSystemId, $this->subsystem);

                if ($pageInDb != null) {
                    $currentTranslations = [];
                    foreach ($pageInDb->getTranslations() as $t) {
                        $currentTranslations[$t->getLanguage()] = [
                            'title' => $t->getTitle(),
                            'content' => $t->getContent(),
                        ];
                    }

                    $jsonTranslations = $pageArray['translations'] ?? [];

                    if ($jsonTranslations !== $currentTranslations) {
                        $updatedPages[] = $pageSystemId;
                    }

                    // 1. update date modified in the database to reflect the change
                    $dateModified = isset($pageArray['changed'])
                        ? new \DateTime($pageArray['changed'])
                        : new \DateTime();
                    $this->cmsPagesService->update($pageInDb->getId(), $dateModified);

                    if ($jsonTranslations === $currentTranslations) {
                        continue;
                    }

                    // 2. remove all existing translations for this page
                    if (method_exists($this->cmsPagesService, 'forceDeleteTranslations')) {
                        $this->cmsPagesService->forceDeleteTranslations($pageInDb->getId());
                    } else {
                        $this->cmsPagesTranslationService->delete($pageInDb->getId());
                    }

                    // 3. write new translations from JSON
                    if (!empty($jsonTranslations)) {
                        foreach ($jsonTranslations as $lang => $data) {
                            $title = $data['title'] ?? '';
                            $content = $data['content'] ?? '';

                            if (method_exists($this->cmsPagesService, 'forceAddTranslation')) {
                                $this->cmsPagesService->forceAddTranslation(
                                    $pageInDb->getId(),
                                    $lang,
                                    $title,
                                    $content
                                );
                            } else {
                                $this->cmsPagesTranslationService->add(
                                    $pageInDb->getId(),
                                    $lang,
                                    $title,
                                    $content
                                );
                            }
                        }
                    }
                }
            }
        }

        // final flush to ensure all changes are committed to the database
        if (method_exists($this->cmsPagesService, 'flushEntityManager')) {
            $this->cmsPagesService->flushEntityManager();
        }

        $msg = 'Database successfully updated from local repository files.';
        if (!empty($updatedPages)) {
            $msg .= ' Changes found and updated in pages: ' . implode(', ', $updatedPages) . '.';
        } else {
            $msg .= ' No changes detected in existing pages.';
        }

        return [
            'success' => true,
            'message' => $msg,
        ];
    }

    /**
     * converts a CmsPages entity to a standardized array format for JSON export.
     *
     * @param  CmsPages|array $page The page entity or array to convert.
     * @return array The standardized array representation of the page.
     * @throws \Exception If the page is not found or invalid.
     */
    public function pageToSharedArray($page): array
    {
        $array = [];
        if (!$page instanceof CmsPages) {
            $page = $this->cmsPagesService->getByID($page['id']);
        }
        $array['created'] = $page->getCreateDate()->format(static::DATETIME_FORMAT);
        $array['changed'] = $page->getChangeDate()->format(static::DATETIME_FORMAT);

        $translations = [];
        foreach ($page->getTranslations() as $translation) {
            $translations[$translation->getLanguage()] = [
                'title' => $translation->getTitle(),
                'content' => $translation->getContent(),
            ];
        }
        $array['translations'] = $translations;

        return $array;
    }

    /**
     * downloads the latest changes from the remote repository and updates the local repository.
     *
     * @return array An array containing the success status and message of the operation.
     * @throws \Exception If the git pull operation fails.
     */
    public function pullRepository(): array
    {
        try {
            $this->gitPull();
            return [
                'success' => true,
                'message' => 'Git pull completed successfully.',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Git pull failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * checks for changes, commits them, and pushes to the remote repository.
     *
     * @return array An array containing the success status and message of the operation.
     * @throws \Exception If the git push operation fails.
     */
    public function pushRepository(): array
    {
        try {
            $repo = $this->initRepo();
            $statusText = $repo->run('status', ['--porcelain']);

            $hasChanges = false;
            $changedFiles = [];

            if (!empty($statusText)) {
                $lines = explode("\n", trim($statusText));
                foreach ($lines as $line) {
                    if (trim($line) === '') {
                        continue;
                    }

                    // if the line does not start with ?? (untracked file), then there are modified JSON files
                    if (!str_starts_with($line, '?? ')) {
                        $hasChanges = true;
                        $changedFiles[] = trim(substr($line, 3)); // Remember the file name for logging
                    }
                }
            }

            if (!$hasChanges) {
                return [
                    'success' => true,
                    'message' => 'Git push not required: Repository is already up to date.',
                ];
            }

            $user = $this->authManager->getUserObject();
            $commitMessage = 'CMS Sync Auto-Commit: ' . date('Y-m-d H:i:s') . ' - ' . $user->getUsername();
            $this->gitCommit($commitMessage);
            $this->gitPush();

            return [
                'success' => true,
                'message' => 'Git push completed successfully! Pushed changes for files: ' . implode(', ', $changedFiles) . '.',
            ];
        } catch (\Exception $e) {
            $outputText = $e->getMessage();
            if (
                str_contains($outputText, 'nothing to commit') ||
                str_contains($outputText, 'up-to-date') ||
                str_contains($outputText, 'Everything up-to-date')
            ) {
                return [
                    'success' => true,
                    'message' => 'Git push not required: Repository is already up to date.',
                ];
            }

            return [
                'success' => false,
                'message' => 'Git push failed: ' . $e->getMessage(),
            ];
        }
    }

    public function gitAdd(string $file = null)
    {
        $files = ($file === null) ? ['.'] : [$file];
        $this->initRepo()->run('add', $files);
    }

    public function gitCheckout(string $file = null)
    {
        $checkoutTarget = ($file != null) ? $file : $this->getBranch();
        $this->initRepo()->run('checkout', $checkoutTarget);
    }

    public function gitCommit(string $message)
    {
        $this->initRepo()->run('commit', ['-m', $message]);
    }

    public function gitPull()
    {
        $this->initRepo()->run('pull');
    }

    public function gitPush()
    {
        $this->initRepo()->run('push');
    }

    public function isEnabled(): bool
    {
        return isset($this->config->sync_enabled) && $this->config->sync_enabled == 1;
    }

    public function getBranch(): string
    {
        return $this->config->repository_branch;
    }

    protected function getRepositoryPath(): string
    {
        return $this->config->repository_path;
    }

    protected function getSubsystemPath(): string
    {
        return $this->getRepositoryPath() . DIRECTORY_SEPARATOR . $this->subsystem;
    }

    protected function getFilesPath(): string
    {
        return $this->getSubsystemPath() . DIRECTORY_SEPARATOR . 'files';
    }

    protected function getPagesPath(): string
    {
        return $this->getSubsystemPath() . DIRECTORY_SEPARATOR . 'pages';
    }

    protected function getPagePath(CmsPages $page): string
    {
        return $this->getPagesPath() . DIRECTORY_SEPARATOR . $page->getPageSystemId() . '.json';
    }

    protected function getSshKeyPath(): string
    {
        return $this->config->ssh_key_path;
    }

    protected function initRepo(): Repository
    {
        if (!isset($this->repository)) {
            $this->repository = new Repository($this->getRepositoryPath());
            $this->repository->run('config', [
                'core.sshCommand',
                'ssh -i ' . escapeshellarg($this->getSshKeyPath()) . ' -o StrictHostKeyChecking=no',
            ]);
        }
        if ($this->repository->getHead()->getName() != $this->getBranch()) {
            throw new \Exception('Wrong branch is active! Please contact server admin!');
        }
        return $this->repository;
    }
}
