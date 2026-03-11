@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header billing-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>
    <a href="#billingPackages" class="btn btn-primary billing-header-cta">Upgrade Package</a>
  </div>

  <section class="billing-hero">
    <article class="billing-status-card">
      <div class="billing-status-top">
        <div>
          <span class="billing-eyebrow">Subscription Health</span>
          <h2>{{ $billingStatus['plan_label'] }}</h2>
          <p>{{ $billingStatus['note'] }}</p>
        </div>

        <span class="billing-payment-badge {{ $billingStatus['is_paid'] ? 'is-paid' : 'is-unpaid' }}">
          {{ $billingStatus['status_label'] }}
        </span>
      </div>

      <div class="billing-status-main">
        <div class="billing-price-block">
          <strong>{{ $billingStatus['price'] }}</strong>
          <span>{{ $billingStatus['billing_cycle'] }}</span>
        </div>

        <div class="billing-status-actions">
          <a href="#billingPackages" class="btn btn-primary">Upgrade Package</a>
          <button type="button" class="btn btn-secondary">Billing History</button>
        </div>
      </div>

      <div class="billing-status-meta">
        <article>
          <span>Current Plan</span>
          <strong>{{ $billingStatus['plan_name'] }}</strong>
        </article>
        <article>
          <span>Renews On</span>
          <strong>{{ $billingStatus['renews_on'] }}</strong>
        </article>
        <article>
          <span>Last Charge</span>
          <strong>{{ $billingStatus['last_charge'] }}</strong>
        </article>
        <article>
          <span>Support</span>
          <strong>{{ $billingStatus['support_level'] }}</strong>
        </article>
      </div>
    </article>

    <aside class="billing-insights-card">
      <span class="billing-eyebrow">Account Summary</span>
      <h3>Paid account with room to grow</h3>
      <p>Your workspace is active, protected, and ready for the next package upgrade whenever order volume increases.</p>

      <div class="billing-insights-list">
        <article>
          <span>Seat Usage</span>
          <strong>{{ $billingStatus['seat_usage'] }}</strong>
        </article>
        <article>
          <span>Payment Status</span>
          <strong>{{ $billingStatus['status_label'] }}</strong>
        </article>
        <article>
          <span>Upgrade Ready</span>
          <strong>Scale features available</strong>
        </article>
      </div>
    </aside>
  </section>

  <section class="billing-package-section mt-xl" id="billingPackages">
    <div class="billing-package-intro">
      <div>
        <span class="billing-eyebrow">Packages</span>
        <h2>Choose the right plan for the next stage</h2>
        <p>Static UI for now. Later this section can be filled with live plans and features from API data.</p>
      </div>
    </div>

    <div class="billing-package-grid">
      @foreach ($packages as $package)
        <article class="billing-package-card {{ $package['is_featured'] ? 'is-featured' : '' }} billing-package-card-{{ $package['accent'] }}">
          <div class="billing-package-head">
            <span class="billing-package-badge">{{ $package['badge'] }}</span>
            <h3>{{ $package['name'] }}</h3>
            <p>{{ $package['description'] }}</p>
          </div>

          <div class="billing-package-price">
            <strong>{{ $package['price'] }}</strong>
            <span>{{ $package['period'] }}</span>
          </div>

          <ul class="billing-package-features">
            @foreach ($package['features'] as $feature)
              <li>{{ $feature }}</li>
            @endforeach
          </ul>

          <div class="billing-package-footer">
            <button
              type="button"
              class="btn {{ $package['is_current'] ? 'btn-secondary' : 'btn-primary' }}"
              {{ $package['is_current'] ? 'disabled' : '' }}
            >
              {{ $package['cta'] }}
            </button>
          </div>
        </article>
      @endforeach
    </div>
  </section>
@endsection
