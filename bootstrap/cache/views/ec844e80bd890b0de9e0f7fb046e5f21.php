<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e(config('app.name', 'Resume Builder')); ?> - Admin</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    </head>
    <body class="font-sans antialiased">
        <div class="flex h-screen bg-gray-100">
            <!-- Sidebar -->
            <div class="hidden md:flex md:flex-shrink-0">
                <div class="flex flex-col w-64 bg-white border-r border-gray-200">
                    <!-- Logo -->
                    <div class="flex items-center justify-center h-16 border-b border-gray-200 bg-blue-600">
                        <a href="<?php echo e(route('home')); ?>" class="text-2xl font-bold text-white">
                            Cvbliss
                        </a>
                    </div>

                    <!-- Navigation -->
                    <nav class="flex-1 px-4 py-6 space-y-2">
                        <a href="<?php echo e(route('admin.dashboard')); ?>" class="<?php if(request()->routeIs('admin.dashboard')): ?> bg-blue-50 text-blue-600 <?php else: ?> text-gray-700 hover:bg-gray-50 <?php endif; ?> px-4 py-2 rounded-lg font-medium transition block">
                             Dashboard
                        </a>
                        <a href="<?php echo e(route('admin.analytics')); ?>" class="<?php if(request()->routeIs('admin.analytics')): ?> bg-blue-50 text-blue-600 <?php else: ?> text-gray-700 hover:bg-gray-50 <?php endif; ?> px-4 py-2 rounded-lg font-medium transition block">
                             Analytics
                        </a>
                        <a href="<?php echo e(route('admin.visits')); ?>" class="<?php if(request()->routeIs('admin.visits')): ?> bg-blue-50 text-blue-600 <?php else: ?> text-gray-700 hover:bg-gray-50 <?php endif; ?> px-4 py-2 rounded-lg font-medium transition block">
                             Visits
                        </a>
                        <a href="<?php echo e(route('admin.purchases')); ?>" class="<?php if(request()->routeIs('admin.purchases')): ?> bg-blue-50 text-blue-600 <?php else: ?> text-gray-700 hover:bg-gray-50 <?php endif; ?> px-4 py-2 rounded-lg font-medium transition block">
                             Purchases
                        </a>
                        
                        <div class="pt-4 mt-4 border-t border-gray-200">
                            <p class="px-4 py-2 text-xs font-semibold text-gray-600 uppercase">Content</p>
                            <a href="<?php echo e(route('admin.templates.index')); ?>" class="<?php if(request()->routeIs('admin.templates.*')): ?> bg-blue-50 text-blue-600 <?php else: ?> text-gray-700 hover:bg-gray-50 <?php endif; ?> px-4 py-2 rounded-lg font-medium transition block">
                                 Templates
                            </a>
                            <a href="<?php echo e(route('admin.articles.index')); ?>" class="<?php if(request()->routeIs('admin.articles.*')): ?> bg-blue-50 text-blue-600 <?php else: ?> text-gray-700 hover:bg-gray-50 <?php endif; ?> px-4 py-2 rounded-lg font-medium transition block">
                                 Articles
                            </a>
                        </div>

                        <div class="pt-4 mt-4 border-t border-gray-200">
                            <p class="px-4 py-2 text-xs font-semibold text-gray-600 uppercase">System</p>
                            <a href="<?php echo e(route('admin.payments')); ?>" class="<?php if(request()->routeIs('admin.payments')): ?> bg-blue-50 text-blue-600 <?php else: ?> text-gray-700 hover:bg-gray-50 <?php endif; ?> px-4 py-2 rounded-lg font-medium transition block">
                                 Payments
                            </a>
                        </div>
                    </nav>

                    <!-- User Menu -->
                    <div class="p-4 border-t border-gray-200 bg-gray-50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                                <?php echo e(substr(Auth::user()->name, 0, 1)); ?>

                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900"><?php echo e(Auth::user()->name); ?></p>
                                <p class="text-xs text-gray-500">Administrator</p>
                            </div>
                            <form method="POST" action="<?php echo e(route('logout')); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" title="Logout" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Top Bar -->
                <?php echo $__env->make('components.navbar-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <!-- Page Content -->
                <div class="flex-1 overflow-auto">
                    <?php echo $__env->yieldContent('content'); ?>
                </div>
            </div>
        </div>
    </body>
</html>
<?php /**PATH C:\xampp\htdocs\resume-builder\resume-builder\resources\views/layouts/admin.blade.php ENDPATH**/ ?>