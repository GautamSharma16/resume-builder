<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-950 mb-6">Pricing Control</h1>
    <div class="space-y-4">
        <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <form method="POST" action="<?php echo e(route('admin.plans.update', $plan)); ?>" class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>
                <div><label class="text-xs font-semibold text-gray-500"><?php echo e($plan->name); ?></label><input name="price_paise" type="number" value="<?php echo e($plan->price_paise); ?>" class="mt-1 w-full rounded-md border-gray-300"></div>
                <div><label class="text-xs font-semibold text-gray-500">Resume Limit</label><input name="resume_limit" type="number" value="<?php echo e($plan->resume_limit); ?>" class="mt-1 w-full rounded-md border-gray-300"></div>
                <div><label class="text-xs font-semibold text-gray-500">Cover Letter Limit</label><input name="cover_letter_limit" type="number" value="<?php echo e($plan->cover_letter_limit); ?>" class="mt-1 w-full rounded-md border-gray-300"></div>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="ai_enabled" value="1" <?php if($plan->ai_enabled): echo 'checked'; endif; ?>> AI</label>
                <button class="rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white">Save</button>
            </form>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resume-builder\resume-builder\resources\views/admin/payments.blade.php ENDPATH**/ ?>