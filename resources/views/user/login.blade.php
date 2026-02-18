@extends('user.master')

@section('title', 'লগইন - Venda Motion Bot')

@section('user.master')

  <main id="page-login" class="page active">
    <section class="page-hero">
      <div class="container">
        <span class="section-tag">Account</span>
        <h1 class="page-hero-title">আপনার <span class="gradient-text">অ্যাকাউন্টে লগইন</span></h1>
      </div>
    </section>
    <section class="section">
      <div class="container contact-container">
        <div class="contact-info">
          <h2>স্বাগতম</h2>
          <div class="contact-options">
            <div class="contact-option">
              <div class="co-icon">
                🔒
              </div>
              <div>
                <h4>নিরাপদ লগইন</h4>
                <p>আপনার তথ্য এনক্রিপটেড চ্যানেলে পাঠানো হয়।</p>
              </div>
            </div>
            <div class="contact-option">
              <div class="co-icon">
                ⚡
              </div>
              <div>
                <h4>দ্রুত অ্যাক্সেস</h4>
                <p>লগইন করলেই আপনার ড্যাশবোর্ড প্রস্তুত।</p>
              </div>
            </div>
          </div>
        </div>
        <div class="contact-form-wrap">
          <h2>Continue to your account</h2>
          <div class="auth-choice" id="authChoice">
            <button type="button" id="googleAuthBtn" class="btn btn-block auth-option-btn google-auth-btn" aria-label="Continue with Google"><span class="google-icon" aria-hidden="true"><svg viewbox="0 0 48 48" width="20" height="20" role="img">
            <path fill="#FFC107" d="M43.61 20.08H42V20H24v8h11.3C33.65 32.66 29.2 36 24 36c-6.63 0-12-5.37-12-12s5.37-12 12-12c3.06 0 5.84 1.15 7.96 3.04l5.66-5.66C34.4 6.54 29.46 4 24 4 12.95 4 4 12.95 4 24s8.95 20 20 20 20-8.95 20-20c0-1.34-.14-2.65-.39-3.92z"></path>
            <path fill="#FF3D00" d="M6.31 14.69l6.57 4.82C14.65 15.11 18.96 12 24 12c3.06 0 5.84 1.15 7.96 3.04l5.66-5.66C34.4 6.54 29.46 4 24 4c-7.68 0-14.35 4.34-17.69 10.69z"></path>
            <path fill="#4CAF50" d="M24 44c5.34 0 10.2-2.05 13.88-5.39l-6.41-5.42C29.41 34.74 26.83 36 24 36c-5.18 0-9.62-3.31-11.28-7.92l-6.52 5.02C9.5 39.56 16.2 44 24 44z"></path>
            <path fill="#1976D2" d="M43.61 20.08H42V20H24v8h11.3c-.79 2.24-2.23 4.17-3.83 5.58l.01-.01 6.41 5.42C37.43 38.59 44 34 44 24c0-1.34-.14-2.65-.39-3.92z"></path></svg></span> <span>Continue with Google</span></button> <button type="button" id="emailAuthBtn" class="btn btn-block auth-option-btn email-auth-btn" aria-label="Continue with Email"><span>Continue with Email</span></button>
            <a href="{{ route('facebook.oauth') }}" class="btn btn-block auth-option-btn" aria-label="Continue with Facebook OAuth"><span>Continue with Facebook OAuth</span></a>
          </div>
          <form class="contact-form auth-email-form" id="loginForm" hidden="" name="loginForm">
            <div class="form-group">
              <label>ইমেইল *</label> <input type="email" required="">
            </div>
            <div class="form-group">
              <label>পাসওয়ার্ড *</label> <input type="password" required="">
            </div><button type="submit" class="btn btn-primary btn-block">Continue with Email</button>
          </form><button type="button" class="btn btn-ghost btn-block auth-back-btn" id="authBackBtn" hidden="">Back to all options</button>
          <div class="form-success" id="loginSuccess">
            <h3>লগইন সফল (ডেমো)</h3>
            <p>ড্যাশবোর্ড ইন্টিগ্রেশন হলে এখান থেকে রিডাইরেক্ট হবে।</p>
          </div>
        </div>
      </div>
    </section>
  </main>

@endsection
