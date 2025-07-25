<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramBotController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);

Route::post('/telegram/webhook', [TelegramBotController::class, 'handle']);
Route::post('/telegram/webhook_two', [TelegramBotController::class, 'handle_two']);
