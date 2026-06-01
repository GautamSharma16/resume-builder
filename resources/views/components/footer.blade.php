<!-- Footer Component -->
<style>
    .ft {
        background: #0d0d0d;
        font-family: 'Inter', sans-serif;
        color: #9ca3af;
        padding: 0;
        margin: 0;
    }

    /* Top bar: Logo left, Socials right */
    .ft-topbar {
        max-width: 1400px;
        margin: 0 auto;
        /* padding: 32px 40px 28px; */
        padding: 0 5px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #1f1f1f;
    }

    .ft-logo {
        display: flex;
        align-items: center;
        text-decoration: none;
    }
    .ft-logo img {
        height: clamp(58px, 7vw, 92px);
        width: auto;
        max-width: min(210px, 58vw);
        object-fit: contain;
        transition: opacity .2s;
    }
    .ft-logo:hover img { opacity: .8; }

    .ft-socials {
        display: flex;
        gap: 20px;
        align-items: center;
    }
    .ft-social {
        color: #6b7280;
        text-decoration: none;
        transition: color .18s;
        display: flex;
        align-items: center;
    }
    .ft-social:hover { color: #ffffff; }
    .ft-social svg { width: 18px; height: 18px; }

    /* Main grid */
    .ft-main {
        max-width: 1400px;
        margin: 0 auto;
        padding: 48px 40px 48px;
    }

    .ft-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 32px;
        padding-bottom: 48px;
        border-bottom: 1px solid #1f1f1f;
    }

    .ft-col-title {
        font-size: 15px;
        font-weight: 600;
        color: #ffffff;
        margin: 0 0 20px;
        letter-spacing: .1px;
    }

    .ft-links {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 13px;
    }

    .ft-link {
        font-size: 13px;
        font-weight: 300;
        color: #6b7280;
        text-decoration: none;
        transition: color .18s;
        line-height: 1.4;
    }
    .ft-link:hover { color: #ffffff; }

    .ft-contact-email {
        font-size: 13px;
        font-weight: 300;
        color: #6b7280;
        margin: 0 0 8px;
        line-height: 1.6;
    }
    .ft-contact-hours {
        font-size: 12px;
        font-weight: 300;
        color: #4b5563;
        line-height: 1.8;
        margin: 0;
    }

    /* Bottom bar */
    .ft-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 28px;
        gap: 20px;
    }

    .ft-copyright {
        font-size: 12.5px;
        font-weight: 300;
        color: #4b5563;
    }

    .ft-legal {
        display: flex;
        gap: 24px;
    }
    .ft-legal a {
        font-size: 12.5px;
        font-weight: 300;
        color: #4b5563;
        text-decoration: none;
        transition: color .18s;
    }
    .ft-legal a:hover { color: #9ca3af; }

    .ft-divider-full {
        border: none;
        border-top: 1px solid #1a1a1a;
        margin: 0;
    }

    .ft-note {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px 40px 28px;
        font-size: 11.5px;
        font-weight: 300;
        color: #374151;
        line-height: 1.7;
    }

    @media (max-width: 1024px) {
        .ft-grid { grid-template-columns: repeat(3, 1fr); }
    }

    @media (max-width: 640px) {
        .ft-topbar { padding: 24px 20px; }
        .ft-socials { gap: 14px; }
        .ft-social svg { width: 16px; height: 16px; }
        .ft-grid { grid-template-columns: repeat(2, 1fr); }
        .ft-bottom { flex-direction: column; align-items: flex-start; gap: 16px; }
        .ft-main { padding: 36px 20px; }
        .ft-note { padding: 16px 20px 24px; }
    }
</style>

<footer class="ft">

    <!-- Top bar: Logo left, Social icons right -->
    <div class="ft-topbar">
        <a href="{{ route('home') }}" class="ft-logo">
            <img src="{{ asset('Logo.png') }}" alt="Cvbliss Logo" class="cvb-logo">
        </a>

        <div class="ft-socials">
            <!-- Facebook -->
            <a class="ft-social" href="https://facebook.com/cvbliss" title="Facebook" target="_blank" rel="noopener noreferrer">
                <svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"/>
                </svg>
            </a>
            <!-- X (Twitter) -->
            <a class="ft-social" href="https://twitter.com/cvbliss" title="X / Twitter" target="_blank" rel="noopener noreferrer">
                <svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.737-8.835L2.25 2.25h6.838l4.265 5.638L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77z"/>
                </svg>
            </a>
            <!-- LinkedIn -->
            <a class="ft-social" href="https://linkedin.com/company/cvbliss" title="LinkedIn" target="_blank" rel="noopener noreferrer">
                <svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                </svg>
            </a>
            <!-- Instagram -->
            <a class="ft-social" href="https://instagram.com/cvbliss" title="Instagram" target="_blank" rel="noopener noreferrer">
                <svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd"/>
                </svg>
            </a>
            <!-- YouTube -->
            <a class="ft-social" href="https://youtube.com/cvbliss" title="YouTube" target="_blank" rel="noopener noreferrer">
                <svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill-rule="evenodd" d="M19.812 5.418c.861.23 1.538.907 1.768 1.768C21.998 8.746 22 12 22 12s0 3.255-.42 4.814a2.504 2.504 0 0 1-1.768 1.768c-1.56.419-7.812.419-7.812.419s-6.252 0-7.812-.419a2.505 2.505 0 0 1-1.768-1.768C2 15.255 2 12 2 12s0-3.255.42-4.814a2.505 2.505 0 0 1 1.768-1.768c1.56-.419 7.812-.419 7.812-.419s6.252 0 7.812.419ZM10 15.464l5.203-3.464L10 8.536v6.928Z" clip-rule="evenodd"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Main link grid -->
    <div class="ft-main">
        <div class="ft-grid">

            <!-- Resume -->
            <div>
                <p class="ft-col-title">Resume</p>
                <ul class="ft-links">
                    <li><a class="ft-link" href="{{ route('resume-maker') }}">Resume Maker</a></li>
                    
                    <li><a class="ft-link" href="{{ route('templates') }}">Resume Templates</a></li>
         
                </ul>
            </div>

            <!-- CV & Cover Letter -->
            <div>
                <p class="ft-col-title">Cover Letter</p>
                <ul class="ft-links">
                    <li><a class="ft-link" href="{{ route('enhance-cv') }}">Enhance CV (AI)</a></li>
                    <li><a class="ft-link" href="{{ route('cover-letter') }}">Cover Letter Builder</a></li>
                    <li><a class="ft-link" href="{{ route('templates') }}">Professional Templates</a></li>
                    
                </ul>
            </div>

            <!-- Tools -->
            <div>
                <p class="ft-col-title">Tools</p>
                <ul class="ft-links">
                    <li><a class="ft-link" href="{{ route('interview') }}">Interview Preparation</a></li>
                    <li><a class="ft-link" href="{{ route('interview') }}">Career Blog</a></li>
                    <li><a class="ft-link" href="{{ route('interview') }}">Job Search Tips</a></li>
                    
                </ul>
            </div>

            <!-- Company -->
            <div>
                <p class="ft-col-title">Company</p>
                <ul class="ft-links">
                  
                    <li><a class="ft-link" href="{{ route('plans') }}">Pricing</a></li>
                   
                </ul>
            </div>

            <!-- Customer Service -->
            <div>
                <p class="ft-col-title">Customer Service</p>
                <p class="ft-contact-email"><a href="mailto:support@cvbliss.in" style="color: inherit; text-decoration: none;">support@cvbliss.in</a></p>
                <p class="ft-contact-hours">
                    +91 98765 43210<br>
                    Mon–Sat 10am – 7pm IST
                </p>
            </div>

        </div>

        <!-- Bottom bar -->
        <div class="ft-bottom">
            <p class="ft-copyright">&copy; {{ date('Y') }}, Cvbliss. All rights reserved.</p>
            <div class="ft-legal">
                <a href="{{ route('privacy') }}">Privacy Policy</a>
                <a href="{{ route('terms') }}">Terms of Use</a>
                <a href="{{ route('contact') }}">Contact Us</a>
                
            </div>
        </div>
    </div>

    <hr class="ft-divider-full">

    <p class="ft-note">
        Cvbliss empowers job seekers with modern resume tools, AI-powered CV enhancement, and expert interview strategies. All product names, logos, and brands referenced are property of their respective owners and do not imply affiliation or endorsement.
    </p>

</footer>
