<?php

namespace VuFindConsole\Command\Generate;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'generate/composerpackage',
    description: 'Composer package generator'
)]
class ComposerPackageCommand extends AbstractCommand {
    protected OutputInterface $output;
    protected Filesystem $filesystem;
    
    protected string $vuFindHomeDirFull;
    
    protected string $targetDirFull;
    
    protected string $gitOrganizationName = 'my-organization';
    protected string $gitRepositoryName = 'my-repository';
    
    protected string $composerJsonName = 'composer.json';
    protected string $composerJsonTargetPathFull;
    protected string $composerJsonTemplatePathFull;
    
    protected string $moduleTargetDirFull;
    protected string $moduleTargetDirRelative = 'src';
    protected string $moduleNamespace = 'MyModule';
    protected string $moduleTemplateName = 'VuFindLocalTemplate';
    protected string $moduleTemplateDirFull;
    
    protected string $mixinName = 'my_mixin';
    protected string $mixinTemplateName = 'local_mixin_example';
    protected string $mixinTemplateDirFull;
    protected string $mixinTargetDirRelative = 'mixin';
    protected string $mixinTargetDirFull;
    
    protected string $testsTargetDirRelative = 'tests';
    protected string $testsTargetDirFull;
    protected string $testsTemplateDirRelative = 'tests';
    protected string $testsTemplateDirFull;
    
    protected string $gitHubTargetDirRelative = '.github';
    protected string $gitHubTargetDirFull;
    protected string $gitHubTemplateDirRelative = '.github';
    protected string $gitHubTemplateDirFull;
    
    protected function configure()
    {
        $this
            ->setHelp('Creates a skeleton module for composer in a given directory with code examples.')
            ->addArgument(
                'target_directory',
                InputArgument::REQUIRED,
                'the path of the target directory where the module should be created'
            );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->filesystem = new Filesystem();
        $this->output = $output;
        $this->vuFindHomeDirFull = getenv('VUFIND_HOME');
        
        $this->targetDirFull = rtrim($input->getArgument('target_directory'), DIRECTORY_SEPARATOR);
        $this->output->writeln('Generating skeleton module in ' . $this->targetDirFull . '...');
        if ($this->filesystem->exists($this->targetDirFull)) {
            $this->filesystem->remove($this->targetDirFull);
        }
        $this->filesystem->mkdir($this->targetDirFull);
        
        $this->generateComposerJson();
        $this->generateModule();
        $this->generateMixin();
        $this->generateTests();
        $this->generateGitHubWorkflows();
        
        return self::SUCCESS;
    }
    
    protected function generateComposerJson()
    {
        $this->composerJsonTargetPathFull = $this->targetDirFull . DIRECTORY_SEPARATOR . $this->composerJsonName;
        $this->composerJsonTemplatePathFull = $this->vuFindHomeDirFull . DIRECTORY_SEPARATOR . $this->composerJsonName;
        $this->output->writeln('Generating ' . $this->composerJsonName . '...');
        
        $templateJson = file_get_contents($this->composerJsonTemplatePathFull);
        $templateArray = json_decode($templateJson, true);
        
        $array = [
            'name' => $this->gitOrganizationName . '/' . $this->gitRepositoryName,
            'description' => 'My custom composer package for VuFind',
            'license' => $templateArray['license'],
            'authors' => [
                'name' => 'My Name',
                'email' => 'my@email.com',
            ],
            'autoload' => [
                'psr-4' => [
                    $this->moduleNamespace . '\\' => $this->moduleTargetDirRelative . '/',
                ],
            ],
            'extra' => [
                'vufind' => [
                    'themes' => [
                        $this->mixinTargetDirRelative => $this->mixinName,
                    ],
                ],
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
                
                'friendsofphp/php-cs-fixer' => $templateArray['require-dev']['friendsofphp/php-cs-fixer'],
                'phpmd/phpmd' => $templateArray['require-dev']['phpmd/phpmd'],
                'phpstan/phpstan' => $templateArray['require-dev']['phpstan/phpstan'],
                'squizlabs/php_codesniffer' => $templateArray['require-dev']['squizlabs/php_codesniffer'],
                'rector/rector' => $templateArray['require-dev']['rector/rector'],
            ],
        ];
        $json = json_encode($array, JSON_PRETTY_PRINT);
        file_put_contents($this->composerJsonTargetPathFull, $json);
    }
    
