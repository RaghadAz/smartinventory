<form action="<?php echo e(route('invoices.destroy', $getRecord()->id)); ?>" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
    <?php echo csrf_field(); ?>
    <?php echo method_field('DELETE'); ?>
    <button type="submit" style="color: red; cursor: pointer; border: none; background: none;">
        🗑️ حذف
    </button>
</form><?php /**PATH C:\xampp\htdocs\ipi405\resources\views/invoices/delete-button.blade.php ENDPATH**/ ?>