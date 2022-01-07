<?php

declare(strict_types=1);

namespace Roots\WordPressPackager\Package;

use Roots\WordPressPackager\License;
use Symfony\Component\Filesystem\Filesystem;

class Writer
{
    /** @var Filesystem */
    protected $filesystem;
    /** @var License */
    protected $license;

    public function __construct(Filesystem $filesystem, License $license)
    {
        $this->filesystem = $filesystem;
        $this->license = $license;
    }

    public function dumpFiles(Package $package, string $dir): array
    {
        $composerJsonContent = json_encode(
            $package,
            JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
        );

        $this->filesystem->dumpFile(
            "${dir}/composer.json",
            $composerJsonContent
        );

        $this->filesystem->dumpFile(
            "${dir}/LICENSE",
            $this->license->getContent()
        );

        return [
            "${dir}/composer.json",
            "${dir}/LICENSE",
        ];
    }
}
