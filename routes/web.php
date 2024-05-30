<?php

use App\Livewire\Offer\OfferIndex;
use App\Livewire\Orderbook;
use App\Livewire\Service\ServiceIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth'])->group(function(){
    // orderbook
    Route::get('/order', Orderbook::class)->name('order');

    // service
    Route::name('service.')->prefix('/service')->group(function(){
        Route::get('/', ServiceIndex::class)->name('index');
    });

    // offer 
    Route::name('offer.')->prefix('/offer')->group(function(){
        Route::get('/{service}', OfferIndex::class)->name('index');
    });
});

require __DIR__.'/auth.php';
