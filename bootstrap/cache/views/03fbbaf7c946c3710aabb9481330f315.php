<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-950">Admin Dashboard</h1>
    <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-5">
        <?php $__currentLoopData = [
            'Total Users' => $totalUsers ?? 0,
            'Total Resumes' => $totalResumes ?? 0,
            'Total Purchases' => $totalPurchases ?? 0,
            'Total Visitors' => $totalVisitors ?? 0,
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
                <p class="text-sm font-semibold text-gray-600"><?php echo e($label); ?></p>
                <p class="mt-2 text-3xl font-bold text-gray-950"><?php echo e($value); ?></p>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-5">
        <a href="<?php echo e(route('admin.templates.index')); ?>" class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm font-semibold">Manage Templates</a>
        <a href="<?php echo e(route('admin.articles.index')); ?>" class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm font-semibold">Manage Articles</a>
        <a href="<?php echo e(route('admin.payments')); ?>" class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm font-semibold">Pricing Control</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resume-builder\resume-builder\resources\views\admin\dashboard.blade.php ENDPATH**/ ?>