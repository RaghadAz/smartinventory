<?php

namespace App\Filament\Admin\Resources\Suppliers;

use App\Filament\Admin\Resources\Suppliers\Pages\CreateSupplier;
use App\Filament\Admin\Resources\Suppliers\Pages\EditSupplier;
use App\Filament\Admin\Resources\Suppliers\Pages\ListSuppliers;
//use App\Filament\Admin\Resources\Suppliers\Schemas\SupplierForm;
//use App\Filament\Admin\Resources\Suppliers\Tables\SuppliersTable;
use App\Models\Supplier;
use BackedEnum;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;


class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'الموردون';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        //return SupplierForm::configure($schema);
        return $schema
        ->schema([
            TextInput::make('name')
            ->label('اسم المورد')
            ->required()
            ->maxLength(255),
            TextInput::make('email')
            ->label('البريد الإلكتروني')
            ->email() // للتحقق من أن الصيغة بريد إلكتروني
            ->required(), // لجعل الحقل إجبارياً
        
        TextInput::make('phone')
            ->label('رقم الهاتف')
            ->tel() // لتغيير نوع لوحة المفاتيح في الموبايل
            ->numeric() // للسماح بالأرقام فقط
            ->required() // لجعل الحقل إجبارياً
            ->minLength(10) // الحد الأدنى لطول الرقم
            ->maxLength(10) // الحد الأقصى لطول الرقم (أو حسب عدد أرقام الموبايل في بلدك)
            ->regex('/^09\d{8}$/'),
        Textarea::make('address')
            ->label('العنوان')
            ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
      //  return SuppliersTable::configure($table);
      return $table->columns([
        TextColumn::make('name')->label('اسم المورد'),
        TextColumn::make('phone')->label('رقم الهاتف'),
    ]);
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
            'index' => ListSuppliers::route('/'),
            'create' => CreateSupplier::route('/create'),
            'edit' => EditSupplier::route('/{record}/edit'),
        ];
    }
}
