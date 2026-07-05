<?php

namespace App\Filament\Admin\Resources\Categories;

use App\Filament\Admin\Resources\Categories\Pages\CreateCategory;
use App\Filament\Admin\Resources\Categories\Pages\EditCategory;
use App\Filament\Admin\Resources\Categories\Pages\ListCategories;
use App\Filament\Admin\Resources\Categories\Schemas\CategoryForm;
use App\Filament\Admin\Resources\Categories\Tables\CategoriesTable;
use App\Models\Category;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationLabel = 'اقسام';
    public static function form(Schema $schema): Schema
   {
     return $schema
     ->schema([
        TextInput::make('name')
            ->label('اسم القسم')
            ->required()
            ->maxLength(255),

        Textarea::make('description')
            ->label('الوصف')
            ->maxLength(65535)
            ->columnSpanFull(),
    ]);
    }
   public static function table(Table $table): Table
   {
       return $table
       ->columns([
        TextColumn::make('name')
            ->label('اسم القسم')
            ->searchable() // ليظهر مربع بحث فوق الجدول
            ->sortable(),  // لترتيب الأسماء أبجدياً
            
        TextColumn::make('description')
            ->label('الوصف')
            ->limit(50), // لكي لا يظهر الوصف الطويل جداً في الجدول
            
        TextColumn::make('created_at')
            ->label('تاريخ الإنشاء')
            ->dateTime()
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true), // لإخفائه اختيارياً
         TextColumn::make('products_sum_quantity')
            ->label('إجمالي المخزون المتبقي')
            ->sum('products', 'quantity') // هذا السطر يقوم بالجمع التلقائي!
            ->color('info')
            ->weight('bold'),
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
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }

}
