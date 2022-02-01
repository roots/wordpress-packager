<?php

declare(strict_types=1);

namespace Roots\WordPressPackager\Package;

use Roots\WordPressPackager\License;
use Symfony\Component\Filesystem\Filesystem;

class Writer
{
    public function __construct(
        protected Filesystem $filesystem,
        protected ?License $license = null
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
            $paths[] = "${dir}/composer.json",
            $composerJsonContent
        );

        if (! is_null($this->license)) {
            $this->filesystem->dumpFile(
                $paths[] = "${dir}/LICENSE",
                $this->license->getContent()
            );
        }

        return $paths;
    }
}
