<div class="mt-2" data-multira-translations="{{ $field }}">
    <p class="small text-muted mb-2">{{ $label }}</p>

    @if (session('translation_message') && session('translated_field') === $field)
        <div class="mt-2 small text-success">{{ session('translation_message') }}</div>
    @endif

    @if (session('translation_error') && session('translated_field') === $field)
        <div class="mt-2 small text-danger">{{ session('translation_error') }}</div>
    @endif

    <div class="row mt-3">
        @foreach (config('multira.target_locales', []) as $locale)
            @php
                $inputName = "{$field}_translations[{$locale}]";
                $inputId = "multira-{$field}-{$locale}";
                $storedValue = $translations[$locale] ?? '';
                $value = old($inputName, $storedValue);
                $hasStoredTranslation = trim((string) $storedValue) !== '';
                $hasValue = trim((string) $value) !== '';
            @endphp
            <div class="col-md-6 mb-3">
                <label for="{{ $inputId }}">{{ strtoupper($locale) }}</label>
                @if ($multiline)
                    <textarea
                        id="{{ $inputId }}"
                        name="{{ $inputName }}"
                        class="form-control multira-translation-input"
                        rows="3"
                        data-locale="{{ $locale }}"
                    >{{ $value }}</textarea>
                @else
                    <input
                        type="text"
                        id="{{ $inputId }}"
                        name="{{ $inputName }}"
                        class="form-control multira-translation-input"
                        value="{{ $value }}"
                        data-locale="{{ $locale }}"
                    >
                @endif
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <button
                        type="submit"
                        formmethod="post"
                        formaction="{{ route('multira.translations.update', ['group' => $group, 'field' => $field]) }}"
                        name="locale"
                        value="{{ $locale }}"
                        class="btn btn-outline-secondary btn-sm multira-save-btn {{ $hasValue ? '' : 'd-none' }}"
                        data-locale="{{ $locale }}"
                    >
                        Save {{ strtoupper($locale) }}
                    </button>
                    @if ($hasStoredTranslation)
                        <button
                            type="submit"
                            formmethod="post"
                            formaction="{{ route('multira.translations.destroy', ['group' => $group, 'field' => $field]) }}"
                            name="locale"
                            value="{{ $locale }}"
                            class="btn btn-outline-danger btn-sm"
                            onclick="return confirm('Delete {{ strtoupper($locale) }} translation?')"
                        >
                            Delete {{ strtoupper($locale) }}
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.querySelectorAll('.multira-translation-input').forEach(function (input) {
                const locale = input.dataset.locale;
                const saveBtn = input.closest('[class*="col-"]')?.querySelector('.multira-save-btn[data-locale="' + locale + '"]');

                if (!saveBtn) {
                    return;
                }

                const toggleSaveButton = function () {
                    saveBtn.classList.toggle('d-none', input.value.trim() === '');
                };

                input.addEventListener('input', toggleSaveButton);
                toggleSaveButton();
            });
        </script>
    @endpush
@endonce
