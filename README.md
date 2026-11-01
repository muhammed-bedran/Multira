# Multira

باكج لارافيل للترجمة وتعدد اللغات. أسلوبه قريب من [mcamara/laravel-localization](https://github.com/mcamara/laravel-localization): بادئة اللغة في الرابط، نفس أسماء الـ middleware تقريباً، وواجهة `Multira::setLocale()`.

زيادة على ذلك: ترجمة حقول النماذج يدوياً في ملفات `lang/{locale}.json` بنفس فكرة مشروع TAM.

**بدون ترجمة غوغل.** الحفظ والتعديل والحذف يدوي فقط.

- PHP 8.2+
- Laravel 11 و 12

---

## التثبيت

المستودع على GitHub. أضفه كمصدر VCS ثم ثبّته:

```bash
composer require melbedran/multira
```

إذا لم يكن على Packagist بعد، أضف في `composer.json`:

```json
{
    "repositories": {
        "melbedran-multira": {
            "type": "vcs",
            "url": "https://github.com/muhammed-bedran/Multira.git"
        }
    }
}
```

ثم:

```bash
composer require melbedran/multira:dev-main
php artisan multira:install
```

الأمر ينشر `config/multira.php` وينشئ ملفات JSON الفارغة للغات الهدف.

الـ Service Provider والـ Facade والـ middleware تُسجَّل تلقائياً. لا تحتاج تعديل `bootstrap/app.php`.

---

## الاستخدام السريع

### 1) تغليف صفحات الموقع

أسهل طريقة:

```php
use Melbedran\Multira\Facades\Multira;

Multira::routes(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
});
```

نفس أسلوب mcamara إذا فضّلت كتابته يدوياً:

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
    Route::get('/', [HomeController::class, 'index'])->name('home');
});
```

الروابط تصبح:

- `/ar`
- `/en/cart`
- `/tr/checkout`

### 2) اتجاه الصفحة ومبدّل اللغة

```blade
<html lang="{{ Multira::getCurrentLocale() }}" dir="{{ Multira::getCurrentLocaleDirection() }}">
```

```blade
<x-multira-switcher />
```

أو يدوياً:

```blade
@foreach (Multira::getSupportedLocales() as $locale => $properties)
    @if ($locale === Multira::getCurrentLocale())
        <strong>{{ $properties['native'] }}</strong>
    @else
        <a href="{{ Multira::getLocalizedURL($locale) }}">{{ $properties['native'] }}</a>
    @endif
@endforeach
```

مسار جاهز لتبديل اللغة: `/multira/switch/{locale}`

### 3) ترجمة نموذج (بدون غوغل)

```php
use Melbedran\Multira\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use Translatable;

    public function getTranslatableAttributes(): array
    {
        return ['name', 'description'];
    }
}
```

العربية تُحفظ في قاعدة البيانات. الإنجليزية والتركية تُحفظ في:

```json
// lang/en.json
{
    "ملابس": "Clothes"
}
```

```php
$category->name = 'ملابس';

$category->getTranslation('name', 'en');     // Clothes
$category->getTranslation('name', 'ar');     // ملابس
$category->getLocalizedValue('name', 'tr');  // null إذا لا توجد ترجمة
$category->addTranslationToJson('ملابس', 'en', 'Clothes');
$category->renameTranslationKey('ملابس', 'ملابس رجالية');
$category->deleteTranslationsFromJson();
```

في `config/multira.php` اربط المجموعة بالموديل:

```php
'groups' => [
    'categories' => \App\Models\Category::class,
    'products' => \App\Models\Product::class,
],
```

في فورم الإنشاء/التعديل:

```blade
<form method="POST" action="{{ route('categories.store') }}">
    @csrf

    <input name="name" value="{{ old('name', $category->name) }}">
    <textarea name="description">{{ old('description', $category->description) }}</textarea>

    <x-multira-translations group="categories" field="name" :model="$category" />
    <x-multira-translations group="categories" field="description" :model="$category" />

    <button type="submit">حفظ</button>
</form>
```

كل لغة لها حقل وحفظ وحذف يدوي. لا يوجد زر ترجمة آلية.

---

## الإعدادات المهمة

`config/multira.php`:

```php
'supported' => ['ar', 'en', 'tr'],
'source_locale' => 'ar',
'target_locales' => ['en', 'tr'],
'hideDefaultLocaleInURL' => false,
'useAcceptLanguageHeader' => false,
```

إضافة لغة جديدة: ضع رمزها في `supported` و `target_locales`. الكتالوج فيه عشرات اللغات (fr, de, fa, ku, ...). لإضافة لغة غير موجودة في الكتالوج استخدم `locales`.

---

## واجهة شبيهة بـ mcamara

| Multira | mcamara |
|---|---|
| `Multira::setLocale()` | `LaravelLocalization::setLocale()` |
| `Multira::getCurrentLocale()` | `LaravelLocalization::getCurrentLocale()` |
| `Multira::getSupportedLocales()` | `LaravelLocalization::getSupportedLocales()` |
| `Multira::getLocalizedURL('en')` | `LaravelLocalization::getLocalizedURL('en')` |
| `Multira::getNonLocalizedURL()` | `LaravelLocalization::getNonLocalizedURL()` |
| `Multira::checkLocaleInSupportedLocales()` | نفس الاسم |
| `Multira::getCurrentLocaleDirection()` | نفس الاسم |
| `localize` | `localize` |
| `localizationRedirect` | `localizationRedirect` |
| `localeSessionRedirect` | `localeSessionRedirect` |
| `localeCookieRedirect` | `localeCookieRedirect` |
| `localeViewPath` | `localeViewPath` |

أسماء Multira الخاصة تعمل أيضاً: `multira`, `multira.redirect`, `multira.session`, `multira.cookie`, `multira.view`.

دوال مساعدة:

```php
multira()->getCurrentLocale();
localized_url('en');
```

---

## أوامر

```bash
php artisan multira:install
php artisan vendor:publish --tag=multira-config
php artisan vendor:publish --tag=multira-views
```

---

## الاختبارات

```bash
composer install
vendor/bin/phpunit
```

---

## الترخيص

MIT © Melbedran
