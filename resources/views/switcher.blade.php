@foreach ($locales as $localeCode => $properties)
    @if ($localeCode === $current)
        <strong>{{ $native ? $properties['native'] : $properties['name'] }}</strong>
    @else
        <a href="{{ \Melbedran\Multira\Facades\Multira::getLocalizedURL($localeCode) }}">
            {{ $native ? $properties['native'] : $properties['name'] }}
        </a>
    @endif
    @if (! $loop->last) {{ $separator }} @endif
@endforeach
