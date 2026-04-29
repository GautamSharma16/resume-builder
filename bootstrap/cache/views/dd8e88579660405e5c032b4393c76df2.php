<header class="border-b-2 border-gray-950 pb-4">
    <h1 class="text-3xl font-bold uppercase text-gray-950"><?php echo e($resume['name'] ?? 'Your Name'); ?></h1>
    <p class="mt-2 text-sm text-gray-600"><?php echo e($resume['contact'] ?? ''); ?></p>
    <p class="text-sm text-gray-600"><?php echo e($resume['address'] ?? ''); ?></p>
</header>
<section class="mt-6"><h2 class="text-xs font-bold uppercase text-teal-700">Summary</h2><p class="mt-2 text-sm leading-6 text-gray-700"><?php echo e($resume['summary'] ?? ''); ?></p></section>
<section class="mt-6"><h2 class="text-xs font-bold uppercase text-teal-700">Skills</h2><p class="mt-2 text-sm text-gray-700"><?php echo e(implode(', ', $resume['skills'] ?? [])); ?></p></section>
<section class="mt-6">
    <h2 class="text-xs font-bold uppercase text-teal-700">Experience</h2>
    <?php $__currentLoopData = ($resume['experience'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="mt-3">
            <h3 class="font-bold text-gray-950"><?php echo e($item['role'] ?? ''); ?></h3>
            <p class="text-sm text-gray-500"><?php echo e($item['company'] ?? ''); ?></p>
            <ul class="mt-2 list-disc pl-5 text-sm text-gray-700"><?php $__currentLoopData = ($item['points'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($point); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</section>
<section class="mt-6"><h2 class="text-xs font-bold uppercase text-teal-700">Education</h2><ul class="mt-2 list-disc pl-5 text-sm text-gray-700"><?php $__currentLoopData = ($resume['education'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($item); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></section>
<?php /**PATH C:\xampp\htdocs\resume-builder\resume-builder\resources\views\resume\partials\preview.blade.php ENDPATH**/ ?>