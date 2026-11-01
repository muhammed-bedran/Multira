<?php

namespace Melbedran\Multira;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Melbedran\Multira\Exceptions\UnsupportedLocaleException;
use Melbedran\Multira\Support\LocaleCatalog;

class Multira
{
    protected Application $app;

    protected ?string $currentLocale = null;

    protected ?array $supportedLocales = null;

    protected ?string $routeName = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Detect the locale from the URL (or the given value), apply it, and
     * return the prefix used by Route::group().
     *
     * Same idea as mcamara: use it as the route prefix.
     *
     * Route::group(['prefix' => Multira::setLocale()], function () { ... });
     */
    public function setLocale(?string $locale = null): ?string
    {
        $fromUrl = true;

        if ($locale === null || $locale === '') {
            $locale = $this->request()->segment(1);
        } else {
            $fromUrl = false;
        }

        $locale = $this->getInversedLocaleFromMapping($locale);

        if ($locale && $this->checkLocaleInSupportedLocales($locale)) {
            $this->currentLocale = $locale;
            $mapped = $this->getLocaleFromMapping($locale);
        } else {
            $mapped = null;

            if ($this->hideDefaultLocaleInURL()) {
                $this->currentLocale = $this->getDefaultLocale();
            } else {
                $this->currentLocale = $this->negotiateLocale();
            }
        }

        $this->applyLocale($this->currentLocale);

        return $fromUrl ? $mapped : $this->getLocaleFromMapping($this->currentLocale);
    }

    /**
     * Wrap localized routes in one call.
     *
     * Multira::routes(function () {
     *     Route::get('/', [HomeController::class, 'index'])->name('home');
     * });
     */
    public function routes(Closure $routes, array $middleware = []): void
    {
        Route::group([
            'prefix' => $this->setLocale(),
            'middleware' => $middleware !== [] ? $middleware : config('multira.route_middleware', [
                'localeSessionRedirect',
                'localizationRedirect',
                'localeViewPath',
            ]),
        ], $routes);
    }

    public function getCurrentLocale(): string
    {
        return $this->currentLocale ?: $this->negotiateLocale();
    }

