<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CreditNoteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GarageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'create'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'store'])->middleware('guest');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/bills/create', [BillController::class, 'create'])->name('bills.create');
    Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
    Route::get('/bills/{bill}', [BillController::class, 'show'])->name('bills.show');
    Route::get('/bills/{bill}/pdf', [BillController::class, 'pdf'])->name('bills.pdf');
    Route::get('/bills/{bill}/print', [BillController::class, 'print'])->name('bills.print');
    Route::post('/bills/{bill}/void', [BillController::class, 'void'])->name('bills.void')->middleware('role:admin');

    Route::get('/bills/{bill}/credit-notes/create', [CreditNoteController::class, 'create'])->name('credit-notes.create');
    Route::post('/bills/{bill}/credit-notes', [CreditNoteController::class, 'store'])->name('credit-notes.store');
    Route::get('/credit-notes/{creditNote}', [CreditNoteController::class, 'show'])->name('credit-notes.show');
    Route::get('/credit-notes/{creditNote}/pdf', [CreditNoteController::class, 'pdf'])->name('credit-notes.pdf');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');

    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/low/export', [StockController::class, 'exportLow'])->name('stock.export');
    Route::post('/stock/{product}/receive', [StockController::class, 'receive'])->name('stock.receive');
    Route::post('/stock/{product}/adjust-out', [StockController::class, 'adjustOut'])->name('stock.adjust-out');

    Route::resource('garages', GarageController::class)->except(['destroy']);

    Route::get('/collections', [CollectionController::class, 'index'])->name('collections.index');
    Route::post('/collections', [CollectionController::class, 'store'])->name('collections.store');

    Route::middleware('role:admin')->group(function () {
        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });
});
