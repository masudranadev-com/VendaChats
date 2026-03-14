@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header settings-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="settings-header-actions">
      <a href="{{ route('admin.shop-settings.domain') }}" class="btn btn-secondary">Open Domain</a>
      <button type="button" class="btn btn-primary">Save General Settings</button>
    </div>
  </div>

  <section class="settings-section-intro">
    <h3>{{ $sectionHeading }}</h3>
    <p>{{ $sectionSubtitle }}</p>
  </section>

  <section class="settings-stats-grid">
    @foreach ($quickStats as $stat)
      <article class="settings-stat-card is-{{ $stat['tone'] }}">
        <span>{{ $stat['label'] }}</span>
        <strong>{{ $stat['value'] }}</strong>
        <small>{{ $stat['note'] }}</small>
      </article>
    @endforeach
  </section>

  <div class="settings-layout mt-md">
    <section class="settings-main-column">
      <article class="card settings-panel">
        <div class="card-header">
          <div>
            <h3 class="card-title">Store Identity</h3>
            <p class="settings-panel-subtitle">Core brand values shown in the storefront, admin header, and public support flow.</p>
          </div>
          <span class="badge badge-primary">Core</span>
        </div>

        <div class="settings-content-grid">
          <div class="form-group">
            <label class="form-label" for="shopGeneralStoreName">Store Name</label>
            <input id="shopGeneralStoreName" type="text" class="form-input" value="{{ $storeProfile['name'] }}">
          </div>
          <div class="form-group">
            <label class="form-label" for="shopGeneralStoreLogo">Store Logo Text</label>
            <input id="shopGeneralStoreLogo" type="text" class="form-input" value="{{ $storeProfile['logo'] }}">
          </div>
          <div class="form-group">
            <label class="form-label" for="shopGeneralPageName">Page Display Name</label>
            <input id="shopGeneralPageName" type="text" class="form-input" value="{{ $storeProfile['page_name'] }}">
          </div>
          <div class="form-group">
            <label class="form-label" for="shopGeneralStorefrontUsername">Storefront Username</label>
            <input id="shopGeneralStorefrontUsername" type="text" class="form-input" value="{{ $storeProfile['storefront_username'] }}">
            <small class="form-help">Used for the default subdomain before any custom domain is connected.</small>
          </div>
          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="shopGeneralStorefrontUrl">Storefront URL</label>
            <input id="shopGeneralStorefrontUrl" type="text" class="form-input" value="{{ $storeProfile['storefront_url'] }}">
          </div>
        </div>
      </article>

      <article class="card settings-panel mt-md">
        <div class="card-header">
          <div>
            <h3 class="card-title">Locale & Support Defaults</h3>
            <p class="settings-panel-subtitle">Set the language and support details that operators and customers expect to see first.</p>
          </div>
          <span class="badge badge-info">Operations</span>
        </div>

        <div class="settings-content-grid">
          <div class="form-group">
            <label class="form-label" for="shopGeneralTimezone">Timezone</label>
            <input id="shopGeneralTimezone" type="text" class="form-input" value="{{ $storeDefaults['timezone'] }}">
          </div>
          <div class="form-group">
            <label class="form-label" for="shopGeneralAdminLanguage">Admin Language</label>
            <input id="shopGeneralAdminLanguage" type="text" class="form-input" value="{{ $storeDefaults['admin_language'] }}">
          </div>
          <div class="form-group">
            <label class="form-label" for="shopGeneralWebsiteLanguage">Website Language</label>
            <input id="shopGeneralWebsiteLanguage" type="text" class="form-input" value="{{ $storeDefaults['website_language'] }}">
          </div>
          <div class="form-group">
            <label class="form-label" for="shopGeneralPrimaryLanguage">Primary Response Language</label>
            <input id="shopGeneralPrimaryLanguage" type="text" class="form-input" value="{{ $storeDefaults['primary_language'] }}">
          </div>
          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="shopGeneralSupportWhatsapp">Support WhatsApp Number</label>
            <input id="shopGeneralSupportWhatsapp" type="text" class="form-input" value="{{ $storeDefaults['support_whatsapp_number'] }}">
          </div>
        </div>
      </article>
    </section>

    <aside class="settings-side-column">
      <article class="card settings-panel">
        <div class="card-header">
          <div>
            <h3 class="card-title">Shop Settings Sections</h3>
            <p class="settings-panel-subtitle">Sidebar navigation now holds all shop settings pages. You can also jump from here.</p>
          </div>
          <span class="badge badge-success">Shortcuts</span>
        </div>

        @foreach ($shopSections as $section)
          <div class="settings-content-card">
            <div class="settings-content-head">
              <strong>{{ $section['label'] }}</strong>
              <span class="badge {{ $section['badge'] }}">{{ $section['status'] }}</span>
            </div>
            <p>{{ $section['note'] }}</p>
            <a href="{{ route($section['route']) }}" class="btn btn-secondary btn-sm">Open {{ $section['label'] }}</a>
          </div>
        @endforeach
      </article>

      <article class="card settings-panel mt-md">
        <div class="card-header">
          <div>
            <h3 class="card-title">Launch Checklist</h3>
            <p class="settings-panel-subtitle">A simple review list before moving into domain, theme, or content work.</p>
          </div>
          <span class="badge badge-warning">Review</span>
        </div>

        <ul class="settings-focus-list">
          @foreach ($launchChecklist as $item)
            <li>{{ $item }}</li>
          @endforeach
        </ul>
      </article>

      @include('admin.shop-settings.partials.recent-activity')
    </aside>
  </div>
@endsection
