<?php

declare(strict_types=1);

namespace Roots\WordPressPackager;

use CzProject\GitPhp\GitRepository;
use Roots\WordPressPackager\Package\Package;
use Roots\WordPressPackager\Package\Writer;

class Target
{
    /** @var string[] */
    protected ?array $gitTags = null;

    public function __construct(
        protected GitRepository $gitRepo,
        protected Writer $packageWriter
    ) {
        //
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

    public function add(Package $package): void
    {
        $version = $package->getPrettyVersion();
        if ($this->hasGitTag($version)) {
            return;
        }

        $this->gitRepo->execute(['checkout', '--orphan', $version]);
        $this->gitRepo->execute(['rm', '--cached', '-r', '.']);

        $files = $this->packageWriter->dumpFiles(
            $package,
            $this->gitRepo->getRepositoryPath()
        );

        $this->gitRepo->addFile($files);
        $this->gitRepo->commit("Version {$version}");
        $this->gitRepo->createTag($version, [
            '--annotate',
            '--message' => "Version {$version}",
        ]);
        $this->gitRepo->push('origin', ["refs/tags/{$version}"]);

        $this->gitTags[] = $version;
    }
}
