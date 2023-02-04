<?php

declare(strict_types=1);

namespace Roots\WordPressPackager\Package;

use Symfony\Component\Filesystem\Filesystem;

class Writer
{
    public function __construct(
        protected Filesystem $filesystem
    ) {
        //
    }

    /**
     * @return array<string>
     */
    public function dumpFiles(Package $package, string $dir): array
    {
        $composerJsonContent = json_encode(
            $package,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );

        $paths = [];

        $this->filesystem->dumpFile(
            $paths[] = "{$dir}/composer.json",
            $composerJsonContent
        );

        return $paths;
    }
}
