@extends('user.master')

@section('title', 'Venda Motion Bot - হোম')

@section('user.master')

  <main id="page-home" class="page active">
    <section class="hero">
      <div class="hero-bg">
        <div class="hero-orb orb1"></div>
        <div class="hero-orb orb2"></div>
        <div class="hero-orb orb3"></div>
        <div class="hero-grid"></div>
      </div>
      <div class="container hero-content">
        <div class="hero-badge">
          <span>এখন বাংলাদেশে লাইভ</span>
        </div>
        <h1 class="hero-title"><span class="gradient-text">নিজে নিজে চলে</span> AI দিয়ে</h1>
        <p class="hero-sub">ইনবক্স, পোস্ট কমেন্ট, দরদাম, WhatsApp recovery এবং courier booking অটোমেট করুন।</p>
        <div class="hero-cta">
          <a href="{{ route('pricing.index') }}" class="btn btn-primary btn-lg">ফ্রি ট্রায়াল শুরু করুন</a> <a href="{{ route('how-it-works.index') }}" class="btn btn-outline btn-lg">ডেমো দেখুন</a>
        </div>
      </div>
    </section>
    <section class="section features-preview">
      <div class="container">
        <div class="section-header">
          <span class="section-tag">আমাদের ফোকাস</span>
          <h2 class="section-title">Venda Motion এর জন্য বাস্তব সমাধান</h2>
        </div>
        <div class="features-grid">
          <a class="feature-card" href="{{ route('features.index') }}">
          <div class="fc-icon">
            📸
          </div>
          <h3>কমেন্ট অটো-রিপ্লাই</h3>
          <p>পোস্ট কমেন্ট থেকে buyer capture করে instant reply দিন।</p></a> <a class="feature-card" href="{{ route('features.index') }}">
          <div class="fc-icon">
            💬
          </div>
          <h3>স্মার্ট দরদাম</h3>
          <p>মার্জিন রেখে স্বয়ংক্রিয় negotiation।</p></a> <a class="feature-card" href="{{ route('features.index') }}">
          <div class="fc-icon">
            📱
          </div>
          <h3>WhatsApp রিকভারি</h3>
          <p>হারানো বিক্রি ফেরত আনুন।</p></a>
        </div>
      </div>
    </section>
    <section class="section">
      <div class="container">
        <div class="section-header">
          <span class="section-tag">প্রাইসিং প্রিভিউ</span>
          <h2 class="section-title">আপনার বাজেট অনুযায়ী প্ল্যান</h2>
          <p class="section-sub">Starter থেকে Pro, সব ধরনের seller-এর জন্য scale-ready প্যাকেজ।</p>
        </div>
        <div class="pricing-grid">
          <div class="pricing-card">
            <div class="plan-name">
              স্টার্টার
            </div>
            <div class="plan-price">
              <span class="price-currency">৳</span><span class="price-amount">999</span><span class="price-period">/মাস</span>
            </div>
            <p class="plan-desc">নতুন seller-এর জন্য দ্রুত শুরু।</p>
          </div>
          <div class="pricing-card pricing-card--popular">
            <div class="popular-badge">
              জনপ্রিয়
            </div>
            <div class="plan-name">
              গ্রোথ
            </div>
            <div class="plan-price">
              <span class="price-currency">৳</span><span class="price-amount">2499</span><span class="price-period">/মাস</span>
            </div>
            <p class="plan-desc">ব্যবসা বাড়ানোর জন্য most balanced plan।</p>
          </div>
          <div class="pricing-card">
            <div class="plan-name">
              প্রো
            </div>
            <div class="plan-price">
              <span class="price-currency">৳</span><span class="price-amount">4999</span><span class="price-period">/মাস</span>
            </div>
            <p class="plan-desc">উচ্চ ভলিউম ও ব্র্যান্ড স্কেলিংয়ের জন্য।</p>
          </div>
        </div>
        <div class="text-center mt-40">
          <a href="{{ route('pricing.index') }}" class="btn btn-primary">সম্পূর্ণ প্রাইসিং দেখুন</a>
        </div>
      </div>
    </section>
    <section class="section features-preview">
      <div class="container">
        <div class="section-header">
          <span class="section-tag">কিভাবে কাজ করে</span>
          <h2 class="section-title">৩ ধাপে লাইভ</h2>
          <p class="section-sub">Page connect করুন, product setup দিন, তারপর bot 24/7 inbox + comment handle করবে।</p>
        </div>
        <div class="steps-grid">
          <div class="step-card">
            <div class="step-num">
              01
            </div>
            <h3>পেজ কানেক্ট</h3>
            <p>এক ক্লিকে Facebook page যুক্ত করুন।</p>
          </div>
          <div class="step-connector">
            →
          </div>
          <div class="step-card">
            <div class="step-num">
              02
            </div>
            <h3>সেটআপ</h3>
            <p>দাম, স্টক ও offer rule সেট করুন।</p>
          </div>
          <div class="step-connector">
            →
          </div>
          <div class="step-card">
            <div class="step-num">
              03
            </div>
            <h3>অটো সেলস</h3>
            <p>বট order, inbox reply, comment reply, follow-up সব করবে।</p>
          </div>
        </div>
        <div class="text-center mt-40">
          <a href="{{ route('how-it-works.index') }}" class="btn btn-outline">আরও জানুন</a>
        </div>
      </div>
    </section>
    <section class="section">
      <div class="container">
        <div class="section-header">
          <span class="section-tag">আমাদের সম্পর্কে</span>
          <h2 class="section-title">লোকাল টিম, লোকাল সমস্যা সমাধান</h2>
          <p class="section-sub">Venda Motion seller-এর daily pain-point থেকে তৈরি practical product।</p>
        </div>
        <div class="about-visual">
          <div class="about-card">
            <div class="about-stat">
              2022
            </div>
            <div class="about-stat-label">
              শুরু
            </div>
          </div>
          <div class="about-card">
            <div class="about-stat">
              10K+
            </div>
            <div class="about-stat-label">
              সেলার
            </div>
          </div>
          <div class="about-card">
            <div class="about-stat">
              Bangladesh
            </div>
            <div class="about-stat-label">
              কেন্দ্র
            </div>
          </div>
        </div>
        <div class="text-center mt-40">
          <a href="{{ route('about.index') }}" class="btn btn-primary">আমাদের গল্প পড়ুন</a>
        </div>
      </div>
    </section>
    <section class="section features-preview">
      <div class="container">
        <div class="section-header">
          <span class="section-tag">যোগাযোগ</span>
          <h2 class="section-title">ডেমো বা সাপোর্ট লাগবে?</h2>
          <p class="section-sub">WhatsApp, Email বা form submit করে দ্রুত যোগাযোগ করুন।</p>
        </div>
        <div class="text-center">
          <a href="{{ route('contact.index') }}" class="btn btn-primary btn-lg">যোগাযোগ পেজে যান</a>
        </div>
      </div>
    </section>
  </main>

@endsection
