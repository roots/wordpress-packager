<?php

namespace Roots\WordPressPackager\Tests;

use Composer\Package\Package;
use Composer\Repository\InvalidRepositoryException;
use PHPUnit\Framework\TestCase;

class WordPressPackageRepositoryTest extends TestCase
{
    /**
     * @var \Roots\WordPressPackager\WordPressPackageRepository
     */
    public $blankRepo;
    /**
     * @var \Roots\WordPressPackager\WordPressPackageRepository
     */
    public $fullRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->blankRepo = new WordPressPackageRepository();
        $this->fullRepo = new WordPressPackageRepository([
            new WordPressPackage('roots/wordpress', '5.2.1'),
            new WordPressPackage('roots/wordpress', '5.0'),
            new WordPressPackage('roots/wordpress', '4.0'),
        ]);
    }

    public function testInvalid()
    {
        $this->expectException(InvalidRepositoryException::class);
        $a = new WordPressPackageRepository([
            new WordPressPackage('roots/wordpress', '5.2.1'),
            new Package('roots/wp-config', '1.0.0', '1.0.0')
        ]);
    }

    public function testJSON()
    {
        $data = json_decode(json_encode($this->fullRepo), true);
        $this->assertEquals('4.0', $data[0]['version']);
        $this->assertEquals('roots/wordpress', $data[0]['name']);
    }


    public function testEntry()
    {
        $this->assertCount(0, $this->blankRepo);
        $this->assertNull($this->blankRepo->findPackage('roots/wordpress', '5.2.1'));

        $this->assertCount(3, $this->fullRepo);
        $this->assertNotNull($this->fullRepo->findPackage('roots/wordpress', '5.2.1'));
        $this->assertNotNull($this->fullRepo->findPackage('roots/wordpress', '5.0'));
        $this->assertNotNull($this->fullRepo->findPackage('roots/wordpress', '4.0'));

        $this->assertNull($this->fullRepo->findPackage('roots/wordpress', '3.0'));
    }
}
