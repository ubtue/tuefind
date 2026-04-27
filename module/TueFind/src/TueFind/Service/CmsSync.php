<?php

namespace TueFind\Service;

use Gitonomy\Git\Repository;

class CmsSync {
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

    public function getRepositoryPath(): string
    {
        return $this->config->repository_path;
    }

    public function getBranch(): string
    {
        return $this->config->repository_branch;
    }

    /**
     * Initialize the repository and make sure the correct branch is active.
     */
    protected function initRepo(): Repository
    {
        if (!isset($this->repository)) {
            $this->repository = new Repository($this->getRepositoryPath());
        }
        $activeBranch = $this->repository->getHead()->getName();
        if ($activeBranch != $this->getBranch()) {
            throw new \Exception('Wrong branch is active! Please contact server admin!');
        }
        return $this->repository;
    }

    protected function cmsPageToArray($pageSystemId, $subSystem, $language)
    {
        $page = $this->cmsPagesService->getByPageSystemID($pageSystemId, $subSystem, $language);

        // TODO: Map page and all translations to array structure
    }

       protected function diffPageArrays(array $array1, array $array2)
    {
        // TODO: Check whether they are similar or there is any conflict
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
            '-m' => $message
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
