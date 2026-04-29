<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-3xl font-bold text-gray-950">Interview Tips</h1>
    <p class="mt-2 text-gray-600">Fresh articles from your content team.</p>
    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php $__empty_1 = true; $__currentLoopData = $articles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <article class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <h2 class="text-xl font-bold text-gray-950"><?php echo e($article->title); ?></h2>
                <p class="mt-2 text-gray-600"><?php echo e($article->excerpt); ?></p>
                <div class="mt-4 text-sm leading-6 text-gray-700"><?php echo e($article->body); ?></div>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-gray-500">No articles published yet.</p>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Resume_Builder\resources\views/pages/interview.blade.php ENDPATH**/ ?>