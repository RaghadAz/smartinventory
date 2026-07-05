<?php

namespace App\Filament\Admin\Pages;

use App\Models\Product;
use App\Models\Sale;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema; 
use Illuminate\Support\Facades\DB;

class MonthlyReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected string $view = 'filament.admin.pages.monthly-report';
    protected static ?string $navigationLabel = 'التقرير المالي الشهري';
    protected ?string $heading = 'كشف الأرباح والخسائر بالتفصيل';

    public $month;
    public $year;
    public $monthlyDetails = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public function mount(): void
    {
        $this->month = date('m');
        $this->year = date('Y');
        
        $this->form->fill([
            'month' => $this->month,
            'year' => $this->year,
        ]);

        $this->getReportData();
        $this->checkSystemAlerts();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('month')
                    ->label('اختر شهر التقرير')
                    ->options([
                        '01' => 'كانون الثاني (1)', '02' => 'شباط (2)', '03' => 'آذار (3)',
                        '04' => 'نيسان (4)', '05' => 'أيار (5)', '06' => 'حزيران (6)',
                        '07' => 'تموز (7)', '08' => 'آب (8)', '09' => 'أيلول (9)',
                        '10' => 'تشرين الأول (10)', '11' => 'تشرين الثاني (11)', '12' => 'كانون الأول (12)',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn () => $this->getReportData()),

                Select::make('year')
                    ->label('اختر السنة')
                    ->options(array_combine(range(date('Y') - 5, date('Y') + 5), range(date('Y') - 5, date('Y') + 5)))
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn () => $this->getReportData()),
            ])->columns(2);
    }

    protected function checkSystemAlerts(): void
    {
        $lowStockCount = Product::where('quantity', '<', 5)->count();
        
        if ($lowStockCount > 0) {
            Notification::make()
                ->title('تنبيه المخزون الحرج! ⚠️')
                ->body("يوجد لديكِ {$lowStockCount} منتجات شارف مخزونها على النفاد في المستودع. يرجى مراجعة النواقص فوراً.")
                ->warning()
                ->duration(10000)
                ->send();
        }
    }

    public function getReportData()
    {
        if (!$this->month || !$this->year) {
            return;
        }

        // 1. جلب المبيعات والأرباح اليومية مجمعة حسب التاريخ
        $salesData = DB::table('sales')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(id) as count'),
                DB::raw('SUM(total_price) as sales'),
                DB::raw('SUM(total_profit) as profit_from_sales') // ✅ جديد: جمع الربح المحفوظ
            )
            ->whereMonth('created_at', $this->month)
            ->whereYear('created_at', $this->year)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get()
            ->keyBy('date');

        // 2. جلب المصاريف اليومية مجمعة حسب التاريخ
        $expensesData = DB::table('expenses')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as expenses')
            )
            ->whereMonth('created_at', $this->month)
            ->whereYear('created_at', $this->year)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get()
            ->keyBy('date');

        // 3. دمج المبيعات والمصاريف في مصفوفة موحدة مرتبة تنازلياً حسب التاريخ الأحدث
        $allDates = $salesData->keys()->merge($expensesData->keys())->unique()->sortDesc();

        $this->monthlyDetails = [];

        foreach ($allDates as $date) {
            $salesRow = $salesData->get($date);
            $expenseRow = $expensesData->get($date);

            $sales = $salesRow ? (float)$salesRow->sales : 0;
            $expenses = $expenseRow ? (float)$expenseRow->expenses : 0;
            
            // ✅ التعديل 1: استخدام total_profit المحفوظ في sales مباشرة
            $profitFromSales = $salesRow ? (float)$salesRow->profit_from_sales : 0;

            // ✅ التعديل 2: صافي الربح = ربح المبيعات - المصاريف
            $netProfit = $profitFromSales - $expenses;

            // ✅ التعديل 3: شيلنا التحديد القسري للصفر - خلي القيمة الحقيقية تظهر
            // if ($netProfit < 0) {
            //     $netProfit = 0;
            // }

            $this->monthlyDetails[] = (object)[
                'date' => $date,
                'count' => $salesRow ? $salesRow->count : 0,
                'sales' => $sales,
                'expenses' => $expenses,
                'profit' => $netProfit, // ✅ صافي الربح الحقيقي (ممكن يكون سالب = خسارة)
            ];
        }
    }

    public function exportToExcel()
    {
        $data = $this->monthlyDetails;

        $books = [
            ['التاريخ', 'عدد العمليات', 'إجمالي المبيعات', 'المصاريف الكلية', 'صافي الأرباح والخسائر']
        ];

        foreach ($data as $row) {
            $profitText = $row->profit >= 0 
                ? '+' . number_format($row->profit, 2) . ' ل.س (ربح)' 
                : number_format(abs($row->profit), 2) . ' ل.س (خسارة)';

            $books[] = [
                (string)$row->date,
                (int)$row->count . ' عمليات',
                (float)$row->sales,
                (float)$row->expenses,
                $profitText
            ];
        }

        $fileName = "Financial_Report_{$this->month}_{$this->year}.xlsx";
        $filePath = storage_path('app/public/' . $fileName);

        \Shuchkin\SimpleXLSXGen::fromArray($books)->saveAs($filePath);

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }
}