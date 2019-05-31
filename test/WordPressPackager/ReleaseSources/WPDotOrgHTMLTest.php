<?php

namespace Roots\WordPressPackager\ReleaseSources;

use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use Roots\WordPressPackager\WordPressPackage;
use Roots\WordPressPackager\WordPressPackageRepository;

class WPDotOrgHTMLTest extends TestCase
{
    public $invalidUrls;
    public $validUrls;
    public $allUrls;

    protected function setUp(): void
    {
        parent::setUp();

        $json = function ($path) {
            return json_decode(
                file_get_contents($path),
                true
            );
        };

        $this->allUrls     = $json(__DIR__ . '/../../resources/dotorg/wordpress-download-urls.json');
        $this->validUrls   = $json(__DIR__ . '/../../resources/dotorg/wordpress-valid-download-urls.json');
        $this->invalidUrls = $json(__DIR__ . '/../../resources/dotorg/wordpress-invalid-download-urls.json');
    }

    public function testInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $a = new WPDotOrgHTML('');
        $a->getRepo();
    }


    public function testGetRepo()
    {
        /** @var WordPressPackageRepository[] $repos */
        $repos = array_map(
            function (string $path): WordPressPackageRepository {
                return (new WPDotOrgHTML(file_get_contents($path)))->getRepo();
            },
            [
                __DIR__ . '/../../resources/dotorg/repo-fail-1.html',
                __DIR__ . '/../../resources/dotorg/repo-fail-2.html',
                __DIR__ . '/../../resources/dotorg/repo-pass-1.html',
                __DIR__ . '/../../resources/dotorg/repo-pass-2.html',
            ]
        );
        [$fail1, $fail2, $pass1, $pass2] = $repos;

        $this->assertEquals(0, $fail1->count());
        $this->assertEquals(0, $fail2->count());

        $this->assertEquals(1, $pass2->count());

        $pkgName = 'roots/wordpress-dotorg';

        $wpLatest = $pass2->findPackage($pkgName, '5.2.1');
        $this->assertEquals(
            '5.2.1',
            $wpLatest->getPrettyVersion()
        );

        $this->assertEquals(
            'https://wordpress.org/wordpress-5.2.1.zip',
            $wpLatest->getDistUrl()
        );
        $releaseUrls = Collection::make($pass1->findPackages($pkgName, '>=4'))
            ->map(function (WordPressPackage $r) {
                return $r->getDistUrl();
            })
            ->values()
            ->toArray();

        $this->assertEqualsCanonicalizing($this->validUrls, $releaseUrls);

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

        // smoke test
        foreach($this->allUrls as $url) {
            WPDotOrgHTML::isValidReleaseURL($url);
        }

        foreach($this->validUrls as $valid) {
            $this->assertTrue(
                WPDotOrgHTML::isValidReleaseURL($valid),
                "expected '$valid' to be valid"
            );
        }
        foreach($this->invalidUrls as $invalid) {
            $this->assertFalse(
                WPDotOrgHTML::isValidReleaseURL($invalid),
                "expected '$invalid' to be invalid"
            );
        }
    }
}
