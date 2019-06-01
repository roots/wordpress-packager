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
    const PACKAGE_TYPE = 'wordpress-core';
    const PACKAGE_DESCRIPTION = 'WordPress is web software you can use to create a beautiful website or blog.';
    const PACKAGE_HOMEPAGE = 'https://wordpress.org/';
    const PACKAGE_LICENCE = ['GPL-2.0-or-later'];

    const PACKAGE_AUTHOR = [
        'name' => 'WordPress Community',
        'homepage' => 'https://wordpress.org/about/',
    ];

    const PACKAGE_KEYWORDS = [
        'wordpress',
        'blog',
        'cms',
    ];

    const PACKAGE_SUPPORT = [
        'issues' => 'https://core.trac.wordpress.org/',
        'forum' => 'https://wordpress.org/support/',
        'wiki' => 'https://codex.wordpress.org/',
        'irc' => 'irc://irc.freenode.net/wordpress',
        'source' => 'https://core.trac.wordpress.org/browser',
        'docs' => 'https://developer.wordpress.org/',
        'rss' => 'https://wordpress.org/news/feed/',
    ];

    const DEPENDENCY_PHP = 'php';
    const DEPENDENCY_WORDPRESS_CORE_INSTALLER = 'roots/wordpress-core-installer';

    public function __construct(string $name, string $version)
    {
        parent::__construct($name, (new VersionParser())->normalize($version), $version);

        $this->setType(self::PACKAGE_TYPE);
        $this->setDescription(self::PACKAGE_DESCRIPTION);
        $this->setAuthors([(object)self::PACKAGE_AUTHOR]);
        $this->setKeywords(self::PACKAGE_KEYWORDS);
        $this->setHomepage(self::PACKAGE_HOMEPAGE);
        $this->setLicense(self::PACKAGE_LICENCE);
        $this->setSupport(self::PACKAGE_SUPPORT);

        $minPhpVersion = self::getMinPhpVersion($this->getVersion());

        $this->setRequires([
            $this->makeLink(self::DEPENDENCY_PHP, new Constraint('>=', $minPhpVersion)),
            $this->makeLink(self::DEPENDENCY_WORDPRESS_CORE_INSTALLER, new Constraint('>=', '1.0.0')),
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
        if (SemverComparator::equalTo($aVer, $bVer)) {
            return strnatcmp($a->getName(), $b->getName());
        }
        if (SemverComparator::greaterThan($aVer, $bVer)) {
            return 1;
        }

        throw new RuntimeException('unable to sort versions');
    }
}
