@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div
    data-order-call-page
    data-default-page-name="{{ $pageName }}"
    data-sample-product-title="{{ $productTitle }}"
  >
    <div class="page-header order-call-page-header">
      <div>
        <h1 class="page-title">{{ $title }}</h1>
        <p class="page-subtitle">{{ $subtitle }}</p>
      </div>
    </div>

    <section class="order-call-stats-grid">
      @foreach ($stats as $stat)
        <article class="order-call-stat-card is-{{ $stat['tone'] }}">
          <span>{{ $stat['label'] }}</span>
          <strong>{{ $stat['value'] }}</strong>
          <small>{{ $stat['meta'] }}</small>
        </article>
      @endforeach
    </section>

    <section class="order-call-control-grid">
      <article class="card">
        <div class="card-header">
          <h3 class="card-title">Token Controls</h3>
          <span class="badge badge-success">Easy Setup</span>
        </div>

        <div class="order-call-control-stack">
          <div class="form-group mb-0">
            <label class="form-label" for="orderCallPageName">Page Name</label>
            <input
              id="orderCallPageName"
              type="text"
              class="form-input"
              value="{{ $pageName }}"
              maxlength="80"
              placeholder="Enter your store or page name"
              data-order-call-page-name-input
            >
            <small class="form-help">This is the only value you set from this page. It is used automatically in the call greeting and saved for product voice previews on this browser.</small>
          </div>

          <div class="order-call-autosave" data-order-call-save-status>
            Auto-saving preview settings...
          </div>

          <div class="order-call-token-list">
            @foreach ($scriptTokens as $token)
              <article class="order-call-token-item">
                <span>{{ $token['token'] }}</span>
                <strong>{{ $token['label'] }}</strong>
                <p>{{ $token['copy'] }}</p>
              </article>
            @endforeach
          </div>
        </div>
      </article>

      <article class="card">
        <div class="card-header">
          <h3 class="card-title">Language Support</h3>
          <span class="badge badge-info">Automatic</span>
        </div>

        <p class="order-call-language-copy">The script wording stays locked. AI handles language adaptation automatically for customers, while you review Bangla and English sample previews below.</p>

        <div class="order-call-language-chips">
          @foreach ($supportedLanguages as $language)
            <span>{{ $language }}</span>
          @endforeach
        </div>

        <div class="order-call-language-note">
          <strong>Customer language is automatic.</strong>
          <p>You do not rewrite the script for each language. The same master flow is reused with the correct language presentation.</p>
        </div>
      </article>
    </section>

    <section class="order-call-hero">
      <article class="card order-call-preview-card">
        <div class="card-header">
          <h3 class="card-title">Locked Script Preview</h3>
          <div class="products-card-tools">
            <span class="badge badge-info">Bangla + English Samples</span>
            <span class="badge badge-warning">Body Not Editable</span>
          </div>
        </div>

        <div class="order-call-preview-top">
          <div>
            <span class="order-call-preview-label">Sample Product Voice</span>
            <strong data-order-call-preview-product-title>{{ $productTitle }}</strong>
            <p>Using <span data-order-call-preview-page-name>{{ $pageName }}</span> as the page name. Actual product title is always taken from product create/edit.</p>
          </div>
          <div class="order-call-preview-meta">
            <span>Auto from product title</span>
            <strong>00:18</strong>
          </div>
        </div>

        <div class="order-call-script-lock-note">
          The master script is fixed. Only <code>{PAGE_NAME}</code> and <code>{PRODUCT_TITLE}</code> change.
        </div>

        <div class="order-call-player">
          <button type="button" class="order-call-player-btn" aria-label="Demo voice preview">▶</button>
          <div class="order-call-waveform" aria-hidden="true">
            @foreach ($voiceWave as $height)
              <span style="height: {{ $height }}px"></span>
            @endforeach
          </div>
        </div>

        <div class="order-call-script-grid">
          <article class="order-call-script-card">
            <span class="order-call-script-tag">Bangla Sample</span>
            <p data-order-call-script-bn>{{ $scripts['bangla'] }}</p>
          </article>

          <article class="order-call-script-card">
            <span class="order-call-script-tag">English Sample</span>
            <p data-order-call-script-en>{{ $scripts['english'] }}</p>
          </article>
        </div>
      </article>

      <aside class="card order-call-pricing-card" id="pricing">
        <div class="order-call-pricing-head">
          <span class="badge badge-success">Premium Add-on</span>
          <h3>Enable for BDT {{ number_format($packagePrice) }}/month</h3>
          <p>This feature is currently unavailable in your plan. Subscribe to unlock automated call confirmation and cancellation voice generation.</p>
        </div>

        <div class="order-call-price-box">
          <span>Package price</span>
          <strong>BDT {{ number_format($packagePrice) }}</strong>
          <span>per month</span>
        </div>

        <ul class="order-call-checklist">
          <li>Locked script engine with token-based personalization</li>
          <li>All-language support through automatic AI adaptation</li>
          <li>Page name set once from this screen</li>
          <li>Product title pulled from product create/edit automatically</li>
          <li>1 = confirm, 2 = cancel keypad flow</li>
        </ul>

        <div class="order-call-pricing-actions">
          <button type="button" class="btn btn-primary">Subscribe Package</button>
          <button type="button" class="btn btn-ghost">Talk to Sales</button>
        </div>

        <small class="order-call-pricing-note">UI only for now. Payment, activation, language engine, and AI generation backend can be connected later without changing this screen structure.</small>
      </aside>
    </section>

    <section class="order-call-content-grid mt-xl">
      <article class="card">
        <div class="card-header">
          <h3 class="card-title">What Unlocks After Activation</h3>
          <span class="badge badge-primary">Feature Scope</span>
        </div>

        <div class="order-call-benefit-grid">
          @foreach ($benefits as $benefit)
            <article class="order-call-benefit-card">
              <strong>{{ $benefit['title'] }}</strong>
              <p>{{ $benefit['copy'] }}</p>
            </article>
          @endforeach
        </div>
      </article>

      <article class="card">
        <div class="card-header">
          <h3 class="card-title">Activation Flow</h3>
          <span class="badge badge-warning">Locked State</span>
        </div>

        <div class="order-call-steps">
          @foreach ($activationSteps as $index => $step)
            <div class="order-call-step">
              <span class="order-call-step-num">{{ $index + 1 }}</span>
              <p>{{ $step }}</p>
            </div>
          @endforeach
        </div>
      </article>
    </section>

    <section class="card mt-xl">
      <div class="card-header">
        <h3 class="card-title">Product Voice Library Preview</h3>
        <span class="badge badge-info">Auto Generated</span>
      </div>

      <div class="order-call-library-list">
        @foreach ($recentVoices as $voice)
          <article class="order-call-library-item">
            <div>
              <strong>{{ $voice['title'] }}</strong>
              <p>{{ $voice['language'] }} voice pack</p>
            </div>
            <div class="order-call-library-meta">
              <span>{{ $voice['duration'] }}</span>
              <small>{{ $voice['status'] }}</small>
            </div>
          </article>
        @endforeach
      </div>
    </section>
  </div>
@endsection
