


<?php $__env->startSection('title', 'ResumeForge - Build a Resume That Commands Attention'); ?>

<?php $__env->startSection('content'); ?>
<style>
    /* Custom styles for the design system */
    .hero-gradient {
        background: linear-gradient(135deg, #254fd1 0%, #456aeb 100%);
    }
    
    .glass-nav {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(196, 197, 215, 0.2);
    }
    
    .floating-card {
        background: #ffffff;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .floating-card:hover {
        transform: translateY(-4px);
        box-shadow: 0px 20px 40px rgba(24, 28, 32, 0.06);
    }
    
    .asymmetric-grid {
        display: grid;
        grid-template-columns: 1fr 0.9fr;
        gap: 4rem;
    }
    
    .section-indicator {
        width: 4px;
        height: 48px;
        background: linear-gradient(135deg, #254fd1 0%, #456aeb 100%);
        border-radius: 4px;
    }
    
    .stat-card {
        background: #f1f4f9;
        border-radius: 1.5rem;
        padding: 2rem;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        background: #ffffff;
        box-shadow: 0px 20px 40px rgba(24, 28, 32, 0.06);
    }
    
    .feature-icon {
        background: linear-gradient(135deg, #cad6ff 0%, #e0e6ff 100%);
        border-radius: 1rem;
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
    }
    
    .step-circle {
        width: 48px;
        height: 48px;
        background: #f1f4f9;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-family: 'Manrope', sans-serif;
        font-size: 1.25rem;
        color: #254fd1;
        margin-bottom: 1rem;
    }
    
    .gradient-text {
        background: linear-gradient(135deg, #254fd1 0%, #456aeb 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    
    .pill-indicator {
        background: #254fd1;
        width: 48px;
        height: 48px;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 800;
        font-family: 'Manrope', sans-serif;
    }
    
    .glass-preview {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(12px);
        border-radius: 1.5rem;
        padding: 2rem;
        box-shadow: 0px 20px 40px rgba(24, 28, 32, 0.06);
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
    
    .animate-float {
        animation: float 4s ease-in-out infinite;
    }
</style>

<div class="bg-[#f7f9fe] overflow-hidden">
    
    
    <div class="relative overflow-hidden">
        
        <div class="absolute top-0 right-0 w-1/2 h-full bg-[#f1f4f9] -z-10"></div>
        
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
            <div class="asymmetric-grid">
                
                <div class="space-y-6">
                    
                    <div class="inline-flex items-center gap-2 bg-[#cad6ff] rounded-full px-4 py-1.5">
                        <span class="w-2 h-2 bg-[#254fd1] rounded-full"></span>
                        <span class="text-sm font-medium text-[#254fd1] font-manrope">✦ AI-Powered Resume Builder</span>
                    </div>
                    
                    
                    <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold font-manrope tracking-[-0.02em] text-gray-900 leading-[1.1]">
                        Build a resume that<br>
                        <span class="gradient-text">commands attention.</span>
                    </h1>
                    
                    
                    <p class="text-lg text-[#444654] font-inter leading-relaxed max-w-lg">
                        Step into the digital afterlife. We don't just format text, we curate your professional identity through premium editorial design and infographic structure.
                    </p>
                    
                    
                    <div class="flex flex-wrap gap-4 pt-4">
                        <a href="<?php echo e(url('/templates')); ?>" class="hero-gradient text-white font-semibold px-8 py-3.5 rounded-xl transition-all hover:shadow-lg hover:scale-105 inline-flex items-center gap-2">
                            Create My Resume
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                        <a href="<?php echo e(url('/templates')); ?>" class="bg-[#e6e8ed] text-[#254fd1] font-semibold px-8 py-3.5 rounded-xl transition-all hover:bg-[#dadce5]">
                            View Templates
                        </a>
                    </div>
                    
                    
                    <div class="flex items-center gap-4 pt-8">
                        <div class="flex -space-x-2">
                            <div class="w-10 h-10 bg-[#cad6ff] rounded-full border-2 border-white flex items-center justify-center text-xs font-bold text-[#254fd1]">JD</div>
                            <div class="w-10 h-10 bg-[#e0e6ff] rounded-full border-2 border-white flex items-center justify-center text-xs font-bold text-[#254fd1]">SM</div>
                            <div class="w-10 h-10 bg-[#f1f4f9] rounded-full border-2 border-white flex items-center justify-center text-xs font-bold text-[#254fd1]">RK</div>
                        </div>
                        <div>
                            <p class="text-sm text-[#444654] font-inter">
                                <span class="font-bold text-[#254fd1]">50,000+</span> professionals trust us
                            </p>
                        </div>
                    </div>
                </div>
                
                
                <div class="relative">
                    <div class="glass-preview animate-float">
                        <div class="space-y-4">
                            
                            <div class="flex items-center justify-between border-b border-[#e0e2e7] pb-3">
                                <div>
                                    <h3 class="font-bold font-manrope text-gray-900">Sarah Johnson</h3>
                                    <p class="text-sm text-[#444654] font-inter">Senior Product Designer</p>
                                </div>
                                <div class="w-10 h-10 bg-gradient-to-br from-[#254fd1] to-[#456aeb] rounded-lg"></div>
                            </div>
                            
                            
                            <div class="space-y-3">
                                <div class="flex gap-3">
                                    <div class="w-1 bg-[#cad6ff] rounded-full"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-gray-900">Lead Product Designer</p>
                                        <p class="text-xs text-[#444654]">Tech Innovations Inc. • 2022-Present</p>
                                        <p class="text-xs text-[#444654] mt-1">Led cross-functional teams to deliver 12+ modules, increasing user engagement by 45%</p>
                                    </div>
                                </div>
                                <div class="flex gap-3">
                                    <div class="w-1 bg-[#e0e2e7] rounded-full"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-gray-900">UI/UX Designer</p>
                                        <p class="text-xs text-[#444654]">Creative Agency • 2019-2022</p>
                                    </div>
                                </div>
                            </div>
                            
                            
                            <div class="flex flex-wrap gap-2 pt-2">
                                <span class="bg-[#cad6ff] text-[#505c7f] px-3 py-1 rounded-full text-xs font-medium">Strategic Design</span>
                                <span class="bg-[#f1f4f9] text-[#505c7f] px-3 py-1 rounded-full text-xs font-medium">User Research</span>
                                <span class="bg-[#f1f4f9] text-[#505c7f] px-3 py-1 rounded-full text-xs font-medium">Prototyping</span>
                            </div>
                        </div>
                    </div>
                    
                                    
                    <div class="absolute -bottom-4 -left-4 w-24 h-24 bg-[#cad6ff] rounded-full opacity-20 -z-10"></div>
                    <div class="absolute -top-4 -right-4 w-32 h-32 bg-[#e0e6ff] rounded-full opacity-30 -z-10"></div>
                </div>
            </div>
        </div>
    </div>
    
    
    <div class="bg-[#f1f4f9] py-16">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="stat-card text-center">
                    <div class="text-3xl md:text-4xl font-bold font-manrope text-[#254fd1]">50K+</div>
                    <p class="text-sm text-[#444654] font-inter mt-2">Resumes Created</p>
                </div>
                <div class="stat-card text-center">
                    <div class="text-3xl md:text-4xl font-bold font-manrope text-[#254fd1]">98%</div>
                    <p class="text-sm text-[#444654] font-inter mt-2">Satisfaction Rate</p>
                </div>
                <div class="stat-card text-center">
                    <div class="text-3xl md:text-4xl font-bold font-manrope text-[#254fd1]">15+</div>
                    <p class="text-sm text-[#444654] font-inter mt-2">Expert Templates</p>
                </div>
                <div class="stat-card text-center">
                    <div class="text-3xl md:text-4xl font-bold font-manrope text-[#254fd1]">24/7</div>
                    <p class="text-sm text-[#444654] font-inter mt-2">AI Support</p>
                </div>
            </div>
        </div>
    </div>
    
    
    <div class="py-24">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div class="space-y-6">
                    <div class="section-indicator"></div>
                    <h2 class="text-4xl md:text-5xl font-bold font-manrope tracking-[-0.02em] text-gray-900">
                        Precision Layouts
                    </h2>
                    <p class="text-lg text-[#444654] font-inter leading-relaxed">
                        Automatically optimized for both ATS systems and human readers. Our layouts ensure your resume passes automated screening while impressing hiring managers.
                    </p>
                    <div class="flex gap-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#006a37]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-inter">ATS-Friendly</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#006a37]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-inter">Human-Readable</span>
                        </div>
                    </div>
                </div>
                <div class="bg-[#ffffff] rounded-2xl p-8 shadow-sm">
                    <div class="space-y-4">
                        <div class="h-2 w-24 bg-[#cad6ff] rounded-full"></div>
                        <div class="h-8 w-3/4 bg-gray-200 rounded-lg"></div>
                        <div class="space-y-2">
                            <div class="h-4 w-full bg-gray-100 rounded"></div>
                            <div class="h-4 w-11/12 bg-gray-100 rounded"></div>
                            <div class="h-4 w-10/12 bg-gray-100 rounded"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 pt-4">
                            <div class="h-16 bg-[#f1f4f9] rounded-xl"></div>
                            <div class="h-16 bg-[#f1f4f9] rounded-xl"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    
    <div class="bg-[#f1f4f9] py-24">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <div class="flex justify-center mb-6">
                    <div class="pill-indicator">AI</div>
                </div>
                <h2 class="text-4xl md:text-5xl font-bold font-manrope tracking-[-0.02em] text-gray-900 mb-4">
                    ENGINEERED FOR IMPACT
                </h2>
                <p class="text-xl text-[#444654] font-inter">
                    AI Content Architect
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <div class="floating-card rounded-2xl p-6 bg-white">
                    <div class="feature-icon">
                        <svg class="w-6 h-6 text-[#254fd1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold font-manrope text-gray-900 mb-3">Smart Analysis</h3>
                    <p class="text-[#444654] font-inter text-sm leading-relaxed">
                        Our engine analyzes your industry and targets powerful action verbs and achievements. Finds points that pass the B-Score rubric test.
                    </p>
                </div>
                
                
                <div class="floating-card rounded-2xl p-6 bg-white">
                    <div class="feature-icon">
                        <svg class="w-6 h-6 text-[#254fd1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold font-manrope text-gray-900 mb-3">Ideal Controls</h3>
                    <p class="text-[#444654] font-inter text-sm leading-relaxed">
                        Strategic messaging, brand positioning, hyperlinking with real-world examples. Narrative storytelling that captivates.
                    </p>
                </div>
                
                
                <div class="floating-card rounded-2xl p-6 bg-white">
                    <div class="feature-icon">
                        <svg class="w-6 h-6 text-[#254fd1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold font-manrope text-gray-900 mb-3">Fine-Tune Typography</h3>
                    <p class="text-[#444654] font-inter text-sm leading-relaxed">
                        Fine-tune typography and layout in our live workshops. Perfect every detail of your professional presentation.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    
    <div class="py-24">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-4xl md:text-5xl font-bold font-manrope tracking-[-0.02em] text-gray-900 mb-4">
                    Your Path to Publish
                </h2>
                <p class="text-lg text-[#444654] font-inter">
                    Simple steps to professional excellence.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 max-w-5xl mx-auto">
                
                <div class="text-center">
                    <div class="step-circle mx-auto">1</div>
                    <h3 class="text-xl font-bold font-manrope text-gray-900 mb-2">Select One</h3>
                    <p class="text-[#444654] font-inter text-sm">
                        Choose a strategic focus from our curated library of professional templates.
                    </p>
                </div>
                
                
                <div class="text-center">
                    <div class="step-circle mx-auto">2</div>
                    <h3 class="text-xl font-bold font-manrope text-gray-900 mb-2">Input Story</h3>
                    <p class="text-[#444654] font-inter text-sm">
                        Detail your journey with AI-assisted crafting tools that optimize every word.
                    </p>
                </div>
                
                
                <div class="text-center">
                    <div class="step-circle mx-auto">3</div>
                    <h3 class="text-xl font-bold font-manrope text-gray-900 mb-2">Refine Style</h3>
                    <p class="text-[#444654] font-inter text-sm">
                        Fine-tune typography and layout in our live interactive workshops.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    
    <div class="relative overflow-hidden">
        <div class="hero-gradient py-20">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
                <h2 class="text-3xl md:text-5xl font-bold font-manrope text-white mb-4">
                    Ready to transform your career?
                </h2>
                <p class="text-xl text-white/90 font-inter mb-8 max-w-2xl mx-auto">
                    Join over 50,000 professionals who secured their dream roles using ResumeForge.
                </p>
                <div class="flex flex-wrap gap-4 justify-center">
                    <a href="<?php echo e(url('/templates')); ?>" class="bg-white text-[#254fd1] font-semibold px-8 py-3.5 rounded-xl transition-all hover:shadow-lg hover:scale-105 inline-flex items-center gap-2">
                        Create My Resume
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                    <a href="<?php echo e(url('/templates')); ?>" class="border-2 border-white text-white font-semibold px-8 py-3.5 rounded-xl transition-all hover:bg-white/10">
                        View Templates
                    </a>
                </div>
            </div>
            
            
            <div class="absolute top-0 left-0 w-64 h-64 bg-white/10 rounded-full -translate-x-32 -translate-y-32"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-white/5 rounded-full translate-x-48 translate-y-48"></div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Add intersection observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe all sections for animation
    document.querySelectorAll('.floating-card, .stat-card, .asymmetric-grid > div').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'all 0.6s ease-out';
        observer.observe(el);
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resume-builder\resume-builder\resources\views\pages\home.blade.php ENDPATH**/ ?>