<?php

namespace Melbedran\Multira\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string|null setLocale(string|null $locale = null)
 * @method static void routes(\Closure $routes, array $middleware = [])
 * @method static string getCurrentLocale()
 * @method static string getDefaultLocale()
 * @method static array getSupportedLocales()
 * @method static void setSupportedLocales(array $locales)
 * @method static array getLocalesOrder()
 * @method static array getSupportedLanguagesKeys()
 * @method static bool checkLocaleInSupportedLocales(string|bool|null $locale)
 * @method static string getCurrentLocaleName()
 * @method static string getCurrentLocaleNative()
 * @method static string getCurrentLocaleNativeReading()
 * @method static string getCurrentLocaleDirection()
 * @method static string getCurrentLocaleScript()
 * @method static string|null getCurrentLocaleRegional()
 * @method static bool hideDefaultLocaleInURL()
 * @method static bool isHiddenDefault(string|null $locale = null)
 * @method static string localizeURL(string|null $url = null, string|bool|null $locale = null)
 * @method static string localizeUrl(string|null $url = null, string|bool|null $locale = null)
 * @method static string getLocalizedURL(string|bool|null $locale = null, string|null $url = null, array $attributes = [], bool $forceDefaultLocation = false)
 * @method static string getLocalizedUrl(string|bool|null $locale = null, string|null $url = null, array $attributes = [], bool $forceDefaultLocation = false)
 * @method static string getNonLocalizedURL(string|null $url = null)
 * @method static array getLocalesMapping()
 * @method static string|null getLocaleFromMapping(string|null $locale)
 * @method static string|null getInversedLocaleFromMapping(string|null $locale)
 * @method static string transRoute(string $routeName)
 * @method static void setRouteName(string $routeName)
 * @method static array getTargetLocales()
 * @method static string getSourceLocale()
 * @method static string sessionKey()
 * @method static bool shouldIgnoreUrl(string|null $path = null)
 * @method static bool shouldIgnoreMethod(string|null $method = null)
 *
 * @see \Melbedran\Multira\Multira
 */
class Multira extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'multira';
    }
}
