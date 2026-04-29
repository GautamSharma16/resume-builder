<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e(config('app.name', 'Resume Builder')); ?></title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50">
        <!-- Public Navbar -->
        <?php echo $__env->make('components.navbar-public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="min-h-screen flex flex-col">
            <div class="flex-1 flex sm:justify-center items-center pt-6 sm:pt-0">
                <div class="w-full sm:max-w-md px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                    <?php echo e($slot); ?>

                </div>
            </div>
        </div>
    </body>
</html>
<?php /**PATH C:\xampp\htdocs\resume-builder\resume-builder\resources\views\layouts\guest.blade.php ENDPATH**/ ?>