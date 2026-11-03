# Multira

A general-purpose Laravel localization package. The public API follows [mcamara/laravel-localization](https://github.com/mcamara/laravel-localization): locale prefixes in URLs, familiar middleware names, and `Multira::setLocale()`.

It also stores Eloquent field translations in `lang/{locale}.json`. Translations are saved, edited, and deleted by you. There is no Google Translate.

Use it in any Laravel 11 or 12 app: a blog, a dashboard, an API site, or a store.

- PHP 8.2+
- Laravel 11 and 12

---

## Install

After the package is published on [Packagist](https://packagist.org/packages/melbedran/multira), install it with one command:

```bash
composer require melbedran/multira
php artisan multira:install
```

No `repositories` entry is needed. Composer downloads it from Packagist automatically.

This publishes `config/multira.php` and creates empty JSON files for target locales.

The service provider, facade, and middleware aliases are auto-registered. You do not need to edit `bootstrap/app.php`.

---

## Configure locales

Edit `config/multira.php` for your app:

```php
'supported' => ['en', 'ar', 'fr'],
'source_locale' => 'en',
'target_locales' => ['ar', 'fr'],
```

Add any locale code from the catalog (`en`, `ar`, `fr`, `de`, `es`, `tr`, `fa`, ...). To add a locale that is not in the catalog, use the `locales` array.

---

## Localized routes

Wrap the routes that should have a locale prefix. Put any routes of your app inside the group: pages, resources, whatever you need.

```php
use Melbedran\Multira\Facades\Multira;

Multira::routes(function () {
    Route::get('/', [PageController::class, 'home'])->name('home');
    Route::get('/about', [PageController::class, 'about'])->name('about');
    Route::resource('posts', PostController::class);
});
```

Same style as mcamara if you prefer to write the group yourself:

```php
use Melbedran\Multira\Facades\Multira;

Route::group([
    'prefix' => Multira::setLocale(),
    'middleware' => [
        'localeSessionRedirect',
        'localizationRedirect',
        'localeViewPath',
    ],
], function () {
    Route::get('/', [PageController::class, 'home'])->name('home');
    Route::get('/about', [PageController::class, 'about'])->name('about');
});
```

Example URLs when `en` and `ar` are enabled:

- `/en`
- `/ar/about`
- `/en/posts/1`

Routes that should stay without a locale (webhooks, health checks, admin APIs) stay outside the group.

---

## Language switcher and page direction

```blade
<html lang="{{ Multira::getCurrentLocale() }}" dir="{{ Multira::getCurrentLocaleDirection() }}">
```

```blade
<x-multira-switcher />
```

Or build your own links:

```blade
@foreach (Multira::getSupportedLocales() as $locale => $properties)
    @if ($locale === Multira::getCurrentLocale())
        <strong>{{ $properties['native'] }}</strong>
    @else
        <a href="{{ Multira::getLocalizedURL($locale) }}">{{ $properties['native'] }}</a>
    @endif
@endforeach
```

Built-in switch route: `/multira/switch/{locale}`

---

## Translate Eloquent fields

```php
use Melbedran\Multira\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use Translatable;

    public function getTranslatableAttributes(): array
    {
        return ['title', 'body'];
    }
}
```

The source locale stays in the database. Other locales go into JSON:

```json
// lang/ar.json
{
    "Hello world": "مرحبا بالعالم"
}
```

```php
$post->title = 'Hello world';

$post->getTranslation('title', 'ar');     // مرحبا بالعالم
$post->getTranslation('title', 'en');     // Hello world
$post->getLocalizedValue('title', 'fr');  // null when missing
$post->addTranslationToJson('Hello world', 'ar', 'مرحبا بالعالم');
$post->renameTranslationKey('Hello world', 'Hello');
$post->deleteTranslationsFromJson();
```

Map models in `config/multira.php`:

```php
'groups' => [
    'posts' => \App\Models\Post::class,
    'pages' => \App\Models\Page::class,
],
```

Optional form fields for manual save and delete:

```blade
<form method="POST" action="{{ route('posts.store') }}">
    @csrf

    <input name="title" value="{{ old('title', $post->title) }}">
    <textarea name="body">{{ old('body', $post->body) }}</textarea>

    <x-multira-translations group="posts" field="title" :model="$post" />
    <x-multira-translations group="posts" field="body" :model="$post" />

    <button type="submit">Save</button>
</form>
```

Each target locale gets an input, a save button, and a delete button. Nothing is auto-translated.

---

## mcamara-compatible API

| Multira | mcamara |
|---|---|
| `Multira::setLocale()` | `LaravelLocalization::setLocale()` |
| `Multira::getCurrentLocale()` | `LaravelLocalization::getCurrentLocale()` |
| `Multira::getSupportedLocales()` | `LaravelLocalization::getSupportedLocales()` |
| `Multira::getLocalizedURL('en')` | `LaravelLocalization::getLocalizedURL('en')` |
| `Multira::getNonLocalizedURL()` | `LaravelLocalization::getNonLocalizedURL()` |
| `Multira::checkLocaleInSupportedLocales()` | same name |
| `Multira::getCurrentLocaleDirection()` | same name |
| `localize` | `localize` |
| `localizationRedirect` | `localizationRedirect` |
| `localeSessionRedirect` | `localeSessionRedirect` |
| `localeCookieRedirect` | `localeCookieRedirect` |
| `localeViewPath` | `localeViewPath` |

Multira aliases also work: `multira`, `multira.redirect`, `multira.session`, `multira.cookie`, `multira.view`.

Helpers:

```php
multira()->getCurrentLocale();
localized_url('ar');
```

---

## Commands

```bash
php artisan multira:install
php artisan vendor:publish --tag=multira-config
php artisan vendor:publish --tag=multira-views
```

---

## Tests

```bash
composer install
vendor/bin/phpunit
```

---

## License

MIT © Melbedran
