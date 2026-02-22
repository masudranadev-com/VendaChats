@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header settings-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>
  </div>

  <section class="settings-tab-row">
    @foreach ($headerTabs as $tab)
      <a href="{{ route($tab['route']) }}" class="settings-tab {{ $activeSection === $tab['key'] ? 'active' : '' }}">
        {{ $tab['label'] }}
      </a>
    @endforeach
  </section>

  <section class="settings-section-intro">
    <h3>{{ $sectionHeading }}</h3>
    <p>{{ $sectionSubtitle }}</p>
  </section>

  @php
    $focusTitle = match ($activeSection) {
      'domain' => 'Domain Checklist',
      'theme' => 'Theme Review',
      'category' => 'Category Actions',
      'offers' => 'Offer Guardrails',
      default => 'Content Checklist',
    };

    $focusItems = match ($activeSection) {
      'domain' => [
        'Verify SSL status for checkout subdomain.',
        'Confirm DNS propagation on all records.',
        'Keep canonical URL as primary domain.',
      ],
      'theme' => [
        'Review active theme before campaign launch.',
        'Run speed audit after any visual change.',
        'Compare conversion of active vs draft theme.',
      ],
      'category' => [
        'Merge weak categories with low product volume.',
        'Prioritize top categories in homepage menu.',
        'Sync category tags with ad campaign groups.',
      ],
      'offers' => [
        'Avoid overlapping discounts in same audience.',
        'Add expiry and cooldown for bundle offers.',
        'Track conversion after each offer edit.',
      ],
      default => [
        'Refresh homepage banners per active campaign.',
        'Review policy pages every month.',
        'Keep FAQ answers synced with support tickets.',
      ],
    };
  @endphp

  <div class="settings-layout mt-xl">
    <section class="settings-main-column">
      @if ($activeSection === 'domain')
        <article class="card settings-panel">
          <div class="card-header">
            <div>
              <h3 class="card-title">Domain Configuration</h3>
              <p class="settings-panel-subtitle">Manage domain mapping, SSL state, DNS health, and redirects.</p>
            </div>
            <button type="button" class="btn btn-ghost btn-sm">Run SSL Check</button>
          </div>

          <div class="settings-domain-list">
            @foreach ($domains as $domain)
              @php
                $statusClass = str_contains($domain['status'], 'Pending') ? 'badge-warning' : 'badge-success';
              @endphp
              <div class="settings-domain-item">
                <div class="settings-domain-top">
                  <div>
                    <strong>{{ $domain['label'] }}</strong>
                    <p>{{ $domain['value'] }}</p>
                  </div>
                  <span class="badge {{ $statusClass }}">{{ $domain['status'] }}</span>
                </div>
                <div class="settings-domain-meta">
                  <span>SSL: {{ $domain['ssl'] }}</span>
                  <span>DNS: {{ $domain['dns'] }}</span>
                  <span>Traffic: {{ $domain['traffic'] }}</span>
                </div>
              </div>
            @endforeach
          </div>

          <div class="settings-field-grid">
            <div class="form-group">
              <label class="form-label">Primary Domain</label>
              <input type="text" class="form-input" value="shop.example.com">
            </div>
            <div class="form-group">
              <label class="form-label">Redirect Domain</label>
              <input type="text" class="form-input" value="www.shop.example.com">
            </div>
            <div class="form-group">
              <label class="form-label">Canonical URL</label>
              <input type="text" class="form-input" value="https://shop.example.com">
            </div>
            <div class="form-group">
              <label class="form-label">Default Language</label>
              <select class="form-select">
                <option>English</option>
                <option>Bangla</option>
              </select>
            </div>
          </div>

          <div class="settings-inline-actions">
            <button type="button" class="btn btn-primary btn-sm">Save Domain Settings</button>
            <button type="button" class="btn btn-secondary btn-sm">Verify DNS</button>
          </div>
        </article>
      @elseif ($activeSection === 'theme')
        <article class="card settings-panel">
          <div class="card-header">
            <div>
              <h3 class="card-title">Theme Studio</h3>
              <p class="settings-panel-subtitle">Switch storefront style while tracking speed and conversion.</p>
            </div>
            <span class="badge badge-info">Theme switcher</span>
          </div>

          <div class="settings-theme-list">
            @foreach ($themes as $theme)
              @php
                $themeClass = match ($theme['status']) {
                  'Active' => 'badge-success',
                  'Draft' => 'badge-warning',
                  default => 'badge-info',
                };
              @endphp
              <div class="settings-theme-item">
                <div class="settings-theme-preview"></div>
                <div class="settings-theme-content">
                  <div class="flex-between">
                    <strong>{{ $theme['name'] }}</strong>
                    <span class="badge {{ $themeClass }}">{{ $theme['status'] }}</span>
                  </div>
                  <p>{{ $theme['note'] }}</p>
                  <div class="settings-theme-meta">
                    <span>Conversion: {{ $theme['conversion'] }}</span>
                    <span>Speed Score: {{ $theme['speed'] }}</span>
                  </div>
                  <button type="button" class="btn {{ $theme['status'] === 'Active' ? 'btn-secondary' : 'btn-ghost' }} btn-sm">
                    {{ $theme['status'] === 'Active' ? 'Active Theme' : 'Apply Theme' }}
                  </button>
                </div>
              </div>
            @endforeach
          </div>
        </article>
      @elseif ($activeSection === 'category')
        <article class="card settings-panel">
          <div class="card-header">
            <div>
              <h3 class="card-title">Category Control</h3>
              <p class="settings-panel-subtitle">Track category weight and keep the catalog balanced.</p>
            </div>
            <span class="badge badge-info">{{ count($categories) }} groups</span>
          </div>

          <div class="settings-category-list">
            @foreach ($categories as $category)
              <div class="settings-category-item">
                <div class="settings-category-top">
                  <span>{{ $category['name'] }}</span>
                  <strong>{{ $category['products'] }} products</strong>
                </div>
                <div class="settings-category-track">
                  <span style="width: {{ $category['share'] }}%"></span>
                </div>
                <small>{{ $category['share'] }}% catalog share</small>
              </div>
            @endforeach
          </div>
        </article>
      @elseif ($activeSection === 'offers')
        <article class="card settings-panel">
          <div class="card-header">
            <div>
              <h3 class="card-title">Offers Engine</h3>
              <p class="settings-panel-subtitle">Tune discount rules by audience and trigger conditions.</p>
            </div>
            <span class="badge badge-primary">{{ count($offers) }} running rules</span>
          </div>

          <div class="table-container">
            <table class="table">
              <thead>
                <tr>
                  <th>Offer Name</th>
                  <th>Type</th>
                  <th>Audience</th>
                  <th>Trigger</th>
                  <th>Status</th>
                  <th>Expires</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($offers as $offer)
                  @php
                    $offerClass = $offer['status'] === 'Live' ? 'badge-success' : 'badge-warning';
                  @endphp
                  <tr>
                    <td class="settings-cell-strong">{{ $offer['name'] }}</td>
                    <td>{{ $offer['type'] }}</td>
                    <td>{{ $offer['audience'] }}</td>
                    <td>{{ $offer['trigger'] }}</td>
                    <td><span class="badge {{ $offerClass }}">{{ $offer['status'] }}</span></td>
                    <td>{{ $offer['expires'] }}</td>
                    <td>
                      <div class="settings-offer-actions">
                        <button type="button" class="btn btn-ghost btn-sm">View</button>
                        <button type="button" class="btn btn-secondary btn-sm">Edit</button>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </article>
      @else
        <article class="card settings-panel">
          <div class="card-header">
            <div>
              <h3 class="card-title">Website Content Data</h3>
              <p class="settings-panel-subtitle">Keep banners, pages, FAQs, and blog content accurate and fresh.</p>
            </div>
            <button type="button" class="btn btn-primary btn-sm">Open Content Center</button>
          </div>

          <div class="settings-content-grid">
            @foreach ($contentData as $content)
              @php
                $contentClass = match ($content['status']) {
                  'Healthy' => 'badge-success',
                  'Review Needed' => 'badge-warning',
                  default => 'badge-info',
                };
              @endphp
              <article class="settings-content-card">
                <div class="settings-content-head">
                  <strong>{{ $content['title'] }}</strong>
                  <span class="badge {{ $contentClass }}">{{ $content['status'] }}</span>
                </div>
                <p>{{ $content['note'] }}</p>
                <small>{{ $content['meta'] }}</small>
                <button type="button" class="btn btn-ghost btn-sm">Manage</button>
              </article>
            @endforeach
          </div>
        </article>
      @endif
    </section>

    <section class="settings-side-column">
      <article class="card settings-panel">
        <div class="card-header">
          <h3 class="card-title">{{ $focusTitle }}</h3>
          <span class="badge badge-info">{{ ucfirst($activeSection) }}</span>
        </div>

        <ul class="settings-focus-list">
          @foreach ($focusItems as $item)
            <li>{{ $item }}</li>
          @endforeach
        </ul>
      </article>

      <article class="card mt-xl settings-panel">
        <div class="card-header">
          <div>
            <h3 class="card-title">Recent Activity</h3>
            <p class="settings-panel-subtitle">Latest setting changes across teams.</p>
          </div>
          <span class="badge badge-warning">{{ count($activityLog) }} updates</span>
        </div>

        <ul class="settings-activity-list">
          @foreach ($activityLog as $activity)
            @php
              $activityClass = $activity['status'] === 'Pending' ? 'is-pending' : 'is-success';
              $activityBadge = $activity['status'] === 'Pending' ? 'badge-warning' : 'badge-success';
            @endphp
            <li>
              <span class="settings-activity-dot {{ $activityClass }}"></span>
              <div class="settings-activity-content">
                <strong>{{ $activity['event'] }}</strong>
                <p>{{ $activity['actor'] }} | {{ $activity['time'] }}</p>
              </div>
              <span class="badge {{ $activityBadge }}">{{ $activity['status'] }}</span>
            </li>
          @endforeach
        </ul>
      </article>
    </section>
  </div>
@endsection
