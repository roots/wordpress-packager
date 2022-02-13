<?php

declare(strict_types=1);

namespace Roots\WordPressPackager\Tests;

use CzProject\GitPhp\GitRepository;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Roots\WordPressPackager\Package\Package;
use Roots\WordPressPackager\Package\Writer;
use Roots\WordPressPackager\Target;

class TargetTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testAddAlreadyTagged()
    {
        $gitRepo = Mockery::spy(GitRepository::class);
        $gitRepo->shouldReceive('fetch')
                ->with('origin')
                ->once();
        $gitRepo->shouldReceive('getTags')
                ->withNoArgs()
                ->once()
                ->andReturn(['1.2.3']);

        $package = Mockery::spy(Package::class);
        $package->shouldReceive('getPrettyVersion')
                ->withNoArgs()
                ->once()
                ->andReturn('1.2.3');

        $packageWriter = Mockery::spy(Writer::class);

        $target = new Target($gitRepo, $packageWriter);

        $target->add($package);

        $gitRepo->shouldNotHaveReceived('execute');
        $gitRepo->shouldNotHaveReceived('push');
    }

    public function testAdd()
    {
        $gitRepo = Mockery::spy(GitRepository::class);
        $gitRepo->shouldReceive('fetch')
                ->with('origin')
                ->once();
        $gitRepo->shouldReceive('getTags')
                ->withNoArgs()
                ->once()
                ->andReturn([]);
        $gitRepo->shouldReceive('getRepositoryPath')
                ->withNoArgs()
                ->once()
                ->andReturn('/fake/path');

        $package = Mockery::spy(Package::class);
        $package->shouldReceive('getPrettyVersion')
                ->withNoArgs()
                ->once()
                ->andReturn('1.2.3');

        $packageWriter = Mockery::spy(Writer::class);
        $packageWriter->shouldReceive('dumpFiles')
                ->with($package, '/fake/path')
                ->once()
                ->andReturn([
                    '/fake/path/file1',
                    '/fake/path/file2',
                ]);

        $target = new Target($gitRepo, $packageWriter);

        $target->add($package);

        $gitRepo->shouldHaveReceived('execute')
                ->with(['checkout', '--orphan', '1.2.3'])
                ->once();
        $packageWriter->shouldHaveReceived('dumpFiles')
                      ->with($package, '/fake/path')
                      ->once();
        $gitRepo->shouldHaveReceived('addFile')
                ->with([
                    '/fake/path/file1',
                    '/fake/path/file2',
                ])
                ->once();
        $gitRepo->shouldHaveReceived('createTag')
                ->with('1.2.3', [
                    '--annotate',
                    '--message' => 'Version 1.2.3',
                ])
                ->once();
        $gitRepo->shouldHaveReceived('push')
                ->with('origin', ['refs/tags/1.2.3'])
                ->once();
    }
}
