<?php

declare(strict_types=1);

namespace Roots\WordPressPackager;

use Composer\Package\PackageInterface;
use CzProject\GitPhp\GitRepository;
use Symfony\Component\Filesystem\Filesystem;

class Target
{
    /** @var GitRepository */
    protected $gitRepo;

    /** @var PackageWriter */
    protected $packageWriter;

    /** @var string[] */
    protected $gitTags;

    public function __construct(GitRepository $gitRepo, PackageWriter $packageWriter)
    {
        $this->gitRepo = $gitRepo;
        $this->packageWriter = $packageWriter;
    }

    protected function getGitTags(): array
    {
        if ($this->gitTags === null) {
            $this->gitRepo->fetch('origin');
            $this->gitTags = (array) $this->gitRepo->getTags();
        }

        return $this->gitTags;
    }

    protected function hasGitTag(string $tag): bool
    {
        return in_array($tag, $this->getGitTags(), true);
    }

    public function add(WordPressPackage $package): void
    {
        $version = $package->getPrettyVersion();
        if ($this->hasGitTag($version)) {
            return;
        }

        $this->gitRepo->execute(['checkout', '--orphan', $version]);

        $files = $this->packageWriter->dumpFiles(
            $package,
            $this->gitRepo->getRepositoryPath()
        );

        $this->gitRepo->addFile($files);
        $this->gitRepo->commit("Version bump ${version}");
        $this->gitRepo->createTag($version, [
            '--annotate',
            '--message' => "Version bump ${version}",
        ]);
        $this->gitRepo->push('origin', ["refs/tags/${version}"]);

        $this->gitTags[] = $version;
    }
}
