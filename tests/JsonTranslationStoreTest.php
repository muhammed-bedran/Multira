<?php

namespace Melbedran\Multira\Tests;

use Illuminate\Support\Facades\File;
use Melbedran\Multira\Support\JsonTranslationStore;

class JsonTranslationStoreTest extends TestCase
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

    public function test_it_stores_and_reads_a_translation(): void
    {
        $store = app(JsonTranslationStore::class);
        $store->put('ملابس', 'en', 'Clothes');

        $this->assertSame('Clothes', $store->get('ملابس', 'en'));
        $this->assertNull($store->get('غير موجود', 'en'));
        $this->assertFileExists($this->langPath().DIRECTORY_SEPARATOR.'en.json');
    }

    public function test_it_renames_a_translation_key(): void
    {
        $store = app(JsonTranslationStore::class);
        $store->put('ملابس', 'en', 'Clothes');
        $store->rename('ملابس', 'ملابس رجالية', ['en']);

        $this->assertNull($store->get('ملابس', 'en'));
        $this->assertSame('Clothes', $store->get('ملابس رجالية', 'en'));
    }

    public function test_it_deletes_a_translation(): void
    {
        $store = app(JsonTranslationStore::class);
        $store->put('ملابس', 'en', 'Clothes');
        $store->forget('ملابس', 'en');

        $this->assertNull($store->get('ملابس', 'en'));
    }

    public function test_it_cleans_html_from_keys(): void
    {
        $store = app(JsonTranslationStore::class);
        $store->put('<b>ملابس</b>', 'en', 'Clothes');

        $this->assertSame('Clothes', $store->get('  ملابس  ', 'en'));
    }

    protected function langPath(): string
    {
        return sys_get_temp_dir().DIRECTORY_SEPARATOR.'multira-lang-tests';
    }
}
