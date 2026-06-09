@extends('layouts.auth')

@section('title', 'Login - SIAPABAJA')

@section('content')
@php
    $role = request('role', 'ppk');
    $hasError = session()->has('errors') || session('status') || request()->boolean('error');
@endphp

<section class="login-figma">
  <div class="login-figma-bg">

    {{-- Banner error --}}
    @if($hasError)
      <div class="login-error">
        @if($errors->has('email'))
          {{ $errors->first('email') }}
        @elseif($errors->has('g-recaptcha-response'))
          {{ $errors->first('g-recaptcha-response') }}
        @elseif(session('status'))
          {{ session('status') }}
        @else
          Email atau Kata Sandi salah!
        @endif
      </div>
    @endif

    <div class="login-figma-card">
      <h2 class="login-figma-title">Masuk</h2>

      <p class="login-figma-desc">
        Silakan masukkan email dan kata sandi Anda untuk melanjutkan.
      </p>

      <form class="login-figma-form" id="loginForm" action="{{ url('/login') }}" method="POST">
        @csrf

        <input type="hidden" name="role" value="{{ $role }}">

        {{-- EMAIL --}}
        <div class="fg">
          <label>Email</label>
          <input
            type="email"
            id="email"
            name="email"
            autocomplete="email"
            value="{{ old('email') }}"
            required
            autofocus
          >
          @error('email')
            <span class="error-text">{{ $message }}</span>
          @enderror
        </div>

        {{-- PASSWORD --}}
        <div class="fg">
          <label>Kata Sandi</label>
          <input
            type="password"
            id="password"
            name="password"
            autocomplete="current-password"
            required
          >
          @error('password')
            <span class="error-text">{{ $message }}</span>
          @enderror
        </div>

        {{-- CAPTCHA --}}
        <div class="fg" style="margin-top:20px;">
          <label style="display:block; text-align:center; margin-bottom:10px;">
            Verifikasi Keamanan
          </label>

          <div style="display:flex; justify-content:center;">
            <div class="g-recaptcha"
                 data-sitekey="{{ config('services.recaptcha.site_key') }}">
            </div>
          </div>

          @error('g-recaptcha-response')
            <div style="text-align:center; margin-top:8px;">
              <span class="error-text">{{ $message }}</span>
            </div>
          @enderror
        </div>

        {{-- BUTTON --}}
        <button class="fg-btn" type="submit">Masuk</button>

        <a class="fg-back" href="{{ url('/') }}">
          Kembali
        </a>
      </form>
    </div>
  </div>
</section>

{{-- ✅ WAJIB: SCRIPT RECAPTC"HA --}}
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

@endsection