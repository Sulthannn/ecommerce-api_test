<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController, ProductController, CheckoutController, PaymentWebhookController, OrderHistoryController};

Route::prefix('auth')->group(function() {
    Route::post('daftar', [AuthController::class, 'register']);
    Route::post('masuk', [AuthController::class, 'login']);
    Route::post('keluar', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('profil', [AuthController::class, 'profile'])->middleware('auth:sanctum');
});

Route::middleware(['api.key'])->group(function() {
    Route::get('produk', [ProductController::class, 'index']);
    Route::get('produk/{product}', [ProductController::class, 'show']);
});

Route::middleware(['api.key','auth:sanctum'])->group(function() {
    Route::post('checkout', [CheckoutController::class, 'checkout']);
    Route::get('riwayat', [OrderHistoryController::class, 'index']);
    Route::get('riwayat/{kode}', [OrderHistoryController::class, 'show']);
});

Route::post('webhook/xendit', [PaymentWebhookController::class, 'xendit']);