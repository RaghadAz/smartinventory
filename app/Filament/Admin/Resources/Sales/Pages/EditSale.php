<?php

namespace App\Filament\Admin\Resources\Sales\Pages;

use App\Filament\Admin\Resources\Sales\SaleResource;
use Filament\Resources\Pages\EditRecord;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    public function updated($state): void
    {
        $this->calculateTotals();
    }

    public function calculateTotals(): void
    {
        $data = $this->form->getState();
        $items = $data['items'] ?? [];

        $totalPrice = collect($items)->sum(fn ($item) =>
            floatval($item['quantity'] ?? 0) * floatval($item['price'] ?? 0)
        );

        $paid = floatval($data['paid_amount'] ?? 0);
        $remaining = $totalPrice - $paid;

        if ($data['payment_type'] === 'cash') {
            $paid = $totalPrice;
            $remaining = 0;
        }

        $totalProfit = collect($items)->sum(function ($item) {
            $productId = $item['product_id'] ?? null;
            $qty = floatval($item['quantity'] ?? 0);
            $price = floatval($item['price'] ?? 0);

            if (!$productId) return 0;

            $product = \App\Models\Product::find($productId);
            if (!$product) return 0;

            $cost = floatval($product->cost_price ?? 0);
            return ($price - $cost) * $qty;
        });

        $this->form->fill(array_merge($data, [
            'total_price' => round($totalPrice, 2),
            'remaining_price' => round($remaining, 2),
            'total_profit' => round($totalProfit, 2),
        ]));
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();
        
        // ✅ حدّث Debt بدل ما تنشئ جديدة
        if ($record->payment_type === 'debt' && $record->remaining_price > 0) {
            $debt = \App\Models\Debt::where('sale_id', $record->id)->first();
            
            if ($debt) {
                // حدّث الموجودة
                $debt->update([
                    'person_name' => $record->customer_name,
                    'amount' => $record->remaining_price,
                    'reason' => 'فاتورة مبيعات رقم: ' . $record->id,
                ]);
            } else {
                // أنشئ جديدة
                \App\Models\Debt::create([
                    'person_name' => $record->customer_name,
                    'type' => 'customer',
                    'amount' => $record->remaining_price,
                    'reason' => 'فاتورة مبيعات رقم: ' . $record->id,
                    'sale_id' => $record->id,
                    'is_paid' => false,
                    'notes' => 'دين تلقائي من فاتورة المبيعات',
                ]);
            }
        } else {
            // إذا صارت cash، احذف Debt
            \App\Models\Debt::where('sale_id', $record->id)->delete();
        }
    }
}