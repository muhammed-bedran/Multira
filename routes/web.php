<?php

use Illuminate\Support\Facades\Route;
use Melbedran\Multira\Http\Controllers\LocaleController;
use Melbedran\Multira\Http\Controllers\TranslationController;

Route::get('switch/{locale}', [LocaleController::class, 'switch'])
    ->name('multira.locale.switch');

Route::match(['post', 'put'], 'translations/{group}/{field}', [TranslationController::class, 'update'])
    ->name('multira.translations.update')
    ->where(['group' => '[A-Za-z0-9_-]+', 'field' => '[A-Za-z0-9_-]+']);

Route::match(['post', 'put', 'delete'], 'translations/{group}/{field}/delete', [TranslationController::class, 'destroy'])
    ->name('multira.translations.destroy')
    ->where(['group' => '[A-Za-z0-9_-]+', 'field' => '[A-Za-z0-9_-]+']);
