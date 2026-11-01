<?php

namespace Melbedran\Multira\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'multira:install';

    protected $description = 'Publish Multira config and create empty JSON translation files';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--provider' => 'Melbedran\\Multira\\MultiraServiceProvider',
            '--tag' => 'multira-config',
        ]);

        $locales = array_unique(array_merge(
            config('multira.target_locales', []),
            config('multira.supported', [])
        ));

        $source = config('multira.source_locale', config('app.locale', 'en'));

        foreach ($locales as $locale) {
            if ($locale === $source) {
                continue;
            }

            $path = lang_path($locale.'.json');

            if (File::exists($path)) {
                $this->line("  exists  {$path}");
                continue;
            }

            File::ensureDirectoryExists(dirname($path));
            File::put($path, '{'.PHP_EOL.'}'.PHP_EOL);
            $this->info("  created {$path}");
        }

        $this->newLine();
        $this->info('Multira is ready.');
        $this->newLine();
        $this->line('Wrap any routes that need a locale prefix:');
        $this->newLine();
        $this->line('    use Melbedran\\Multira\\Facades\\Multira;');
        $this->newLine();
        $this->line('    Multira::routes(function () {');
        $this->line("        Route::get('/', [PageController::class, 'home'])->name('home');");
        $this->line("        Route::get('/about', [PageController::class, 'about'])->name('about');");
        $this->line('    });');
        $this->newLine();
        $this->line('Add Translatable to any Eloquent model:');
        $this->newLine();
        $this->line('    use Melbedran\\Multira\\Traits\\Translatable;');
        $this->newLine();
        $this->line('    class Post extends Model');
        $this->line('    {');
        $this->line('        use Translatable;');
        $this->newLine();
        $this->line('        public function getTranslatableAttributes(): array');
        $this->line("        {");
        $this->line("            return ['title', 'body'];");
        $this->line('        }');
        $this->line('    }');
        $this->newLine();

        return self::SUCCESS;
    }
}
