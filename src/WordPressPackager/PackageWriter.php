<?php

declare(strict_types=1);

namespace Roots\WordPressPackager;

use Symfony\Component\Filesystem\Filesystem;

class PackageWriter
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

    public function dumpFiles(WordPressPackage $package, string $dir): array
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
