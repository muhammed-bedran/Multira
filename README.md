# Multira

A general-purpose Laravel localization package. The public API follows [mcamara/laravel-localization](https://github.com/mcamara/laravel-localization): locale prefixes in URLs, familiar middleware names, and `Multira::setLocale()`.

It also stores Eloquent field translations in `lang/{locale}.json`. Translations are saved, edited, and deleted by you. There is no Google Translate.

Use it in any Laravel 11 or 12 app: a blog, a dashboard, an API site, or a store.

- PHP 8.2+
- Laravel 11 and 12
- Packagist: [melbedran/multira](https://packagist.org/packages/melbedran/multira)

---

## Install

```bash
composer require melbedran/multira
php artisan multira:install
```

No `repositories` entry is needed. Composer downloads it from Packagist automatically.

This publishes `config/multira.php` and creates empty JSON files for target locales.

The service provider, facade, and middleware aliases are auto-registered. You do not need to edit `bootstrap/app.php`.

---

## Configure locales

Edit `config/multira.php`:

```php
'supported' => ['ar', 'en'],
'localesOrder' => ['ar', 'en'],
'source_locale' => 'ar',          // language stored in the database
'target_locales' => ['en'],       // languages stored in lang/{locale}.json
```

Also set the app default locale in `.env`:

```env
APP_LOCALE=ar
APP_FALLBACK_LOCALE=ar
```

Add any locale code from the catalog (`en`, `ar`, `fr`, `de`, `es`, `tr`, `fa`, ...). To add a locale that is not in the catalog, use the `locales` array.

---

## Two kinds of translation

Multira helps with **locale URLs** and **Eloquent field translations**.

Static UI text (menu, buttons, titles) uses normal Laravel language files. Do not mix the two.

| Type | Example | How |
|---|---|---|
| Dynamic DB data | category name, product title | `Translatable` + `$model->getTranslation('name')` |
| Static UI text | Home, Cart, Login, hero title | `__('website.home')` + `lang/{locale}/website.php` |

---

## Localized routes

### What to wrap

Wrap only the public pages that should have a locale prefix (`/ar`, `/en`).

Keep admin, auth, APIs, and private panels outside Multira so their URLs stay stable.

| Routes | Wrap with Multira? | Result |
|---|---|---|
| Public website pages | Yes | `/ar`, `/en/about` |
| Admin dashboard | No | `/admin/dashboard/...` |
| User panel | No | `/user/...` |
| Auth (login/register) | No | `/login`, `/register` |
| Webhooks / health | No | `/up`, `/stripe/webhook` |

### Example `routes/web.php`

```php
use App\Http\Controllers\Front\HomeController;
use Illuminate\Support\Facades\Route;
use Melbedran\Multira\Facades\Multira;

// Public pages: locale prefix
Multira::routes(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/about', [PageController::class, 'about'])->name('about');
});

// Keep these outside Multira
require __DIR__.'/auth.php';
require __DIR__.'/dashboard.php';
require __DIR__.'/user.php';
```

Same style as mcamara if you prefer to write the group yourself:

```php
Route::group([
    'prefix' => Multira::setLocale(),
    'middleware' => [
        'localeSessionRedirect',
        'localizationRedirect',
        'localeViewPath',
    ],
], function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
});
```

Example URLs:

- Public: `/ar`, `/en`, `/ar/about`
- Admin stays: `/admin/dashboard/categories`

---

## Language switcher and page direction

In your public layout:

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

## Translate Eloquent fields (categories, products, posts...)

### 1) Add the trait to the model

`groups` in config is not enough. The model **must** use `Translatable`.

If you see `This group is not supported.`, the model is missing the trait or is not mapped in `groups`.

```php
use Melbedran\Multira\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use Translatable;

    protected $fillable = ['name', 'description', 'status'];

    public function getTranslatableAttributes(): array
    {
        return ['name', 'description'];
    }
}
```

### 2) Map the model in config

```php
'groups' => [
    'categories' => \App\Models\Category::class,
    'products' => \App\Models\Product::class,
],
```

### 3) Add translation inputs in the admin form

```blade
<form method="POST" action="{{ route('dashboard.categories.update', $category->id) }}">
    @csrf
    @method('PUT')

    <input name="name" value="{{ old('name', $category->name) }}">
    <textarea name="description">{{ old('description', $category->description) }}</textarea>

    <x-multira-translations group="categories" field="name" :model="$category" />
    <x-multira-translations group="categories" field="description" :model="$category" />

    <button type="submit">Save</button>
</form>
```

Each target locale gets an input, a save button, and a delete button. Nothing is auto-translated.

Saved into JSON like:

```json
// lang/en.json
{
    "كهربائيات": "Electronics"
}
```

### 4) Show the translated value on the website

Do **not** print `$category->name` on public pages if you want locale-aware output.

```blade
{{-- wrong for multilingual public pages --}}
{{ $category->name }}

{{-- correct --}}
{{ $category->getTranslation('name') }}
{{ $category->getTranslation('description') }}
```

Examples:

```blade
@foreach ($categories as $category)
    <button data-id="{{ $category->id }}">
        {{ $category->getTranslation('name') }}
    </button>
@endforeach
```

```blade
<b>{{ $product->getTranslation('name') }}</b>
@if ($product->category)
    <small>{{ $product->category->getTranslation('name') }}</small>
@endif
```

Behavior:

- current locale = source locale (`ar`) → value from the database
- current locale = target locale (`en`) → value from `lang/en.json`
- missing translation → falls back to the source text

Optional helpers on the model:

```php
public function translatedName(?string $locale = null): string
{
    return $this->getTranslation('name', $locale) ?? $this->name;
}

public function translatedDescription(?string $locale = null): ?string
{
    return $this->getTranslation('description', $locale);
}
```

Then in Blade:

```blade
{{ $category->translatedName() }}
```

Useful methods:

```php
$category->getTranslation('name');              // current locale
$category->getTranslation('name', 'en');         // force locale
$category->getLocalizedValue('name', 'en');      // null if missing (no fallback)
$category->hasTranslationFor('name', 'en');
$category->addTranslationToJson('كهربائيات', 'en', 'Electronics');
$category->renameTranslationKey('قديم', 'جديد');
$category->deleteTranslationsFromJson();
```

---

## Static UI text (menu, titles, buttons)

This is normal Laravel localization. Multira only sets the current locale; Laravel reads the language files.

### Create language files

`lang/ar/website.php`

```php
<?php

return [
    'home' => 'الرئيسية',
    'products' => 'المنتجات',
    'login' => 'تسجيل الدخول',
    'register' => 'إنشاء حساب',
    'cart' => 'السلة',
    'shop_now' => 'تسوّق المنتجات',
    'best_sellers' => 'الأكثر مبيعًا',
    'why_us' => 'لماذا بلقيس؟',
    'hero_title' => 'دفء وأناقة في كل تفصيلة',
    'hero_text' => 'إلكترونيات وملابس ومستلزمات منزل وقهوة وكتب وأكثر.',
];
```

`lang/en/website.php`

```php
<?php

return [
    'home' => 'Home',
    'products' => 'Products',
    'login' => 'Login',
    'register' => 'Register',
    'cart' => 'Cart',
    'shop_now' => 'Shop products',
    'best_sellers' => 'Best sellers',
    'why_us' => 'Why us?',
    'hero_title' => 'Warmth and elegance in every detail',
    'hero_text' => 'Electronics, clothing, home essentials, coffee, books and more.',
];
```

### Use them in Blade

```blade
<a href="#home">{{ __('website.home') }}</a>
<a href="#products">{{ __('website.products') }}</a>
<a href="{{ route('login') }}">{{ __('website.login') }}</a>
<button>{{ __('website.cart') }}</button>

<h1>{{ __('website.hero_title') }}</h1>
<p>{{ __('website.hero_text') }}</p>
<a class="btn" href="#products">{{ __('website.shop_now') }}</a>
```

Because Multira calls `app()->setLocale(...)`, `__('website.home')` automatically returns Arabic on `/ar` and English on `/en`.

Keep static UI keys in PHP language files (`lang/{locale}/website.php`). Keep Eloquent translations in JSON (`lang/en.json`). That separation avoids conflicts.

---

## Recommended setup order

1. Install Multira and run `php artisan multira:install`
2. Configure `supported`, `source_locale`, and `target_locales`
3. Wrap only public routes with `Multira::routes(...)`
4. Update the public layout `lang` / `dir` and add `<x-multira-switcher />`
5. Add `Translatable` to models + map them in `groups`
6. Add `<x-multira-translations />` in admin forms
7. Show fields with `getTranslation(...)` on public pages
8. Move fixed menu/hero text to `lang/{locale}/website.php` and use `__('website.*')`

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
