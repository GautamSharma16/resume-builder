@inject('captcha', 'App\Services\TurnstileCaptcha')

@if($captcha->enabled())
    @once
        @push('scripts')
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        @endpush
    @endonce

    <div class="field-group">
        <div class="cf-turnstile" data-sitekey="{{ $captcha->siteKey() }}" data-theme="dark"></div>
        @error('cf-turnstile-response')
            <p style="color:#ef4444; font-size:13px; margin-top:8px;">{{ $message }}</p>
        @enderror
    </div>
@endif
