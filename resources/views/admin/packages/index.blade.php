@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="billing-page-shell" data-packages-page data-api-base-url="{{ $packagesApiBaseUrl }}">
    <div class="page-header billing-page-header">
      <div>
        <h1 class="page-title">{{ $title }}</h1>
        <p class="page-subtitle">{{ $subtitle }}</p>
      </div>

      <div class="packages-header-actions">
        <a href="#packagesCatalog" class="btn btn-primary billing-header-cta">Browse Packages</a>
        <button type="button" class="btn btn-secondary" data-packages-reload>Refresh Data</button>
      </div>
    </div>

    <section class="billing-hero">
      <article class="billing-status-card" data-packages-summary-card aria-busy="true">
        <div class="billing-status-top">
          <div>
            <span class="billing-eyebrow">Current Package</span>
            <h2 data-packages-current-title>Loading current package...</h2>
            <p data-packages-current-description>Fetching your active package details from the live package API.</p>
          </div>

          <span class="billing-payment-badge is-info" data-packages-current-status>Loading</span>
        </div>

        <div class="billing-status-main">
          <div class="billing-price-block">
            <strong data-packages-current-price>--</strong>
            <span data-packages-current-cycle>Checking active billing cycle...</span>
            <small class="billing-price-note" data-packages-current-price-note>Preparing live pricing details...</small>
          </div>

          <div class="billing-status-actions">
            <a href="#packagesCatalog" class="btn btn-primary">Browse Packages</a>
            <button type="button" class="btn btn-secondary" data-packages-reload>Refresh Data</button>
          </div>
        </div>

        <div class="billing-status-meta">
          <article>
            <span>Current Package</span>
            <strong data-packages-meta-name>--</strong>
          </article>
          <article>
            <span>Activated On</span>
            <strong data-packages-meta-activated>--</strong>
          </article>
          <article>
            <span>Expires On</span>
            <strong data-packages-meta-expires>--</strong>
          </article>
          <article>
            <span>Billing Cycle</span>
            <strong data-packages-meta-cycle>--</strong>
          </article>
        </div>

        <div class="billing-card-message is-info" data-packages-summary-message>
          Live package details will appear here after the API response arrives.
        </div>
      </article>

      <aside class="billing-insights-card" data-packages-insights-card aria-busy="true">
        <span class="billing-eyebrow">Account Snapshot</span>
        <h3 data-packages-insights-title>Loading package snapshot...</h3>
        <p data-packages-insights-copy>Reading current credits, enabled modules, and validity information from the package info API.</p>

        <div class="billing-insights-list">
          <article>
            <span>Product Credits</span>
            <strong data-packages-insight-products>--</strong>
          </article>
          <article>
            <span>Calling Credits</span>
            <strong data-packages-insight-calls>--</strong>
          </article>
          <article>
            <span>SMS Credits</span>
            <strong data-packages-insight-sms>--</strong>
          </article>
          <article>
            <span>Enabled Modules</span>
            <strong data-packages-insight-modules>--</strong>
          </article>
        </div>

        <div class="billing-card-message billing-card-message-dark is-info" data-packages-insights-message>
          Waiting for the active package snapshot.
        </div>
      </aside>
    </section>

    <section class="billing-package-section mt-xl" id="packagesCatalog">
      <div class="billing-section-toolbar">
        <div class="billing-package-intro">
          <div>
            <span class="billing-eyebrow">Packages</span>
            <h2>Choose the right package for the next stage</h2>
            <p data-packages-intro-copy>Loading package catalog and billing-cycle pricing...</p>
          </div>
        </div>

        <div class="billing-cycle-tabs" role="tablist" aria-label="Package billing cycle">
          <button type="button" class="billing-cycle-tab is-active" data-package-cycle-tab="monthly" aria-pressed="true">Monthly</button>
          <button type="button" class="billing-cycle-tab" data-package-cycle-tab="quarterly" aria-pressed="false">Quarterly</button>
          <button type="button" class="billing-cycle-tab" data-package-cycle-tab="yearly" aria-pressed="false">Yearly</button>
        </div>
      </div>

      <div class="billing-package-grid" data-packages-grid aria-busy="true">
        @for ($i = 0; $i < 3; $i++)
          <article class="billing-package-card is-skeleton" aria-hidden="true">
            <div class="billing-package-head">
              <span class="billing-package-badge billing-skeleton billing-skeleton-block"></span>
              <div class="billing-skeleton billing-skeleton-block is-title"></div>
              <div class="billing-skeleton billing-skeleton-block is-copy"></div>
              <div class="billing-skeleton billing-skeleton-block is-copy"></div>
            </div>

            <div class="billing-package-price-stack">
              <div class="billing-package-price">
                <strong class="billing-skeleton billing-skeleton-block is-price"></strong>
                <span class="billing-skeleton billing-skeleton-block is-inline"></span>
              </div>
              <div class="billing-skeleton billing-skeleton-block is-copy"></div>
            </div>

            <div class="billing-package-credit-grid">
              @for ($j = 0; $j < 3; $j++)
                <article class="billing-package-credit">
                  <span class="billing-skeleton billing-skeleton-block is-copy"></span>
                  <strong class="billing-skeleton billing-skeleton-block is-copy"></strong>
                </article>
              @endfor
            </div>

            <div class="billing-package-capabilities">
              <span class="billing-package-capability billing-skeleton billing-skeleton-block is-chip"></span>
              <span class="billing-package-capability billing-skeleton billing-skeleton-block is-chip"></span>
              <span class="billing-package-capability billing-skeleton billing-skeleton-block is-chip"></span>
            </div>

            <ul class="billing-package-features">
              @for ($j = 0; $j < 4; $j++)
                <li><span class="billing-skeleton billing-skeleton-block is-copy"></span></li>
              @endfor
            </ul>

            <div class="billing-package-footer">
              <span class="billing-skeleton billing-skeleton-block is-button"></span>
            </div>
          </article>
        @endfor
      </div>
    </section>
  </div>
@endsection
