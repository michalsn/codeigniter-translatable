<?php

declare(strict_types=1);

namespace Michalsn\CodeIgniterTranslatable\Traits;

use CodeIgniter\BaseModel;
use Michalsn\CodeIgniterTranslatable\Config\Translatable;
use Michalsn\CodeIgniterTranslatable\Exceptions\TranslatableException;
use ReflectionException;
use ReflectionObject;

trait HasTranslations
{
    private BaseModel $translatableModel;
    private ?Translatable $translatableConfig;
    private array $translations       = [];
    private string $defaultLocale     = '';
    private array $supportedLocales   = [];
    private array $activeTranslations = [];
    private bool $tempUseFallbackLocale;
    private string $tempFallbackLocale;
    private bool $tempFillWithEmpty;
    private bool $searchInTranslations = false;

    /**
     * Set up model events and initialize
     * translatable model related stuff.
     */
    protected function initTranslations(BaseModel|string $translatableModel): void
    {
        $this->beforeInsert[]  = 'translationsBeforeInsert';
        $this->afterInsert[]   = 'translationsAfterInsert';
        $this->beforeUpdate[]  = 'translationsBeforeUpdate';
        $this->afterUpdate[]   = 'translationsAfterUpdate';
        $this->beforeFind[]    = 'translationsBeforeFind';
        $this->afterFind[]     = 'translationsAfterFind';
        $this->allowedFields[] = 'translations';

        $this->translatableModel  = $translatableModel instanceof BaseModel ? $translatableModel : model($translatableModel);
        $this->translatableConfig = config('Translatable');

        $this->defaultLocale    = config('App')->defaultLocale;
        $this->supportedLocales = config('App')->supportedLocales;

        $this->tempUseFallbackLocale = $this->translatableConfig->useFallbackLocale;
        $this->tempFallbackLocale    = $this->translatableConfig->fallbackLocale ?? $this->defaultLocale;
        $this->tempFillWithEmpty     = $this->translatableConfig->fillWithEmpty;

        helper('inflector');
    }

    /**
     * Will get all the supported translations.
     */
    public function withAllTranslations(): static
    {
        $this->activeTranslations = $this->supportedLocales;

        return $this;
    }

    /**
     * Will get only listed translations.
     */
    public function withTranslations(array $locales): static
    {
        foreach ($locales as $locale) {
            if (! in_array($locale, $this->supportedLocales, true)) {
                throw TranslatableException::forLocaleNotSupported($locale);
            }
        }

        $this->activeTranslations = $locales;

        return $this;
    }

    public function useFallbackLocale(bool $fallback = true): self
    {
        $this->tempUseFallbackLocale = $fallback;

        return $this;
    }

    public function setFallbackLocale(string $locale): self
    {
        if (! in_array($locale, $this->supportedLocales, true)) {
            throw TranslatableException::forLocaleNotSupported($locale);
        }

        $this->tempFallbackLocale = $locale;
        $this->useFallbackLocale();

        return $this;
    }

    public function useFillOnEmpty(bool $fill = true): self
    {
        $this->tempFillWithEmpty = $fill;

        return $this;
    }

    /**
     * Sets the default locale for current request.
     */
    private function setDefaultTranslations(): void
    {
        $this->activeTranslations = [is_cli() ? $this->defaultLocale : service('request')->getLocale()];
    }

    /**
     * Build foreign key based on current model settings.
     */
    private function buildForeignKeyField(): string
    {
        return singular($this->table) . '_id';
    }

    /**
     * Build primary key based on a translatable model.
     */
    private function buildPrimaryKeyField(): string
    {
        $refObj = new ReflectionObject($this->translatableModel);

        $refProp = $refObj->getProperty('primaryKey');

        return $refProp->getValue($this->translatableModel);
    }

