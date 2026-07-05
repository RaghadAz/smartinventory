<div>
    <x-filament-panels::page>
        
        <div style="background: #1e1e24; padding: 25px; border-radius: 15px; border: 1px solid #2d2d35; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; color: #e2e8f0; font-weight: bold;">
                <span style="font-size: 20px;">📅</span>
                <h3 style="font-size: 16px;">تخصيص فترة التقرير المالي بالتفصيل</h3>
            </div>
            <form wire:submit.prevent="getReportData">
                {{ $this->form }}
            </form>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-bottom: 15px;">
            <button 
                wire:click="exportToExcel" 
                wire:loading.attr="disabled"
                style="background: #10b981; color: white; padding: 10px 20px; border-radius: 8px; font-weight: bold; font-size: 14px; border: none; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.3s;"
                onmouseover="this.style.background='#059669'" 
                onmouseout="this.style.background='#10b981'"
            >
                <svg wire:loading.remove style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span wire:loading.remove>Excel تصدير كشف الحركات المالي لملف</span>
                <span wire:loading>جاري التجهيز...</span>
            </button>
        </div>

        <div style="background: #111111; border-radius: 15px; border: 1px solid #2d2d35; overflow: hidden;">
            <div style="padding: 20px; border-bottom: 1px solid #2d2d35; color: #94a3b8; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                <span>📈</span>
                <span>كشف الأرباح والخسائر يوماً بيوم خلال الشهر</span>
            </div>

            <table style="width: 100%; border-collapse: collapse; direction: rtl; text-align: right;">
                <thead>
                    <tr style="background: #1e1e24; color: #ffffff; border-bottom: 2px solid #2d2d35;">
                        <th style="padding: 15px; font-size: 15px; font-weight: bold;">التاريخ</th>
                        <th style="padding: 15px; font-size: 15px; font-weight: bold;">عدد العمليات</th>
                        <th style="padding: 15px; font-size: 15px; font-weight: bold;">إجمالي المبيعات</th>
                        <th style="padding: 15px; font-size: 15px; font-weight: bold;">المصاريف الكلية</th>
                        <th style="padding: 15px; font-size: 15px; font-weight: bold;">صافي الأرباح / الخسائر</th>
                    </tr>
                </thead>
                <tbody style="color: #e2e8f0;">
                    @forelse($monthlyDetails as $index => $row)
                        <tr style="background: {{ $index % 2 == 0 ? '#111111' : '#18181b' }}; border-bottom: 1px solid #2d2d35;">
                            <td style="padding: 15px; font-size: 14px; color: #94a3b8;">{{ $row->date }}</td>
                            <td style="padding: 15px; font-size: 14px;">{{ $row->count }} عملية بيع</td>
                            <td style="padding: 15px; font-size: 15px; font-weight: bold;">{{ number_format($row->sales) }} ل.س</td>
                            <td style="padding: 15px; font-size: 15px; font-weight: bold; color: #ef4444;">{{ number_format($row->expenses) }} ل.س</td>
                            <td style="padding: 15px; font-size: 15px; font-weight: bold;">
                                @if($row->profit > 0)
                                    <span style="color: #10b981;">+{{ number_format($row->profit) }} ل.س (ربح)</span>
                                @elseif($row->profit < 0)
                                    <span style="color: #ef4444;">{{ number_format($row->profit) }} ل.س (عجز)</span>
                                @else
                                    <span>0 ل.س</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 40px; text-align: center; color: #64748b;">لا توجد بيانات للفترة المحددة</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </x-filament-panels::page>

    @livewire('notifications')
</div>