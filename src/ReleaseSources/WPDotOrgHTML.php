<?php

namespace Roots\WordPressPackager\ReleaseSources;

use Composer\Semver\VersionParser;
use DOMElement;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use League\Uri\Components\HierarchicalPath as Path;
use League\Uri\Components\Host;
use League\Uri\Http;
use PHPUnit\TextUI\RuntimeException;
use Roots\WordPressPackager\Package\Package;
use Roots\WordPressPackager\Package\Repository;
use Symfony\Component\DomCrawler\Crawler;

class WPDotOrgHTML implements SourceInterface
{
    const ENDPOINT = 'https://wordpress.org/download/releases/';
    
    protected \Countable $data;

    public function __construct(
        protected Package $packageBase
    ) {
        //
    }

    private static function isValidBasename(string $basename): bool
    {
        return (
            // whitelist
            Str::startsWith($basename, 'wordpress-') &&
            Str::endsWith($basename, ['.zip']) &&
            // blacklist
            !Str::startsWith($basename, 'wordpress-mu') &&
            !Str::endsWith($basename, ['-IIS.zip'])
        );
    }

    public static function isValidReleaseURL(string $url): bool
    {
        $httpUrl = Http::createFromString($url);
        $host = new Host($httpUrl->getHost());
        $basename = self::getBasename($httpUrl);
        $isHttps = $httpUrl->getScheme() === 'https';
        $wpOrgDomain = $host->getContent() === 'wordpress.org';

        if ($basename === '' || !$isHttps || !$wpOrgDomain || !self::isValidBasename($basename)) {
            return false;
        }

        $version = self::getVersionFromBasename($basename);

        return self::isVersion($version);
    }

    protected static function getBasename(Http $url): string
    {
        return (new Path($url->getPath()))->getBasename();
    }

    protected static function getVersionFromBasename(string $basename): ?string
    {
        preg_match('/^wordpress-(?<version>\S+)\.zip$/', $basename, $matches);

        return $matches['version'] ?? null;
    }

    protected static function getVersionFromURL(string $url): ?string
    {
        $httpUrl = Http::createFromString($url);
        return self::getVersionFromBasename(self::getBasename($httpUrl));
    }

    private static function isVersion(?string $version): bool
    {
        try {
            return is_string($version) && (new VersionParser())->normalize($version);
        } catch (\UnexpectedValueException $_) {
            return false;
        }
    }

    protected function packageFromUrl(string $releaseUrl): Package
    {
        /** @var string $version Version is guaranteed because of `isValidReleaseURL` above. */
        $version = self::getVersionFromURL($releaseUrl);

        $package = $this->packageBase
            ->clone()
            ->withVersion($version);

        $package->setDistType('zip');
        $package->setDistUrl($releaseUrl);

        return $package;
    }

    public function fetch(string $endpoint = null): self
    {
        $endpoint = $endpoint ?? $this::ENDPOINT;
        
        $html = file_get_contents($endpoint);
        
        if (! is_string($html)) {
            throw new RuntimeException("Failed to download HTML from {$endpoint}");
        }
        if ($html === '') {
            throw new InvalidArgumentException('blank html');
        }

        $this->data = (new Crawler($html))->filter('a[href$=".zip"]');
        
        return $this;
    }

    public function get(): Repository
    {
        return new Repository(
            Collection::make($this->data)
                ->map(function (DOMElement $zipLink): ?string {
                    $href = $zipLink->getAttribute('href');
                    if (self::isValidReleaseURL($href)) {
                        return $href;
                    }
                    return null;
                })
                ->filter()
                ->unique()
                ->map(function ($url) {
                    return $this->packageFromUrl($url);
                })
                ->toArray()
        );
    }
}
