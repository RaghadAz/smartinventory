<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\StatsOverview;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('month')
                            ->label('شهر التقرير المالي')
                            ->options([
                                '01' => '(1) كانون الثاني', '02' => '(2) شباط', '03' => '(3) آذار',
                                '04' => '(4) نيسان', '05' => '(5) أيار', '06' => '(6) حزيران',
                                '07' => '(7) تموز', '08' => '(8) آب', '09' => '(9) أيلول',
                                '10' => '(10) تشرين الأول', '11' => '(11) تشرين الثاني', '12' => '(12) كانون الأول',
                            ])
                            ->default(date('m'))
                            ->live(),

                        Select::make('year')
                            ->label('السنة')
                            ->options([
                                '2025' => '2025',
                                '2026' => '2026',
                                '2027' => '2027',
                            ])
                            ->default(date('Y'))
                            ->live(),
                    ])
                    ->columns(2),
            ]);
    }

    // ✅ أضف Widgets
    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
        ];
    }

    public static function canViewAny(): bool
    {
        return (string) auth()->user()?->getAttribute('role') === 'admin';
    }
}