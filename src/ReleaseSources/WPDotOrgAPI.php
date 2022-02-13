<?php

declare(strict_types=1);

namespace Roots\WordPressPackager\ReleaseSources;

use Composer\Package\PackageInterface;
use Illuminate\Support\Collection;
use Roots\WordPressPackager\Package\Package;
use Roots\WordPressPackager\Package\Repository;
use Roots\WordPressPackager\ReleaseSources\Concerns\ReleaseType;
use stdClass;

class WPDotOrgAPI implements SourceInterface
{
    const ENDPOINT = 'https://api.wordpress.org/core/version-check/1.7/';

    protected bool $unstable = false;

    /** @var array<string, mixed> */
    protected array $data = [];

    public function __construct(
        protected Package $packageBase,
        protected ReleaseType $type = ReleaseType::Full
    ) {
        //
    }

    protected function packageFromObject(stdClass $release): PackageInterface
    {
        $package = $this->packageBase
            ->clone()
            ->withVersion($release->version);

        $package->setDistType('zip');
        $package->setDistUrl($release->packages->{$this->type->apiName()});
        $package->withRequires($release->php_version);
        $package->withProvides();

        return $package;
    }

    public function fetchUnstable(string $endpoint = null): void
    {
        $this->fetch(($endpoint ?? $this::ENDPOINT) . '?channel=beta');
    }

    public function fetch(string $endpoint = null): self
    {
        $this->data = array_merge(
            $this->data,
            json_decode((string) file_get_contents($endpoint ?? $this::ENDPOINT))->offers
        );

        return $this;
    }

    public function get(): Repository
    {
        return new Repository(
            Collection::make($this->data)
                      ->filter(fn($release) => $release->response === 'autoupdate' && $release->packages->{$this->type->apiName()})
                      ->map(fn($release) => $this->packageFromObject($release))
                      ->unique()
                      ->toArray()
        );
    }
}
