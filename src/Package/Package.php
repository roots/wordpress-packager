<?php

declare(strict_types=1);

namespace Roots\WordPressPackager\Package;

use Composer\Package\CompletePackage;
use Composer\Package\Dumper\ArrayDumper;
use Composer\Package\Link;
use Composer\Package\Version\VersionParser;
use Composer\Semver\Comparator as SemverComparator;
use Composer\Semver\Constraint\Constraint;
use Composer\Semver\Semver;
use JsonSerializable;

class Package extends CompletePackage implements JsonSerializable
{
    private \Closure $distCallback;

    public function __construct(string $name)
    {
        parent::__construct(
            $name,
            '0.0.0.0',
            '0.0.0',
        );
    }

    /**
     * Clone the package.
     *
     * @return static
     */
    public function clone(): self
    {
        return clone $this;
    }

    public function withVersion(string $version): self
    {
        $this->version = (new VersionParser())->normalize($version);
        $this->prettyVersion = $version;

        $this->stability = VersionParser::parseStability($this->version);
        $this->dev = $this->stability === 'dev';

        return $this;
    }

    protected function withMetadata(): self
    {
        $this->setType('wordpress-core');
        $this->setDescription(
            'WordPress is open source software you can use to create a beautiful website, blog, or app.'
        );
        $this->setAuthors([
            [
                'name' => 'WordPress Community',
                'homepage' => 'https://wordpress.org/about/'
            ]
        ]);
        $this->setKeywords([
            'wordpress',
            'blog',
            'cms'
        ]);
        $this->setLicense(['GPL-2.0-or-later']);

        return $this;
    }

    protected function withLinks(): self
    {
        $this->setHomepage('https://wordpress.org/');
        $this->setSupport([
            'issues' => 'https://core.trac.wordpress.org/',
            'forum' => 'https://wordpress.org/support/',
            'wiki' => 'https://codex.wordpress.org/',
            'irc' => 'irc://irc.freenode.net/wordpress',
            'source' => 'https://core.trac.wordpress.org/browser',
            'docs' => 'https://developer.wordpress.org/',
            'rss' => 'https://wordpress.org/news/feed/'
        ]);
        $this->setFunding([
            [
                'type' => 'other',
                'url' => 'https://wordpressfoundation.org/donate/'
            ]
        ]);

        return $this;
    }

    public function withRequires(string $minPhpVersion = null): self
    {
        if (is_null($minPhpVersion)) {
            $minPhpVersion = self::getMinPhpVersion($this->getVersion());
        }

        $this->setRequires([
            'php' => new Link(
                $this->getName(),
                'php',
                $phpConstraint = new Constraint(Constraint::STR_OP_GE, $minPhpVersion),
                Link::TYPE_REQUIRE,
                $phpConstraint->getPrettyString()
            )
        ]);

        return $this;
    }

    public function withProvides(): self
    {
        if ($this->version === '0.0.0.0') {
            throw new \Exception('The version must be set before setting implementations provided');
        }

        $this->setProvides([
            'wordpress/core-implementation' => new Link(
                $this->getName(),
                'wordpress/core-implementation',
                new Constraint(Constraint::STR_OP_EQ, $this->version),
                Link::TYPE_PROVIDE,
                $this->prettyVersion
            )
        ]);

        return $this;
    }

    /**
     * Suggest PHP extensions that WordPress expects to be provided.
     *
     * @see https://make.wordpress.org/hosting/handbook/handbook/server-environment/#php-extensions
     */
    protected function withSuggests(): self
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

        return $this;
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

    public function setDistCallback(\Closure $callback): void
    {
        $this->distCallback = $callback;
    }

    /**
     * Just in time metadata insertion before serialization.
     *
     * @return void
     */
    private function prepareData(): void
    {
        $this->withMetadata();
        $this->withLinks();
        $this->withSuggests();

        if (isset($this->distCallback)) {
            ($this->distCallback)($this);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $this->prepareData();

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
     * @param Package $b
     * @return int
     */
    public function compareTo(Package $b): int
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

    public function __toString(): string
    {
        return $this->getPrettyVersion();
    }
}
