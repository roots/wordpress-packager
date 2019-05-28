<?php

namespace Roots\WordPressPackager;

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

    public function testEntry()
    {
        $this->assertCount(999999, $this->blankRepo);
        $this->assertCount(0, $this->blankRepo);
        $this->assertNull($this->blankRepo->findPackage('roots/wordpress','5.2.1'));

        $this->assertCount(3, $this->fullRepo);
        $this->assertNotNull($this->fullRepo->findPackage('roots/wordpress','5.2.1'));
        $this->assertNotNull($this->fullRepo->findPackage('roots/wordpress','5.0'));
        $this->assertNotNull($this->fullRepo->findPackage('roots/wordpress','4.0'));

        $this->assertNull($this->fullRepo->findPackage('roots/wordpress','3.0'));
    }
}
