# Configuration

- [Publishing the config file](#publishing-the-config-file)
    - [$useFallbackLocale](#usefallbacklocale)
    - [$fallbackLocale](#fallbacklocale)
    - [$fillWithEmpty](#fillwithempty)
- [Generating the skeleton](#generating-the-skeleton)

## Publishing the config file

To make changes to the config file, we have to have our copy in the `app/Config/Translatable.php`. Luckily, this package comes with a handy command that will make this easy.

When we run:

    php spark translatable:publish

We will get our copy ready for modifications.


#### $useFallbackLocale

This allows us to decide whether to use fallback to locale functionality when given translation is not found. Default: `false`.

#### $fallbackLocale

This allows us to set the desired fallback locale. You can leave it as `null` to follow the settings from `App::defaultLocale`. Default: `null`

#### $fillWithEmpty

This allows us to decide whether we should fill empty values when translation is not found. Default: `false`.

## Generating the skeleton

You can generate the skeleton for your translatable models. To do so, you have to use command:

    php spark translatable:generate

You have to provide a main table name as a parameter.

If we use: `php spark translatable:generate articles`, then it will generate:

- `ArticlesWithTranslations` migration class which you can supplement with missing data for your use case
- `ArticleModel` class (you should complete the `$allowedFields` parameter)
- `ArticleTranslationModel` class (you should complete the `$allowedFields` parameter)
- `Article` entity class
