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
        $model = new FakePost(['title' => 'Hello world']);

        $this->assertSame('Hello world', $model->getTranslation('title', 'ar'));
    }

    public function test_it_reads_json_translations_and_falls_back_to_source(): void
    {
        $model = new FakePost(['title' => 'Hello world']);
        $model->addTranslationToJson('Hello world', 'en', 'Hello');

        $this->assertSame('Hello', $model->getTranslation('title', 'en'));
        $this->assertSame('Hello world', $model->getTranslation('title', 'tr'));
        $this->assertTrue($model->hasTranslationFor('title', 'en'));
        $this->assertFalse($model->hasTranslationFor('title', 'tr'));
        $this->assertSame('Hello', $model->getLocalizedValue('title', 'en'));
        $this->assertNull($model->getLocalizedValue('title', 'tr'));
    }

    public function test_it_reports_translation_status(): void
    {
        $model = new FakePost(['title' => 'Hello world', 'body' => 'Body text']);
        $model->addTranslationToJson('Hello world', 'en', 'Hello');

        $status = $model->getTranslationStatus();

        $this->assertTrue($status['title']['en']);
        $this->assertFalse($status['title']['tr']);
        $this->assertFalse($status['body']['en']);
    }

    protected function langPath(): string
    {
        return sys_get_temp_dir().DIRECTORY_SEPARATOR.'multira-translatable-tests';
    }
}

class FakePost extends Model
{
    use Translatable;

    public $timestamps = false;

    protected $guarded = [];

    public function getTranslatableAttributes(): array
    {
        return ['title', 'body'];
    }
}
