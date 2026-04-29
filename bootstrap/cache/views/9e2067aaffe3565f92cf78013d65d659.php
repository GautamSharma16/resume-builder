<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-950">Preview</h1>
        <a href="<?php echo e(route('resume.edit', $resume)); ?>" class="text-sm font-semibold text-teal-700">Edit</a>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-8 shadow-sm">
        <?php echo $__env->make('resume.partials.preview', ['resume' => $resume->data], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resume-builder\resume-builder\resources\views\resume\preview.blade.php ENDPATH**/ ?>