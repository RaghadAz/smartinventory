<?php

use App\Http\Controllers\SalePdfController;  // ← غيّر هون
use App\Models\Sale;
use Illuminate\Support\Facades\Route;

// المسار الافتراضي للصفحة الرئيسية
// Route::get('/', function () {
//     return view('');
// });
Route::redirect('/', '/admin/login');
// مسار طباعة فاتورة واحدة ← غيّر هون
Route::get('/admin/sales/{record}/print', [SalePdfController::class, 'print'])
    ->name('sales.print');  // ← غيّر هون

// مسار عرض فاتورة واحدة
Route::get('/invoice/{sale}', function (Sale $sale) {
    $sale->load('saleItems.product');
    return view('invoices.single', compact('sale'));
})->name('sales.invoice');

// مسار طباعة مجموعة فواتير
Route::get('/bulk-invoice/{ids}', function ($ids) {
    $saleIds = explode(',', $ids);
    $sales = Sale::whereIn('id', $saleIds)->get();
    return view('invoices.bulk', compact('sales'));
})->name('sales.bulk.invoice');
// routes/web.php


Route::middleware(['auth', 'role:admin'])->group(function () {
    // Routes للأدمن فقط

    Route::get('/admin/settings', function () {
        return view('admin.settings');
    });
});

Route::middleware(['auth', 'role:cashier'])->group(function () {
    // Routes للكاشير
});
// routes/web.php
Route::delete('/invoices/{id}', function ($id) {
    $sale = \App\Models\Sale::findOrFail($id);

    foreach ($sale->saleItems as $item) {
        $product = $item->product;
        if ($product) {
            $product->increment('quantity', $item->quantity);
        }
    }

    $sale->debts()->delete();
    $sale->saleItems()->delete();
    $sale->delete();

    return redirect()->route('filament.admin.resources.sales.index')->with('success', 'تم حذف الفاتورة بنجاح');
})->middleware(['web', 'auth'])->name('invoices.destroy');
