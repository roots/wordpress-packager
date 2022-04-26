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

    /**
     * Get target Git tags
     *
     * @return array<string>
     * @throws \CzProject\GitPhp\GitException
     */
    public function get(): array
    {
        if ($this->gitTags === null) {
            $this->gitRepo->fetch('origin');
            $this->gitTags = (array) $this->gitRepo->getTags();
        }

        return $this->gitTags;
    }

    /**
     * Check target Git tag existence
     *
     * @param Package $package
     * @return bool
     * @throws \CzProject\GitPhp\GitException
     */
    public function has(string $version): bool
    {
        return in_array($version, $this->get(), true);
    }

    /**
     * Set a Git tag on the target
     *
     * @param Package $package
     * @return void
     *
     * @throws \CzProject\GitPhp\GitException
     * @throws \JsonException
     */
    public function add(Package $package): void
    {
        $version = $package->getPrettyVersion();

        if ($this->has($version)) {
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
