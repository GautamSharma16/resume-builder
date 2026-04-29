<?php $__env->startSection('content'); ?>
<div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white border border-gray-200 rounded-lg p-6 sm:p-8 shadow-sm">
        <h1 class="text-3xl font-bold text-gray-950">Forgot password</h1>
        <p class="mt-2 text-gray-600">Enter your email and we will send a password reset link.</p>
        <?php if(session('status')): ?><p class="mt-4 rounded-md bg-teal-50 p-3 text-sm text-teal-800"><?php echo e(session('status')); ?></p><?php endif; ?>
        <?php if($errors->any()): ?><p class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700"><?php echo e($errors->first()); ?></p><?php endif; ?>
        <form method="POST" action="<?php echo e(route('password.email')); ?>" class="mt-6 space-y-4">
            <?php echo csrf_field(); ?>
            <input name="email" type="email" required class="w-full rounded-md border-gray-300 text-sm focus:border-teal-600 focus:ring-teal-600" placeholder="you@example.com">
            <button class="w-full rounded-md bg-teal-700 px-5 py-3 text-sm font-semibold text-white hover:bg-teal-800">Send reset link</button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resume-builder\resume-builder\resources\views/auth/forgot-password.blade.php ENDPATH**/ ?>