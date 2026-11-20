<?php

declare(strict_types=1);

use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\SeoController;
use Illuminate\Support\Facades\Route;

/*
| Public website. Route names are prefixed with "public.".
*/

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/azienda', [PageController::class, 'azienda'])->name('azienda');
Route::get('/prodotti', [PageController::class, 'prodotti'])->name('prodotti');
Route::get('/servizi', [PageController::class, 'servizi'])->name('servizi');
Route::get('/contatti', [PageController::class, 'contatti'])->name('contatti');
Route::post('/contatti', [ContactController::class, 'store'])->middleware('throttle:contact-form')->name('contatti.store');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/cookie', [PageController::class, 'cookie'])->name('cookie');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
