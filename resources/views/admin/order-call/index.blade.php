@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div
    class="order-call-shell"
    data-order-call-page
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
              <p>Show the remaining call balance from your main package here. This is static demo data for now and can be wired dynamically later.</p>
            </div>

            <div class="order-call-active-badges">
              <span class="badge badge-success">Active</span>
              <span class="badge badge-info">{{ $packageLabel }}</span>
            </div>
          </div>

          <div class="order-call-usage-hero">
            <div>
              <span class="order-call-usage-label">Available Now</span>
              <strong>{{ $remainingCalls }}</strong>
              <small>Out of {{ $totalCalls }} calls included</small>
            </div>
            <div class="order-call-usage-ring" style="--usage-progress: {{ $remainingCallPercent }};">
              <span>
                <strong>{{ $remainingCallPercent }}%</strong>
                <small>Left</small>
              </span>
            </div>
          </div>

          <div class="order-call-usage-progress" aria-hidden="true">
            <span class="order-call-usage-progress-fill" style="width: {{ $remainingCallPercent }}%;"></span>
          </div>

          <div class="order-call-usage-meta">
            <article>
              <span>Remaining</span>
              <strong>{{ $remainingCalls }} Calls</strong>
            </article>
            <article>
              <span>Used</span>
              <strong>{{ $usedCalls }} Calls</strong>
            </article>
            <article>
              <span>Total Limit</span>
              <strong>{{ $totalCalls }} Calls</strong>
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
            <div>
              <span>Buyer Scope</span>
              <strong data-order-call-side-scope>{{ $defaultCallScope === 'all_buyers' ? 'All Buyers' : 'Cash on Delivery Buyers' }}</strong>
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

            <div class="order-call-scope-block">
              <div class="order-call-scope-head">
                <label class="form-label">Call Buyer Scope</label>
                <p class="order-call-setting-copy">Choose which buyers should receive the automated order confirmation call.</p>
              </div>

              <div class="order-call-scope-grid">
                <label class="order-call-scope-card{{ $defaultCallScope === 'all_buyers' ? ' is-active' : '' }}" data-order-call-scope-card>
                  <input
                    type="radio"
                    name="orderCallScope"
                    value="all_buyers"
                    data-order-call-scope-input
                    {{ $defaultCallScope === 'all_buyers' ? 'checked' : '' }}
                  >
                  <span class="order-call-scope-title">Call All Buyers</span>
                  <small>Use the call flow for every eligible buyer order.</small>
                </label>

                <label class="order-call-scope-card{{ $defaultCallScope === 'cash_on_delivery' ? ' is-active' : '' }}" data-order-call-scope-card>
                  <input
                    type="radio"
                    name="orderCallScope"
                    value="cash_on_delivery"
                    data-order-call-scope-input
                    {{ $defaultCallScope === 'cash_on_delivery' ? 'checked' : '' }}
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
              <button type="button" class="btn btn-primary btn-lg" data-order-call-submit>Save Settings</button>
              <div class="order-call-save-pill" data-order-call-save-status>Preview updated. Save when you are ready.</div>
            </div>
          </fieldset>
        </article>
      </div>
    </section>
  </div>
@endsection
