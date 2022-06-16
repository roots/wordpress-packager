<?php

declare(strict_types=1);

namespace Roots\WordPressPackager\Tests\Package;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Roots\WordPressPackager\Package\Package;
use Roots\WordPressPackager\Package\Writer;
use Symfony\Component\Filesystem\Filesystem;

class WriterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testDumpFiles()
    {
        $composerJsonArray = [
            'name' => 'xxx/yyy',
        ];

        $package = Mockery::spy(Package::class);
        $package->shouldReceive('jsonSerialize')
                ->withNoArgs()
                ->once()
                ->andReturn($composerJsonArray);

        $filesystem = Mockery::spy(Filesystem::class);

        $packageWriter = new Writer($filesystem);

        $actuals = $packageWriter->dumpFiles($package, '/fake/path');

        $filesystem->shouldHaveReceived('dumpFile')
                   ->with(
                       '/fake/path/composer.json',
                       json_encode($composerJsonArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
                   )
                   ->once();
        $this->assertEquals(['/fake/path/composer.json'], $actuals);
    }
}
