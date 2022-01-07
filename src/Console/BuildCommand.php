<?php

namespace Roots\WordPressPackager\Console;

use CzProject\GitPhp\Git;
use Roots\WordPressPackager\License;
use Roots\WordPressPackager\Package\Package;
use Roots\WordPressPackager\Package\Writer;
use Roots\WordPressPackager\ReleaseSources\Concerns\ReleaseType;
use Roots\WordPressPackager\ReleaseSources\SourceInterface;
use Roots\WordPressPackager\Target;
use Roots\WordPressPackager\Util\Directory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class BuildCommand extends Command
{
    protected static $defaultName = 'build';

    protected function configure(): void
    {
        $this
            ->setDescription('Run WordPress Packager helper')
            ->addArgument('remote', InputArgument::REQUIRED, 'Git remote repository')
            ->addArgument('package', InputArgument::REQUIRED, 'Package name')
            ->addOption('source', null, InputOption::VALUE_REQUIRED, 'Release source', 'WPDotOrgAPI')
            ->addOption('type', 't', InputOption::VALUE_REQUIRED, 'Release type', ReleaseType::Full->value)
            ->addOption('instable', 'i', InputOption::VALUE_NONE, 'Includes instable')
            ->addOption('license', null, InputOption::VALUE_REQUIRED, 'License author');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('Running WordPress Packager helper');
        
        $fs = new Filesystem();
        
        if ($input->getOption('license')) {
            $license = new License(
                date('Y'),
                $input->getOption('license')
            );
        }

        $gitRepo = (new Git())->cloneRepository(
            $input->getArgument('remote'),
            Directory::mktemp($fs)
        );
        
        $target = new Target($gitRepo, new Writer($fs, $license ?? null));

        $builder = new Package($input->getArgument('package'));
        $sourceClass = '\\Roots\\WordPressPackager\\ReleaseSources\\' . $input->getOption('source');
        
        /** @var SourceInterface $source */
        $source = new $sourceClass($builder, ReleaseType::from($input->getOption('type')));
        $source->fetch();
        if ($input->getOption('instable')) {
            $source->fetchInstable();
        }
        $packages = $source->get()->getPackages();

        array_map(function (Package $package) use ($target): void {
            $target->add($package);
        }, $packages);
    }
}
