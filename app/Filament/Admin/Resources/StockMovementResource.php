<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StockMovementResource\Pages;
use App\Models\StockMovement;
use BackedEnum;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use UnitEnum;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationLabel = 'حركة المخزون';
    protected static UnitEnum|string|null $navigationGroup = 'إدارة المخزون';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('product_id')
                ->relationship('product', 'name')
                ->label('المنتج')
                ->required(),

            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->label('المستخدم')
                ->required(),

            Forms\Components\Select::make('change_type')
                ->options([
                    'increase' => 'زيادة',
                    'decrease' => 'نقصان',
                ])
                ->label('نوع الحركة')
                ->required(),

            Forms\Components\TextInput::make('amount')
                ->numeric()
                ->label('الكمية')
                ->required(),

            Forms\Components\DatePicker::make('date')
                ->label('التاريخ')
                ->required(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('product.name')
                ->label('المنتج')
                ->searchable(),

            Tables\Columns\TextColumn::make('user.name')
                ->label('المستخدم'),

            Tables\Columns\BadgeColumn::make('change_type')
                ->label('النوع')
                ->colors([
                    'success' => 'increase',
                    'danger' => 'decrease',
                ])
                ->formatStateUsing(fn($state) => $state === 'increase' ? 'زيادة' : 'نقصان'),

            Tables\Columns\TextColumn::make('amount')
                ->label('الكمية'),

            Tables\Columns\TextColumn::make('date')
                ->label('التاريخ')
                ->date(),

            Tables\Columns\TextColumn::make('created_at')
                ->label('أضيف بتاريخ')
                ->dateTime(),
        ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockMovements::route('/'),
        ];
    }
}
