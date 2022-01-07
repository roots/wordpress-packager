<?php

declare(strict_types=1);

namespace Roots\WordPressPackager\Package;

use Composer\Repository\ArrayRepository;
use Composer\Repository\InvalidRepositoryException;
use Illuminate\Support\Collection;
use JsonSerializable;

/**
 * Class ReleaseRepo
 * @package Roots\WordPressPackager
 */
class Repository extends ArrayRepository implements JsonSerializable
{
    /**
     * Repository constructor.
     *
     * @param Package[] $packages
     * @throws InvalidRepositoryException
     */
    public function __construct(array $packages = [])
    {
        $wpPackages = Collection::make($packages);

        $wpPackages->each(function ($p) {
            if (!($p instanceof Package)) {
                $msg = sprintf('all packages need to be of type "%s"', Package::class);
                throw new InvalidRepositoryException($msg);
            }
        });

        $this->initialize();
        parent::__construct(
            $wpPackages
                ->sort(function (Package $a, Package $b) {
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
    public function jsonSerialize(): mixed
    {
        return $this->packages;
    }
}
