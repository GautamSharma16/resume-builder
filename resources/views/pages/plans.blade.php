@extends('layouts.app')

@section('content')
<style>
    .plans-root {
        padding: 6rem 1.5rem;
        max-width: 1200px;
        margin: 0 auto;
    }
    .plans-header {
        text-align: center;
        margin-bottom: 4rem;
    }
    .plans-header h1 {
        font-family: var(--font-display);
        font-size: clamp(2.5rem, 5vw, 4rem);
        color: var(--navy);
        margin-bottom: 1rem;
    }
    .plans-header p {
        color: var(--muted);
        font-size: 1.125rem;
        max-width: 600px;
        margin: 0 auto;
    }
    .plan-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 2rem;
    }
    .plan-card {
        background: white;
        border: 1px solid var(--border);
        border-radius: var(--r-2xl);
        padding: 3rem 2.5rem;
        display: flex;
        flex-direction: column;
        transition: all 0.3s var(--ease-out);
        position: relative;
        overflow: hidden;
    }
    .plan-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.08);
        border-color: var(--blue);
    }
    .plan-name {
        font-family: var(--font-display);
        font-size: 1.75rem;
        color: var(--navy);
        margin-bottom: 0.5rem;
    }
    .plan-price {
        font-size: 3rem;
        font-weight: 800;
        color: var(--navy);
        margin-bottom: 2rem;
        display: flex;
        align-items: baseline;
        gap: 0.25rem;
    }
    .plan-price span {
        font-size: 1rem;
        color: var(--muted);
        font-weight: 500;
    }
    .plan-features {
        list-style: none;
        padding: 0;
        margin: 0 0 3rem;
        flex: 1;
    }
    .plan-features li {
        padding: 0.75rem 0;
        color: var(--ink);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1rem;
    }
    .plan-features li::before {
        content: '✓';
        color: var(--green);
        font-weight: 900;
    }
    .plan-btn {
        display: block;
        width: 100%;
        padding: 1.25rem;
        text-align: center;
        border-radius: var(--r-md);
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s;
        background: var(--blue);
        color: white;
    }
    .plan-btn:hover {
        background: var(--blue-dark);
        transform: scale(1.02);
    }
    .plan-card.featured {
        border: 2px solid var(--blue);
    }
    .badge {
        position: absolute;
        top: 1.5rem;
        right: 1.5rem;
        background: var(--blue-light);
        color: var(--blue);
        padding: 0.4rem 1rem;
        border-radius: var(--r-full);
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }
</style>

<div class="plans-root">
    <div class="plans-header">
        <h1>Simple Pricing</h1>
        <p>Unlock professional templates and AI-powered enhancements to land your dream job faster.</p>
        @if(session('status'))
            <div style="margin-top: 2rem; color: var(--blue); font-weight: 600;">{{ session('status') }}</div>
        @endif
    </div>

    <div class="plan-grid">
        @foreach($plans as $plan)
            <div class="plan-card {{ $loop->index === 1 ? 'featured' : '' }}">
                @if($loop->index === 1)
                    <div class="badge">Most Popular</div>
                @endif
                <h2 class="plan-name">{{ $plan->name }}</h2>
                <div class="plan-price">
                    Rs. {{ number_format($plan->price_paise / 100) }}
                    <span>/ {{ $plan->duration_days }} days</span>
                </div>
                
                <ul class="plan-features">
                    <li>{{ is_null($plan->downloads_allowed) ? 'Unlimited' : $plan->downloads_allowed }} High-Quality Downloads</li>
                    <li>Premium ATS-Friendly Templates</li>
                    <li>{{ $plan->ai_enabled ? 'AI Resume Enhancer Included' : 'Standard Document Builder' }}</li>
                    <li>{{ $plan->duration_days }} Days Priority Access</li>
                    <li>Expert Formatting Tools</li>
                </ul>

                @auth
                    <a href="{{ route('plans.checkout', $plan) }}" class="plan-btn">
                        Get Started
                    </a>
                @else
                    <a href="{{ route('login', ['redirect' => route('plans.checkout', $plan)]) }}" class="plan-btn">
                        Login to Buy
                    </a>
                @endauth
            </div>
        @endforeach
    </div>
</div>
@endsection