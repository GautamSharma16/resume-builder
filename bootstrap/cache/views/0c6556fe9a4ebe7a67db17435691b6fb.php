<?php $__env->startSection('content'); ?>
<div
    id="improve-cv-app"
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
    data-analyze-url="<?php echo e(route('resume.analyze')); ?>"
    data-improve-url="<?php echo e(route('resume.improve')); ?>"
    data-grammar-url="<?php echo e(route('resume.grammar')); ?>"
    data-save-url="<?php echo e(route('resume.save')); ?>"
    data-download-url="<?php echo e(route('resume.download-improved')); ?>"
    data-payment-order-url="<?php echo e(route('resume.payment.order')); ?>"
    data-payment-verify-url="<?php echo e(route('resume.payment.verify')); ?>"
    data-razorpay-key="<?php echo e($razorpayKey); ?>"
    data-download-amount="<?php echo e($downloadAmount); ?>"
    data-download-currency="<?php echo e($downloadCurrency); ?>"
>
    <div class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">AI Resume Optimizer</p>
        <h1 class="mt-2 text-3xl sm:text-4xl font-bold text-gray-950">Improve CV</h1>
        <p class="mt-3 max-w-3xl text-gray-600">Upload your resume, target a role, edit the AI-enhanced structure, and download an ATS-friendly PDF after unlocking export.</p>
    </div>

    <section class="bg-white border border-gray-200 rounded-lg p-5 sm:p-6 shadow-sm mb-6">
        <form id="resume-upload-form" class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            <div class="lg:col-span-4">
                <label for="resume-file" class="block text-sm font-semibold text-gray-800 mb-2">Resume file</label>
                <input id="resume-file" name="resume" type="file" accept=".pdf,.doc,.docx" required class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-teal-600 focus:ring-teal-600">
                <p class="mt-2 text-xs text-gray-500">PDF, DOC, or DOCX up to 10 MB.</p>
            </div>

            <div class="lg:col-span-3">
                <label for="job-role" class="block text-sm font-semibold text-gray-800 mb-2">Job role</label>
                <input id="job-role" name="job_role" type="text" required placeholder="Frontend Developer" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-teal-600 focus:ring-teal-600">
            </div>

            <div class="lg:col-span-5">
                <label for="job-description" class="block text-sm font-semibold text-gray-800 mb-2">Job description</label>
                <textarea id="job-description" name="job_description" rows="3" placeholder="Paste the role requirements, tools, and responsibilities." class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-teal-600 focus:ring-teal-600"></textarea>
            </div>

            <div class="lg:col-span-12 flex flex-wrap items-center gap-3">
                <button id="analyze-button" type="submit" class="inline-flex items-center gap-2 rounded-md bg-teal-700 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3v3m0 12v3m9-9h-3M6 12H3m15.36-6.36-2.12 2.12M7.76 16.24l-2.12 2.12m12.72 0-2.12-2.12M7.76 7.76 5.64 5.64"/><circle cx="12" cy="12" r="3"/></svg>
                    Analyze Resume
                </button>
                <span id="app-status" class="text-sm text-gray-600" role="status"></span>
            </div>
        </form>
    </section>

    <section id="analysis-dashboard" class="hidden mb-6">
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
            <div class="xl:col-span-3 bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-700">ATS Score</p>
                        <p class="mt-1 text-4xl font-bold text-gray-950"><span id="score-value">0</span><span class="text-lg text-gray-500">/100</span></p>
                    </div>
                </div>
                <div class="mt-5 h-3 overflow-hidden rounded-full bg-gray-100">
                    <div id="score-bar" class="h-full w-0 rounded-full bg-teal-600 transition-all duration-500"></div>
                </div>
            </div>

            <div class="xl:col-span-9 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
                    <h2 class="text-sm font-semibold text-gray-950">Strengths</h2>
                    <ul id="strengths-list" class="mt-3 space-y-2 text-sm text-gray-600"></ul>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
                    <h2 class="text-sm font-semibold text-gray-950">Weaknesses</h2>
                    <ul id="weaknesses-list" class="mt-3 space-y-2 text-sm text-gray-600"></ul>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
                    <h2 class="text-sm font-semibold text-gray-950">Missing Keywords</h2>
                    <ul id="keywords-list" class="mt-3 space-y-2 text-sm text-gray-600"></ul>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
                    <h2 class="text-sm font-semibold text-gray-950">Suggestions</h2>
                    <ul id="suggestions-list" class="mt-3 space-y-2 text-sm text-gray-600"></ul>
                </div>
            </div>
        </div>
    </section>

    <section id="resume-workspace" class="hidden grid grid-cols-1 xl:grid-cols-2 gap-6 items-start">
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 p-5">
                <div>
                    <h2 class="text-lg font-bold text-gray-950">Editor</h2>
                    <p class="text-sm text-gray-500">Structured fields update the preview instantly.</p>
                </div>
                <button id="improve-again-button" type="button" class="inline-flex items-center gap-2 rounded-md border border-teal-700 px-4 py-2 text-sm font-semibold text-teal-800 hover:bg-teal-50">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 12a9 9 0 1 1-2.64-6.36"/><path d="M21 3v6h-6"/></svg>
                    Improve Again
                </button>
                <button id="grammar-fix-button" type="button" class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m4 12 4 4L20 4"/><path d="M4 20h16"/></svg>
                    Grammar Fix
                </button>
            </div>

            <div class="p-5 space-y-6">
                <div>
                    <label for="resume-name" class="block text-sm font-semibold text-gray-800 mb-2">Name</label>
                    <input id="resume-name" type="text" data-field="name" class="resume-input block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-teal-600 focus:ring-teal-600">
                </div>

                <div>
                    <label for="resume-summary" class="block text-sm font-semibold text-gray-800 mb-2">Summary</label>
                    <textarea id="resume-summary" rows="5" data-field="summary" class="resume-input block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-teal-600 focus:ring-teal-600"></textarea>
                </div>

                <div>
                    <label for="resume-skills" class="block text-sm font-semibold text-gray-800 mb-2">Skills</label>
                    <input id="resume-skills" type="text" data-field="skills" placeholder="Laravel, Blade, Vite, MySQL" class="resume-input block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-teal-600 focus:ring-teal-600">
                </div>

                <div>
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <h3 class="text-sm font-bold text-gray-950">Experience</h3>
                        <button id="add-experience" type="button" class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                            Add
                        </button>
                    </div>
                    <div id="experience-editor" class="space-y-4"></div>
                </div>

                <div>
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <h3 class="text-sm font-bold text-gray-950">Education</h3>
                        <button id="add-education" type="button" class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                            Add
                        </button>
                    </div>
                    <div id="education-editor" class="space-y-3"></div>
                </div>
            </div>
        </div>

        <div class="xl:sticky xl:top-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-950">Live Preview</h2>
                    <p class="text-sm text-gray-500">This HTML structure is reused for the PDF.</p>
                </div>
                <button id="download-button" type="button" class="inline-flex items-center gap-2 rounded-md bg-gray-950 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-gray-800">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
                    Download
                </button>
            </div>

            <article id="resume-preview" class="min-h-[900px] bg-white border border-gray-200 rounded-lg shadow-sm p-8 text-gray-950"></article>
        </div>
    </section>

    <div id="payment-modal" class="hidden fixed inset-0 z-50">
        <div class="absolute inset-0 bg-gray-950/60"></div>
        <div class="relative mx-auto mt-24 w-[calc(100%-2rem)] max-w-md rounded-lg bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-950">Unlock Download</h2>
                    <p class="mt-2 text-sm text-gray-600">Pay ₹49 once for this improved resume and download the PDF.</p>
                </div>
                <button id="close-payment-modal" type="button" class="rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900" aria-label="Close">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="mt-6 flex items-center justify-between rounded-md bg-gray-50 p-4">
                <span class="text-sm font-semibold text-gray-700">Resume PDF export</span>
                <span class="text-2xl font-bold text-gray-950">₹49</span>
            </div>
            <button id="pay-button" type="button" class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-md bg-teal-700 px-5 py-3 text-sm font-semibold text-white hover:bg-teal-800">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 7h16v10H4z"/><path d="M4 10h16"/></svg>
                Pay and Download
            </button>
        </div>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/improve-cv.js'); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Resume_Builder\resources\views/pages/improve.blade.php ENDPATH**/ ?>