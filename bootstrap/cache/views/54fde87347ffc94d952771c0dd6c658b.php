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
                <a href="<?php echo e(route('resume-maker')); ?>" class="text-gray-700 hover:text-blue-600 transition">Resume Maker</a>
                <a href="<?php echo e(route('enhance-cv')); ?>" class="text-gray-700 hover:text-blue-600 transition">Enhance CV</a>
                <a href="<?php echo e(route('templates')); ?>" class="text-gray-700 hover:text-blue-600 transition">Templates</a>
                <a href="<?php echo e(route('cover-letter')); ?>" class="text-gray-700 hover:text-blue-600 transition">Cover Letter</a>
                <a href="<?php echo e(route('interview')); ?>" class="text-gray-700 hover:text-blue-600 transition">Interview Tips</a>
                <a href="<?php echo e(route('contact')); ?>" class="text-gray-700 hover:text-blue-600 transition">Contact Us</a>
            </div>

            <!-- Auth Buttons -->
            <div class="flex items-center space-x-4">
                <a href="<?php echo e(route('login')); ?>" class="text-gray-700 hover:text-blue-600 transition">Login / Register</a>
            </div>
        </div>
    </div>
</nav>
<?php /**PATH C:\xampp\htdocs\resume-builder\resume-builder\resources\views/components/navbar-public.blade.php ENDPATH**/ ?>