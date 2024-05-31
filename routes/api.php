<?php

use App\Http\Controllers\Api\OfferController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('offer', OfferController::class)->except('index', 'show', 'update', 'destroy')->middleware('auth:sanctum');