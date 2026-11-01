<?php

namespace Melbedran\Multira\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Melbedran\Multira\Traits\Translatable;

class TranslatableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->useLangPath($this->langPath());
        File::deleteDirectory($this->langPath());
        File::ensureDirectoryExists($this->langPath());
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->langPath());

        parent::tearDown();
    }

    public function test_it_returns_the_database_value_for_the_source_locale(): void
    {
        $model = new FakeCategory(['name' => 'ملابس']);

        $this->assertSame('ملابس', $model->getTranslation('name', 'ar'));
    }

    public function test_it_reads_json_translations_and_falls_back_to_source(): void
    {
        $model = new FakeCategory(['name' => 'ملابس']);
        $model->addTranslationToJson('ملابس', 'en', 'Clothes');

        $this->assertSame('Clothes', $model->getTranslation('name', 'en'));
        $this->assertSame('ملابس', $model->getTranslation('name', 'tr'));
        $this->assertTrue($model->hasTranslationFor('name', 'en'));
        $this->assertFalse($model->hasTranslationFor('name', 'tr'));
        $this->assertSame('Clothes', $model->getLocalizedValue('name', 'en'));
        $this->assertNull($model->getLocalizedValue('name', 'tr'));
    }

    public function test_it_reports_translation_status(): void
    {
        $model = new FakeCategory(['name' => 'ملابس', 'description' => 'وصف']);
        $model->addTranslationToJson('ملابس', 'en', 'Clothes');

        $status = $model->getTranslationStatus();

        $this->assertTrue($status['name']['en']);
        $this->assertFalse($status['name']['tr']);
        $this->assertFalse($status['description']['en']);
    }

    protected function langPath(): string
    {
        return sys_get_temp_dir().DIRECTORY_SEPARATOR.'multira-translatable-tests';
    }
}

class FakeCategory extends Model
{
    use Translatable;

    public $timestamps = false;

    protected $guarded = [];

    public function getTranslatableAttributes(): array
    {
        return ['name', 'description'];
    }
}