    public function getDefaultLocale(): string
    {
        return (string) config('app.locale', 'en');
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function getSupportedLocales(): array
    {
        if ($this->supportedLocales !== null) {
            return $this->supportedLocales;
        }

        $custom = config('multira.locales', []);
        $keys = config('multira.supported', array_keys($custom));

        $locales = [];

        foreach ($keys as $key) {
            $locales[$key] = array_replace(LocaleCatalog::get($key), $custom[$key] ?? []);
        }

        foreach ($custom as $key => $meta) {
            if (! isset($locales[$key])) {
                $locales[$key] = array_replace(LocaleCatalog::get($key), $meta);
            }
        }

        return $this->supportedLocales = $locales;
    }

    public function setSupportedLocales(array $locales): void
    {
        $this->supportedLocales = $locales;
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function getLocalesOrder(): array
    {
        $order = config('multira.localesOrder', []);
        $supported = $this->getSupportedLocales();

        if ($order === []) {
            return $supported;
        }

        $sorted = [];

        foreach ($order as $locale) {
            if (isset($supported[$locale])) {
                $sorted[$locale] = $supported[$locale];
            }
        }

        foreach ($supported as $locale => $meta) {
            if (! isset($sorted[$locale])) {
                $sorted[$locale] = $meta;
            }
        }

        return $sorted;
    }

    /**
     * @return list<string>
     */
    public function getSupportedLanguagesKeys(): array
    {
        return array_keys($this->getSupportedLocales());
    }

    public function checkLocaleInSupportedLocales(string|bool|null $locale): bool
    {
        if (! is_string($locale) || $locale === '') {
            return false;
        }

        return array_key_exists($locale, $this->getSupportedLocales());
    }

    public function getCurrentLocaleName(): string
    {
        return $this->localeMeta($this->getCurrentLocale())['name'] ?? $this->getCurrentLocale();
    }

    public function getCurrentLocaleNative(): string
    {
        return $this->localeMeta($this->getCurrentLocale())['native'] ?? $this->getCurrentLocale();
    }

    public function getCurrentLocaleNativeReading(): string
    {
        return $this->getCurrentLocaleNative();
    }

    public function getCurrentLocaleDirection(): string
    {
        $dir = $this->localeMeta($this->getCurrentLocale())['dir'] ?? null;

        if ($dir) {
            return $dir;
        }

        $script = $this->getCurrentLocaleScript();

        return in_array($script, ['Arab', 'Hebr'], true) ? 'rtl' : 'ltr';
    }

    public function getCurrentLocaleScript(): string
    {
        return $this->localeMeta($this->getCurrentLocale())['script'] ?? 'Latn';
    }

    public function getCurrentLocaleRegional(): ?string
    {
        return $this->localeMeta($this->getCurrentLocale())['regional'] ?? null;
    }

    public function hideDefaultLocaleInURL(): bool
    {
        return (bool) config('multira.hideDefaultLocaleInURL', false);
    }

    public function isHiddenDefault(?string $locale = null): bool
    {
        $locale = $locale ?? $this->getCurrentLocale();

        return $locale === $this->getDefaultLocale() && $this->hideDefaultLocaleInURL();
    }

    public function localizeURL(?string $url = null, string|bool|null $locale = null): string
    {
        return $this->getLocalizedURL($locale ?: null, $url);
    }

    /**
     * Build the same URL in another locale. Query string is kept.
     */
    public function getLocalizedURL(string|bool|null $locale = null, ?string $url = null, array $attributes = [], bool $forceDefaultLocation = false): string
    {
        unset($attributes);

        if ($locale === false) {
            return $this->getNonLocalizedURL($url);
        }

        $locale = $locale ?: $this->getCurrentLocale();

        if (! $this->checkLocaleInSupportedLocales($locale)) {
            throw new UnsupportedLocaleException("Locale [{$locale}] is not supported.");
        }

        $url = $url ?: $this->request()->fullUrl();
        $parts = $this->parseUrl($url);
        $path = $this->stripLocaleFromPath($parts['path']);

        $hide = $this->hideDefaultLocaleInURL()
            && ! $forceDefaultLocation
            && $locale === $this->getDefaultLocale();

        $localizedPath = $hide
            ? ($path === '' ? '/' : $path)
            : '/'.$this->getLocaleFromMapping($locale).($path === '/' || $path === '' ? '' : $path);

        return $this->assembleUrl($parts, $localizedPath);
    }

    public function getNonLocalizedURL(?string $url = null): string
    {
        $url = $url ?: $this->request()->fullUrl();
        $parts = $this->parseUrl($url);

        return $this->assembleUrl($parts, $this->stripLocaleFromPath($parts['path']) ?: '/');
    }

    /**
     * @return array<string, string>
     */
    public function getLocalesMapping(): array
    {
        return config('multira.localesMapping', []);
    }

    public function getLocaleFromMapping(?string $locale): ?string
    {
        if ($locale === null) {
            return null;
        }

        $mapping = $this->getLocalesMapping();

        return $mapping[$locale] ?? $locale;
    }

    public function getInversedLocaleFromMapping(?string $locale): ?string
    {
        if ($locale === null) {
            return null;
        }

        $inverse = array_flip($this->getLocalesMapping());

        return $inverse[$locale] ?? $locale;
    }

    public function transRoute(string $routeName): string
    {
        $this->routeName = $routeName;

        return trans($routeName);
    }

    public function setRouteName(string $routeName): void
    {
        $this->routeName = $routeName;
    }

    /**
     * @return list<string>
     */
    public function getTargetLocales(): array
    {
        return array_values(config('multira.target_locales', []));
    }

    public function getSourceLocale(): string
    {
        return (string) config('multira.source_locale', $this->getDefaultLocale());
    }

    public function sessionKey(): string
    {
        return (string) config('multira.session_key', 'locale');
    }

    public function cookieName(): string
    {
        return (string) config('multira.cookie.name', 'locale');
    }

    public function cookieMinutes(): int
    {
        return (int) config('multira.cookie.minutes', 60 * 24 * 365);
    }

    public function shouldIgnoreUrl(?string $path = null): bool
    {
        $path = $path ?? '/'.$this->request()->path();

        foreach (config('multira.urlsIgnored', []) as $ignored) {
            if ($this->pathMatches($path, $ignored)) {
                return true;
            }
        }

        return false;
    }

    public function shouldIgnoreMethod(?string $method = null): bool
    {
        $method = strtoupper($method ?? $this->request()->method());

        return in_array($method, config('multira.httpMethodsIgnored', []), true);
    }

    /**
     * @return array<string, mixed>
     */
    protected function localeMeta(string $locale): array
    {
        return $this->getSupportedLocales()[$locale] ?? [];
    }

    protected function negotiateLocale(): string
    {
        $sessionLocale = $this->sessionLocale();

        if ($sessionLocale && $this->checkLocaleInSupportedLocales($sessionLocale)) {
            return $sessionLocale;
        }

        if (config('multira.useAcceptLanguageHeader')) {
            $preferred = $this->request()->getPreferredLanguage($this->getSupportedLanguagesKeys());

            if ($preferred && $this->checkLocaleInSupportedLocales($preferred)) {
                return $preferred;
            }
        }

        return $this->getDefaultLocale();
    }

    protected function sessionLocale(): ?string
    {
        if (! $this->app->bound('session') || ! $this->app->resolved('session')) {
            try {
                $value = session($this->sessionKey());

                return is_string($value) ? $value : null;
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            $value = session($this->sessionKey());

            return is_string($value) ? $value : null;
        } catch (\Throwable) {
            return null;
        }
    }

    protected function applyLocale(string $locale): void
    {
        $this->app->setLocale($locale);

        if ($this->app->bound('translator')) {
            $this->app->make('translator')->setLocale($locale);
        }

        $regional = $this->getCurrentLocaleRegional();
        $suffix = (string) config('multira.utf8suffix', '');

        if ($regional) {
            setlocale(LC_TIME, $regional.$suffix);
        }
    }

    protected function stripLocaleFromPath(string $path): string
    {
        $path = '/'.ltrim($path, '/');

        foreach ($this->getSupportedLanguagesKeys() as $locale) {
            $mapped = $this->getLocaleFromMapping($locale);

            if (preg_match('#^/'.preg_quote($mapped, '#').'(?:/|$)#', $path) === 1) {
                $path = substr($path, strlen($mapped) + 1) ?: '/';
                break;
            }
        }

        return $path === '' ? '/' : $path;
    }

    /**
     * @return array{scheme: ?string, host: ?string, port: ?int, path: string, query: string, fragment: string}
     */
    protected function parseUrl(string $url): array
    {
        if (! preg_match('#^https?://#i', $url)) {
            $url = $this->request()->root().'/'.ltrim($url, '/');
        }

        $parts = parse_url($url) ?: [];

        return [
            'scheme' => $parts['scheme'] ?? $this->request()->getScheme(),
            'host' => $parts['host'] ?? $this->request()->getHost(),
            'port' => $parts['port'] ?? null,
            'path' => $parts['path'] ?? '/',
            'query' => isset($parts['query']) ? '?'.$parts['query'] : '',
            'fragment' => isset($parts['fragment']) ? '#'.$parts['fragment'] : '',
        ];
    }

    /**
     * @param  array{scheme: ?string, host: ?string, port: ?int, path: string, query: string, fragment: string}  $parts
     */
    protected function assembleUrl(array $parts, string $path): string
    {
        $port = $parts['port'] ? ':'.$parts['port'] : '';

        return $parts['scheme'].'://'.$parts['host'].$port.$path.$parts['query'].$parts['fragment'];
    }

    protected function pathMatches(string $path, string $pattern): bool
    {
        $path = '/'.ltrim($path, '/');
        $regex = '#^'.str_replace('\*', '.*', preg_quote($pattern, '#')).'$#';

        return preg_match($regex, $path) === 1;
    }

    protected function request(): Request
    {
        return $this->app->make('request');
    }
}
