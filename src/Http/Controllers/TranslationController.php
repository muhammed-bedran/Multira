<?php

namespace Melbedran\Multira\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Melbedran\Multira\Traits\Translatable;

class TranslationController extends Controller
{
    public function update(Request $request, string $group, string $field): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', Rule::in(config('multira.target_locales', []))],
        ]);

        $locale = $validated['locale'];

        $request->validate([
            "{$field}_translations.{$locale}" => ['required', 'string', 'max:5000'],
        ]);

        $model = $this->makeModelInstance($group);

        if (! $model) {
            return $this->backWithError($request, $field, 'This group is not supported.');
        }

        if (! in_array($field, $model->getTranslatableAttributes(), true)) {
            return $this->backWithError($request, $field, 'This field is not translatable.');
        }

        $text = trim((string) $request->input($field, ''));

        if ($text === '') {
            return $this->backWithError($request, $field, 'Enter the source text first.');
        }

        $translation = trim((string) $request->input("{$field}_translations.{$locale}", ''));
        $model->addTranslationToJson($text, $locale, $translation);

        return $this->redirectWithTranslations($request, $model, $field, 'Translation saved.');
    }

    public function destroy(Request $request, string $group, string $field): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', Rule::in(config('multira.target_locales', []))],
        ]);

        $locale = $validated['locale'];
        $model = $this->makeModelInstance($group);

        if (! $model) {
            return $this->backWithError($request, $field, 'This group is not supported.');
        }

        if (! in_array($field, $model->getTranslatableAttributes(), true)) {
            return $this->backWithError($request, $field, 'This field is not translatable.');
        }

        $text = trim((string) $request->input($field, ''));

        if ($text === '') {
            return $this->backWithError($request, $field, 'Enter the source text first.');
        }

        $model->removeTranslationFromJson($text, $locale);

        $input = $request->except('_method');
        $translationsInput = $request->input("{$field}_translations", []);

        if (is_array($translationsInput)) {
            unset($translationsInput[$locale]);
            $input["{$field}_translations"] = $translationsInput;
        }

        $redirect = redirect()->back()->withInput($input);

        foreach ($this->resolveAllFieldTranslations($request, $model, $field, $locale) as $key => $value) {
            $redirect->with($key, $value);
        }

        return $redirect
            ->with('translation_message', 'Translation deleted.')
            ->with('translated_field', $field);
    }

    protected function redirectWithTranslations(
        Request $request,
        object $model,
        string $field,
        string $message
    ): RedirectResponse {
        $redirect = redirect()->back()->withInput();

        foreach ($this->resolveAllFieldTranslations($request, $model) as $key => $value) {
            $redirect->with($key, $value);
        }

        return $redirect
            ->with('translation_message', $message)
            ->with('translated_field', $field);
    }

    protected function backWithError(Request $request, string $field, string $message): RedirectResponse
    {
        return redirect()->back()
            ->withInput()
            ->with('translation_error', $message)
            ->with('translated_field', $field);
    }

    /**
     * @return array<string, array<string, string|null>>
     */
    protected function resolveAllFieldTranslations(
        Request $request,
        object $model,
        ?string $excludeField = null,
        ?string $excludeLocale = null,
    ): array {
        $flash = [];

        foreach ($model->getTranslatableAttributes() as $attr) {
            $text = trim((string) $request->input($attr, ''));

            if ($text === '') {
                continue;
            }

            $resolved = $model->translationsForText($text);
            $submitted = $request->input("{$attr}_translations", []);

            if (is_array($submitted)) {
                foreach ($submitted as $locale => $value) {
                    if ($attr === $excludeField && $locale === $excludeLocale) {
                        continue;
                    }

                    $trimmed = trim((string) $value);

                    if ($trimmed !== '') {
                        $resolved[$locale] = $trimmed;
                    }
                }
            }

            $flash["{$attr}_translations"] = $resolved;
        }

        return $flash;
    }

    protected function makeModelInstance(string $group): ?object
    {
        $class = config("multira.groups.{$group}");

        if (! is_string($class) || ! class_exists($class)) {
            return null;
        }

        if (! in_array(Translatable::class, class_uses_recursive($class), true)) {
            return null;
        }

        return new $class;
    }
}
