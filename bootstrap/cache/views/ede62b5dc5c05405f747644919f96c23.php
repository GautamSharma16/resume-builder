

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Analytics</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Website Traffic</h3>
                <p class="text-gray-600">Traffic analytics will be displayed here</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">User Growth</h3>
                <p class="text-gray-600">User growth charts will be displayed here</p>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resume-builder\resume-builder\resources\views/admin/analytics.blade.php ENDPATH**/ ?>