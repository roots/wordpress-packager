<?php
declare(strict_types=1);

namespace Roots\WordPressPackager;

use Composer\Package\CompletePackage;
use Composer\Package\Dumper\ArrayDumper;
use Composer\Package\Link;
use Composer\Semver\Comparator as SemverComparator;
use Composer\Semver\Constraint\Constraint;
use Composer\Semver\Semver;
use Composer\Semver\VersionParser;
use JsonSerializable;
use RuntimeException;

class WordPressPackage extends CompletePackage implements JsonSerializable
{
    public function __construct(string $name, string $version)
    {
        parent::__construct($name, (new VersionParser())->normalize($version), $version);
        $this->withMetadata();
        $this->withRequires();
        $this->withSuggests();
    }

    protected function withMetadata(): void
    {
        $this->setType('wordpress-core');
        $this->setDescription('WordPress is web software you can use to create a beautiful website or blog.');
        $this->setAuthors([
            (object)[
                'name' => 'WordPress Community',
                'homepage' => 'https://wordpress.org/about/'
            ]
        ]);
        $this->setKeywords([
            'wordpress',
            'blog',
            'cms'
        ]);
        $this->setHomepage('https://wordpress.org/');
        $this->setLicense(['GPL-2.0-or-later']);
        $this->setSupport([
            'issues' => 'https://core.trac.wordpress.org/',
            'forum' => 'https://wordpress.org/support/',
            'wiki' => 'https://codex.wordpress.org/',
            'irc' => 'irc://irc.freenode.net/wordpress',
            'source' => 'https://core.trac.wordpress.org/browser',
            'docs' => 'https://developer.wordpress.org/',
            'rss' => 'https://wordpress.org/news/feed/'
        ]);
    }

    protected function withRequires(): void
    {
        $minPhpVersion = self::getMinPhpVersion($this->getVersion());

        $this->setRequires([
            $this->makeLink('php', new Constraint('>=', $minPhpVersion)),
            $this->makeLink('roots/wordpress-core-installer', new Constraint('>=', '1.0.0')),
        ]);
    }

    /**
     * Suggest PHP extensions that WordPress expects to be provided.
     *
     * @see https://make.wordpress.org/hosting/handbook/handbook/server-environment/#php-extensions
     */
    protected function withSuggests(): void
    {
        $this->setSuggests([
            'ext-curl' => 'Performs remote request operations.',
            'ext-dom' => 'Used to validate Text Widget content and to automatically configuring IIS7+.',
            'ext-exif' => 'Works with metadata stored in images.',
            'ext-fileinfo' => 'Used to detect mimetype of file uploads.',
            'ext-hash' => 'Used for hashing, including passwords and update packages.',
            'ext-imagick' => 'Provides better image quality for media uploads.',
            'ext-json' => 'Used for communications with other servers.',
            'ext-libsodium' => 'Validates Signatures and provides securely random bytes.',
            'ext-mbstring' => 'Used to properly handle UTF8 text.',
            'ext-mysqli' => 'Connects to MySQL for database interactions.',
            'ext-openssl' => 'Permits SSL-based connections to other hosts.',
            'ext-pcre' => 'Increases performance of pattern matching in code searches.',
            'ext-xml' => 'Used for XML parsing, such as from a third-party site.',
            'ext-zip' => 'Used for decompressing Plugins, Themes, and WordPress update packages.',
        ]);
    }

    private function makeLink(string $name, Constraint $constraint): Link
    {
        return new Link(
            $this->getName(),
            $name,
            $constraint,
            'requires ',
            $constraint->getPrettyString()
        );
    }

    /**
     * Determine minimum PHP version for WordPress core.
     *
     * @see https://wordpress.org/news/2019/04/minimum-php-version-update/
     * @see http://displaywp.com/wordpress-minimum-php-version/
     *
     * @param string $version WordPress core version.
     *
     * @return string
     */
    protected static function getMinPhpVersion(string $version): string
    {
        if (Semver::satisfies($version, '< 5.2-dev')) {
            return '5.2.4';
        }

        return '5.6.20';
    }

    public function jsonSerialize(): array
    {
        $dump = (new ArrayDumper())->dump($this);
        // TODO: figure out why I have to do this in upstream
        unset($dump['version_normalized']);
        return $dump;
    }

    public function greaterThanOrEqualTo(string $version): bool
    {
        return SemverComparator::greaterThanOrEqualTo($this->getVersion(), $version);
    }

    /**
     * https://docs.oracle.com/javase/8/docs/api/java/lang/Comparable.html
     * @param WordPressPackage $b
     * @return int
     */
    public function compareTo(WordPressPackage $b): int
    {
        $a = $this;
        $aVer = $a->getVersion();
        $bVer = $b->getVersion();
        if (SemverComparator::lessThan($aVer, $bVer)) {
            return -1;
        }
        if (SemverComparator::greaterThan($aVer, $bVer)) {
            return 1;
        }

        return strnatcmp($a->getName(), $b->getName());
    }
}
