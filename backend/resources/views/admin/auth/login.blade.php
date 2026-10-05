{{--
    Staff login + 2FA — AdminLogin.dc.html.
    Wide screens show both steps side by side (current step highlighted); below 760px only the current step shows.
    Static export: "Login" moves to step 2, "Verify & continue" links to the dashboard. No credentials are sent anywhere.
--}}
@extends('admin.layouts.auth')

@section('title', 'Staff login')

@section('content')
<div class="auth" x-data="{
        step: 1,
        showPw: false,
        goStep2() { this.step = 2; this.$nextTick(() => { const el = this.$root.querySelector('.otp__digit:not(.is-filled)'); el && el.focus(); }); },
        goStep1() { this.step = 1; this.$nextTick(() => this.$refs.username.focus()); },
        nextDigit(e) { if (e.target.value && e.target.nextElementSibling) e.target.nextElementSibling.focus(); },
        prevDigit(e) { if (!e.target.value && e.target.previousElementSibling) e.target.previousElementSibling.focus(); }
    }">

    <div class="auth__brand">
        <div class="auth__logo"><span class="auth__logo-name">YOUR BRAND</span><span class="auth__logo-tag">Admin</span></div>
        <p class="auth__tagline">Online store &amp; POS management · for staff only</p>
    </div>

    <div class="auth__cards">
        {{-- Step 1: credentials --}}
        <section class="auth-card is-current" :class="{ 'is-current': step === 1 }" aria-labelledby="login-title">
            <div class="stack" style="--gap: 6px">
                <span class="auth-card__icon"><x-admin.icon name="lock" :size="24" /></span>
                <h1 id="login-title" class="auth-card__title">Staff login</h1>
                <p class="auth-card__lead">Sign in with the email or mobile number your admin registered.</p>
            </div>

            <form class="stack" style="--gap: 18px" action="#" method="post" @submit.prevent="goStep2()">
                <label class="field">Email or mobile number
                    <input class="input input--lg" type="text" name="login" x-ref="username" autocomplete="username" placeholder="name@[DOMAIN] or 01XXXXXXXXX">
                </label>

                <div class="field">
                    <label for="pw" class="field__label">Password</label>
                    <div class="input-group input-group--lg">
                        <input id="pw" name="password" type="password" :type="showPw ? 'text' : 'password'" autocomplete="current-password">
                        <button type="button" class="pw-toggle" @click="showPw = !showPw"
                            aria-pressed="false" :aria-pressed="showPw.toString()"
                            aria-label="Show password" :aria-label="showPw ? 'Hide password' : 'Show password'">
                            <x-admin.icon name="eye" :size="18" /><span x-text="showPw ? 'Hide' : 'Show'">Show</span>
                        </button>
                    </div>
                </div>

                <div class="split" style="--gap: 8px">
                    <label class="check"><input type="checkbox" name="remember" checked>Remember me on this device</label>
                    <a href="#forgot" class="auth-link">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn--brand btn--lg btn--block">Login</button>

                <div class="alert alert--warn">After 3 wrong passwords the account is locked for 15 minutes and the owner gets an SMS alert.</div>
            </form>
        </section>

        {{-- Step 2: verification code --}}
        <section class="auth-card" :class="{ 'is-current': step === 2 }" aria-labelledby="otp-title">
            <div class="stack" style="--gap: 6px">
                <span class="auth-card__eyebrow">Step 2 of 2 · two-factor check</span>
                <h2 id="otp-title" class="auth-card__title auth-card__title--step">Enter verification code</h2>
                <p class="auth-card__lead">We sent a 6-digit code by SMS to <strong>{{ $otpPhone }}</strong>. It expires in 5 minutes.</p>
            </div>

            <fieldset class="otp">
                <legend class="visually-hidden">6-digit verification code</legend>
                <div class="otp__grid">
                    @foreach ($otpDigits as $i => $digit)
                        <input type="text" inputmode="numeric" maxlength="1" pattern="[0-9]*" autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                            class="otp__digit {{ $digit !== '' ? 'is-filled' : '' }}" value="{{ $digit }}" aria-label="Digit {{ $i + 1 }}"
                            @input="$event.target.classList.toggle('is-filled', !!$event.target.value); nextDigit($event)"
                            @keydown.backspace="prevDigit($event)">
                    @endforeach
                </div>
            </fieldset>

            <div class="split fs-14" style="--gap: 8px">
                <span class="muted">Resend code in <strong class="auth-countdown">0:42</strong></span>
                <a href="#backup" class="fw-600">Use a backup code</a>
            </div>

            <label class="check"><input type="checkbox" name="trust_device">Trust this device for 30 days</label>

            <a class="btn btn--brand btn--lg btn--block" href="{{ route('admin.dashboard') }}">Verify &amp; continue</a>
            <button type="button" class="btn btn--outline-strong btn--block" @click="goStep1()">Back to login</button>
        </section>
    </div>

    <div class="auth__foot">
        <span class="auth__foot-secure"><x-admin.icon name="shield" :size="16" :stroke="2" class="text-brand" />Protected area · all actions are logged</span>
        <span class="muted">Your IP and device are recorded at login · <a href="{{ \App\Support\DemoData::storeUrl() }}" class="fw-600">Go to online store</a></span>
    </div>
</div>
@endsection
