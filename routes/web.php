<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\Orderbook::class)->name('orderbook');
