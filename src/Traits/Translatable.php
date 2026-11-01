<?php

namespace Melbedran\Multira\Traits;

use Melbedran\Multira\Support\JsonTranslationStore;

/**
 * JSON model translations.
 *
 * The source locale stays in the database. Other locales are stored in
 * lang/{locale}.json using the source text as the key.
 *
 * @example
 * // lang/en.json: { "ملابس": "Clothes" }
 * // $category->getTranslation('name', 'en') => "Clothes"
 */
trait Translatable
{
    protected static function bootTranslatable(): void
    {
        static::deleted(function ($model) {
            $model->deleteTranslationsFromJson();
        });
    }

    /**
     * @return list<string>
     */
    abstract public function getTranslatableAttributes(): array;

    public function getTranslation(string $field, ?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $value = $this->{$field};

        if ($locale === $this->multiraSourceLocale() || $value === null || trim((string) $value) === '') {
            return $value;
        }

        return $this->getTranslationFromJson((string) $value, $locale) ?? $value;
    }

    public function hasTranslationFor(string $field, ?string $locale = null): bool
    {
        $locale = $locale ?? app()->getLocale();
        $value = trim((string) ($this->{$field} ?? ''));

        if ($value === '') {
            return false;
        }

        if ($locale === $this->multiraSourceLocale()) {
            return true;
        }

        return $this->getTranslationFromJson($value, $locale) !== null;
    }

    public function isVisibleForLocale(?string $locale = null): bool
    {
        return $this->hasTranslationFor('name', $locale);
    }

    /**
     * @return array<string, array<string, bool>>
     */
    public function getTranslationStatus(): array
    {
        $status = [];

        foreach ($this->getTranslatableAttributes() as $field) {
            foreach ($this->multiraTargetLocales() as $locale) {
                $status[$field][$locale] = $this->hasTranslationFor($field, $locale);
            }
        }

        return $status;
    }

    public function getLocalizedValue(string $field, ?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $value = trim((string) ($this->{$field} ?? ''));

        if ($value === '') {
            return null;
        }

        if ($locale === $this->multiraSourceLocale()) {
            return $value;
        }

        return $this->getTranslationFromJson($value, $locale);
    }

    public function scopeVisibleForLocale($query, ?string $locale = null)
    {
        return $query->whereNotNull('name')->where('name', '!=', '');
    }

    public function getTranslationFromJson(string $sourceText, string $locale): ?string
    {
        return $this->multiraStore()->get($sourceText, $locale);
    }

    public function addTranslationToJson(string $sourceText, string $locale, string $translation): void
    {
        $this->multiraStore()->put($sourceText, $locale, $translation);
    }

    public function removeTranslationFromJson(string $sourceText, string $locale): void
    {
        $this->multiraStore()->forget($sourceText, $locale);
    }

    public function renameTranslationKey(string $oldText, string $newText): void
    {
        $this->multiraStore()->rename($oldText, $newText, $this->multiraTargetLocales());
    }

    public function deleteTranslationsFromJson(): void
    {
        foreach ($this->getTranslatableAttributes() as $field) {
            $value = $this->{$field};

            if ($value === null || trim((string) $value) === '') {
                continue;
            }

            foreach ($this->multiraTargetLocales() as $locale) {
                $this->removeTranslationFromJson((string) $value, $locale);
            }
        }
    }

    /**
     * @return array<string, string|null>
     */
    public function translationsForText(string $sourceText): array
    {
        return $this->multiraStore()->forText($sourceText, $this->multiraTargetLocales());
    }

    /**
     * @return array<string, string|null>
     */
    public function resolveTranslationsForField(string $field, ?string $sourceText = null): array
    {
        $sourceText = trim((string) ($sourceText ?? $this->{$field} ?? ''));

        if (session()->has("{$field}_translations")) {
            return session("{$field}_translations");
        }

        return $sourceText !== '' ? $this->translationsForText($sourceText) : [];
    }

    /**
     * @param  array<string, string>|null  $sourceTexts
     * @return array<string, array<string, string|null>>
     */
    public function resolveAllTranslations(?array $sourceTexts = null): array
    {
        $translations = [];

        foreach ($this->getTranslatableAttributes() as $field) {
            $sourceText = isset($sourceTexts[$field]) ? trim((string) $sourceTexts[$field]) : null;
            $translations[$field] = $this->resolveTranslationsForField($field, $sourceText);
        }

        return $translations;
    }

    protected function multiraStore(): JsonTranslationStore
    {
        return app(JsonTranslationStore::class);
    }

    protected function multiraSourceLocale(): string
    {
        return (string) config('multira.source_locale', 'ar');
    }

    /**
     * @return list<string>
     */
    protected function multiraTargetLocales(): array
    {
        return array_values(config('multira.target_locales', ['en']));
    }
}
