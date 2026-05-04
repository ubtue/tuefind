<?php

namespace TueFind\Service;

use Gitonomy\Git\Repository;
use TueFind\Db\Entity\CmsPages;

class CmsSync {
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    // Passed through via constructor
    protected $config;
    protected $dbServiceManager;
    protected $cmsPagesService;

    // Internal cache
    protected $repository;

    public function __construct(\VuFind\Config\Config $config, string $subsystem, \VuFind\Db\Service\PluginManager $dbServiceManager)
    {
        $this->dbServiceManager = $dbServiceManager;
        $this->cmsPagesService = $this->dbServiceManager->get(\TueFind\Db\Service\CmsPagesServiceInterface::class);
        $this->config = $config;
        $this->subsystem = $subsystem;
    }

    public function isEnabled(): bool
    {
        return isset($this->config->enabled) && $this->config->enabled == 1;
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

    /**
     * Initialize the repository and make sure the correct branch is active.
     */
    protected function initRepo(): Repository
    {
        if (!isset($this->repository)) {
            $this->repository = new Repository($this->getRepositoryPath());
            $this->repository->run('config', [
                'core.sshCommand',
                'ssh -i ' . escapeshellarg($this->getSshKeyPath()) . ' -o StrictHostKeyChecking=no'
            ]);
        }
        $activeBranch = $this->repository->getHead()->getName();
        if ($activeBranch != $this->getBranch()) {
            throw new \Exception('Wrong branch is active! Please contact server admin!');
        }
        return $this->repository;
    }

    /**
     * This will map either an array or a DB object to an array without local IDs etc.
     * so we can use it to sync with other systems.
     *
     * @return array
     */
    public function pageToArray($page): array
    {
        $array = [];
        if (!$page instanceof CmsPages) {
            $page = $this->cmsPagesService->getByID($page['id']);
        }
        $array['created'] = $page->getCreateDate()->format(static::DATETIME_FORMAT);
        $array['changed'] = $page->getChangeDate()->format(static::DATETIME_FORMAT);

        $translations = [];
        foreach ($page->getTranslations() as $translation) {
            $translationArray = [];
            $translationArray['title'] = $translation->getTitle();
            $translationArray['content'] = $translation->getContent();
            $translations[$translation->getLanguage()] = $translationArray;
        }
        $array['translations'] = $translations;

        return $array;
    }

    /**
     * Handle with care!
     *
     * This sample function will write the whole DB to JSON files and push to the
     * selected file in the git repo!
     */
    public function pushAll()
    {
        // get all pages
        // TODO: Do we filter by subsytem here, or does the DB Service already handle this?
        $pages = $this->cmsPagesService->getAll();
        if (count($pages) > 0) {
            foreach ($pages as $page) {
                if (!$page instanceof CmsPages) {
                    $page = $this->cmsPagesService->getByID($page['id']);
                }
                $pageArray = $this->pageToArray($page);
                $pagePath = $this->getPagePath($page);
                $pageJson = json_encode($pageArray);
                file_put_contents($pagePath, $pageJson);
                $this->gitAdd($pagePath);
            }
            // TODO: Check whether there have actually been changes before commit/push!!!
            $this->gitCommit('Server-sided push');
            $this->gitPush();
        }
    }

    public function pullAll()
    {
        $this->gitPull();
        $jsonFiles = array_diff(scandir($this->getPagesPath()), array('.', '..'));
        foreach ($jsonFiles as $jsonFile) {
            if (preg_match('"\.json$"', $jsonFile)) {
                $pageJson = file_get_contents($this->getPagesPath() . DIRECTORY_SEPARATOR . $jsonFile);
                $pageArray = json_decode($pageJson, /* associative */true);
                $pageSystemId = preg_replace('"\.json$"', '', $jsonFile);

                // Does it exist in DB?
                $pageInDb = $this->cmsPagesService->getByPageSystemIDWithoutTranslations($pageSystemId, $this->subsystem);

                if ($pageInDb != null) {
                    // TODO: import page
                } else {
                    // TODO: Page exists, diff content
                    // What should we do if different? Ask the user which one we should use?
                }
            }
        }
    }

    /**
     * Add a single file (if given), or the whole branch otherwise.
     */
    public function gitAdd(string $file=null)
    {
        if ($file === null) {
            $files = ['.'];
        } else {
            $files = [$file];
        }
        $repo = $this->initRepo();
        $repo->run('add', $files);
    }

    /**
     * Checkout a special file (by path if given) or the whole branch otherwise.
     */
    public function gitCheckout(string $file=null)
    {
        $checkoutTarget = $this->getBranch();
        if ($file != null) {
            $checkoutTarget = $file;
        }

        $repo = $this->initRepo();
        $repo->run('checkout', $checkoutTarget);
    }

    /**
     * Create a commit with the current message.
     */
    public function gitCommit(string $message)
    {
        $repo = $this->initRepo();
        $repo->run('commit', [
            '-m',
            $message
        ]);
    }

    /**
     * Pull the current branch from the server
     */
    public function gitPull()
    {
        $repo = $this->initRepo();
        $repo->run('pull');
    }

    /**
     * Push all commits in the current branch to the server
     */
    public function gitPush()
    {
        $repo = $this->initRepo();
        $repo->run('push');
    }
}
