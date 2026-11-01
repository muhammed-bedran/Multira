<?php

use Melbedran\Multira\Multira;

if (! function_exists('multira')) {
    function multira(): Multira
    {
        return app('multira');
    }
}

if (! function_exists('localized_url')) {
    function localized_url(?string $locale = null, ?string $url = null): string
    {
        return app('multira')->getLocalizedURL($locale, $url);
    }
}
