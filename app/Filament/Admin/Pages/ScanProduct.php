<?php

namespace App\Filament\Admin\Pages;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Debt;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ScanProduct extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'شاشة المبيعات (الكاشير)';
    protected string $view = 'filament.admin.pages.scan-product';

    public $barcode = '';
    public $searchTerm = ''; // ✅ جديد: للبحث اليدوي
    public $lastScannedProduct = null;
    public $cart = [];
    public $totalPrice = 0;
    public $paymentType = 'cash'; // ✅ جديد: طريقة الدفع

    // ✅ دالة البحث بالباركود (من الكاميرا)
    public function searchProduct($barcode)
    {
        // تنظيف الباركود
        $barcode = trim($barcode);
        
        if (empty($barcode)) {
            Notification::make()->title('الباركود فارغ')->danger()->send();
            return;
        }

        // البحث عن المنتج
        $product = Product::where('barcode', $barcode)
            ->orWhere('sku', $barcode)
            ->first();

        if (!$product) {
            Notification::make()->title('المنتج غير موجود: ' . $barcode)->danger()->send();
            return;
        }

        if ($product->quantity <= 0) {
            Notification::make()->title('المنتج غير متوفر: ' . $product->name)->warning()->send();
            return;
        }

        // إضافة للسلة
        $this->addToCart($product);
        
        // تنظيف الباركود
        $this->barcode = '';
    }

    // ✅ دالة البحث اليدوي (بدون كاميرا)
    public function manualSearch()
    {
        $searchTerm = trim($this->searchTerm);
        
        if (empty($searchTerm)) {
            Notification::make()->title('حقل البحث فارغ')->danger()->send();
            return;
        }

        // البحث بالباركود أو SKU أو الاسم
        $product = Product::where('barcode', $searchTerm)
            ->orWhere('sku', $searchTerm)
            ->orWhere('name', 'like', '%' . $searchTerm . '%')
            ->first();

        if (!$product) {
            Notification::make()->title('المنتج غير موجود: ' . $searchTerm)->danger()->send();
            return;
        }

        if ($product->quantity <= 0) {
            Notification::make()->title('المنتج غير متوفر: ' . $product->name)->warning()->send();
            return;
        }

        // إضافة للسلة
        $this->addToCart($product);
        
        // تنظيف حقل البحث
        $this->searchTerm = '';
    }

    // ✅ دالة إضافة للسلة
    protected function addToCart(Product $product): void
    {
        // تحقق إذا المنتج موجود في السلة
        $existingIndex = null;
        foreach ($this->cart as $index => $item) {
            if ($item['product_id'] == $product->id) {
                $existingIndex = $index;
                break;
            }
        }

        if ($existingIndex !== null) {
            // زيادة الكمية
            $this->cart[$existingIndex]['quantity']++;
            $this->cart[$existingIndex]['total'] = $this->cart[$existingIndex]['quantity'] * $product->price;
        } else {
            // إضافة جديد
            $this->cart[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'cost_price' => $product->cost_price,
                'quantity' => 1,
                'total' => $product->price,
            ];
        }

        // حساب الإجمالي
        $this->totalPrice = collect($this->cart)->sum('total');
        
        $this->lastScannedProduct = $product;

        Notification::make()->title('تمت الإضافة: ' . $product->name)->success()->send();
    }

    // ✅ دالة حذف من السلة
    public function removeFromCart($index)
    {
        if (isset($this->cart[$index])) {
            $this->totalPrice -= $this->cart[$index]['total'];
            unset($this->cart[$index]);
            $this->cart = array_values($this->cart); // إعادة ترتيب المصفوفة
        }
    }

    // ✅ دالة تغيير الكمية
    public function updateQuantity($index, $quantity)
    {
        if (!isset($this->cart[$index]) || $quantity < 1) {
            return;
        }

        $product = Product::find($this->cart[$index]['product_id']);
        if (!$product || $product->quantity < $quantity) {
            Notification::make()->title('الكمية غير متوفرة')->warning()->send();
            return;
        }

        $this->cart[$index]['quantity'] = $quantity;
        $this->cart[$index]['total'] = $quantity * $this->cart[$index]['price'];
        
        // إعادة حساب الإجمالي
        $this->totalPrice = collect($this->cart)->sum('total');
    }

    // ✅ دالة إتمام البيع
    public function completeSale()
    {
        if (empty($this->cart)) {
            Notification::make()->title('السلة فارغة')->warning()->send();
            return;
        }

        $totalPrice = $this->totalPrice;
        $totalProfit = collect($this->cart)->sum(function ($item) {
            return ($item['price'] - $item['cost_price']) * $item['quantity'];
        });

        // إنشاء الفاتورة
        $sale = Sale::create([
            'customer_name' => $this->paymentType === 'debt' ? 'زبون دين' : 'زبون سريع',
            'payment_type' => $this->paymentType,
            'total_price' => $totalPrice,
            'total_profit' => $totalProfit,
            'paid_amount' => $this->paymentType === 'cash' ? $totalPrice : 0,
            'paid_amount_display' => $this->paymentType === 'cash' ? $totalPrice : 0,
            'remaining_price' => $this->paymentType === 'cash' ? 0 : $totalPrice,
            'status' => 'completed',
        ]);

        // إضافة items
        foreach ($this->cart as $item) {
            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'cost_price' => $item['cost_price'],
                'line_profit' => ($item['price'] - $item['cost_price']) * $item['quantity'],
                'total' => $item['total'],
            ]);

            // خصم من المخزون
            Product::find($item['product_id'])->decrement('quantity', $item['quantity']);
        }

        // إذا دين، أنشئ Debt
        if ($this->paymentType === 'debt' && $sale->remaining_price > 0) {
            Debt::create([
                'person_name' => $sale->customer_name,
                'type' => 'customer',
                'amount' => $sale->remaining_price,
                'reason' => 'فاتورة كاشير رقم: ' . $sale->id,
                'sale_id' => $sale->id,
                'is_paid' => false,
                'notes' => 'دين تلقائي من كاشير',
            ]);
        }

        Notification::make()->title('تم البيع بنجاح! #' . $sale->id)->success()->send();
        
        $this->resetScanner();
    }

    // ✅ دالة إعادة تعيين
    public function resetScanner()
    {
        $this->lastScannedProduct = null;
        $this->barcode = '';
        $this->searchTerm = '';
        $this->cart = [];
        $this->totalPrice = 0;
        $this->paymentType = 'cash';
        
        $this->dispatch('refresh-page');
    }
}