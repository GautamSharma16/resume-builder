<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-3xl font-bold text-gray-950">Plans</h1>
    <?php if(session('status')): ?><p class="mt-3 text-sm text-teal-700"><?php echo e(session('status')); ?></p><?php endif; ?>
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <h2 class="text-2xl font-bold text-gray-950"><?php echo e($plan->name); ?></h2>
                <p class="mt-2 text-4xl font-bold">₹<?php echo e(number_format($plan->price_paise / 100)); ?></p>
                <ul class="mt-5 space-y-2 text-sm text-gray-600">
                    <li><?php echo e($plan->resume_limit ?: 'Unlimited'); ?> Resume<?php echo e($plan->resume_limit === 1 ? '' : 's'); ?></li>
                    <li><?php echo e($plan->cover_letter_limit ?: 'Unlimited'); ?> Cover Letters</li>
                    <li><?php echo e($plan->ai_enabled ? 'AI enabled' : 'No AI'); ?></li>
                </ul>
                <?php if(auth()->guard()->check()): ?>
                    <button
                        class="buy-plan mt-6 w-full rounded-md bg-teal-700 px-4 py-3 text-sm font-semibold text-white"
                        data-order-url="<?php echo e(route('plans.order', $plan)); ?>"
                    >Buy Plan</button>
                <?php else: ?>
                    <a href="<?php echo e(route('login')); ?>" class="mt-6 block text-center rounded-md bg-teal-700 px-4 py-3 text-sm font-semibold text-white">Login to Buy</a>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<?php $__env->startPush('scripts'); ?>
<script>
document.querySelectorAll('.buy-plan').forEach((button) => {
    button.addEventListener('click', async () => {
        button.disabled = true;
        button.textContent = 'Opening checkout...';
        try {
            const { data } = await axios.post(button.dataset.orderUrl);
            const checkout = new Razorpay({
                key: data.key,
                amount: data.amount,
                currency: data.currency,
                name: '<?php echo e(config('app.name', 'Cvbliss')); ?>',
                description: 'Subscription plan',
                order_id: data.order_id,
                handler: async (payment) => {
                    await axios.post(`/purchases/${data.purchase_id}/verify`, payment);
                    window.location.href = '<?php echo e(route('dashboard')); ?>';
                },
                modal: {
                    ondismiss: () => {
                        button.disabled = false;
                        button.textContent = 'Buy Plan';
                    }
                },
                theme: { color: '#0f766e' }
            });
            checkout.open();
        } catch (error) {
            button.disabled = false;
            button.textContent = 'Buy Plan';
            alert(error.response?.data?.message || 'Payment could not be started.');
        }
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resume-builder\resume-builder\resources\views/pages/plans.blade.php ENDPATH**/ ?>