<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Tables\Actions\Action;

class LowStockProducts extends BaseWidget
{
    // العنوان اللي رح يظهر فوق الجدول باللوحة
    protected static ?string $heading = 'منتجات قاربت على النفاد (أقل من 5 قطع)';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()->where('quantity', '<=', 5)->orderBy('quantity', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المنتج')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('الكمية الحالية')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 0 => 'danger',
                        default => 'warning',
                    }),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('المورد')
                    ->placeholder('لا يوجد مورد'),
            ])
            // تأكيد: لا يوجد أي أكشنات أو عمليات جماعية
            ->actions([])
            ->bulkActions([])
            ->paginated(false); // إخفاء أرقام الصفحات ليضل الجدول قطعة واحدة مرتبة
    }
}