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
            config('multira.target_locales', ['en', 'tr']),
            config('multira.supported', [])
        ));

        $source = config('multira.source_locale', 'ar');

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
        $this->line('Wrap your public routes:');
        $this->newLine();
        $this->line('    use Melbedran\\Multira\\Facades\\Multira;');
        $this->newLine();
        $this->line('    Multira::routes(function () {');
        $this->line("        Route::get('/', [HomeController::class, 'index'])->name('home');");
        $this->line('    });');
        $this->newLine();
        $this->line('Add Translatable to a model:');
        $this->newLine();
        $this->line('    use Melbedran\\Multira\\Traits\\Translatable;');
        $this->newLine();
        $this->line('    class Category extends Model');
        $this->line('    {');
        $this->line('        use Translatable;');
        $this->newLine();
        $this->line('        public function getTranslatableAttributes(): array');
        $this->line("        {");
        $this->line("            return ['name', 'description'];");
        $this->line('        }');
        $this->line('    }');
        $this->newLine();

        return self::SUCCESS;
    }
}
