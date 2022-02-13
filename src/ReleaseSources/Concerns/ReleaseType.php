<?php

declare(strict_types=1);

namespace Roots\WordPressPackager\ReleaseSources\Concerns;

use Illuminate\Support\Str;

// phpcs:disable
enum ReleaseType: string
{
    case Full = 'full';
    case NewBundled = 'new-bundled';
    case NoContent = 'no-content';

    public function apiName(): string
    {
        return Str::replace(['-', '_', ' '], '_', $this->value);
    }
}
// phpcs:enable
