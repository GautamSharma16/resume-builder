<?php $__env->startSection('content'); ?>
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-950 mb-6">Edit Template</h1>
    <form method="POST" action="<?php echo e(route('admin.templates.update', $template)); ?>" class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm space-y-4">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PATCH'); ?>
        <?php echo $__env->make('admin.templates.form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resume-builder\resume-builder\resources\views\admin\templates\edit.blade.php ENDPATH**/ ?>