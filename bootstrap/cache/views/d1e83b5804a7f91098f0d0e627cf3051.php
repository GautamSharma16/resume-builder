<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { color: #111827; font-family: DejaVu Sans, Arial, sans-serif; font-size: 13px; line-height: 1.65; margin: 36px; }
        h1 { font-size: 24px; margin: 0 0 24px; }
        .meta { color: #4b5563; margin-bottom: 24px; }
        p { white-space: pre-line; }
    </style>
</head>
<body>
    <h1><?php echo e($letter['name'] ?? 'Cover Letter'); ?></h1>
    <div class="meta"><?php echo e($letter['job_role'] ?? ''); ?> <?php if(!empty($letter['company'])): ?> · <?php echo e($letter['company']); ?> <?php endif; ?></div>
    <p><?php echo e($letter['body'] ?? ''); ?></p>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\resume-builder\resume-builder\resources\views\cover-letter\pdf.blade.php ENDPATH**/ ?>