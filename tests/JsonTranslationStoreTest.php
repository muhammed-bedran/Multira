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
        $store->put('Hello world', 'ar', 'مرحبا بالعالم');

        $this->assertSame('مرحبا بالعالم', $store->get('Hello world', 'ar'));
        $this->assertNull($store->get('Missing key', 'ar'));
        $this->assertFileExists($this->langPath().DIRECTORY_SEPARATOR.'ar.json');
    }

    public function test_it_renames_a_translation_key(): void
    {
        $store = app(JsonTranslationStore::class);
        $store->put('Hello world', 'ar', 'مرحبا بالعالم');
        $store->rename('Hello world', 'Hello', ['ar']);

        $this->assertNull($store->get('Hello world', 'ar'));
        $this->assertSame('مرحبا بالعالم', $store->get('Hello', 'ar'));
    }

    public function test_it_deletes_a_translation(): void
    {
        $store = app(JsonTranslationStore::class);
        $store->put('Hello world', 'ar', 'مرحبا بالعالم');
        $store->forget('Hello world', 'ar');

        $this->assertNull($store->get('Hello world', 'ar'));
    }

    public function test_it_cleans_html_from_keys(): void
    {
        $store = app(JsonTranslationStore::class);
        $store->put('<b>Hello world</b>', 'ar', 'مرحبا بالعالم');

        $this->assertSame('مرحبا بالعالم', $store->get('  Hello world  ', 'ar'));
    }

    protected function langPath(): string
    {
        return sys_get_temp_dir().DIRECTORY_SEPARATOR.'multira-lang-tests';
    }
}
