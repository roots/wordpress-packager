<?php

namespace Roots\WordPressPackager\Tests\Package;

use Composer\Json\JsonFile;
use Composer\Package\Link;
use Composer\Semver\Constraint\Constraint;
use Composer\Semver\Constraint\ConstraintInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;
use Roots\WordPressPackager\Package\Package;

class PackageTest extends TestCase
{
    public Package $pkgA;
    public Package $pkgAAlpha;
    public Package $pkgB;
    public Package $pkgBAlpha;
    public Package $pkgC;

    public string $jsonFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = new Package('roots/wordpress');

        $this->pkgA = $this->builder->clone()->withVersion('5.2.1');
        $this->pkgB = $this->builder->clone()->withVersion('5.2.1');
        $this->pkgC = $this->builder->clone()->withVersion('5.0');

        $this->pkgAAlpha = (new Package('roots/wordpress-a'))->withVersion('5.2.1');
        $this->pkgBAlpha = (new Package('roots/wordpress-b'))->withVersion('5.2.1');

        $this->jsonFile = tempnam('/tmp', Str::slug(self::class));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->jsonFile && file_exists($this->jsonFile)) {
            unlink($this->jsonFile);
        }
    }


    public function testCompareTo()
    {
        $this->assertEquals(-1, $this->pkgC->compareTo($this->pkgA));
        $this->assertEquals(-1, $this->pkgAAlpha->compareTo($this->pkgBAlpha));

        $this->assertEquals(0, $this->pkgA->compareTo($this->pkgB));

        $this->assertEquals(1, $this->pkgA->compareTo($this->pkgC));
        $this->assertEquals(1, $this->pkgBAlpha->compareTo($this->pkgAAlpha));
    }

    public function testJsonSerialize()
    {
        $this->pkgA->withRequires();
        $this->pkgA->withProvides();

        $json = new JsonFile($this->jsonFile);
        $serialized = $this->pkgA->jsonSerialize();
        $json->write($serialized);
        $this->assertTrue($json->validateSchema());

        file_put_contents($this->jsonFile, json_encode($this->pkgA));
        $json = new JsonFile($this->jsonFile);
        $this->assertTrue($json->validateSchema());

        $this->assertJsonFileEqualsJsonFile($this->jsonFile, __DIR__ . '/../resources/composer.json');
    }

    public function testGreaterThanOrEqualTo()
    {
        $this->assertTrue($this->pkgA->greaterThanOrEqualTo($this->pkgB->getPrettyVersion()));
        $this->assertTrue($this->pkgB->greaterThanOrEqualTo($this->pkgB->getPrettyVersion()));
        $this->assertTrue($this->pkgA->greaterThanOrEqualTo($this->pkgC->getPrettyVersion()));

        $this->assertFalse($this->pkgC->greaterThanOrEqualTo($this->pkgA->getPrettyVersion()));
    }

    public function testMinPhpVersion()
    {
        $getPhpPackage = function (Package $p): ConstraintInterface {
            /** @var Link $link */
            $link = Collection::make($p->getRequires())->first(function (Link $l) {
                return $l->getTarget() === 'php';
            });
            return $link->getConstraint();
        };

        $builder = new Package('roots/wordpress');

        $phpNew1 = $getPhpPackage($builder->clone()->withVersion('5.2.1'));
        $phpNew2 = $getPhpPackage($builder->clone()->withVersion('5.2'));
        $phpNew3 = $getPhpPackage($builder->clone()->withVersion('5.2-beta1'));
        $phpOld1 = $getPhpPackage($builder->clone()->withVersion('5.1'));
        $phpOld2 = $getPhpPackage($builder->clone()->withVersion('4.0'));

        $this->assertTrue($phpNew1->matches(new Constraint('=', '5.6.20')));
        $this->assertTrue($phpNew2->matches(new Constraint('=', '5.6.20')));
        $this->assertTrue($phpNew3->matches(new Constraint('=', '5.6.20')));
        $this->assertTrue($phpOld1->matches(new Constraint('=', '5.2.4')));
        $this->assertTrue($phpOld2->matches(new Constraint('=', '5.2.4')));
    }
}
