<?php

namespace App\Filament\Admin\Resources\Sales;

use App\Filament\Admin\Resources\Sales\Pages\CreateSale;
use App\Filament\Admin\Resources\Sales\Pages\EditSale;
use App\Filament\Admin\Resources\Sales\Pages\ListSales;
use App\Models\Product;
use App\Models\Sale;
use BackedEnum;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Log;


    
    class SaleResource extends Resource
    {
        
        protected static ?string $model = Sale::class;
        protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
        protected static ?string $navigationLabel = 'المبيعات';
    
        // 🔹 صلاحيات - نتأكد من الـ role
       
    public static function canViewAny(): bool
    {
        return (string) auth()->user()?->getAttribute('role') === 'admin';
    }
    

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            
            Section::make('📋 بيانات الفاتورة')->schema([
                Grid::make(3)->schema([
                    Select::make('payment_type')
                    ->label('💳 طريقة الدفع')
                    ->options([
                        'cash' => '💵 كاش',
                        'debt' => '📝 دين',
                    ])
                    ->default('cash')
                    ->live()  // ← مهم!
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $total = floatval($get('total_price') ?? 0);
                        
                        if ($state === 'cash') {
                            $set('paid_amount', $total);
                            $set('paid_amount_display', $total);
                            $set('remaining_price', 0);
                            $set('customer_name', null);
                        } else {
                            $set('paid_amount', 0);
                            $set('paid_amount_display', 0);
                            $set('remaining_price', $total);
                        }
                    }),

                    TextInput::make('customer_name')
                        ->label('👤 اسم الزبون')
                        ->required(fn ($get) => $get('payment_type') === 'debt')
                        ->hidden(fn ($get) => $get('payment_type') === 'cash')
                        ->live(),

                    TextInput::make('paid_amount')
                        ->label('💰 المبلغ المدفوع')
                        ->numeric()
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, $get, $set) {
                            $total = floatval($get('total_price') ?? 0);
                            $paid = floatval($state);
                            $set('remaining_price', $total - $paid);
                        })
                        ->suffix('SYP')
                        ->disabled(fn ($get) => $get('payment_type') === 'cash')
                        ->dehydrated(true),

                ]),
            ]),
            Repeater::make('items')
            ->relationship('saleItems')
            ->label('🛒 الأصناف')
            ->live()  // ← مهم!
            ->collapsible()
            ->schema([
                Grid::make(12)->schema([
                    
                    Select::make('product_id')
                        ->label('📦 المنتج')
                        ->columnSpan(4)
                        ->options(
                            Product::query()
                                ->where('quantity', '>', 0)
                                ->get()
                                ->mapWithKeys(fn ($p) => [
                                    $p->id => "{$p->name} (📊 متوفر: {$p->quantity})"
                                ])
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()  // ← مهم!
                        ->afterStateUpdated(function ($state, $set, $get) {
                            $product = Product::find($state);
                            if ($product) {
                                $set('price', $product->price);
                                $set('cost_price', $product->cost_price);
                                $set('available_stock', $product->quantity);
                                $set('quantity', 1);
                                
                                // 🔧 حدث المجموع فوراً
                                $lineTotal = 1 * $product->price;
                                $set('line_total', round($lineTotal, 2));
                                
                                // 🔧 حدث الإجمالي الكلي
                                self::updateTotals($get, $set);
                            }
                        }),
        
                    TextInput::make('quantity')
                        ->label('🔢 الكمية')
                        ->columnSpan(2)
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->live(onBlur: true)  // ← مهم!
                        ->afterStateUpdated(function ($state, $get, $set) {
                            $available = floatval($get('available_stock') ?? 0);
                            $qty = floatval($state);
                            
                            if ($qty > $available) {
                                Notification::make()
                                    ->title('⚠️ تجاوز المخزون!')
                                    ->body("الكمية المطلوبة ({$qty}) أكبر من المتاح ({$available})")
                                    ->danger()
                                    ->send();
                                
                                $set('quantity', $available);
                                $qty = $available;
                            }
                            
                            $price = floatval($get('price') ?? 0);
                            $lineTotal = $qty * $price;
                            $set('line_total', round($lineTotal, 2));
                            
                            self::updateTotals($get, $set);
                        }),
        
                    TextInput::make('price')
                        ->label('💵 سعر البيع')
                        ->columnSpan(3)
                        ->numeric()
                        ->required()
                        ->live(onBlur: true)  // ← مهم!
                        ->afterStateUpdated(function ($get, $set) {
                            $qty = floatval($get('quantity') ?? 1);
                            $price = floatval($get('price') ?? 0);
                            $lineTotal = $qty * $price;
                            $set('line_total', round($lineTotal, 2));
                            
                            self::updateTotals($get, $set);
                        })
                        ->suffix('SYP'),
        
                    TextInput::make('line_total')
                        ->label('🧮 المجموع')
                        ->columnSpan(3)
                        ->numeric()
                        ->readOnly()
                        ->suffix('SYP')
                        ->live(),  // ← مهم!
        
                    Hidden::make('cost_price')->default(0),
                    Hidden::make('available_stock')->default(0),
                    Hidden::make('line_profit')->default(0),
        
                ]),
            ])
            // 🔧 هذا مهم جداً - لما يتغير أي شي في الـ Repeater
            ->afterStateUpdated(function ($get, $set) {
                self::updateTotals($get, $set);
            }),
            Section::make('📊 ملخص الفاتورة')
                ->icon('heroicon-o-calculator')
                ->schema([
                    Grid::make(3)->schema([
                        
                        TextInput::make('total_price')
                        ->label('💰 الإجمالي')
                        ->readOnly()
                        ->numeric()
                        ->suffix('SYP')
                        ->live()
                        ->default(0)
                        ->dehydrated(true)  // ← مهم!
                        ->extraInputAttributes([
                            'style' => 'font-size: 18px; font-weight: 700; color: #3b82f6;'
                        ]),
                    
                    TextInput::make('paid_amount_display')
                        ->label('💵 المدفوع')
                        ->readOnly()
                        ->numeric()
                        ->suffix('SYP')
                        ->live()
                        ->default(0)
                        ->dehydrated(true),  // ← مهم!
                    
                    TextInput::make('remaining_price')
                        ->label('📋 المتبقي')
                        ->readOnly()
                        ->numeric()
                        ->suffix('SYP')
                        ->live()
                        ->default(0)
                        ->dehydrated(true)  // ← مهم!
                        ->extraInputAttributes([
                            'style' => 'font-size: 18px; font-weight: 700;'
                        ]),
                    
                    TextInput::make('total_profit')
                        ->label('📈 الربح الصافي')
                        ->columnSpan(3)
                        ->readOnly()
                        ->numeric()
                        ->suffix('SYP')
                        ->live()
                        ->default(0)
                        ->dehydrated(true)  // ← مهم!
                        ->extraInputAttributes([
                            'style' => 'font-size: 18px; font-weight: 700; color: #10b981;'
                        ]),

                    ]),
                ]),
                
        ]);
    }
    protected static function updateTotals($get, $set): void
{
    $items = $get('items') ?? [];
    
    // 🔧 نتأكد من items موجود و array
    if (empty($items) || !is_array($items)) {
        $items = [];
    }

    $totalPrice = 0;
    $totalProfit = 0;

    foreach ($items as $index => $item) {
        if (!is_array($item) || empty($item['product_id'])) {
            continue;
        }

        $qty = floatval($item['quantity'] ?? 0);
        $price = floatval($item['price'] ?? 0);
        $costPrice = floatval($item['cost_price'] ?? 0);
        
        $lineTotal = $qty * $price;
        $lineProfit = ($price - $costPrice) * $qty;
        
        $totalPrice += $lineTotal;
        $totalProfit += $lineProfit;
    }

    $paymentType = $get('payment_type');
    
    if ($paymentType === 'cash') {
        $paid = $totalPrice;
        $remaining = 0;
    } else {
        $paidAmount = floatval($get('paid_amount') ?? 0);
        $paid = $paidAmount;
        $remaining = $totalPrice - $paidAmount;
    }

    $set('total_price', round($totalPrice, 2));
    $set('total_profit', round($totalProfit, 2));
    $set('paid_amount', round($paid, 2));
    $set('paid_amount_display', round($paid, 2));
    $set('remaining_price', round($remaining, 2));
    
    // 🔧 Log للتشخيص
    \Log::info('updateTotals', [
        'total_price' => $totalPrice,
        'total_profit' => $totalProfit,
        'items_count' => count($items),
    ]);
}

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        return self::processSaleData($data);
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        return self::processSaleData($data);
    }
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // تحميل العلاقات مسبقاً دفعة واحدة لتسريع عرض الجدول كلياً
        return parent::getEloquentQuery()->with(['saleItems.product']);
    }
    // 🔹 هون تحط الكود
    protected static function processSaleData(array $data): array
    {
        $items = $data['items'] ?? [];
        
        if (empty($items) || !is_array($items)) {
            $data['total_price'] = 0;
            $data['total_profit'] = 0;
            $data['paid_amount'] = 0;
            $data['paid_amount_display'] = 0;
            $data['remaining_price'] = 0;
            return $data;
        }
        
        $totalPrice = 0;
        $totalProfit = 0;
        
        $productIds = collect($items)
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values()
            ->all();
            
        $products = !empty($productIds) 
            ? Product::whereIn('id', $productIds)->get()->keyBy('id')
            : collect();
    
        $processedItems = [];
        foreach ($items as $index => $item) {
            if (!is_array($item) || empty($item['product_id'])) {
                continue;
            }
            
            $qty = floatval($item['quantity'] ?? 1);
            $price = floatval($item['price'] ?? 0);
            
            $lineTotal = $qty * $price;
            $totalPrice += $lineTotal;
            
            $item['total'] = round($lineTotal, 2);
            
            $product = $products[$item['product_id']] ?? null;
            if ($product) {
                $costPrice = floatval($product->cost_price ?? 0);
                $lineProfit = ($price - $costPrice) * $qty;
                $totalProfit += $lineProfit;
                
                $item['cost_price'] = $costPrice;
                $item['line_profit'] = round($lineProfit, 2);
            }
            
            $processedItems[] = $item;
        }
        
        $data['items'] = $processedItems;
    
        $paymentType = $data['payment_type'] ?? 'cash';
        
        if ($paymentType === 'cash') {
            $data['paid_amount'] = round($totalPrice, 2);
            $data['paid_amount_display'] = round($totalPrice, 2);
            $data['remaining_price'] = 0;
            $data['customer_name'] = null;
        } else {
            $paidAmount = floatval($data['paid_amount'] ?? 0);
            $remaining = $totalPrice - $paidAmount;
            
            $data['paid_amount'] = round($paidAmount, 2);
            $data['paid_amount_display'] = round($paidAmount, 2);
            $data['remaining_price'] = round($remaining, 2);
            
            // ❌ شيل إنشاء Debt من هون! رح يصير في afterCreate بس
            // if ($remaining > 0 && !empty($data['customer_name'])) {
            //     \App\Models\Debt::create([...]);
            // }
        }
    
        $data['total_price'] = round($totalPrice, 2);
        $data['total_profit'] = round($totalProfit, 2);
    
        return $data;
    }
       
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                
                TextColumn::make('customer_name')
                    ->label('👤 الزبون')
                    ->formatStateUsing(fn ($state) => $state ?? '💵 كاش'),
                
                    TextColumn::make('products_list')
    ->label('📦 المنتجات')
    ->state(function ($record) {
        $grouped = [];
        
        // بفضل الـ Eager loading، هذا الـ Loop سيعمل بالذاكرة فوراً دون أي استعلام إضافي
        foreach ($record->saleItems as $item) {
            $id = $item->product_id;
            $name = $item->product?->name ?? 'منتج محذوف';
            $qty = (int) $item->quantity;
            
            if (!isset($grouped[$id])) {
                $grouped[$id] = ['name' => $name, 'qty' => 0];
            }
            $grouped[$id]['qty'] += $qty;
        }
        
        $lines = [];
        foreach ($grouped as $product) {
            $lines[] = "• {$product['name']} ({$product['qty']})";
        }
        
        return implode("<br>", $lines);
    })
    ->html()
    ->wrap(),
                
                TextColumn::make('total_price')->label('💰 الإجمالي')->money('SYP')->sortable(),
                TextColumn::make('paid_amount')->label('💵 المدفوع')->money('SYP'),
                TextColumn::make('remaining_price')->label('📋 المتبقي')->money('SYP')->color('danger'),
                TextColumn::make('total_profit')->label('📈 الربح')->money('SYP')->color('success'),
                TextColumn::make('payment_type')->label('💳 الدفع')->badge(),
                TextColumn::make('created_at')->label('📅 التاريخ')->dateTime('Y-m-d H:i'),
                ViewColumn::make('delete')   ->label('إجراءات')   ->view('invoices.delete-button'),
                
                // 🔹 زر الطباعة هون!
                ViewColumn::make('print')
                    ->label('🖨️ طباعة')
                    ->view('invoices.print-button')
                    ->alignCenter(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSales::route('/'),
            'create' => CreateSale::route('/create'),
            'edit' => EditSale::route('/{record}/edit'),
        ];
    }
}