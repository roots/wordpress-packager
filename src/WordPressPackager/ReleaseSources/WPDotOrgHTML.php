<?php


namespace Roots\WordPressPackager\ReleaseSources;

use Composer\Semver\VersionParser;
use DOMDocument;
use DOMElement;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Roots\WordPressPackager\WordPressPackage;
use Roots\WordPressPackager\WordPressPackageRepository;

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

    public static function isValidReleaseURL(string $url): bool
    {
        $httpUrl = Collection::make(parse_url($url));

        if ($httpUrl->get('scheme') !== 'https') {
            return false;
        }
        if ($httpUrl->get('host') !== 'wordpress.org') {
            return false;
        }

        $basename = self::getBasename($httpUrl);

        if ($basename === '') {
            return false;
        }

        // whitelist
        if (!Str::startsWith($basename, 'wordpress-')) {
            return false;
        }
        if (!Str::endsWith($basename, ['.zip'])) {
            return false;
        }
        // blacklist
        if (Str::startsWith($basename, 'wordpress-mu')) {
            return false;
        }
        if (Str::endsWith($basename, ['-IIS.zip'])) {
            return false;
        }

        $version = self::getVersionFromBasename($basename);

        return self::isVersion($version);
    }

    protected static function getBasename(Collection $urlParts): string
    {
        return Collection::make(explode('/', $urlParts->get('path', '')))->last();
    }

    protected static function getVersionFromBasename(string $basename): ?string
    {
        preg_match('/^wordpress-(?<version>\S+)\.zip$/', $basename, $matches);

        return $matches['version'] ?? null;
    }

    protected static function getVersionFromURL(string $url): ?string
    {
        $httpUrl = Collection::make(parse_url($url));
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

    public function getRepo(): WordPressPackageRepository
    {
        $html = $this->html;
        if ($html === '') {
            throw new InvalidArgumentException('blank html');
        }

        $dom = new DOMDocument('1.0', 'UTF-8'); // infers encoding from html but set encoding for safety
        $prev = libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);


        $zipLinks = $dom->getElementsByTagName('a');
        if ($zipLinks->length < 1) {
            return new WordPressPackageRepository([]);
        }

        return new WordPressPackageRepository(
            Collection::make($zipLinks)
                ->map(function ($zipLink): ?string {
                    if ($zipLink instanceof DOMElement) {
                        $href = $zipLink->getAttribute('href');
                        if (self::isValidReleaseURL($href)) {
                            return $href;
                        }
                    }
                    return null;
                })
                ->filter()
                ->unique()
                ->map(function (string $releaseUrl): WordPressPackage {
                    // TODO: don't hardcode package org
                    $org = 'roots';

                    $name = "$org/wordpress-dotorg";
                    /** @var string $version Version is guaranteed because of `isValidReleaseURL` above. */
                    $version = self::getVersionFromURL($releaseUrl);

                    $package = new WordPressPackage($name, $version);

                    $package->setDistType('zip');
                    $package->setDistUrl($releaseUrl);

                    return $package;
                })
                ->toArray()
        );
    }
}
