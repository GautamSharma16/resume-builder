<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 34px 42px; }
        body {
            color: #111827;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            line-height: 1.42;
        }
        h1 {
            border-bottom: 2px solid #111827;
            font-size: 26px;
            letter-spacing: 0;
            margin: 0 0 14px;
            padding-bottom: 9px;
            text-transform: uppercase;
        }
        h2 {
            color: #0f766e;
            font-size: 12px;
            letter-spacing: 0;
            margin: 18px 0 7px;
            text-transform: uppercase;
        }
        p { margin: 0 0 8px; }
        ul { margin: 6px 0 0 18px; padding: 0; }
        li { margin-bottom: 4px; }
        .skills span {
            display: inline-block;
            margin: 0 6px 6px 0;
        }
        .item { margin-bottom: 12px; }
        .item-title { font-weight: bold; }
        .muted { color: #4b5563; }
        .contact { color: #4b5563; margin: -6px 0 12px; }
    </style>
</head>
<body>
    <h1><?php echo e($resume['name'] ?: 'Your Name'); ?></h1>
    <?php if(!empty($resume['contact']) || !empty($resume['address'])): ?>
        <p class="contact"><?php echo e($resume['contact'] ?? ''); ?> <?php if(!empty($resume['address'])): ?> | <?php echo e($resume['address']); ?> <?php endif; ?></p>
    <?php endif; ?>

    <?php if($resume['summary']): ?>
        <h2>Summary</h2>
        <p><?php echo e($resume['summary']); ?></p>
    <?php endif; ?>

    <?php if(count($resume['skills'])): ?>
        <h2>Skills</h2>
        <p class="skills">
            <?php $__currentLoopData = $resume['skills']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $skill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span><?php echo e($skill); ?></span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </p>
    <?php endif; ?>

    <?php if(count($resume['experience'])): ?>
        <h2>Experience</h2>
        <?php $__currentLoopData = $resume['experience']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $experience): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="item">
                <div class="item-title"><?php echo e($experience['role'] ?: 'Role'); ?></div>
                <?php if($experience['company']): ?>
                    <div class="muted"><?php echo e($experience['company']); ?></div>
                <?php endif; ?>
                <?php if(count($experience['points'])): ?>
                    <ul>
                        <?php $__currentLoopData = $experience['points']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($point); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>

    <?php if(count($resume['education'])): ?>
        <h2>Education</h2>
        <ul>
            <?php $__currentLoopData = $resume['education']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $education): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($education); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    <?php endif; ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\resume-builder\resume-builder\resources\views\resume\pdf.blade.php ENDPATH**/ ?>