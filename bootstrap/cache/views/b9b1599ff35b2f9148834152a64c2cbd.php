<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <!-- Header -->
        <div class="mb-12">
            <h1 class="text-4xl font-bold text-gray-900">Welcome, <?php echo e(Auth::user()->name); ?>! 👋</h1>
            <p class="text-gray-600 text-lg mt-2">Let's build an amazing resume that stands out</p>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-600">
                <p class="text-gray-600 text-sm font-medium">Resumes Created</p>
                <p class="text-4xl font-bold text-gray-900 mt-2">0</p>
                <p class="text-gray-500 text-sm mt-2">Start your first resume</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-600">
                <p class="text-gray-600 text-sm font-medium">Downloads</p>
                <p class="text-4xl font-bold text-gray-900 mt-2">0</p>
                <p class="text-gray-500 text-sm mt-2">Downloaded resumes</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-600">
                <p class="text-gray-600 text-sm font-medium">Profile Completion</p>
                <div class="mt-2">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-2xl font-bold text-gray-900">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-purple-600 h-2 rounded-full" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
            <!-- Create New Resume Card -->
            <a href="<?php echo e(route('resume.create')); ?>" class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg shadow-lg hover:shadow-xl transition overflow-hidden group cursor-pointer">
                <div class="p-8">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mb-4 group-hover:bg-opacity-30 transition">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">Create New Resume</h3>
                    <p class="text-blue-100">Start building your professional resume from scratch</p>
                </div>
            </a>

            <!-- Browse Templates Card -->
            <a href="<?php echo e(route('templates')); ?>" class="bg-gradient-to-br from-purple-600 to-purple-700 rounded-lg shadow-lg hover:shadow-xl transition overflow-hidden group cursor-pointer">
                <div class="p-8">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mb-4 group-hover:bg-opacity-30 transition">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21A2 2 0 015 19V5a2 2 0 012-2h4a2 2 0 012 2v14a2 2 0 01-2 2h4a2 2 0 002-2V5a2 2 0 012-2h4a2 2 0 012 2v14a2 2 0 01-2 2h-4.5"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">Browse Templates</h3>
                    <p class="text-purple-100">Choose from professionally designed resume templates</p>
                </div>
            </a>
        </div>

        <!-- Recent Resumes Section -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-xl font-bold text-gray-900">Your Resumes</h2>
            </div>
            <div class="p-6">
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="mt-4 text-gray-600">No resumes yet. <a href="<?php echo e(route('resume.create')); ?>" class="text-blue-600 font-medium hover:text-blue-700">Create your first one</a></p>
                </div>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\resume-builder\resume-builder\resources\views/dashboard.blade.php ENDPATH**/ ?>