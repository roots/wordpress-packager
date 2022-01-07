<?php

declare(strict_types=1);

namespace Roots\WordPressPackager;

use Composer\Package\Dumper\ArrayDumper;
use Composer\Repository\ArrayRepository;
use Composer\Repository\InvalidRepositoryException;
use Illuminate\Support\Collection;
use JsonSerializable;

/**
 * Class ReleaseRepo
 * @package Roots\WordPressPackager
 */
class WordPressPackageRepository extends ArrayRepository implements JsonSerializable
{
    /**
     * WordPressPackageRepository constructor.
     * @param WordPressPackage[] $packages
     */
    public function __construct(array $packages = [])
    {
        $wpPackages = Collection::make($packages);

        $wpPackages->each(function ($p) {
            if (!($p instanceof WordPressPackage)) {
                $msg = sprintf('all packages need to be of type "%s"', WordPressPackage::class);
                throw new InvalidRepositoryException($msg);
            }
        });

        $this->initialize();
        parent::__construct(
            $wpPackages
                ->sort(function (WordPressPackage $a, WordPressPackage $b) {
                    return $a->compareTo($b);
                })
                ->values()
                ->toArray()
        );
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->packages;
    }
}
