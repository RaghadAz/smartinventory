<?php

namespace App\Filament\Admin\Resources\Debts;

use App\Filament\Admin\Resources\Debts\Pages\CreateDebt;
use App\Filament\Admin\Resources\Debts\Pages\EditDebt;
use App\Filament\Admin\Resources\Debts\Pages\ListDebts;
use App\Models\Debt;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn; // ✅ فقط TextColumn
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DebtResource extends Resource
{
    protected static ?string $model = Debt::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationLabel = 'إدارة الديون';
    protected static ?string $pluralModelLabel = 'الديون والالتزامات';
    protected static ?string $modelLabel = 'دين';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('person_name')
                    ->label('اسم الشخص (زبون/مورد)')
                    ->required(),

                Select::make('type')
                    ->label('الفئة')
                    ->options([
                        'customer' => 'زبون',
                        'supplier' => 'مورد',
                    ])
                    ->required(),

                TextInput::make('amount')
                    ->label('المبلغ')
                    ->numeric()
                    ->prefix('SYP')
                    ->required(),

                TextInput::make('reason')
                    ->label('السبب (مثلاً: دفعة عطور)'),

                DatePicker::make('due_date')
                    ->label('موعد السداد المتوقع'),

                Toggle::make('is_paid')
                    ->label('تم التسديد بالكامل')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // ✅ مصلح: getStateUsing بـ TextColumn
                TextColumn::make('person_name')
                    ->label('المورد / الشخص / الزبون')
                    ->getStateUsing(function ($record) {
                        if (!empty($record->person_name)) {
                            return $record->person_name;
                        }

                        if ($record->sale_id && $record->sale && !empty($record->sale->customer_name)) {
                            return $record->sale->customer_name;
                        }

                        if ($record->user) {
                            return $record->user->name;
                        }

                        if (str_contains($record->notes ?? '', 'طلب تلقائي')) {
                            return 'نقص مخزني تلقائي';
                        }

                        return 'زبون نقدي عابر';
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('التفاصيل')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('SYP'),

                // ✅ مصلح: badge() بدل BadgeColumn
                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'supplier' => 'primary',
                        'customer' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'supplier' => 'مورد',
                        'customer' => 'زبون',
                        default => $state,
                    }),

                TextColumn::make('due_date')
                    ->label('تاريخ الاستحقاق')
                    ->date(),

                // ✅ جديد: عرض حالة السداد
                TextColumn::make('is_paid')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn (bool $state): string => $state ? '✅ تم السداد' : '❌ غير مسدد'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('الفئة')
                    ->options([
                        'customer' => 'زبون',
                        'supplier' => 'مورد',
                    ]),
                
                SelectFilter::make('is_paid')
                    ->label('حالة السداد')
                    ->options([
                        true => 'تم السداد',
                        false => 'غير مسدد',
                    ]),
            ])
            ->actions([
                // ✅ جديد: زر تسديد سريع
              //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDebts::route('/'),
            'create' => CreateDebt::route('/create'),
            'edit' => EditDebt::route('/{record}/edit'),
        ];
    }
}