<?php

namespace App\Observers;

use App\Models\Debt;
use App\Models\Product;
use App\Models\Sale;

class SaleObserver
{
    public function created(Sale $sale): void
    {
        // 🔧 تحديث المخزون
        foreach ($sale->saleItems as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->quantity -= $item->quantity;
                $product->save();
            }
        }

        // إذا دين
        if ($sale->payment_type === 'debt' && $sale->remaining_price > 0) {
            Debt::create([
                'sale_id' => $sale->id,
                'person_name' => $sale->customer_name,
                'type' => 'customer',
                'amount' => $sale->remaining_price,
                'reason' => 'فاتورة مبيعات #' . $sale->id,
                'is_paid' => false,
                'notes' => 'دين تلقائي من فاتورة المبيعات',
            ]);
        }
    }

    public function deleted(Sale $sale): void
    {
        // إرجاع المخزون
        foreach ($sale->saleItems as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->quantity += $item->quantity;
                $product->save();
            }
        }

        $sale->debts()->delete();
    }
}