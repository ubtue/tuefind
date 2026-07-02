<?php

namespace VuFindConsole\Command\Generate;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

use VuFind\Exception\FileAccess as FileAccessException;

#[AsCommand(
    name: 'generate/composerpackagetemplate',
    description: 'Composer package template generator'
)]
class ComposerPackageTemplateCommand extends AbstractCommand {
    protected InputInterface $input;
    protected OutputInterface $output;
    protected Filesystem $filesystem;

    protected string $vuFindHomeDirAbsolute;

    protected string $targetDirAbsolute;

    protected string $gitOrganizationName = 'my-organization';
    protected string $gitRepositoryName = 'my-repository';

    protected string $composerJsonName = 'composer.json';
    protected string $composerJsonTargetPathAbsolute;
    protected string $composerJsonTemplatePathAbsolute;

    protected string $configIniDirAbsolute;
    protected string $configIniDirRelative = 'config';
    protected string $configIniFileAbsolute;
    protected string $configIniFileName = 'config.ini';
    protected string $configIniFileRelative;

    protected string $moduleTargetDirAbsolute;
    protected string $moduleTargetDirRelative = 'src';
    protected string $moduleNamespace = 'MyModule';
    protected string $moduleTemplateName = 'VuFindLocalTemplate';
    protected string $moduleTemplateDirAbsolute;
    protected string $moduleConfigPhpFileRelative = 'config/module.config.php';
    protected string $moduleConfigPhpFileAbsolute;
    protected string $modulePhpFileRelative = 'Module.php';
    protected string $modulePhpFileAbsolute;

    protected string $mixinName = 'my_mixin';
    protected string $mixinTemplateName = 'local_mixin_example';
    protected string $mixinTemplateDirAbsolute;
    protected string $mixinTargetDirRelative = 'mixin';
    protected string $mixinTargetDirAbsolute;

    protected string $testsTargetDirRelative = 'tests';
    protected string $testsTargetDirAbsolute;
    protected string $testsTemplateDirRelative = 'tests';
    protected string $testsTemplateDirAbsolute;

    protected string $gitHubTargetDirRelative = '.github';
    protected string $gitHubTargetDirAbsolute;
    protected string $gitHubTemplateDirRelative = '.github';
    protected string $gitHubTemplateDirAbsolute;

