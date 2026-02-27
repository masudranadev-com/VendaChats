@php
  $routeName = request()->route()?->getName();
  $routeToPage = [
    'home.index' => 'home',
    'features.index' => 'features',
    'pricing.index' => 'pricing',
    'how-it-works.index' => 'how-it-works',
    'about.index' => 'about',
    'contact.index' => 'contact',
    'login.index' => 'login',
    'signup.index' => 'signup',
    'privacy.index' => 'privacy',
    'terms.index' => 'terms',
    'data-deletion.index' => 'data-deletion',
    'data-deletion.status' => 'data-deletion',
  ];
  $currentPage = $routeToPage[$routeName] ?? 'home';
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Venda Motion Bot')</title>

  {{-- Open Graph / Facebook --}}
  <meta property="og:site_name"   content="Venda Motion Bot">
  <meta property="og:type"        content="website">
  <meta property="og:url"         content="{{ url()->current() }}">
  <meta property="og:title"       content="@yield('og_title', 'Venda Motion Bot – AI Sales Assistant')">
  <meta property="og:description" content="@yield('og_description', 'বাংলাদেশের সেলারদের জন্য সেরা AI সেলস সহকারী। ফেসবুক মেসেঞ্জারে অটোমেটিক অর্ডার, কাস্টমার সার্ভিস ও সেলস ম্যানেজমেন্ট।')">
  <meta property="og:image"       content="@yield('og_image', asset('assets/images/og-image.png'))">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:locale"     content="bn_BD">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
  {{-- <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&amp;family=Noto+Sans+Bengali:wght@400;500;600;700&amp;display=swap" rel="stylesheet"> --}}
  <link rel="stylesheet" href="{{ asset('assets/css/user.css') }}">
</head>
<body data-theme="light">
  <header class="navbar" id="navbar">
    <div class="container nav-inner">
      <a href="{{ route('home.index') }}" class="logo"><span class="logo-icon">⚡</span><span class="logo-text"><span class="logo-accent">Venda</span>Motion</span></a>
      <nav class="nav-links" id="navLinks">
        <a href="{{ route('home.index') }}" class="nav-link {{ $currentPage === 'home' ? 'active' : '' }}" data-page="home">হোম</a>
        <a href="{{ route('features.index') }}" class="nav-link {{ $currentPage === 'features' ? 'active' : '' }}" data-page="features">ফিচারস</a>
        <a href="{{ route('pricing.index') }}" class="nav-link {{ $currentPage === 'pricing' ? 'active' : '' }}" data-page="pricing">প্রাইসিং</a>
        <a href="{{ route('how-it-works.index') }}" class="nav-link {{ $currentPage === 'how-it-works' ? 'active' : '' }}" data-page="how-it-works">কিভাবে কাজ করে</a>
        <a href="{{ route('about.index') }}" class="nav-link {{ $currentPage === 'about' ? 'active' : '' }}" data-page="about">আমাদের সম্পর্কে</a>
        <a href="{{ route('contact.index') }}" class="nav-link {{ $currentPage === 'contact' ? 'active' : '' }}" data-page="contact">যোগাযোগ</a>
      </nav>
      <div class="nav-actions">
        <a href="{{ route('login.index') }}" class="btn btn-primary">লগইন</a> <button class="hamburger" id="hamburger" aria-label="মেনু"><span></span><span></span><span></span></button>
      </div>
    </div>
  </header>

  @yield('user.master')

  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <p>বাংলাদেশের সেলারদের জন্য AI সেলস সহকারী।</p>
        </div>
        <div class="footer-col">
          <h4>প্রোডাক্ট</h4>
          <ul>
            <li>
              <a href="{{ route('features.index') }}">ফিচারস</a>
            </li>
            <li>
              <a href="{{ route('pricing.index') }}">প্রাইসিং</a>
            </li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>কোম্পানি</h4>
          <ul>
            <li>
              <a href="{{ route('about.index') }}">আমাদের সম্পর্কে</a>
            </li>
            <li>
              <a href="{{ route('contact.index') }}">যোগাযোগ</a>
            </li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2026 Venda Motion Bot</p>
        <p>All rights reserved &bull; <a href="{{ route('terms.index') }}">Terms &amp; Conditions</a> | <a href="{{ route('privacy.index') }}">Privacy Policy</a> | <a href="{{ route('data-deletion.index') }}">User Data Deletion</a></p>
      </div>
    </div>
  </footer><button class="back-to-top" id="backToTop" onclick="scrollToTop()" aria-label="উপরে যান">&uarr;</button> <button class="chat-fab" id="chatFab" aria-label="চ্যাট খুলুন" aria-expanded="false" aria-controls="chatWidget"><span class="chat-fab-label">হ্যালো, ডেমো দেখতে এখানে ক্লিক করুন</span> <span class="chat-fab-icon chat-fab-open" aria-hidden="true">💬</span> <span class="chat-fab-icon chat-fab-close" aria-hidden="true">✕</span></button>
  <div class="hero-mockup" id="chatWidget">
    <div class="chat-window">
      <div class="chat-header">
        <div class="chat-avatar">
          🛍️
        </div>
        <div>
          <div class="chat-shop-name">
            ঢাকা ফ্যাশন হাউস
          </div>
          <div class="chat-status">
            <span class="status-dot"></span>অনলাইন
          </div>
        </div><button class="chat-close" id="chatCloseBtn" aria-label="চ্যাট বন্ধ করুন">✕</button>
      </div>
      <div class="chat-body" id="chatBody">
        <div class="chat-msg bot">
          <div class="msg-bubble">
            আসসালামু আলাইকুম! কী জানতে চান?
          </div>
          <div class="msg-time">
            এইমাত্র
          </div>
        </div>
        <div class="chat-msg bot typing" id="typingIndicator" style="display:none;">
          <div class="msg-bubble">
            <span class="dot"></span><span class="dot"></span><span class="dot"></span>
          </div>
        </div>
      </div>
      <div class="chat-responses">
        <form class="chat-form" id="chatForm" name="chatForm">
          <input type="text" id="chatInput" class="chat-input" placeholder="আপনার মেসেজ লিখুন..." autocomplete="off"><button type="submit" class="chat-send">পাঠান</button>
        </form>
      </div>
    </div>
  </div>
  <script>window.__INITIAL_SITE_PAGE = @json($currentPage);</script>
  <script src="{{ asset('assets/js/user.js') }}"></script>
</body>
</html>
