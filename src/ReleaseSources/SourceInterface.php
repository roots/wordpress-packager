<?php

namespace Roots\WordPressPackager\ReleaseSources;

use Roots\WordPressPackager\Package\Package;
use Roots\WordPressPackager\Package\Repository;

interface SourceInterface
{
    public function __construct(Package $packageBase);
    public function fetch(string $endpoint): self;
    public function get(): Repository;
}
