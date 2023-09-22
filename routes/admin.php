<?php

use Illuminate\Support\Facades\Route;
use LambdaDigamma\MMEvents\Http\Controllers\EventActionController;
use LambdaDigamma\MMEvents\Http\Controllers\EventController;
use LambdaDigamma\MMEvents\Http\Controllers\EventPlaceController;

/**
 * ------------------------------
 * Event Place
 * ------------------------------
 */
Route::put('/events/{anyevent}/place', [EventPlaceController::class, 'update'])->name('events.place.update');

/**
 * ------------------------------
 * Event General
 * ------------------------------
 */
Route::put('/events/{anyevent}', [EventController::class, 'update'])->name('events.update');
Route::post('/events', [EventController::class, 'store'])->name('events.store');

Route::post('events/{anyevent}/archive', [EventActionController::class, 'archive'])->name('events.archive');
Route::post('events/{anyevent}/unarchive', [EventActionController::class, 'unarchive'])->name('events.unarchive');
Route::post('events/{anyevent}/publish', [EventActionController::class, 'publish'])->name('events.publish');
Route::post('events/{anyevent}/unpublish', [EventActionController::class, 'unpublish'])->name('events.unpublish');
