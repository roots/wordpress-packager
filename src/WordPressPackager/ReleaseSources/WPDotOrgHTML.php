<?php


namespace Roots\WordPressPackager\ReleaseSources;

use Composer\Semver\VersionParser;
use DOMDocument;
use DOMElement;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use League\Uri\Components\HierarchicalPath as Path;
use League\Uri\Components\Host;
use League\Uri\Http;
use Roots\WordPressPackager\WordPressPackage;
use Roots\WordPressPackager\WordPressPackageRepository;
use Symfony\Component\DomCrawler\Crawler;

class WPDotOrgHTML implements SourceInterface
{
    /**
     * @var string
     */
    protected $html;

    /**
     * WPDotOrgHTML constructor.
     * @param string $html
     */
    public function __construct(string $html)
    {
        $this->html = $html;
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
        $wpOrgDomain = $host->getRegistrableDomain() === 'wordpress.org';

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
            return is_string($version) ? (bool)(new VersionParser())->normalize($version) : false;
        } catch (\UnexpectedValueException $_) {
            return false;
        }
    }

    protected static function packageFromUrl(string $releaseUrl): WordPressPackage
    {
        // TODO: don't hardcode package org
        $org = 'roots';

        $name = "$org/wordpress-dotorg";
        /** @var string $version Version is guaranteed because of `isValidReleaseURL` above. */
        $version = self::getVersionFromURL($releaseUrl);

        $package = new WordPressPackage($name, $version);

        $package->setDistType('zip');
        $package->setDistUrl($releaseUrl);

        return $package;
    }

    public function getRepo(): WordPressPackageRepository
    {
        $html = $this->html;
        if ($html === '') {
            throw new InvalidArgumentException('blank html');
        }

        $zipLinks = (new Crawler($html))->filter('a[href$=".zip"]');
        if (count($zipLinks) < 1) {
            return new WordPressPackageRepository([]);
        }

        return new WordPressPackageRepository(
            Collection::make($zipLinks)
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
                    return self::packageFromUrl($url);
                })
                ->toArray()
        );
    }
}
