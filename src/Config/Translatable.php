<?php

declare(strict_types=1);

namespace Michalsn\CodeIgniterTranslatable\Config;

use CodeIgniter\Config\BaseConfig;

class Translatable extends BaseConfig
{
    /**
     * Whether to use fallback functionality.
     */
    public bool $useFallbackLocale = false;

    /**
     * Fallback locale. Set null to use App::defaultLocale.
     */
    public ?string $fallbackLocale = null;

    /**
     * Fill empty values when translation is not found.
     */
    public bool $fillWithEmpty = false;
}
