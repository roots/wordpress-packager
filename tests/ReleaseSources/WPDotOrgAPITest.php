<?php

namespace Roots\WordPressPackager\Tests\ReleaseSources;

use PHPUnit\Framework\TestCase;
use Roots\WordPressPackager\Package\Package;
use Roots\WordPressPackager\Package\Repository;
use Roots\WordPressPackager\ReleaseSources\WPDotOrgAPI;

class WPDotOrgAPITest extends TestCase
{
    public function testGet(): void
    {
        $builder = new Package($pkgName = 'roots/wordpress-dotorg');

        $repo = (new WPDotOrgAPI($builder))->fetch(
            __DIR__ . '/../resources/dotorg/repo-pass-1.json'
        )->get();

        $this->assertInstanceOf(Repository::class, $repo);
        $this->assertNotEmpty($repo);

        /** @var Package $wpLatest */
        $wpLatest = $repo->findPackage($pkgName, '5.8.3');
        $this->assertEquals(
            '5.8.3',
            $wpLatest->getPrettyVersion()
        );

        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../resources/source-composer.json',
            json_encode($wpLatest, JSON_UNESCAPED_SLASHES)
        );
    }

    public function testGetWithUnstable(): void
    {
        $builder = new Package($pkgName = 'roots/wordpress-dotorg');

        $repo = (new WPDotOrgAPI($builder))->fetch(
            __DIR__ . '/../resources/dotorg/repo-pass-1.json'
        )->fetch(
            __DIR__ . '/../resources/dotorg/repo-pass-2.json'
        )->get();

        $this->assertInstanceOf(Repository::class, $repo);
        $this->assertNotEmpty($repo);

        $wpLatest = $repo->findPackage($pkgName, '5.9-rc1');
        $this->assertEquals(
            '5.9-RC1',
            $wpLatest->getPrettyVersion()
        );
    }
}