    /**
     * Store translations before insert/update.
     */
    private function setTranslations(array $translations): void
    {
        foreach ($translations as $locale => $fields) {
            if (in_array($locale, $this->supportedLocales, true)) {
                $this->translations[$locale] = $fields;
            }
        }
    }

    /**
     * Before insert event.
     */
    protected function translationsBeforeInsert(array $eventData): array
    {
        if (array_key_exists('translations', $eventData['data'])) {
            $this->setTranslations($eventData['data']['translations']);
            unset($eventData['data']['translations']);
        }

        return $eventData;
    }

    /**
     * After insert event.
     */
    protected function translationsAfterInsert(array $eventData): void
    {
        if ($this->translations !== [] && $eventData['result']) {
            $foreignKeyField = $this->buildForeignKeyField();

            foreach ($this->translations as $locale => $translations) {
                $this->translatableModel->insert(array_merge($translations, [
                    $foreignKeyField => $eventData[$this->primaryKey],
                    'locale'         => $locale,
                ]));
            }

            $this->translations = [];
        }
    }

    /**
     * Before update event.
     */
    protected function translationsBeforeUpdate(array $eventData): array
    {
        if (array_key_exists('translations', $eventData['data'])) {
            $this->setTranslations($eventData['data']['translations']);
            unset($eventData['data']['translations']);
        }

        return $eventData;
    }

    /**
     * After update event.
     *
     * @throws ReflectionException
     */
    protected function translationsAfterUpdate(array $eventData): void
    {
        if ($this->translations !== [] && $eventData['result']) {
            $foreignKeyField = $this->buildForeignKeyField();

            foreach ($this->translations as $locale => $translations) {
                foreach ($eventData[$this->primaryKey] as $id) {
                    $where = [
                        $foreignKeyField => $id,
                        'locale'         => $locale,
                    ];

                    $found = $this->translatableModel
                        ->where($where)
                        ->countAllResults();

                    $translations = array_merge($translations, $where);

                    if ($found === 1) {
                        $this->translatableModel
                            ->where($where)
                            ->update(null, $translations);
                    } else {
                        $this->translatableModel->insert($translations);
                    }
                }
            }

            $this->translations = [];
        }
    }

    /**
     * Before find event.
     */
    protected function translationsBeforeFind(array $eventData): array
    {
        if (! $this->searchInTranslations) {
            return $eventData;
        }

        if ($this->activeTranslations === []) {
            $this->setDefaultTranslations();
        }

        $locales = $this->activeTranslations;

        // Make sure the fallback locale is used
        if ($this->tempUseFallbackLocale && ! in_array($this->tempFallbackLocale, $locales, true)) {
            $locales[] = $this->tempFallbackLocale;
        }

        $this
            ->groupEnd()
            ->whereIn($this->translatableModel->getTable() . '.locale', $locales)
            ->select('DISTINCT ' . $this->db->prefixTable($this->table) . '.*', false)
            ->join(
                $this->translatableModel->getTable(),
                sprintf(
                    '%s.%s = %s.%s',
                    $this->translatableModel->getTable(),
                    $this->buildForeignKeyField(),
                    $this->table,
                    $this->primaryKey
                )
            );

        return $eventData;
    }

    /**
     * After find event.
     */
    protected function translationsAfterFind(array $eventData): array
    {
        if (empty($eventData['data'])) {
            return $eventData;
        }

        if ($this->activeTranslations === []) {
            $this->setDefaultTranslations();
        }

        $locales = $this->activeTranslations;

        // Make sure the fallback locale is used
        if ($this->tempUseFallbackLocale && ! in_array($this->tempFallbackLocale, $locales, true)) {
            $locales[] = $this->tempFallbackLocale;
        }

        if ($eventData['singleton']) {
            if ($this->tempReturnType === 'array') {
                $eventData['data']['translations'] = $this->getTranslatableById($eventData['data'][$this->primaryKey], $locales);
            } else {
                $eventData['data']->translations = $this->getTranslatableById($eventData['data']->{$this->primaryKey}, $locales);
            }
        } else {
            $keys         = array_column($eventData['data'], $this->primaryKey);
            $translations = $this->getTranslatableByIds($keys, $locales);

            foreach ($eventData['data'] as &$data) {
                if ($this->tempReturnType === 'array') {
                    $data['translations'] = $translations[$data[$this->primaryKey]] ?? [];
                } else {
                    $data->translations = $translations[$data->{$this->primaryKey}] ?? [];
                }
            }
        }

        $this->resetTranslations();

        return $eventData;
    }

