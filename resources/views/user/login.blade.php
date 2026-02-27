@extends('user.master')

@section('title', 'Login - Venda Motion Bot')

@section('user.master')

  <main id="page-login" class="page active">
    <section class="page-hero">
      <div class="container">
        <span class="section-tag">Account</span>
        <h1 class="page-hero-title">Login to your <span class="gradient-text">account</span></h1>
      </div>
    </section>
    <section class="section">
      <div class="container">
        <div class="contact-form-wrap" style="max-width: 460px; margin: 0 auto;">
          <h2>Login</h2>

          @if (session('status'))
            <div role="status" style="margin-bottom: 1rem; border: 1px solid #bbf7d0; background: #f0fdf4; color: #166534; border-radius: 10px; padding: 0.75rem 0.9rem;">
              {{ session('status') }}
            </div>
          @endif

          @if ($errors->has('login'))
            <div class="bot-settings-alert error" role="alert" style="margin-bottom: 1rem;">
              <span>!</span>
              <span>{{ $errors->first('login') }}</span>
            </div>
          @endif

          <form class="contact-form" id="loginForm" action="{{ route('login.submit') }}" method="POST" name="loginForm">
            @csrf
            <div class="form-group">
              <label>Email *</label>
              <input type="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div class="form-group">
              <label>Password *</label>
              <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
            <a href="{{ route('signup.index') }}" class="btn btn-ghost btn-block">Sign up</a>
          </form>
        </div>
      </div>
    </section>
  </main>

@endsection
