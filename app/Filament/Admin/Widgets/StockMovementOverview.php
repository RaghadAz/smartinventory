<?php

namespace App\Filament\Admin\Widgets;

use App\Models\StockMovement;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StockMovementOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('إجمالي الحركات', StockMovement::count())
                ->description('كل حركات المخزون')
                ->color('primary'),

            Stat::make('زيادات المخزون', StockMovement::where('change_type', 'increase')->count())
                ->description('عدد حركات الزيادة')
                ->color('success'),

            Stat::make('نقصان المخزون', StockMovement::where('change_type', 'decrease')->count())
                ->description('عدد حركات النقصان')
                ->color('danger'),
        ];
    }
}
