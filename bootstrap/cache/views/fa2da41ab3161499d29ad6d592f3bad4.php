<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-950">My Resumes</h1>
            <p class="text-gray-600 mt-1">Create, edit, preview, and download your saved resumes.</p>
        </div>
        <a href="<?php echo e(route('resume.create')); ?>" class="inline-flex items-center justify-center rounded-md bg-teal-700 px-5 py-3 text-sm font-semibold text-white hover:bg-teal-800">Create CV</a>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Title</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Source</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Updated</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php $__empty_1 = true; $__currentLoopData = $resumes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resume): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="px-5 py-4 font-medium text-gray-950"><?php echo e($resume->title); ?></td>
                        <td class="px-5 py-4 text-gray-600"><?php echo e(ucfirst($resume->source)); ?></td>
                        <td class="px-5 py-4 text-gray-600"><?php echo e($resume->updated_at->format('d M Y')); ?></td>
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap gap-3 text-sm font-semibold">
                                <a class="text-teal-700" href="<?php echo e(route('resume.edit', $resume)); ?>">Edit</a>
                                <a class="text-gray-700" href="<?php echo e(route('resume.preview', $resume)); ?>">Preview</a>
                                <a class="text-gray-950" href="<?php echo e(route('resume.download', [$resume, 'pdf'])); ?>">PDF</a>
                                <a class="text-gray-950" href="<?php echo e(route('resume.download', [$resume, 'doc'])); ?>">DOC</a>
                                <a class="text-gray-950" href="<?php echo e(route('resume.download', [$resume, 'ppt'])); ?>">PPT</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="4" class="px-5 py-12 text-center text-gray-500">No resumes yet. Start with Create CV.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resume-builder\resume-builder\resources\views\resume\index.blade.php ENDPATH**/ ?>