    /**
     * Reset translations.
     */
    private function resetTranslations(): void
    {
        $this->tempUseFallbackLocale = $this->translatableConfig->useFallbackLocale;
        $this->tempFallbackLocale    = $this->translatableConfig->fallbackLocale ?? $this->defaultLocale;
        $this->tempFillWithEmpty     = $this->translatableConfig->fillWithEmpty;
        $this->activeTranslations    = [];
        $this->searchInTranslations  = false;
    }

    /**
     * @param list<string> $locales
     */
    protected function getTranslatableById(int|string $foreignKeyId, array $locales): array
    {
        $foreignKeyField = $this->buildForeignKeyField();
        $items           = $this->translatableModel
            ->where($foreignKeyField, $foreignKeyId)
            ->whereIn('locale', $locales)
            ->findAll();

        // Format
        $items = array_map(static function ($item) use ($foreignKeyField) {
            if (is_array($item)) {
                unset($item[$foreignKeyField], $item['locale']);
            } else {
                unset($item->{$foreignKeyField}, $item->locale);
            }

            return $item;
        }, array_column($items, null, 'locale'));

        // Fill missing translations
        if (($this->tempFillWithEmpty || $this->tempUseFallbackLocale)
            && ($missingLocales = array_diff($locales, array_keys($items))) !== []) {
            $primaryKeyField = $this->buildPrimaryKeyField();

            foreach ($missingLocales as $missing) {
                if ($this->tempUseFallbackLocale && isset($items[$this->tempFallbackLocale])) {
                    $items[$missing] = $items[$this->tempFallbackLocale] ?? null;
                    if (is_array($items[$missing])) {
                        $items[$missing][$primaryKeyField] = null;
                    } else {
                        $items[$missing]->{$primaryKeyField} = null;
                    }
                } else {
                    $items[$missing] = $this->fillEmptyTranslation($foreignKeyField, $foreignKeyId);
                }
            }
        }

        // Remove fallback locale if conditions are met
        if ($this->tempUseFallbackLocale && count($this->activeTranslations) < count($locales)) {
            unset($items[$this->tempFallbackLocale]);
        }

        return $items;
    }

    /**
     * @param list<int|string> $foreignKeyIds
     * @param list<string>     $locales
     */
    protected function getTranslatableByIds(array $foreignKeyIds, array $locales): array
    {
        $foreignKeyField = $this->buildForeignKeyField();
        $items           = $this->translatableModel
            ->whereIn($foreignKeyField, $foreignKeyIds)
            ->whereIn('locale', $locales)
            ->findAll();

        $results = [];

        // Format found translations
        foreach ($items as &$item) {
            if (is_array($item)) {
                $id     = $item[$foreignKeyField];
                $locale = $item['locale'];
                unset($item[$foreignKeyField], $item['locale']);
            } else {
                $id     = $item->{$foreignKeyField};
                $locale = $item->locale;
                unset($item->{$foreignKeyField}, $item->locale);
            }
            $results[$id][$locale] = $item;
        }

        unset($items);

        if ($this->tempFillWithEmpty || $this->tempUseFallbackLocale) {
            $removeFallback  = count($this->activeTranslations) < count($locales);
            $primaryKeyField = $this->buildPrimaryKeyField();

            foreach ($foreignKeyIds as $foreignKeyId) {
                // Fill missing translations
                if (($missingLocales = array_diff($locales, array_keys($results[$foreignKeyId] ?? []))) !== []) {
                    foreach ($missingLocales as $missing) {
                        if ($this->tempUseFallbackLocale && isset($results[$foreignKeyId][$this->tempFallbackLocale])) {
                            $results[$foreignKeyId][$missing] = $results[$foreignKeyId][$this->tempFallbackLocale] ?? null;
                            if (is_array($results[$foreignKeyId][$missing])) {
                                $results[$foreignKeyId][$missing][$primaryKeyField] = null;
                            } else {
                                $results[$foreignKeyId][$missing]->{$primaryKeyField} = null;
                            }
                        } else {
                            $results[$foreignKeyId][$missing] = $this->fillEmptyTranslation($foreignKeyField, $foreignKeyId);
                        }
                    }
                }

                // Remove fallback locale if conditions are met
                if ($this->tempUseFallbackLocale && $removeFallback) {
                    unset($results[$foreignKeyId][$this->tempFallbackLocale]);
                }
            }
        }

        return $results;
    }