    protected function configure()
    {
        $this
            ->setHelp('Creates a skeleton module for composer in a given directory with code examples.')
            ->addArgument(
                'target_directory',
                InputArgument::REQUIRED,
                'the path of the target directory where the module should be created'
            )->addOption(
                'module',
                null,
                InputOption::VALUE_NONE,
                'when set, also create a custom module'
            )->addOption(
                'config',
                null,
                InputOption::VALUE_NONE,
                'when set, also create a custom config.ini file'
            )
            ->addOption(
                'mixin',
                null,
                InputOption::VALUE_NONE,
                'when set, also create a custom mixin'
            )->addOption(
                'tests',
                null,
                InputOption::VALUE_NONE,
                'when set, also create tests'
            )->addOption(
                'workflows',
                null,
                InputOption::VALUE_NONE,
                'when set, also create custom GitHub-workflows'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->vuFindHomeDirAbsolute = getenv('VUFIND_HOME');
        $this->filesystem = new Filesystem();

        $this->targetDirAbsolute = rtrim($input->getArgument('target_directory'), DIRECTORY_SEPARATOR);
        $this->output->writeln('Generating skeleton module in ' . $this->targetDirAbsolute . '...');
        if ($this->filesystem->exists($this->targetDirAbsolute)) {
            $this->filesystem->remove($this->targetDirAbsolute);
        }
        $this->filesystem->mkdir($this->targetDirAbsolute);

        if ($this->input->getOption('module')) {
            $this->generateModule();
        }
        if ($this->input->getOption('config')) {
            $this->generateConfigIni();
        }
        if ($this->input->getOption('mixin')) {
            $this->generateMixin();
        }
        if ($this->input->getOption('tests')) {
            $this->generateTests();
        }
        if ($this->input->getOption('workflows')) {
            $this->generateGitHubWorkflows();
        }

        $this->generateComposerJson();

        return self::SUCCESS;
    }

    protected function generateComposerJson()
    {
        $this->composerJsonTargetPathAbsolute = $this->targetDirAbsolute . DIRECTORY_SEPARATOR . $this->composerJsonName;
        $this->composerJsonTemplatePathAbsolute = $this->vuFindHomeDirAbsolute . DIRECTORY_SEPARATOR . $this->composerJsonName;
        $this->output->writeln('Generating ' . $this->composerJsonName . '...');

        $templateJson = $this->readFile($this->composerJsonTemplatePathAbsolute);
        $templateArray = json_decode($templateJson, true);

        $targetArray = [
            'name' => $this->gitOrganizationName . '/' . $this->gitRepositoryName,
            'description' => 'My custom composer package for VuFind',
            'license' => $templateArray['license'],
            'authors' => [
                'name' => 'My Name',
                'email' => 'my@email.com',
            ],
            'config' => [
                'platform' => [
                    'php' => $templateArray['config']['platform']['php'],
                ],
            ],
            'require' => [
                'php' => '>=' . $templateArray['require']['php'],
            ],
            'require-dev' => [
                'phing/phing' => $templateArray['require']['phing/phing'],
            ],
        ];

        if ($this->input->getOption('module')) {
            $targetArray['autoload']['psr-4'][$this->moduleNamespace . '\\'] = $this->moduleTargetDirRelative . '/';
        }
        if ($this->input->getOption('mixin')) {
            // depends on optimization from FINC/alex-pu
            $targetArray['extra']['vufind']['themes'][$this->mixinTargetDirRelative] = $this->mixinName;
        }
        if ($this->input->getOption('tests')) {
            $dependencies = [
                'friendsofphp/php-cs-fixer',
                'phpmd/phpmd',
                'phpstan/phpstan',
                'squizlabs/php_codesniffer',
                'rector/rector',
            ];
            foreach ($dependencies as $dependency) {
                $targetArray['require-dev'][$dependency] = $templateArray['require-dev'][$dependency];
            }
        }

        $json = json_encode($targetArray, JSON_PRETTY_PRINT);
        $this->filesystem->dumpFile($this->composerJsonTargetPathAbsolute, $json);
    }

    protected function generateModule()
    {
        //  Should we share code with \VuFindConsole\Command\InstallCommand?
        $this->moduleTemplateDirAbsolute = $this->vuFindHomeDirAbsolute . DIRECTORY_SEPARATOR . 'module' . DIRECTORY_SEPARATOR . $this->moduleTemplateName;
        $this->moduleTargetDirAbsolute = $this->targetDirAbsolute . DIRECTORY_SEPARATOR . $this->moduleTargetDirRelative;
        $this->output->writeln('Generating module in ' . $this->moduleTargetDirRelative . '...');
        $this->filesystem->mirror($this->moduleTemplateDirAbsolute, $this->moduleTargetDirAbsolute);

        // rewrite namespace
        $this->modulePhpFileAbsolute = $this->moduleTargetDirAbsolute . DIRECTORY_SEPARATOR . $this->modulePhpFileRelative;
        $this->rewriteNamespace($this->modulePhpFileAbsolute );
        $this->moduleConfigPhpFileAbsolute = $this->moduleTargetDirAbsolute . DIRECTORY_SEPARATOR . $this->moduleConfigPhpFileRelative;
        $this->rewriteNamespace($this->moduleConfigPhpFileAbsolute );
    }

    protected function rewriteNamespace(string $path)
    {
        $config = $this->readFile($path);
        $config = str_replace($this->moduleTemplateName, $this->moduleNamespace, $config);
        $this->filesystem->dumpFile($path, $config);
    }

    /**
     * This method so far only creates a sample config folder with a config.ini file.
     * - How will the file be accessed from the module, themes, ...?
     * - Should we also add a sample config reader to the module? (is adding a module a prerequisite)?
     */
    protected function generateConfigIni()
    {
        $this->configIniDirAbsolute = $this->targetDirAbsolute . DIRECTORY_SEPARATOR . $this->configIniDirRelative;
        $this->configIniFileRelative = $this->configIniDirRelative . DIRECTORY_SEPARATOR . $this->configIniFileName;
        $this->configIniFileAbsolute = $this->configIniDirAbsolute . DIRECTORY_SEPARATOR . $this->configIniFileName;

        $this->output->writeln('Generating ' . $this->configIniFileRelative . '...');

        $config = <<<CONFIG
;
; Custom Module Configuration
;
[Sample]
enabled=true

CONFIG;

        $this->filesystem->dumpFile($this->configIniFileAbsolute, $config);
    }

    protected function generateMixin()
    {
        // Should we share code with ThemeMixinCommand + \VuFindTheme\MixinGenerator?
        $this->mixinTemplateDirAbsolute = $this->vuFindHomeDirAbsolute . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $this->mixinTemplateName;
        $this->mixinTargetDirAbsolute = $this->targetDirAbsolute . DIRECTORY_SEPARATOR . $this->mixinTargetDirRelative;
        $this->output->writeln('Generating mixin in ' . $this->mixinTargetDirRelative . '...');
        $this->filesystem->mirror($this->mixinTemplateDirAbsolute, $this->mixinTargetDirAbsolute);
    }

    protected function generateTests()
    {
        $this->testsTemplateDirAbsolute = $this->vuFindHomeDirAbsolute . DIRECTORY_SEPARATOR . $this->testsTemplateDirRelative;
        $this->testsTargetDirAbsolute = $this->targetDirAbsolute . DIRECTORY_SEPARATOR . $this->testsTargetDirRelative;

        $this->output->writeln('Generating tests in ' . $this->testsTargetDirRelative . '...');

        $this->filesystem->mirror($this->testsTemplateDirAbsolute, $this->testsTargetDirAbsolute);
        $this->filesystem->remove($this->testsTargetDirAbsolute . DIRECTORY_SEPARATOR . 'data');

        $this->rewritePhpCsPaths([$this->moduleTargetDirRelative, $this->testsTargetDirRelative]);
        $this->rewritePhpCsFixerPaths('vufind.php-cs-fixer.php', [$this->moduleTargetDirRelative, $this->testsTargetDirRelative]);
        $this->rewritePhpCsFixerPaths('vufind_templates.php-cs-fixer.php', [$this->mixinTargetDirRelative]);
        $this->rewriteRectorPaths([$this->moduleTargetDirRelative, $this->testsTargetDirRelative]);
    }

    protected function rewritePhpCsPaths(array $pathsToInsert)
    {
        $phpCsPath = $this->testsTargetDirAbsolute . DIRECTORY_SEPARATOR . 'phpcs.xml';

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        $dom->load($phpCsPath);

        $description = null;

        $child = $dom->documentElement->firstChild;
        while ($child != null) {
            $next = $child->nextSibling;
            if ($child instanceof \DOMElement) {
                if ($child->tagName == 'description') {
                    $description = $child;
                } elseif ($child->tagName == 'file') {
                    $dom->documentElement->removeChild($child);
                }
            }

            $child = $next;
        }

        if ($description != null) {
            foreach ($pathsToInsert as $pathToInsert) {
                $file = $dom->createElement('file');
                $file->textContent = '../' . $pathToInsert;
                $dom->documentElement->insertBefore($file, $description);
            }
        } else {
            throw new \Exception('<description> element not found in ' . $phpCsPath);
        }

        $dom->save($phpCsPath);
    }

    protected function rewritePhpCsFixerPaths(string $configFilename, array $pathsToInsert)
    {
        $configPath = $this->testsTargetDirAbsolute . DIRECTORY_SEPARATOR . $configFilename;
        $config = $this->readFile($configPath);

        $pattern = '"(->in\([^)]+\)\s*)+"';
        $replace = '';
        $i = 0;
        foreach ($pathsToInsert as $pathToInsert) {
            if ($i > 0) {
                $replace .= PHP_EOL . '    ';
            }

            $replace .= '->in(__DIR__ . \'/../' . $pathToInsert . '\')';
            ++$i;
        }

        $configModified = preg_replace($pattern, $replace, $config);
        $this->filesystem->dumpFile($configPath, $configModified);
    }

    protected function rewriteRectorPaths(array $pathsToInsert)
    {
        $configPath = $this->testsTargetDirAbsolute . DIRECTORY_SEPARATOR . 'rector.php';
        $config = $this->readFile($configPath);

        $pattern = '"->withPaths\(\[(\s|[^\]])+\]\)"';
        $replace = '->withPaths([';
        foreach ($pathsToInsert as $pathToInsert) {
            $replace .= PHP_EOL . '        ';
            $replace .= '__DIR__ . \'/../' . $pathToInsert . '\',';
        }
        $replace .= PHP_EOL . '    ])';

        $configModified = preg_replace($pattern, $replace, $config);
        $this->filesystem->dumpFile($configPath, $configModified);
    }

    protected function generateGitHubWorkflows()
    {
        $this->output->writeln('Generating GitHub-Workflows in ' . $this->gitHubTargetDirRelative . '...');
        $this->gitHubTargetDirAbsolute = $this->targetDirAbsolute . DIRECTORY_SEPARATOR . $this->gitHubTargetDirRelative;
        $this->gitHubTemplateDirAbsolute = $this->vuFindHomeDirAbsolute . DIRECTORY_SEPARATOR . $this->gitHubTemplateDirRelative;
        $this->filesystem->mirror($this->gitHubTemplateDirAbsolute, $this->gitHubTargetDirAbsolute);
    }

    /**
     * Call file_get_contents and throw FileAccessException on error
     *
     * $this->filesystem->readFile() is only available in newer Symfony versions
     * https://symfony.com/doc/current/components/filesystem.html
     */
    protected function readFile(string $path): string
    {
        $contents = file_get_contents($path);
        if (!$contents) {
            throw new FileAccessException('Could not read file ' . $path);
        }
        return $contents;
    }
}
