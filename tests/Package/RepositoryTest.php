<?php

namespace Roots\WordPressPackager\Tests\Package;

use Composer\Package\Package as InvalidPackage;
use Composer\Repository\InvalidRepositoryException;
use PHPUnit\Framework\TestCase;
use Roots\WordPressPackager\Package\Package;
use Roots\WordPressPackager\Package\Repository;

class RepositoryTest extends TestCase
{
    public Package $builder;
    public Repository $blankRepo;
    public Repository $fullRepo;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->builder = new Package('roots/wordpress');
        
        $this->blankRepo = new Repository();
        $this->fullRepo = new Repository([
            $this->builder->clone()->withVersion('5.2.1'),
            $this->builder->clone()->withVersion('5.0'),
            $this->builder->clone()->withVersion('4.0'),
        ]);
    }

    public function testInvalid()
    {
        $this->expectException(InvalidRepositoryException::class);
        
        new Repository([
            $this->builder->clone()->withVersion('5.2.1'),
            new InvalidPackage('roots/wp-config', '1.0.0', '1.0.0')
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
