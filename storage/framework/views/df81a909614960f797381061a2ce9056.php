<div>
    <?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        
        <div style="background: #1e1e24; padding: 25px; border-radius: 15px; border: 1px solid #2d2d35; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; color: #e2e8f0; font-weight: bold;">
                <span style="font-size: 20px;">📅</span>
                <h3 style="font-size: 16px;">تخصيص فترة التقرير المالي بالتفصيل</h3>
            </div>
            <form wire:submit.prevent="getReportData">
                <?php echo e($this->form); ?>

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
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $monthlyDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr style="background: <?php echo e($index % 2 == 0 ? '#111111' : '#18181b'); ?>; border-bottom: 1px solid #2d2d35;">
                            <td style="padding: 15px; font-size: 14px; color: #94a3b8;"><?php echo e($row->date); ?></td>
                            <td style="padding: 15px; font-size: 14px;"><?php echo e($row->count); ?> عملية بيع</td>
                            <td style="padding: 15px; font-size: 15px; font-weight: bold;"><?php echo e(number_format($row->sales)); ?> ل.س</td>
                            <td style="padding: 15px; font-size: 15px; font-weight: bold; color: #ef4444;"><?php echo e(number_format($row->expenses)); ?> ل.س</td>
                            <td style="padding: 15px; font-size: 15px; font-weight: bold;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row->profit > 0): ?>
                                    <span style="color: #10b981;">+<?php echo e(number_format($row->profit)); ?> ل.س (ربح)</span>
                                <?php elseif($row->profit < 0): ?>
                                    <span style="color: #ef4444;"><?php echo e(number_format($row->profit)); ?> ل.س (عجز)</span>
                                <?php else: ?>
                                    <span>0 ل.س</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="5" style="padding: 40px; text-align: center; color: #64748b;">لا توجد بيانات للفترة المحددة</td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>

    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('notifications');

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3453962243-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
</div><?php /**PATH C:\xampp\htdocs\ipi405\resources\views/filament/admin/pages/monthly-report.blade.php ENDPATH**/ ?>