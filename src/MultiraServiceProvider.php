<?php

namespace Melbedran\Multira;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Melbedran\Multira\Console\InstallCommand;
use Melbedran\Multira\Middleware\LocaleCookieRedirect;
use Melbedran\Multira\Middleware\LocaleSessionRedirect;
use Melbedran\Multira\Middleware\LocaleViewPath;
use Melbedran\Multira\Middleware\MultiraRedirectFilter;
use Melbedran\Multira\Middleware\MultiraRoutes;
use Melbedran\Multira\Support\JsonTranslationStore;
use Melbedran\Multira\View\Components\Switcher;
use Melbedran\Multira\View\Components\Translations;

class MultiraServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/multira.php', 'multira');

        $this->app->singleton('multira', function ($app) {
            return new Multira($app);
        });

        $this->app->alias('multira', Multira::class);

        $this->app->singleton(JsonTranslationStore::class);
    }

    public function boot(): void
    {
        $this->registerMiddleware();
        $this->registerViews();
        $this->registerRoutes();
        $this->registerPublishing();
        $this->registerCommands();
    }

    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];

        $aliases = [
            'localize' => MultiraRoutes::class,
            'localizationRedirect' => MultiraRedirectFilter::class,
            'localeSessionRedirect' => LocaleSessionRedirect::class,
            'localeCookieRedirect' => LocaleCookieRedirect::class,
            'localeViewPath' => LocaleViewPath::class,
            'multira' => MultiraRoutes::class,
            'multira.redirect' => MultiraRedirectFilter::class,
            'multira.session' => LocaleSessionRedirect::class,
            'multira.cookie' => LocaleCookieRedirect::class,
            'multira.view' => LocaleViewPath::class,
        ];

        foreach ($aliases as $alias => $class) {
            $router->aliasMiddleware($alias, $class);
        }
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'multira');

        Blade::component('multira-switcher', Switcher::class);
        Blade::component('multira-translations', Translations::class);
    }

    protected function registerRoutes(): void
    {
        if (! config('multira.routes.enabled', true)) {
            return;
        }

        Route::middleware(config('multira.routes.middleware', ['web']))
            ->prefix(config('multira.routes.prefix', 'multira'))
            ->group(__DIR__.'/../routes/web.php');
    }

    protected function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/multira.php' => config_path('multira.php'),
        ], 'multira-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/multira'),
        ], 'multira-views');
    }

    protected function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallCommand::class,
        ]);
    }
}