    /**
     * Fill empty translations.
     */
    private function fillEmptyTranslation(string $foreignKeyField, int|string $foreignKeyId)
    {
        $refObj = new ReflectionObject($this->translatableModel);

        $refProp = $refObj->getProperty('primaryKey');
        $idField = $refProp->getValue($this->translatableModel);

        $refProp = $refObj->getProperty('allowedFields');
        $fields  = $refProp->getValue($this->translatableModel);

        array_unshift($fields, $idField);

        return array_reduce(
            $fields,
            static function ($acc, $key) use ($foreignKeyField) {
                if ($key === 'locale') {
                    return $acc;
                }
                if ($key === $foreignKeyField) {
                    // $acc[$key] = $foreignKeyId;
                    unset($acc[$key]);

                    return $acc;
                }
                $acc[$key] = $key === 'id' ? null : '';

                return $acc;
            },
            []
        );
    }

    private function handleTranslationSearch(): void
    {
        if ($this->searchInTranslations === false) {
            $this->searchInTranslations = true;
            $this->groupStart();
        }
    }

    public function whereTranslation(string $field, int|string|null $value = null): self
    {
        $this->handleTranslationSearch();
        $this->where($this->translatableModel->getTable() . '.' . $field, $value);

        return $this;
    }

    public function orWhereTranslation(string $field, int|string|null $value = null): self
    {
        $this->handleTranslationSearch();
        $this->orWhere($this->translatableModel->getTable() . '.' . $field, $value);

        return $this;
    }

    public function whereInTranslation(string $field, array $value): self
    {
        $this->handleTranslationSearch();
        $this->whereIn($this->translatableModel->getTable() . '.' . $field, $value);

        return $this;
    }

    public function whereNotInTranslation(string $field, array $value): self
    {
        $this->handleTranslationSearch();
        $this->whereNotIn($this->translatableModel->getTable() . '.' . $field, $value);

        return $this;
    }

    public function likeTranslation(string $field, string $value, string $type = 'both'): self
    {
        $this->handleTranslationSearch();
        $this->like($this->translatableModel->getTable() . '.' . $field, $value, $type);

        return $this;
    }

    public function orLikeTranslation(string $field, string $value, string $type = 'both'): self
    {
        $this->handleTranslationSearch();
        $this->orLike($this->translatableModel->getTable() . '.' . $field, $value, $type);

        return $this;
    }

    public function notLikeTranslation(string $field, string $value, string $type = 'both'): self
    {
        $this->handleTranslationSearch();
        $this->notLike($this->translatableModel->getTable() . '.' . $field, $value, $type);

        return $this;
    }

    public function orNotLikeTranslation(string $field, string $value, string $type = 'both'): self
    {
        $this->handleTranslationSearch();
        $this->orNotLike($this->translatableModel->getTable() . '.' . $field, $value, $type);

        return $this;
    }
}
