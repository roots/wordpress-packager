<?php
declare(strict_types=1);

namespace Roots\WordPressPackager\Tests;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class PackageWriterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testDumpFiles()
    {
        $composerJsonArray = [
            'name' => 'xxx/yyy',
        ];

        $package = Mockery::spy(WordPressPackage::class);
        $package->shouldReceive('jsonSerialize')
                ->withNoArgs()
                ->once()
                ->andReturn($composerJsonArray);

        $filesystem = Mockery::spy(Filesystem::class);

        $license = Mockery::mock(License::class);
        $license->shouldReceive('getContent')
                ->withNoArgs()
                ->once()
                ->andReturn('I am license content');

        $packageWriter = new PackageWriter($filesystem, $license);

        $actuals = $packageWriter->dumpFiles($package, '/fake/path');

        $filesystem->shouldHaveReceived('dumpFile')
                   ->with(
                       '/fake/path/composer.json',
                       json_encode($composerJsonArray, JSON_PRETTY_PRINT)
                   )
                   ->once();
        $filesystem->shouldHaveReceived('dumpFile')
                   ->with('/fake/path/LICENSE', 'I am license content')
                   ->once();
        $this->assertEquals([
            '/fake/path/composer.json',
            '/fake/path/LICENSE',
        ], $actuals);
    }
}
