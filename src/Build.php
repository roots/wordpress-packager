<?php

declare(strict_types=1);

namespace Roots\WordPressPackager;

use CzProject\GitPhp\Git;
use Roots\WordPressPackager\Package\Package;
use Roots\WordPressPackager\Package\Writer;
use Roots\WordPressPackager\ReleaseSources\WPDotOrgHTML;
use Roots\WordPressPackager\Util\Directory;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

class Build
{
    public static function execute(string $gitRemote): void
    {
        $fs = new Filesystem();
        $license = new License(
            date('Y'),
            'Roots'
        );
        $gitRepo = (new Git())->cloneRepository(
            $gitRemote,
            Directory::mktemp($fs)
        );
        $target = new Target($gitRepo, new Writer($fs, $license));
        
        array_map(function (Package $package) use ($target): void {
            $target->add($package);
        }, static::getPackages());
    }

    protected static function getPackages(): array
    {
        $builder = new Package("roots/wordpress-dotorg");
        $wpDotOrgHtmlUrl = new WPDotOrgHTML($builder);

        return $wpDotOrgHtmlUrl
            ->fetch()
            ->get()
            ->getPackages();
    }
}
