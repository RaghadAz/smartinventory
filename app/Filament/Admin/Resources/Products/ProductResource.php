<?php

namespace App\Filament\Admin\Resources\Products;

use App\Filament\Admin\Resources\Products\Pages\CreateProduct;
use App\Filament\Admin\Resources\Products\Pages\EditProduct;
use App\Filament\Admin\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'المنتجات';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // اختيار القسم من القائمة
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required()
                    ->label('القسم'),

                Select::make('supplier_id')
                    ->label('المورد')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('name')
                    ->required()
                    ->label('اسم المنتج'),

                TextInput::make('price')
                    ->label('سعر المبيع')
                    ->numeric()
                    ->prefix('ل.س')
                    ->required(),

                // حقل سعر التكلفة
                TextInput::make('cost_price')
                    ->label('سعر التكلفة (سري)')
                    ->numeric()
                    ->prefix('ل.س')
                    ->required(),

                TextInput::make('sku')
                    ->label('رمز المنتج (SKU)')
                    ->nullable(),

                // ⚡️ إضافة الباركود مع ميزة التركيز التلقائي عند الإنشاء لسرعة إدخال المنتجات الجديدة بالمشروع
                TextInput::make('barcode')
                    ->label('باركود المنتج')
                    ->autofocus() // المؤشر يذهب هنا فوراً لتقرئي الباركود بالمسدس (Scanner)
                    ->placeholder('امسحي باركود المنتج هنا...')
                    ->default(fn () => 'PROD' . rand(100000, 999999)) 
                    ->unique(ignoreRecord: true) 
                    ->required(),
                    

                    TextInput::make('quantity')
                    ->numeric()
                    ->label('الكمية الحالية')
                    ->default(0)
                    ->minValue(0),  // ✅ عشان ما يصير سالب

                FileUpload::make('image')
                    ->label('صورة المنتج')
                    ->image()
                    ->disk('assets_disk')
                    ->directory('')
                    ->visibility('public')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('اسم المنتج')->searchable()->sortable(),

                TextColumn::make('supplier.name')
                    ->label('المورد')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label('القسم')
                    ->sortable(),
                  
                TextColumn::make('quantity')
                    ->label('الكمية المتوفرة')
                    ->numeric()
                    ->sortable()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 0 => 'danger',   
                        $state <= 3 => 'warning',  
                        default => 'success',      
                    })
                    ->icon(fn (int $state): string => match (true) {
                        $state <= 3 => 'heroicon-m-exclamation-triangle',
                        default => '',
                    })
                    ->description(fn (int $state): string => $state <= 3 ? 'مخزون منخفض!' : ''),

                TextColumn::make('price')
                    ->label('السعر')
                    ->money('SYP', locale: 'ar_SY')
                    ->sortable(),

                ImageColumn::make('image')
                    ->label('صورة المنتج')
                    ->state(function ($record): string {
                        return asset('aseet/images/' . $record->image);
                    })
                    ->square(),

                    ViewColumn::make('barcode')
                    ->label('الباركود')
                    ->view('filament.tables.columns.barcode-viewer'),
                    
                
            ])
            // ⚡️ التعديل السحري للمناقشة: جعل صندوق البحث العلوي في الجدول يقرأ ويبحث بالباركود فوراً
            ->searchPlaceholder('ابحثي بالاسم، المورد، أو مرري الباركود حالاً...')
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->label('تصفية حسب القسم'),
                SelectFilter::make('supplier')
                    ->relationship('supplier', 'name')
                    ->label('تصفية حسب المورد'),
            ]);
    }

    public static function canViewAny(): bool
    {
        return (string) auth()->user()?->getAttribute('role') === 'admin';
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}