<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-3xl font-bold text-gray-950">Company Dashboard</h1>
    <p class="mt-2 text-gray-600">Welcome, <?php echo e(auth()->user()->name); ?>. Company hiring tools can be added here.</p>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resume-builder\resume-builder\resources\views/company/dashboard.blade.php ENDPATH**/ ?>