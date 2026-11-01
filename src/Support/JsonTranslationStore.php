<?php

namespace Melbedran\Multira\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class JsonTranslationStore
{
    public function get(string $sourceText, string $locale): ?string
    {
        $key = $this->cleanKey($sourceText);

        if ($key === '') {
            return null;
        }

        return $this->all($locale)[$key] ?? null;
    }

    public function put(string $sourceText, string $locale, string $translation): void
    {
        $key = $this->cleanKey($sourceText);

        if ($key === '') {
            return;
        }

        $translations = $this->read($locale);
        $translations[$key] = $translation;
        ksort($translations);

        $this->write($locale, $translations);
    }

    public function forget(string $sourceText, string $locale): void
    {
        $key = $this->cleanKey($sourceText);
        $translations = $this->read($locale);

        if (! isset($translations[$key])) {
            return;
        }

        unset($translations[$key]);
        $this->write($locale, $translations);
    }

    public function rename(string $oldText, string $newText, ?array $locales = null): void
    {
        if ($oldText === $newText || trim($oldText) === '') {
            return;
        }

        $oldKey = $this->cleanKey($oldText);
        $newKey = $this->cleanKey($newText);

        foreach ($locales ?? $this->targetLocales() as $locale) {
            $translations = $this->read($locale);

            if (! isset($translations[$oldKey])) {
                continue;
            }

            $translations[$newKey] = $translations[$oldKey];
            unset($translations[$oldKey]);
            $this->write($locale, $translations);
        }
    }

    /**
     * @return array<string, string>
     */
    public function all(string $locale): array
    {
        return Cache::remember($this->cacheKey($locale), 600, function () use ($locale) {
            return $this->read($locale);
        });
    }

    /**
     * @return array<string, string|null>
     */
    public function forText(string $sourceText, ?array $locales = null): array
    {
        $translations = [];

        foreach ($locales ?? $this->targetLocales() as $locale) {
            $translations[$locale] = $this->get($sourceText, $locale);
        }

        return $translations;
    }

    public function forgetCache(string $locale): void
    {
        Cache::forget($this->cacheKey($locale));
    }

    public function cleanKey(string $text): string
    {
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    /**
     * @return array<string, string>
     */
    protected function read(string $locale): array
    {
        $path = $this->path($locale);

        if (! File::exists($path)) {
            return [];
        }

        return json_decode(File::get($path), true) ?? [];
    }

    /**
     * @param  array<string, string>  $translations
     */
    protected function write(string $locale, array $translations): void
    {
        $path = $this->path($locale);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).PHP_EOL);
        $this->forgetCache($locale);
    }

    protected function path(string $locale): string
    {
        return lang_path($locale.'.json');
    }

    protected function cacheKey(string $locale): string
    {
        return "multira.json:{$locale}";
    }

    /**
     * @return list<string>
     */
    protected function targetLocales(): array
    {
        return array_values(config('multira.target_locales', []));
    }
}
