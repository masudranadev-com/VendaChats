@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div
    class="order-call-shell"
    data-order-call-page
    data-api-base-url="{{ $orderCallApiBaseUrl }}"
    data-default-page-name="{{ $pageName }}"
    data-default-language="{{ $defaultLanguage }}"
    data-default-call-scope="{{ $defaultCallScope }}"
  >
    <div class="page-header order-call-page-header">
      <div>
        <span class="order-call-eyebrow">Voice Commerce Setup</span>
        <h1 class="page-title">{{ $title }}</h1>
        <p class="page-subtitle">{{ $subtitle }}</p>
      </div>
    </div>

    <section class="order-call-active-stage">
      <div class="order-call-active-top">
        <article class="card order-call-usage-card">
          <div class="order-call-card-top">
            <div>
              <span class="order-call-card-kicker">Call Balance</span>
              <h3 class="card-title">Remaining Calls Available</h3>
              <p>Live call balance and active call settings are loaded directly from the calling configuration API.</p>
            </div>

            <div class="order-call-active-badges">
              <span class="badge badge-info" data-order-call-live-badge>Loading</span>
              <span class="badge badge-info">{{ $packageLabel }}</span>
            </div>
          </div>

          <div class="order-call-usage-hero">
            <div>
              <span class="order-call-usage-label">Available Now</span>
              <strong data-order-call-available-count>--</strong>
              <small data-order-call-available-copy>Loading current call balance...</small>
            </div>
            <div class="order-call-usage-ring" data-order-call-usage-ring style="--usage-progress: 0;">
              <span>
                <strong data-order-call-usage-ring-value>--</strong>
                <small data-order-call-usage-ring-label>Calls Left</small>
              </span>
            </div>
          </div>

          <div class="order-call-usage-progress" aria-hidden="true">
            <span class="order-call-usage-progress-fill" data-order-call-usage-progress-fill style="width: 0%;"></span>
          </div>

          <div class="order-call-usage-meta">
            <article>
              <span>Available</span>
              <strong data-order-call-available-meta>-- Calls</strong>
            </article>
            <article>
              <span>Language</span>
              <strong data-order-call-language-meta>{{ $supportedLanguages[$defaultLanguage] ?? ucfirst($defaultLanguage) }}</strong>
            </article>
            <article>
              <span>Scope</span>
              <strong data-order-call-scope-meta>{{ $defaultCallScope === 'all' ? 'All Buyers' : 'Cash on Delivery Buyers' }}</strong>
            </article>
          </div>
        </article>

        <article class="card order-call-active-status-card">
          <div class="order-call-card-top">
            <div>
              <span class="order-call-card-kicker">Live Summary</span>
              <h3 class="card-title">Current Call State</h3>
              <p>Quick visibility for the settings that matter most while the package is active.</p>
            </div>
          </div>

          <div class="order-call-live-summary">
            <div>
              <span>Call Status</span>
              <strong data-order-call-side-status>Loading...</strong>
            </div>
            <div>
              <span>Page Name</span>
              <strong data-order-call-side-page>{{ $pageName }}</strong>
            </div>
            <div>
              <span>Language</span>
              <strong data-order-call-side-language>{{ $supportedLanguages[$defaultLanguage] ?? ucfirst($defaultLanguage) }}</strong>
            </div>
            <div>
              <span>Buyer Scope</span>
              <strong data-order-call-side-scope>{{ $defaultCallScope === 'all' ? 'All Buyers' : 'Cash on Delivery Buyers' }}</strong>
            </div>
          </div>

          <div class="order-call-active-note">
            <strong>Included With {{ $packageLabel }}</strong>
            <p>This call confirmation service is now part of your main package, so this screen focuses on live settings and your available call balance.</p>
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
            <span class="order-call-config-state is-info" data-order-call-config-badge>Loading</span>
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
                  @foreach ($supportedLanguages as $languageValue => $languageLabel)
                    <option value="{{ $languageValue }}" {{ $languageValue === $defaultLanguage ? 'selected' : '' }}>{{ $languageLabel }}</option>
                  @endforeach
                </select>
                <small class="form-help">The engine still supports automatic fallback. This sets the main language shown in setup.</small>
              </div>
            </div>

            <div class="order-call-scope-block">
              <div class="order-call-scope-head">
                <label class="form-label">Call Buyer Scope</label>
                <p class="order-call-setting-copy">Choose which buyers should receive the automated order confirmation call.</p>
              </div>

              <div class="order-call-scope-grid">
                <label class="order-call-scope-card{{ $defaultCallScope === 'all' ? ' is-active' : '' }}" data-order-call-scope-card>
                  <input
                    type="radio"
                    name="orderCallScope"
                    value="all"
                    data-order-call-scope-input
                    {{ $defaultCallScope === 'all' ? 'checked' : '' }}
                  >
                  <span class="order-call-scope-title">Call All Buyers</span>
                  <small>Use the call flow for every eligible buyer order.</small>
                </label>

                <label class="order-call-scope-card{{ $defaultCallScope === 'cod' ? ' is-active' : '' }}" data-order-call-scope-card>
                  <input
                    type="radio"
                    name="orderCallScope"
                    value="cod"
                    data-order-call-scope-input
                    {{ $defaultCallScope === 'cod' ? 'checked' : '' }}
                  >
                  <span class="order-call-scope-title">Call Cash on Delivery Buyers</span>
                  <small>Limit the call flow to COD orders that need confirmation.</small>
                </label>
              </div>
            </div>

            <div class="order-call-eligibility-note">
              <strong>Physical products only</strong>
              <p>Calling system is available only for physical products. Digital, subscription, and package products will not trigger automated calls.</p>
            </div>

            <div class="order-call-submit-row">
              <button type="button" class="btn btn-primary btn-lg" data-order-call-submit disabled>Loading...</button>
              <div class="order-call-save-pill" data-order-call-save-status>Loading call settings...</div>
            </div>
          </fieldset>
        </article>
      </div>
    </section>
  </div>
@endsection
