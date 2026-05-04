@php
    $plans = $plans ?? collect();
@endphp

<div id="plan-download-modal" class="fixed inset-0 z-[1200] hidden items-center justify-center bg-slate-950/70 px-4 py-6 backdrop-blur-sm">
    <div class="modal-fade-in max-h-[92vh] w-full max-w-6xl overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4 sm:px-8">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Choose your plan</p>
                <h2 class="mt-1 text-2xl font-black text-gray-950">Unlock downloads</h2>
            </div>
            <button type="button" data-close-plan-modal class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 text-gray-500 transition hover:border-gray-300 hover:bg-gray-50" aria-label="Close pricing plans">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="modal-scroll-container max-h-[calc(92vh-88px)] overflow-y-auto px-5 py-6 sm:px-8">
            <div class="grid gap-5 lg:grid-cols-3">
                @foreach($plans as $index => $plan)
                    <div class="relative rounded-2xl border {{ $index === 1 ? 'border-blue-600 bg-slate-950 text-white shadow-xl' : 'border-gray-200 bg-white text-gray-950 shadow-sm' }} p-6">
                        @if($index === 1)
                            <div class="absolute right-5 top-5 rounded-full bg-blue-600 px-3 py-1 text-xs font-bold text-white">Most Popular</div>
                        @endif
                        <div class="mb-5 inline-flex h-12 w-12 items-center justify-center rounded-2xl {{ $index === 1 ? 'bg-white/10 text-white' : 'bg-blue-50 text-blue-600' }}">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m9-9H3"/></svg>
                        </div>
                        <h3 class="text-sm font-bold uppercase tracking-[0.18em] {{ $index === 1 ? 'text-white/60' : 'text-gray-500' }}">{{ $plan->name }}</h3>
                        <div class="mt-3 flex items-end gap-1">
                            <span class="text-xl font-black">Rs.</span>
                            <span class="text-5xl font-black tracking-normal">{{ number_format($plan->price_paise / 100, 0) }}</span>
                        </div>
                        <p class="mt-2 text-sm {{ $index === 1 ? 'text-white/60' : 'text-gray-500' }}">valid for {{ $plan->duration_days }} days</p>
                        <div class="my-5 h-px {{ $index === 1 ? 'bg-white/10' : 'bg-gray-100' }}"></div>
                        <ul class="space-y-3 text-sm font-semibold">
                            <li>{{ $plan->resume_limit ?: 'Unlimited' }} Resumes</li>
                            <li>{{ $plan->cover_letter_limit ?: 'Unlimited' }} Cover Letters</li>
                            <li>{{ $plan->ai_enabled ? 'Advanced AI Features' : 'Basic Features' }}</li>
                            <li>{{ $plan->downloads_allowed ?: 'Unlimited' }} Downloads</li>
                        </ul>
                        <a href="{{ auth()->check() ? route('plans.checkout', $plan) : route('login', ['redirect' => route('plans.checkout', $plan)]) }}" class="mt-6 inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-black transition {{ $index === 1 ? 'bg-blue-600 text-white hover:bg-blue-500' : 'bg-slate-950 text-white hover:bg-slate-800' }}">
                            Choose {{ $plan->name }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const modal = document.getElementById('plan-download-modal');
    if (!modal) return;

    const open = () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    };

    const close = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    };

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-open-plan-modal]');
        if (trigger) {
            event.preventDefault();
            open();
            return;
        }

        if (event.target === modal || event.target.closest('[data-close-plan-modal]')) {
            event.preventDefault();
            close();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) close();
    });

    window.openPlanDownloadModal = open;
})();
</script>
@endpush
