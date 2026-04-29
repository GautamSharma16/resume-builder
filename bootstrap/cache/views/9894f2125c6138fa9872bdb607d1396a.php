<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-950">Templates</h1>
        <a href="<?php echo e(route('admin.templates.create')); ?>" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white">New Template</a>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <tbody class="divide-y divide-gray-100">
                <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="px-5 py-4 font-semibold"><?php echo e($template->name); ?></td>
                        <td class="px-5 py-4"><?php echo e($template->type); ?></td>
                        <td class="px-5 py-4"><?php echo e($template->is_active ? 'Active' : 'Inactive'); ?></td>
                        <td class="px-5 py-4"><a class="text-teal-700 font-semibold" href="<?php echo e(route('admin.templates.edit', $template)); ?>">Edit</a></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resume-builder\resume-builder\resources\views/admin/templates/index.blade.php ENDPATH**/ ?>