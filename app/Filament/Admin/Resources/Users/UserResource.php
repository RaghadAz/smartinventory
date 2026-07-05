<?php

namespace App\Filament\Admin\Resources\Users;

use App\Filament\Admin\Resources\Users\Pages\CreateUser;
use App\Filament\Admin\Resources\Users\Pages\EditUser;
use App\Filament\Admin\Resources\Users\Pages\ListUsers;
use App\Filament\Admin\Resources\Users\Schemas\UserForm;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'المستخدمين';
    protected static ?string $recordTitleAttribute = 'User';

    public static function form(Schema $schema): Schema
    {
        return $schema
        ->schema([
            // قسم البيانات الأساسية
            Section::make('معلومات الموظف')
                ->schema([
                    TextInput::make('name')
                        ->label('الاسم الكامل')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('البريد الإلكتروني')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true),

                    // اختيار الرتبة (آدمن أو كاشير)
                    Select::make('role')
                        ->label('رتبة المستخدم')
                        ->options([
                            'admin' => 'مدير (Admin)',
                            'cashier' => 'كاشير (Cashier)',
                        ])
                        ->required()
                        ->native(false),
                ])->columns(2),

            // قسم كلمة المرور
            Section::make('الأمان')
                ->schema([
                    TextInput::make('password')
                        ->label('كلمة المرور')
                        ->password()
                        ->required(fn (string $context): bool => $context === 'create') // مطلوبة فقط عند الإنشاء
                        ->dehydrated(fn ($state) => filled($state)) // لا تحفظ إذا كانت فارغة عند التعديل
                        ->maxLength(255),
                ]),
        ]);
    }
    public static function canViewAny(): bool
{
    return auth()->user()?->role === 'admin';
}

    public static function table(Table $table): Table
    {
        return$table
        ->columns([
            // عرض اسم المستخدم
            TextColumn::make('name')
                ->label('الاسم')
                ->searchable(),

            // عرض البريد الإلكتروني
           TextColumn::make('email')
                ->label('البريد الإلكتروني')
                ->searchable(),

            // عرض الرتبة (admin أو cashier)
            TextColumn::make('role')
                ->label('الرتبة')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'admin' => 'success',
                    'cashier' => 'warning',
                    default => 'gray',
                }),
                
            // تاريخ الإنشاء
            TextColumn::make('created_at')
                ->label('تاريخ التسجيل')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            // يمكنك إضافة فلاتر هنا لاحقاً
        ])
        ->actions([
           
        ])
        ->bulkActions([
           
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
