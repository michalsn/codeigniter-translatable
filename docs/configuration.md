# Configuration

To make changes to the config file, we have to have our copy in the `app/Config/Translatable.php`. Luckily, this package comes with a handy command that will make this easy.

When we run:

    php spark translatable:publish

We will get our copy ready for modifications.

---

Available options:

- [$useFallbackLocale](#useFallbackLocale)
- [$fallbackLocale](#fallbackLocale)
- [$fillWithEmpty](#fillWithEmpty)

### $useFallbackLocale

This allows us to decide whether to use fallback to locale functionality when given translation is not found. Default: `false`.

### $fallbackLocale

This allows us to set the desired fallback locale. You can leave it as `null` to follow the settings from `App::defaultLocale`. Default: `null`

### $fillWithEmpty

This allows us to decide whether we should fill empty values when translation is not found. Default: `false`.

