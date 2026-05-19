<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\ResumeBuilderController;
use App\Http\Controllers\CoverLetterController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManualTestActivationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RazorpayWebhookController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\LeadController as AdminLeadController;
use App\Http\Controllers\Admin\PricingController;
use App\Http\Controllers\Admin\TemplateController as AdminTemplateController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;

/*
|--------------------------------------------------------------------------
| Public Pages (No Auth Required)
|--------------------------------------------------------------------------
*/

Route::get('/', [PageController::class, 'home'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store')->middleware('throttle:5,1');
    Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.store')->middleware('throttle:5,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store')->middleware('throttle:5,1');
    Route::get('/forgot-password', [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email')->middleware('throttle:5,1');
    Route::get('/reset-password/{token}', [AuthController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.store');
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::get('/verify-otp', [AuthController::class, 'showOtp'])->name('otp.verify.form');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('otp.verify')->middleware('throttle:10,1');
Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->name('otp.resend')->middleware('throttle:2,1');
Route::post('/send-otp', [AuthController::class, 'resendOtp'])->name('otp.send')->middleware('throttle:2,1');
Route::put('/password', [AuthController::class, 'updatePassword'])->name('password.update')->middleware('auth');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/improve-cv', [ResumeController::class, 'index'])->name('improve-cv');
Route::get('/ats-checker', [ResumeController::class, 'atsChecker'])->name('ats-checker');
Route::post('/analyze-resume', [ResumeController::class, 'analyze'])->name('resume.analyze');
Route::post('/improve-resume', [ResumeController::class, 'improveAgain'])->name('resume.improve');
Route::post('/grammar-fix-resume', [ResumeController::class, 'grammarFix'])->name('resume.grammar');
Route::post('/save-resume', [ResumeController::class, 'saveResume'])->name('resume.save');
Route::post('/resume-payment/order', [ResumeController::class, 'createPaymentOrder'])->name('resume.payment.order');
Route::post('/resume-payment/verify', [ResumeController::class, 'verifyPayment'])->name('resume.payment.verify');
Route::get('/download-resume', [ResumeController::class, 'download'])->name('resume.download-improved');
Route::get('/enhance-cv', [ResumeController::class, 'index'])->name('enhance-cv');
Route::get('/cover-letter', [CoverLetterController::class, 'create'])->name('cover-letter');
Route::post('/cover-letter', [CoverLetterController::class, 'store'])->name('cover-letter.store');
Route::post('/cover-letter/generate', [CoverLetterController::class, 'generate'])->name('cover-letter.generate');
Route::patch('/cover-letter/{coverLetter}', [CoverLetterController::class, 'save'])->name('cover-letter.save');
Route::patch('/cover-letter/{coverLetter}/rename', [CoverLetterController::class, 'rename'])->name('cover-letter.rename');
Route::get('/cover-letter/{coverLetter}/download/{format?}', [CoverLetterController::class, 'download'])->name('cover-letter.download');
Route::get('/templates', [PageController::class, 'templates'])->name('templates');
Route::get('/interview', [PageController::class, 'interview'])->name('interview');
Route::get('/interview/{slug}', [PageController::class, 'blogShow'])->name('blog.show');
Route::get('/contact', fn() => view('pages.contact'))->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::get('/privacy-policy', fn() => view('pages.privacy'))->name('privacy');
Route::get('/terms-of-use', fn() => view('pages.terms'))->name('terms');
Route::get('/plans', [SubscriptionController::class, 'plans'])->name('plans');
Route::get('/plans/payment/callback', [SubscriptionController::class, 'paymentLinkCallback'])->name('plans.callback');
Route::post('/razorpay/webhook', RazorpayWebhookController::class)->name('razorpay.webhook');
Route::get('/test/activate-plan/{userId}/{plan}', ManualTestActivationController::class)->name('test.activate-plan');

Route::get('/resume', [ResumeBuilderController::class, 'index'])->name('resume.index');
Route::get('/resume/create', [ResumeBuilderController::class, 'create'])->name('resume.create');
Route::get('/resume-maker/{category?}', [ResumeBuilderController::class, 'create'])->name('resume-maker');
Route::post('/resume/ai-text', [ResumeBuilderController::class, 'generateAiText'])->name('resume.ai-text');
Route::post('/resume', [ResumeBuilderController::class, 'store'])->name('resume.store');
Route::get('/resume/edit/{resume}', [ResumeBuilderController::class, 'edit'])->name('resume.edit');
Route::patch('/resume/{resume}', [ResumeBuilderController::class, 'update'])->name('resume.update');
Route::patch('/resume/{resume}/rename', [ResumeBuilderController::class, 'rename'])->name('resume.rename');
Route::get('/resume/{resume}/preview', [ResumeBuilderController::class, 'preview'])->name('resume.preview');
Route::get('/resume/{resume}/preview/document', [ResumeBuilderController::class, 'previewDocument'])->name('resume.preview.document');
Route::get('/resume/{resume}/download/{format?}', [ResumeBuilderController::class, 'download'])->name('resume.download');


/*
|--------------------------------------------------------------------------
| User Dashboard (Auth + User Role)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'user'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/cover-letters', [DashboardController::class, 'coverLetters'])->name('dashboard.cover-letters');
    Route::get('/plans/{plan}/checkout', [SubscriptionController::class, 'checkout'])->name('plans.checkout');
    Route::post('/plans/{plan}/order', [SubscriptionController::class, 'order'])->name('plans.order');
    Route::post('/purchases/{purchase}/verify', [SubscriptionController::class, 'verify'])->name('plans.verify');

    // Candidate Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'company'])->group(function () {
    Route::get('/company/dashboard', fn() => view('company.dashboard'))->name('company.dashboard');
});


/*
|--------------------------------------------------------------------------
| Admin Dashboard (Auth + Admin Role)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
        Route::get('/dashboard-data', [DashboardController::class, 'getData'])->name('dashboard.data');

        // Analytics
        Route::get('/analytics', fn() => view('admin.analytics'))->name('analytics')->middleware('permission:analytics');
        Route::get('/visits', [DashboardController::class, 'visits'])->name('visits')->middleware('permission:visits');

        // Content Management
        Route::middleware('permission:templates')->group(function () {
            Route::resource('templates', AdminTemplateController::class)->except(['show']);
            Route::get('templates/{template}/preview', [AdminTemplateController::class, 'preview'])->name('templates.preview');
            Route::get('templates/{template}/download', [AdminTemplateController::class, 'download'])->name('templates.download');
        });

        Route::middleware('permission:articles')->group(function () {
            Route::resource('articles', AdminArticleController::class)->except(['show', 'destroy']);
        });

        // Financial Management
        Route::middleware('admin')->group(function () {
            Route::get('/purchases', fn() => view('admin.purchases'))->name('purchases')->middleware('permission:purchases');
            Route::get('/payments', [PricingController::class, 'index'])->name('payments')->middleware('permission:pricing');
            Route::patch('/plans/{plan}', [PricingController::class, 'update'])->name('plans.update')->middleware('permission:pricing');
            Route::get('/transactions', [App\Http\Controllers\Admin\TransactionController::class, 'index'])->name('transactions')->middleware('permission:transactions');
            Route::get('/transactions/export', [App\Http\Controllers\Admin\TransactionController::class, 'exportCsv'])->name('transactions.export')->middleware('permission:transactions');
        });

        // User Management (Admin role only)
        Route::middleware('admin')->group(function () {
            Route::resource('users', App\Http\Controllers\Admin\UserController::class)->except(['show']);
        });

        // Shared Enquiries (Team permission)
        Route::middleware('permission:team')->group(function () {
            Route::resource('leads', AdminLeadController::class)->only(['index', 'show', 'destroy']);
        });
    });
});


/*
|--------------------------------------------------------------------------
| Profile (Authenticated Users Only)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
