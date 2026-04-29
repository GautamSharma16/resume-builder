

<?php if(auth()->guard()->guest()): ?>
    
    <?php echo $__env->make('components.navbar-public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>

<?php if(auth()->guard()->check()): ?>
    <?php if(Auth::user()->isAdmin()): ?>
        <?php if (! (request()->routeIs('admin.*'))): ?>
            <?php echo $__env->make('components.navbar-admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>
    <?php else: ?>
        
        <?php echo $__env->make('components.navbar-user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\resume-builder\resume-builder\resources\views\components\navbar.blade.php ENDPATH**/ ?>