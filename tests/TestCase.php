<?php

namespace Melbedran\Multira\Tests;

use Melbedran\Multira\MultiraServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [MultiraServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Multira' => \Melbedran\Multira\Facades\Multira::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.locale', 'ar');
        $app['config']->set('app.fallback_locale', 'ar');
        $app['config']->set('app.url', 'http://localhost');
        $app['config']->set('multira.supported', ['ar', 'en', 'tr']);
        $app['config']->set('multira.source_locale', 'ar');
        $app['config']->set('multira.target_locales', ['en', 'tr']);
        $app['config']->set('multira.hideDefaultLocaleInURL', false);
        $app['config']->set('multira.useAcceptLanguageHeader', false);
        $app['config']->set('multira.routes.enabled', false);
    }
}
