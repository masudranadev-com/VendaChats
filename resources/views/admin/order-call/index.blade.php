@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div
    class="order-call-shell"
    data-order-call-page
    data-default-page-name="{{ $pageName }}"
    data-default-language="{{ $defaultLanguage }}"
    data-default-billing="{{ $defaultBillingCycle }}"
    data-monthly-price="{{ $packagePrice }}"
    data-yearly-price="{{ $packageYearlyPrice }}"
    data-yearly-discount="{{ $packageYearlyDiscount }}"
    data-package-expires-at="{{ $packageExpiresAt }}"
    data-package-expires-label="{{ $packageExpiresLabel }}"
    data-sample-product-title="{{ $productTitle }}"
    data-feature-locked="{{ $featureLocked ? '1' : '0' }}"
    data-subscription-mode="{{ $featureLocked ? 'before' : 'after' }}"
  >
    <div class="page-header order-call-page-header">
      <div>
        <span class="order-call-eyebrow">Voice Commerce Setup</span>
        <h1 class="page-title">{{ $title }}</h1>
        <p class="page-subtitle">{{ $subtitle }}</p>
      </div>

      <div class="order-call-mode-review">
        <span class="order-call-mode-review-label">Preview State</span>
        <div class="order-call-mode-toggle" role="tablist" aria-label="Subscription preview mode">
          <button type="button" class="order-call-mode-btn{{ $featureLocked ? ' is-active' : '' }}" data-order-call-mode-button="before">Before Upgrade</button>
          <button type="button" class="order-call-mode-btn{{ $featureLocked ? '' : ' is-active' }}" data-order-call-mode-button="after">After Upgrade</button>
        </div>
        <small class="order-call-mode-review-note">Temporary switch for UI review. Remove later when real subscription state is connected.</small>
      </div>
    </div>

    <section class="order-call-subscribe-stage" data-order-call-before-panel {{ $featureLocked ? '' : 'hidden' }}>
      <article class="card order-call-plan-showcase">
        <div class="order-call-plan-head">
          <div>
            <span class="badge badge-warning">Subscription System</span>
            <h2>Upgrade to unlock automated order confirmation calls.</h2>
            <p>Before upgrade, this page focuses only on subscription and package value. Choose monthly billing or switch to yearly for a 10% discount.</p>
          </div>

          <div class="order-call-billing-switch" aria-label="Billing cycle switch">
            <button type="button" class="order-call-billing-btn is-active" data-order-call-billing-button="monthly">1 Month</button>
            <button type="button" class="order-call-billing-btn" data-order-call-billing-button="yearly">1 Year</button>
          </div>
        </div>

        <div class="order-call-plan-main">
          <div class="order-call-price-hero">
            <span class="order-call-price-label">Premium order call package</span>
            <strong>BDT <span data-order-call-price-value>{{ number_format($packagePrice) }}</span></strong>
            <small data-order-call-price-term>/month</small>
            <span class="order-call-save-badge" data-order-call-price-save>Pay monthly</span>
            <p data-order-call-price-note>Flexible monthly billing for stores getting started with AI voice confirmation.</p>

            <div class="order-call-plan-actions">
              <button type="button" class="btn btn-primary btn-lg">Subscribe Now</button>
              <button type="button" class="btn btn-ghost btn-lg">Talk to Sales</button>
            </div>
          </div>

          <div class="order-call-plan-side">
            <div class="order-call-plan-side-card">
              <span>What unlocks</span>
              <strong>Live call confirmation flow</strong>
              <p>Customers hear the store name, the ordered product, and can press 1 to confirm or 2 to cancel.</p>
            </div>

            <div class="order-call-plan-side-card">
              <span>Language coverage</span>
              <strong>{{ count($supportedLanguages) }} major languages</strong>
              <p>Built to cover Bangla, English, Arabic, Hindi, and other large Facebook-market languages.</p>
            </div>
          </div>
        </div>

        <div class="order-call-plan-feature-grid">
          @foreach ($benefits as $benefit)
            <article class="order-call-plan-feature">
              <strong>{{ $benefit['title'] }}</strong>
              <p>{{ $benefit['copy'] }}</p>
            </article>
          @endforeach
        </div>

        <div class="order-call-plan-footer">
          <div>
            <span class="order-call-footer-label">Included Languages</span>
            <div class="order-call-language-cloud">
              @foreach ($supportedLanguages as $language)
                <span>{{ $language }}</span>
              @endforeach
            </div>
          </div>

          <div class="order-call-plan-yearly-note">
            <strong>Yearly billing saves {{ $packageYearlyDiscount }}%</strong>
            <p>Switch to the 1 year option to reduce cost while keeping the same features and setup flow.</p>
          </div>
        </div>
      </article>
    </section>

    <section class="order-call-active-stage" data-order-call-after-panel {{ $featureLocked ? 'hidden' : '' }}>
      <div class="order-call-active-top">
        <article class="card order-call-countdown-card">
          <div class="order-call-card-top">
            <div>
              <span class="order-call-card-kicker">Active Package</span>
              <h3 class="card-title">Package Expiry Countdown</h3>
              <p>Your live package view should center on renewal urgency and remaining active time.</p>
            </div>

            <div class="order-call-active-badges">
              <span class="badge badge-success">Subscribed</span>
              <span class="badge badge-info" data-order-call-active-billing>Monthly Plan</span>
            </div>
          </div>

          <div class="order-call-countdown-grid">
            <article>
              <strong data-order-call-countdown-days>00</strong>
              <span>Days</span>
            </article>
            <article>
              <strong data-order-call-countdown-hours>00</strong>
              <span>Hours</span>
            </article>
            <article>
              <strong data-order-call-countdown-minutes>00</strong>
              <span>Minutes</span>
            </article>
            <article>
              <strong data-order-call-countdown-seconds>00</strong>
              <span>Seconds</span>
            </article>
          </div>

          <div class="order-call-expiry-meta">
            <div>
              <span>Expires On</span>
              <strong data-order-call-expiry-label>{{ $packageExpiresLabel }}</strong>
            </div>
            <div>
              <span>Status</span>
              <strong data-order-call-countdown-note>Renew before expiry to keep automated calls active.</strong>
            </div>
          </div>
        </article>

        <article class="card order-call-active-status-card">
          <div class="order-call-card-top">
            <div>
              <span class="order-call-card-kicker">Live Summary</span>
              <h3 class="card-title">Current Package State</h3>
              <p>Quick visibility for the settings that matter most while the package is active.</p>
            </div>
          </div>

          <div class="order-call-live-summary">
            <div>
              <span>Call Status</span>
              <strong data-order-call-side-status>Off</strong>
            </div>
            <div>
              <span>Page Name</span>
              <strong data-order-call-side-page>{{ $pageName }}</strong>
            </div>
            <div>
              <span>Language</span>
              <strong data-order-call-side-language>{{ $defaultLanguage }}</strong>
            </div>
          </div>

          <div class="order-call-active-note">
            <strong>Renewal Reminder</strong>
            <p>When the countdown gets low, this panel should make renewal and package status obvious without opening another screen.</p>
          </div>
        </article>
      </div>

      <div class="order-call-active-layout">
        <article class="card order-call-settings-card">
          <div class="order-call-card-top">
            <div>
              <span class="order-call-card-kicker">Settings</span>
              <h3 class="card-title">Live Call Configuration</h3>
              <p>Edit On/Off, page name, and primary language directly from the active package screen.</p>
            </div>
            <span class="order-call-config-state is-success" data-order-call-config-badge>Ready to Edit</span>
          </div>

          <fieldset class="order-call-settings-fieldset" data-order-call-settings-fields>
            <div class="order-call-setting-switch-row">
              <div>
                <label class="form-label">Order Call</label>
                <p class="order-call-setting-copy">Turn automated confirmation calls on or off for new incoming orders.</p>
              </div>

              <div class="order-call-setting-switch-box">
                <span class="order-call-setting-state" data-order-call-enabled-label>Off</span>
                <label class="bot-switch">
                  <input class="bot-toggle-input" type="checkbox" data-order-call-enabled-input>
                  <span class="bot-switch-ui"></span>
                </label>
              </div>
            </div>

            <div class="order-call-settings-grid">
              <div class="form-group">
                <label class="form-label" for="orderCallPageName">Page Name</label>
                <input
                  id="orderCallPageName"
                  type="text"
                  class="form-input"
                  value="{{ $pageName }}"
                  maxlength="80"
                  placeholder="Enter your store or Facebook page name"
                  data-order-call-page-name-input
                >
                <small class="form-help">Used inside the greeting and also synced to product voice previews.</small>
              </div>

              <div class="form-group">
                <label class="form-label" for="orderCallLanguage">Primary Language</label>
                <select id="orderCallLanguage" class="form-select" data-order-call-language-input>
                  @foreach ($supportedLanguages as $language)
                    <option value="{{ $language }}" {{ $language === $defaultLanguage ? 'selected' : '' }}>{{ $language }}</option>
                  @endforeach
                </select>
                <small class="form-help">The engine still supports automatic fallback. This sets the main language shown in setup.</small>
              </div>
            </div>

            <div class="order-call-submit-row">
              <button type="button" class="btn btn-primary btn-lg" data-order-call-submit>Save Settings</button>
              <div class="order-call-save-pill" data-order-call-save-status>Preview updated. Save when you are ready.</div>
            </div>
          </fieldset>
        </article>
      </div>
    </section>
  </div>
@endsection
