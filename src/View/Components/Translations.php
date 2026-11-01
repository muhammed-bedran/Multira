<?php

namespace Melbedran\Multira\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;
use Melbedran\Multira\Traits\Translatable;

class Translations extends Component
{
    public string $group;

    public string $field;

    public ?string $label;

    public array $translations;

    public bool $multiline;

    public function __construct(
        string $group,
        string $field,
        ?Model $model = null,
        ?string $label = null,
        ?bool $multiline = null,
        array $translations = [],
    ) {
        $this->group = $group;
        $this->field = $field;
        $this->label = $label ?? $this->defaultLabel($field);
        $this->multiline = $multiline ?? str_contains($field, 'description');
        $this->translations = $this->resolveTranslations($model, $field, $translations);
    }

    public function render(): View
    {
        return view('multira::translations');
    }

    protected function resolveTranslations(?Model $model, string $field, array $translations): array
    {
        if ($translations !== []) {
            return $translations;
        }

        if ($model && in_array(Translatable::class, class_uses_recursive($model), true)) {
            $sourceText = trim((string) old($field, ''));

            return $model->resolveTranslationsForField(
                $field,
                $sourceText !== '' ? $sourceText : null
            );
        }

        return session("{$field}_translations", []);
    }

    protected function defaultLabel(string $field): string
    {
        return match ($field) {
            'name' => 'Name translations',
            'description' => 'Description translations',
            default => ucfirst(str_replace('_', ' ', $field)).' translations',
        };
    }
}
