<?php $__env->startSection('content'); ?>
<div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white border border-gray-200 rounded-lg p-6 sm:p-8 shadow-sm">
        <h1 class="text-3xl font-bold text-gray-950">Reset password</h1>
        <?php if($errors->any()): ?><p class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700"><?php echo e($errors->first()); ?></p><?php endif; ?>
        <form method="POST" action="<?php echo e(route('password.store')); ?>" class="mt-6 space-y-4">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="token" value="<?php echo e($token); ?>">
            <input name="email" type="email" value="<?php echo e(old('email', $email)); ?>" required class="w-full rounded-md border-gray-300 text-sm focus:border-teal-600 focus:ring-teal-600" placeholder="Email">
            <input name="password" type="password" required class="w-full rounded-md border-gray-300 text-sm focus:border-teal-600 focus:ring-teal-600" placeholder="New password">
            <input name="password_confirmation" type="password" required class="w-full rounded-md border-gray-300 text-sm focus:border-teal-600 focus:ring-teal-600" placeholder="Confirm password">
            <button class="w-full rounded-md bg-teal-700 px-5 py-3 text-sm font-semibold text-white hover:bg-teal-800">Reset password</button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resume-builder\resume-builder\resources\views/auth/reset-password.blade.php ENDPATH**/ ?>