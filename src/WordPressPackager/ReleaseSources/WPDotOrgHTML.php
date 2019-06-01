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
    const DOM_DOCUMENT_VERSION = '1.0';
    const DOM_DOCUMENT_ENCODING = 'UTF-8';

    // TODO: don't hardcode package org
    const PACKAGE_ORG = 'roots';
    const PACKAGE_NAME = self::PACKAGE_ORG . '/wordpress-dotorg';

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

        if ($httpUrl->get('scheme') !== 'https' || $httpUrl->get('host') !== 'wordpress.org') {
            return false;
        }

        $basename = self::getBasename($httpUrl);

        // Blacklist
        if ($basename === '' || Str::startsWith($basename, 'wordpress-mu') || Str::endsWith($basename, ['-IIS.zip'])) {
            return false;
        }

        // Whitelist
        if (!Str::startsWith($basename, 'wordpress-') || !Str::endsWith($basename, ['.zip'])) {
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
        $dom = $this->parseHtml($this->html);

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
                    /** @var string $version Version is guaranteed because of `isValidReleaseURL` above. */
                    $version = self::getVersionFromURL($releaseUrl);
                    $package = new WordPressPackage(self::PACKAGE_NAME, $version);

                    $package->setDistType('zip');
                    $package->setDistUrl($releaseUrl);

                    return $package;
                })
                ->toArray()
        );
    }

    private function parseHtml(string $html): DOMDocument
    {
        if ($html === '') {
            throw new InvalidArgumentException('Blank HTML');
        }

        // infers encoding from html but set encoding for safety
        $dom = new DOMDocument(self::DOM_DOCUMENT_VERSION, self::DOM_DOCUMENT_ENCODING);
        $prevErrorFlag = libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($prevErrorFlag);

        return $dom;
    }
}
