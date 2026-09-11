<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CreditNoteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FloorPriceController;
use App\Http\Controllers\GarageController;
use App\Http\Controllers\GodownReceiptController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\TargetController;
use App\Http\Controllers\VendorBillController;
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
    Route::post('/bills/{bill}/void', [BillController::class, 'void'])->name('bills.void');

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
    Route::post('/stock/{product}/adjust-out', [StockController::class, 'adjustOut'])->name('stock.adjust-out');

    Route::get('/godown', [GodownReceiptController::class, 'index'])->name('godown.index');
    Route::get('/godown/{purchase}', [GodownReceiptController::class, 'show'])->name('godown.show');
    Route::post('/godown/{purchase}', [GodownReceiptController::class, 'confirm'])->name('godown.confirm');

    Route::get('/vendor-bills', [VendorBillController::class, 'index'])->name('vendor-bills.index');
    Route::get('/vendor-bills/{purchase}', [VendorBillController::class, 'show'])->name('vendor-bills.show');
    Route::post('/vendor-bills/{purchase}/pay', [VendorBillController::class, 'markPaid'])->name('vendor-bills.paid');
    Route::post('/vendor-bills/{purchase}/unpay', [VendorBillController::class, 'markUnpaid'])->name('vendor-bills.unpaid');

    Route::resource('garages', GarageController::class)->except(['destroy']);

    Route::get('/collections', [CollectionController::class, 'index'])->name('collections.index');
    Route::post('/collections', [CollectionController::class, 'store'])->name('collections.store');

    Route::resource('branches', BranchController::class)->except(['show', 'destroy']);
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::get('/staff/{staff}/edit', [StaffController::class, 'edit'])->name('staff.edit');
    Route::put('/staff/{staff}', [StaffController::class, 'update'])->name('staff.update');

    Route::get('/pricing', [FloorPriceController::class, 'index'])->name('pricing.index');
    Route::post('/pricing', [FloorPriceController::class, 'update'])->name('pricing.update');

    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');

    Route::get('/targets', [TargetController::class, 'index'])->name('targets.index');
    Route::post('/targets', [TargetController::class, 'store'])->name('targets.store');

    Route::get('/attendance', AttendanceController::class)->name('attendance.index');
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::post('/payroll', [PayrollController::class, 'update'])->name('payroll.update');
    Route::get('/reports/sales', SalesReportController::class)->name('reports.sales');

    Route::middleware('role:admin')->group(function () {
        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });
});
