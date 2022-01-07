<?php

namespace Roots\WordPressPackager\ReleaseSources;

use Roots\WordPressPackager\WordPressPackageRepository;

interface SourceInterface
{
    public function getRepo(): WordPressPackageRepository;
}
