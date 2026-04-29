<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <section class="bg-white border border-gray-200 rounded-lg p-6 sm:p-8 shadow-sm">
            <p class="text-sm font-semibold uppercase text-teal-700">User Login</p>
            <h1 class="mt-2 text-3xl font-bold text-gray-950">Welcome back</h1>
            <p class="mt-2 text-gray-600">Login to build, improve, and download resumes.</p>

            <?php if(session('status')): ?><p class="mt-4 rounded-md bg-teal-50 p-3 text-sm text-teal-800"><?php echo e(session('status')); ?></p><?php endif; ?>
            <?php if($errors->any()): ?><p class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700"><?php echo e($errors->first()); ?></p><?php endif; ?>

            <a href="<?php echo e(route('auth.google')); ?>" class="mt-6 flex w-full items-center justify-center gap-3 rounded-md border border-gray-300 px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50">
                <span class="text-lg font-bold">G</span> Continue with Google
            </a>

            <form method="POST" action="<?php echo e(route('login.store')); ?>" class="mt-6 space-y-4">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="role_scope" value="user">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Email</label>
                    <input name="email" type="email" value="<?php echo e(old('email')); ?>" required class="w-full rounded-md border-gray-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Password</label>
                    <input name="password" type="password" required class="w-full rounded-md border-gray-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                </div>
                <button class="w-full rounded-md bg-teal-700 px-5 py-3 text-sm font-semibold text-white hover:bg-teal-800">Login</button>
            </form>

            <div class="mt-5 flex flex-wrap justify-between gap-3 text-sm">
                <a href="<?php echo e(route('password.request')); ?>" class="font-semibold text-teal-700">Forgot password?</a>
                <a href="<?php echo e(route('register')); ?>" class="font-semibold text-gray-700">Create account</a>
            </div>
        </section>

        <section class="bg-gray-950 text-white rounded-lg p-6 sm:p-8 shadow-sm">
            <p class="text-sm font-semibold uppercase text-teal-300">Company / Admin</p>
            <h2 class="mt-2 text-3xl font-bold">Secure staff login</h2>
            <p class="mt-2 text-gray-300">For company, admin, SEO, developer, and article writer accounts.</p>

            <form method="POST" action="<?php echo e(route('login.store')); ?>" class="mt-8 space-y-4">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="role_scope" value="staff">
                <div>
                    <label class="block text-sm font-semibold mb-1">Email</label>
                    <input name="email" type="email" required class="w-full rounded-md border-gray-700 bg-gray-900 text-sm text-white focus:border-teal-400 focus:ring-teal-400">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Password</label>
                    <input name="password" type="password" required class="w-full rounded-md border-gray-700 bg-gray-900 text-sm text-white focus:border-teal-400 focus:ring-teal-400">
                </div>
                <button class="w-full rounded-md bg-white px-5 py-3 text-sm font-semibold text-gray-950 hover:bg-gray-100">Staff Login</button>
            </form>
        </section>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Resume_Builder\resources\views/auth/login.blade.php ENDPATH**/ ?>