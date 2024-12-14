<?php

declare(strict_types=1);

namespace Michalsn\CodeIgniterTranslatable\Exceptions;

use RuntimeException;

final class TranslatableException extends RuntimeException
{
    public static function forLocaleNotSupported(string $name): static
    {
        return new self(lang('Translatable.localeNotSupported', [$name]));
    }
}