    protected function generateModule()
    {
        // see VuFindConsole\Command\InstallCommand
        $this->moduleTemplateDirFull = $this->vuFindHomeDirFull . DIRECTORY_SEPARATOR . 'module' . DIRECTORY_SEPARATOR . $this->moduleTemplateName;
        $this->moduleTargetDirFull = $this->targetDirFull . DIRECTORY_SEPARATOR . $this->moduleTargetDirRelative;
        $this->output->writeln('Generating module in ' . $this->moduleTargetDirRelative . '...');
        $this->filesystem->mirror($this->moduleTemplateDirFull, $this->moduleTargetDirFull);
    }
    
    protected function generateMixin()
    {
        // Should we share code with VuFindTheme\MixinGenerator?
        $this->mixinTemplateDirFull = $this->vuFindHomeDirFull . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $this->mixinTemplateName;
        $this->mixinTargetDirFull = $this->targetDirFull . DIRECTORY_SEPARATOR . $this->mixinTargetDirRelative;
        $this->output->writeln('Generating mixin in ' . $this->mixinTargetDirRelative . '...');
        $this->filesystem->mirror($this->mixinTemplateDirFull, $this->mixinTargetDirFull);
    }
    
    protected function generateTests()
    {
        $this->testsTemplateDirFull = $this->vuFindHomeDirFull . DIRECTORY_SEPARATOR . $this->testsTemplateDirRelative;
        $this->testsTargetDirFull = $this->targetDirFull . DIRECTORY_SEPARATOR . $this->testsTargetDirRelative;
        
        $this->output->writeln('Generating tests in ' . $this->testsTargetDirRelative . '...');
        
        $this->filesystem->mirror($this->testsTemplateDirFull, $this->testsTargetDirFull);
        $this->filesystem->remove($this->testsTargetDirFull . DIRECTORY_SEPARATOR . 'data');
        
        $this->rewritePhpCsPaths([$this->moduleTargetDirRelative, $this->testsTargetDirRelative]);
        $this->rewritePhpCsFixerPaths('vufind.php-cs-fixer.php', [$this->moduleTargetDirRelative, $this->testsTargetDirRelative]);
        $this->rewritePhpCsFixerPaths('vufind_templates.php-cs-fixer.php', [$this->mixinTargetDirRelative]);
        $this->rewriteRectorPaths([$this->moduleTargetDirRelative, $this->testsTargetDirRelative]);
    }
    
    protected function rewritePhpCsPaths(array $pathsToInsert)
    {
        $phpCsPath = $this->testsTargetDirFull . DIRECTORY_SEPARATOR . 'phpcs.xml';
        
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
        $configPath = $this->testsTargetDirFull . DIRECTORY_SEPARATOR . $configFilename;
        $config = file_get_contents($configPath);
        
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
        file_put_contents($configPath, $configModified);
    }
    
    protected function rewriteRectorPaths(array $pathsToInsert)
    {
        $configPath = $this->testsTargetDirFull . DIRECTORY_SEPARATOR . 'rector.php';
        $config = file_get_contents($configPath);
        
        $pattern = '"->withPaths\(\[(\s|[^\]])+\]\)"';
        $replace = '->withPaths([';
        foreach ($pathsToInsert as $pathToInsert) {
            $replace .= PHP_EOL . '        ';
            $replace .= '__DIR__ . \'/../' . $pathToInsert . '\',';
        }
        $replace .= PHP_EOL . '    ])';
        
        $configModified = preg_replace($pattern, $replace, $config);
        file_put_contents($configPath, $configModified);
    }
    
    public function generateGitHubWorkflows()
    {
        $this->output->writeln('Generating GitHub-Workflows in ' . $this->gitHubTargetDirRelative . '...');
        $this->gitHubTargetDirFull = $this->targetDirFull . DIRECTORY_SEPARATOR . $this->gitHubTargetDirRelative;
        $this->gitHubTemplateDirFull = $this->vuFindHomeDirFull . DIRECTORY_SEPARATOR . $this->gitHubTemplateDirRelative;
        $this->filesystem->mirror($this->gitHubTemplateDirFull, $this->gitHubTargetDirFull);
    }
}
