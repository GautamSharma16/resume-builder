<!-- Public Navbar (Unauthenticated Users) -->
<nav class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex-shrink-0">
                <a href="<?php echo e(route('home')); ?>" class="text-2xl font-bold text-blue-600">
                    Cvbliss
                </a>
            </div>

            <!-- Navigation Links -->
            <div class="hidden md:flex space-x-8">
                <a href="<?php echo e(route('home')); ?>" class="text-gray-700 hover:text-blue-600 transition">Home</a>
                <a href="<?php echo e(route('templates')); ?>" class="text-gray-700 hover:text-blue-600 transition">Templates</a>
                <a href="<?php echo e(route('improve-cv')); ?>" class="text-gray-700 hover:text-blue-600 transition">Improve CV</a>
                <a href="<?php echo e(route('interview')); ?>" class="text-gray-700 hover:text-blue-600 transition">Interview</a>
                <a href="<?php echo e(route('contact')); ?>" class="text-gray-700 hover:text-blue-600 transition">Contact</a>
            </div>

            <!-- Auth Buttons -->
            <div class="flex items-center space-x-4">
                <a href="<?php echo e(route('login')); ?>" class="text-gray-700 hover:text-blue-600 transition">Login</a>
                <a href="<?php echo e(route('register')); ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Register
                </a>
            </div>
        </div>
    </div>
</nav>
<?php /**PATH D:\Resume_Builder\resources\views/components/navbar-public.blade.php ENDPATH**/ ?>