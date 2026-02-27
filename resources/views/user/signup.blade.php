@extends('user.master')

@section('title', 'Sign up - Venda Motion Bot')

@section('user.master')

  <main id="page-signup" class="page active">
    <section class="page-hero">
      <div class="container">
        <span class="section-tag">Account</span>
        <h1 class="page-hero-title">Create your <span class="gradient-text">account</span></h1>
      </div>
    </section>
    <section class="section">
      <div class="container">
        <div class="contact-form-wrap" style="max-width: 460px; margin: 0 auto;">
          <h2>Sign up</h2>

          @if ($errors->any())
            <div role="alert" style="margin-bottom: 1rem; border: 1px solid #fecaca; background: #fef2f2; color: #991b1b; border-radius: 10px; padding: 0.75rem 0.9rem;">
              @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
              @endforeach
            </div>
          @endif

          <form class="contact-form" action="{{ route('signup.submit') }}" method="POST" name="signupForm">
            @csrf
            <div class="form-group">
              <label>Email *</label>
              <input type="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div class="form-group">
              <label>WhatsApp Number *</label>
              <input type="tel" name="whatsapp_number" value="{{ old('whatsapp_number') }}" placeholder="+8801XXXXXXXXX" required>
              <small style="color: var(--text-muted); font-size: 12px;">Note: Later you need to verify your WhatsApp number.</small>
            </div>
            <div class="form-group">
              <label>Password *</label>
              <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Sign up</button>
            <a href="{{ route('login.index') }}" class="btn btn-ghost btn-block">Back to login</a>
          </form>
        </div>
      </div>
    </section>
  </main>

@endsection
