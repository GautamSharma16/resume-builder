

<?php $__env->startSection('content'); ?>

<div class="max-w-6xl mx-auto py-12">

    <h1 class="text-3xl font-bold mb-6">Contact Us</h1>

    <p class="text-gray-600 mb-10">
        Have questions or need help? Reach out to us.
    </p>

    <div class="grid md:grid-cols-2 gap-10">

        
        <div class="bg-white p-8 rounded-xl shadow-md">

            <form>

                <input type="text" placeholder="Your Name"
                    class="w-full border p-3 rounded mb-4">

                <input type="email" placeholder="Email"
                    class="w-full border p-3 rounded mb-4">

                <textarea placeholder="Your Message"
                    class="w-full border p-3 rounded mb-6"></textarea>

                <button class="bg-blue-600 text-white px-6 py-3 rounded">
                    Send Message
                </button>

            </form>

        </div>

        
        <div>
            <h2 class="text-xl font-semibold mb-4">Contact Info</h2>
            <p class="text-gray-600 mb-2">Email: support@cvbliss.in</p>
            <p class="text-gray-600 mb-2">Phone: +91 9876543210</p>
            <p class="text-gray-600">Location: India</p>
        </div>

    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resume-builder\resume-builder\resources\views\pages\contact.blade.php ENDPATH**/ ?>