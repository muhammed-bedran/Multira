<?php

namespace Melbedran\Multira\Tests;

use Illuminate\Http\Request;
use Melbedran\Multira\Exceptions\UnsupportedLocaleException;
use Melbedran\Multira\Facades\Multira;

class MultiraTest extends TestCase
{
    public function test_it_detects_locale_from_the_first_url_segment(): void
    {
        $this->setRequestUrl('http://localhost/en/about');

        $prefix = Multira::setLocale();

        $this->assertSame('en', $prefix);
        $this->assertSame('en', Multira::getCurrentLocale());
        $this->assertSame('en', app()->getLocale());
    }

    public function test_it_returns_supported_locales_from_the_catalog(): void
    {
        $locales = Multira::getSupportedLocales();

        $this->assertArrayHasKey('ar', $locales);
        $this->assertArrayHasKey('en', $locales);
        $this->assertArrayHasKey('tr', $locales);
        $this->assertSame('العربية', $locales['ar']['native']);
        $this->assertSame('rtl', $locales['ar']['dir']);
        $this->assertSame('ltr', $locales['en']['dir']);
    }

    public function test_it_builds_a_localized_url(): void
    {
        $this->setRequestUrl('http://localhost/ar/about?page=2');
        Multira::setLocale();

        $this->assertSame(
            'http://localhost/en/about?page=2',
            Multira::getLocalizedURL('en')
        );
    }

    public function test_it_hides_the_default_locale_when_configured(): void
    {
        config()->set('multira.hideDefaultLocaleInURL', true);
        $this->setRequestUrl('http://localhost/about');
        Multira::setLocale();

        $this->assertSame(
            'http://localhost/about',
            Multira::getLocalizedURL('ar')
        );
        $this->assertSame(
            'http://localhost/en/about',
            Multira::getLocalizedURL('en')
        );
        $this->assertTrue(Multira::isHiddenDefault('ar'));
    }

    public function test_it_strips_the_locale_from_a_url(): void
    {
        $this->setRequestUrl('http://localhost/tr/contact');
        Multira::setLocale();

        $this->assertSame(
            'http://localhost/contact',
            Multira::getNonLocalizedURL()
        );
    }

    public function test_it_rejects_unsupported_locales(): void
    {
        $this->expectException(UnsupportedLocaleException::class);

        Multira::getLocalizedURL('xx', 'http://localhost/about');
    }

    public function test_it_reports_rtl_for_arabic(): void
    {
        $this->setRequestUrl('http://localhost/ar');
        Multira::setLocale();

        $this->assertSame('rtl', Multira::getCurrentLocaleDirection());
        $this->assertSame('Arabic', Multira::getCurrentLocaleName());
        $this->assertSame('العربية', Multira::getCurrentLocaleNative());
    }

    public function test_check_locale_helper_matches_mcamara(): void
    {
        $this->assertTrue(Multira::checkLocaleInSupportedLocales('tr'));
        $this->assertFalse(Multira::checkLocaleInSupportedLocales('xx'));
        $this->assertFalse(Multira::checkLocaleInSupportedLocales(null));
    }

    protected function setRequestUrl(string $url): void
    {
        $request = Request::create($url);
        $this->app->instance('request', $request);
        $this->app['url']->forceRootUrl('http://localhost');
    }
}
