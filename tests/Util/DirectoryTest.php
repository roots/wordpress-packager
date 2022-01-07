<?php
declare(strict_types=1);

namespace Roots\WordPressPackager\Tests\Util;

use League\Uri\Components\HierarchicalPath as Path;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class DirectoryTest extends TestCase
{
    public function testMktemp()
    {
        $filesystem = $this->getMockBuilder(Filesystem::class)
                           ->setMethods(['mkdir'])
                           ->getMock();

        $filesystem->expects($this->once())
                   ->method('mkdir')
                   ->with(
                       $this->stringStartsWith(sys_get_temp_dir())
                   );

        // this will throw if invalid path
        $tempPath = new Path(Directory::mktemp($filesystem));

        $this->assertEquals(sys_get_temp_dir(), $tempPath->getDirname());
        $this->assertStringStartsWith('wordpress-packager', $tempPath->getBasename());
    }
}
