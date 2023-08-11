<?php

declare(strict_types=1);

namespace Roots\WordPressPackager\Util;

use Symfony\Component\Filesystem\Filesystem;
use League\Uri\Components\HierarchicalPath as Path;

class Directory
{
    public static function mktemp(Filesystem $filesystem): string
    {
        $path = (string) (Path::new(sys_get_temp_dir()))
            ->append(uniqid('wordpress-packager', true));
        $filesystem->mkdir($path);
        return $path;
    }
}
