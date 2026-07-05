<?php

namespace App\Filament\Admin\Resources\Expenses;

use App\Filament\Admin\Resources\Expenses\Pages\CreateExpense;
use App\Filament\Admin\Resources\Expenses\Pages\EditExpense;
use App\Filament\Admin\Resources\Expenses\Pages\ListExpenses;
use App\Filament\Admin\Resources\Expenses\Schemas\ExpenseForm;
use App\Filament\Admin\Resources\Expenses\Tables\ExpensesTable;
use App\Models\Expense;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static string|BackedEnum|null $navigationIcon ='heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'النفقات';
    protected static ?string $recordTitleAttribute = 'reason';
    public static function canViewAny(): bool
    {
        return (string) auth()->user()?->getAttribute('role') === 'admin';
    }
    public static function form(Schema $schema): Schema
    {
        return $schema
        ->schema([
            TextInput::make('reason')->required()->label('السبب'),
            TextInput::make('amount')->numeric()->required()->label('المبلغ'),
            DatePicker::make('spent_at')->default(now())->required()->label('تاريخ الصرف'),
            Textarea::make('notes')->label('ملاحظات'),
        ]);
}
    

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('reason')->label('السبب'),
            TextColumn::make('amount')->money('SYP')->label('المبلغ'),
            TextColumn::make('spent_at')->date()->label('التاريخ'),
        ])
        ->actions([]) // مابدي أكشن مثل ما اتفقنا
        ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExpenses::route('/'),
            'create' => CreateExpense::route('/create'),
            'edit' => EditExpense::route('/{record}/edit'),
        ];
    }
}
