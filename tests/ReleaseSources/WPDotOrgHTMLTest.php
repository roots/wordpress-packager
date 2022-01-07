<?php

namespace Roots\WordPressPackager\Tests\ReleaseSources;

use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use Roots\WordPressPackager\Package\Package;
use Roots\WordPressPackager\Package\Repository;
use Roots\WordPressPackager\ReleaseSources\WPDotOrgHTML;

class WPDotOrgHTMLTest extends TestCase
{
    public function testGet()
    {
        $builder = new Package($pkgName = 'roots/wordpress-dotorg');
        /** @var Repository[] $repos */
        $repos = array_map(
            function (string $path) use ($builder): Repository {
                return (new WPDotOrgHTML($builder))->fetch($path)->get();
            },
            [
                __DIR__ . '/../resources/dotorg/repo-fail-1.html',
                __DIR__ . '/../resources/dotorg/repo-fail-2.html',
                __DIR__ . '/../resources/dotorg/repo-pass-1.html',
                __DIR__ . '/../resources/dotorg/repo-pass-2.html',
            ]
        );
        [$fail1, $fail2, $pass1, $pass2] = $repos;

        $this->assertEquals(0, $fail1->count());
        $this->assertEquals(0, $fail2->count());

        $this->assertEquals(1, $pass2->count());

        $wpLatest = $pass2->findPackage($pkgName, '5.2.1');
        $this->assertEquals(
            '5.2.1',
            $wpLatest->getPrettyVersion()
        );

        $this->assertEquals(
            'https://wordpress.org/wordpress-5.2.1.zip',
            $wpLatest->getDistUrl()
        );

        $this->assertEquals(
            '5.2.1',
            $pass1->findPackage($pkgName, '5.2.1')->getPrettyVersion()
        );
    }

    public function testIsValidReleaseUrl(): void
    {
        $this->assertFalse(WPDotOrgHTML::isValidReleaseURL('https://www.itineris.co.uk/'));
        $this->assertFalse(WPDotOrgHTML::isValidReleaseURL('https://wordpress.org/'));
        $this->assertFalse(WPDotOrgHTML::isValidReleaseURL('https://wordpress.org/completely-unrelated.zip'));
        $this->assertFalse(WPDotOrgHTML::isValidReleaseURL('https://wordpress.org/wordpress-5.2.1.tar.gz'));

        // force http
        $this->assertFalse(WPDotOrgHTML::isValidReleaseURL('http://wordpress.org/wordpress-5.2.1.zip'));

        $this->assertTrue(WPDotOrgHTML::isValidReleaseURL('https://wordpress.org/wordpress-5.2.1.zip'));
        $this->assertTrue(WPDotOrgHTML::isValidReleaseURL('https://wordpress.org/future/proof/wordpress-5.2.1.zip'));
    }
}
