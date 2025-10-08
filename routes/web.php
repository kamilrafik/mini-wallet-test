<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Broadcast;

Route::get('/', function () {
    return Inertia::render('Home', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/balance', [WalletController::class, 'getBalance'])->name('wallet.balance');
    Route::get('/transactions', [WalletController::class, 'getLatestTransactions'])->name('wallet.latest-transactions');
    Route::post('/transactions', [WalletController::class, 'createTransaction'])->name('wallet.create-transaction');
});

Broadcast::routes(['middleware' => ['auth:sanctum']]);

require __DIR__.'/auth.php';
