<?php

declare(strict_types=1);

namespace Michalsn\CodeIgniterTranslatable\Traits;

use Michalsn\CodeIgniterTranslatable\Exceptions\TranslatableException;

trait TranslatableEntity
{
    public function translate(?string $locale = null)
    {
        if ($locale === null) {
            $locale = is_cli() ? config('App')->defaultLocale : service('request')->getLocale();
        }

        if (! in_array($locale, config('App')->supportedLocales, true)) {
            throw TranslatableException::forLocaleNotSupported($locale);
        }

        return $this->attributes['translations'][$locale];
    }

    public function hasTranslation(string $locale): bool
    {
        return isset($this->attributes['translations'][$locale]);
    }

    public function getTranslationKeys(): array
    {
        return array_keys($this->attributes['translations']);
    }
}
