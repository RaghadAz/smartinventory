<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Sale;
use App\Models\Expense;
use App\Models\Debt;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $today = today();
        
        // 🔧 تشخيص
        $todaySales = (float) Sale::whereDate('created_at', $today)
            ->sum('total_price');
        
        \Log::info('StatsOverview', [
            'today' => $today->format('Y-m-d'),
            'sales' => $todaySales,
        ]);
        
        // إذا لسّات 0، جرب whereBetween
        if ($todaySales == 0) {
            $todaySales = (float) Sale::whereBetween('created_at', [
                $today->startOfDay(),
                $today->endOfDay()
            ])->sum('total_price');
            
            \Log::info('StatsOverview whereBetween', ['sales' => $todaySales]);
        }

        $todayPaid = (float) Sale::whereDate('created_at', $today)
            ->sum('paid_amount');
        
        $todayExpenses = (float) Expense::whereDate('created_at', $today)
            ->sum('amount');
        
        $totalDebts = (float) Debt::where('is_paid', false)
            ->sum('amount');

        return [
            Stat::make('مبيعات اليوم', number_format($todaySales, 0) . ' س.ل')
                ->description('المقبوضات: ' . number_format($todayPaid, 0) . ' س.ل')
                ->color($todaySales > 0 ? 'success' : 'gray'),
                
            Stat::make('مصاريف اليوم', number_format($todayExpenses, 0) . ' س.ل')
                ->description('الشهر الحالي')
                ->color('danger'),
                
            Stat::make('الديون المستحقة', number_format($totalDebts, 0) . ' س.ل')
                ->description('غير مسددة')
                ->color('warning'),
        ];
    }
}