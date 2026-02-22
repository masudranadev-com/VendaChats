@extends('admin.master')

@section('title', $title)

@section('admin.content')
  @php
    $scheduleItems = [
      ['time' => '10:00 AM', 'title' => 'Launch Story Ad', 'channel' => 'Facebook + Instagram'],
      ['time' => '01:30 PM', 'title' => 'WhatsApp Follow-up Batch', 'channel' => 'WhatsApp'],
      ['time' => '05:00 PM', 'title' => 'Retargeting Creative Refresh', 'channel' => 'Facebook'],
      ['time' => '08:15 PM', 'title' => 'Last Chance Reminder', 'channel' => 'Messenger'],
    ];

    $templates = [
      'Price-sensitive Buyers',
      'Quality-focused Buyers',
      'Cart Abandon Follow-up',
      'Low Stock Urgency',
      'Review + Social Proof',
    ];
  @endphp

  <div class="page-header campaign-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>
  </div>

  <section class="campaign-metrics">
    <article class="campaign-metric-card">
      <span>Total Campaigns</span>
      <strong>{{ $campaignSummary['total'] }}</strong>
    </article>
    <article class="campaign-metric-card">
      <span>Live Campaigns</span>
      <strong>{{ $campaignSummary['live'] }}</strong>
    </article>
    <article class="campaign-metric-card">
      <span>Schedule Campaigns</span>
      <strong>{{ $campaignSummary['scheduled'] }}</strong>
    </article>
    <article class="campaign-metric-card">
      <span>Active Templates</span>
      <strong>{{ $campaignSummary['templates'] }}</strong>
    </article>
  </section>

  <div class="campaign-layout mt-xl">
    <section class="card campaign-form-card">
      <div class="card-header">
        <h3 class="card-title">Campaign Builder</h3>
      </div>

      <form class="campaign-builder-form" data-campaign-builder-form novalidate>
        <div class="campaign-form-grid">
          <div class="form-group">
            <label class="form-label" for="campaignName">Campaign Name</label>
            <input id="campaignName" name="campaign_name" type="text" class="form-input" placeholder="Example: Summer Conversion Push" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="campaignProduct">Product Focus</label>
            <select id="campaignProduct" name="product_focus" class="form-select" required>
              <option value="" selected disabled>Select Product</option>
              <option>Premium Cotton T-Shirt</option>
              <option>AirFlex Running Shoes</option>
              <option>Wireless Earbuds Pro</option>
              <option>Smart Casual Hoodie</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="campaignAudience">Audience Type</label>
            <select id="campaignAudience" name="audience_type" class="form-select" required>
              <option value="" selected disabled>Select Audience</option>
              <option>Price-sensitive</option>
              <option>Quality-focused</option>
              <option>All Audience</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="campaignTemplate">Select Templates</label>
            <select id="campaignTemplate" name="template" class="form-select" required>
              <option value="" selected disabled>Select Template</option>
              <option>Price-sensitive Buyers</option>
              <option>Quality-focused Buyers</option>
              <option>Cart Abandon Follow-up</option>
              <option>Low Stock Urgency</option>
              <option>Review + Social Proof</option>
            </select>
          </div>
        </div>

        <div class="campaign-mode-panel">
          <h4 class="campaign-panel-title">Launch Mode</h4>
          <div class="campaign-mode-options">
            <label class="campaign-mode-item">
              <input type="radio" name="campaign_mode" value="instant" data-campaign-mode checked>
              <span>Instant</span>
            </label>
            <label class="campaign-mode-item">
              <input type="radio" name="campaign_mode" value="scheduled" data-campaign-mode>
              <span>Scheduled</span>
            </label>
          </div>
          <small class="form-help">Use Instant for immediate launch. Use Scheduled to queue campaign execution.</small>
        </div>

        <div class="campaign-schedule-config hidden" data-campaign-schedule-fields>
          <div class="campaign-form-grid">
            <div class="form-group">
              <label class="form-label" for="campaignStartDate">Start Date</label>
              <input id="campaignStartDate" name="start_date" type="date" class="form-input" data-campaign-start-date disabled>
            </div>

            <div class="form-group">
              <label class="form-label" for="campaignStartTime">Start Time</label>
              <input id="campaignStartTime" name="start_time" type="time" class="form-input" data-campaign-start-time disabled>
            </div>
          </div>
        </div>

        <p class="campaign-builder-status" data-campaign-builder-status aria-live="polite">
          Choose a mode and complete campaign details.
        </p>

        <div class="page-actions">
          <button type="submit" class="btn btn-success" data-campaign-submit-action>Launch Instant</button>
        </div>
      </form>
    </section>

    <section class="card campaign-side-card">
      <div class="card-header">
        <h3 class="card-title">Schedule Campaign</h3>
        <span class="badge badge-primary">Auto Queue</span>
      </div>

      <ul class="campaign-schedule-list" data-campaign-schedule-list>
        @foreach ($scheduleItems as $item)
          <li>
            <div class="campaign-schedule-time">{{ $item['time'] }}</div>
            <div class="campaign-schedule-copy">
              <strong>{{ $item['title'] }}</strong>
              <span>{{ $item['channel'] }}</span>
            </div>
          </li>
        @endforeach
      </ul>
    </section>
  </div>

  <section class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Active Campaign Board</h3>
      <span class="badge badge-success">{{ $campaigns->total() }} campaign history</span>
    </div>

    <div class="campaign-history-meta">
      @if ($campaigns->count() > 0)
        Showing {{ $campaigns->firstItem() }}-{{ $campaigns->lastItem() }} of {{ $campaigns->total() }} campaigns
      @else
        Showing 0 campaigns
      @endif
    </div>

    <div class="campaign-board">
      @forelse ($campaigns as $campaign)
        @php
          $statusClass = match (strtolower($campaign['status'])) {
            'live' => 'campaign-status-live',
            'scheduled' => 'campaign-status-scheduled',
            'completed' => 'campaign-status-completed',
            'paused' => 'campaign-status-paused',
            default => 'campaign-status-draft',
          };
        @endphp
        <article class="campaign-card">
          <div class="campaign-card-top">
            <div>
              <h4>{{ $campaign['name'] }}</h4>
              <p>{{ $campaign['product'] }}</p>
              <small class="campaign-card-time">{{ $campaign['launched_at'] }}</small>
            </div>
            <span class="campaign-status {{ $statusClass }}">{{ $campaign['status'] }}</span>
          </div>

          <div class="campaign-quick-grid">
            <div><span>Budget</span><strong>{{ $campaign['budget'] }}</strong></div>
            <div><span>Reach</span><strong>{{ $campaign['reach'] }}</strong></div>
            <div><span>Conversion</span><strong>{{ $campaign['conversion'] }}</strong></div>
          </div>

          <div class="campaign-progress">
            <div class="campaign-progress-label">
              <span>Execution Progress</span>
              <strong>{{ $campaign['progress'] }}%</strong>
            </div>
            <div class="campaign-progress-track">
              <span style="width: {{ $campaign['progress'] }}%"></span>
            </div>
          </div>
        </article>
      @empty
        <p class="campaign-history-empty">No campaigns found for this page.</p>
      @endforelse
    </div>

    @if ($campaigns->hasPages())
      <div class="campaign-table-footer">
        <p class="campaign-pagination-summary">
          Page {{ $campaigns->currentPage() }} of {{ $campaigns->lastPage() }}
        </p>

        <nav class="campaign-pagination-controls" aria-label="Campaign pagination">
          @if ($campaigns->onFirstPage())
            <span class="campaign-page-btn is-disabled" aria-disabled="true">Prev</span>
          @else
            <a href="{{ $campaigns->previousPageUrl() }}" class="campaign-page-btn">Prev</a>
          @endif

          @for ($page = 1; $page <= $campaigns->lastPage(); $page++)
            @if ($page === $campaigns->currentPage())
              <span class="campaign-page-btn is-active" aria-current="page">{{ $page }}</span>
            @else
              <a href="{{ $campaigns->url($page) }}" class="campaign-page-btn">{{ $page }}</a>
            @endif
          @endfor

          @if ($campaigns->hasMorePages())
            <a href="{{ $campaigns->nextPageUrl() }}" class="campaign-page-btn">Next</a>
          @else
            <span class="campaign-page-btn is-disabled" aria-disabled="true">Next</span>
          @endif
        </nav>
      </div>
    @endif
  </section>

  <div class="campaign-layout mt-xl">
    <section class="card">
      <div class="card-header">
        <h3 class="card-title">Template Library</h3>
        <span class="badge badge-warning">Product Messaging</span>
      </div>

      <ul class="campaign-template-list">
        @foreach ($templates as $template)
          <li>
            <span>{{ $template }}</span>
            <button type="button" class="btn btn-ghost btn-sm">Use Template</button>
          </li>
        @endforeach
      </ul>
    </section>

    <section class="card campaign-side-card">
      <div class="card-header">
        <h3 class="card-title">Channel Split</h3>
        <span class="badge badge-info">Last 7 Days</span>
      </div>

      <div class="campaign-channel-item">
        <div class="flex-between">
          <span>Facebook Ads</span>
          <strong>44%</strong>
        </div>
        <div class="campaign-progress-track"><span style="width: 44%"></span></div>
      </div>

      <div class="campaign-channel-item">
        <div class="flex-between">
          <span>Messenger</span>
          <strong>28%</strong>
        </div>
        <div class="campaign-progress-track"><span style="width: 28%"></span></div>
      </div>

      <div class="campaign-channel-item">
        <div class="flex-between">
          <span>WhatsApp</span>
          <strong>18%</strong>
        </div>
        <div class="campaign-progress-track"><span style="width: 18%"></span></div>
      </div>

      <div class="campaign-channel-item">
        <div class="flex-between">
          <span>Instagram</span>
          <strong>10%</strong>
        </div>
        <div class="campaign-progress-track"><span style="width: 10%"></span></div>
      </div>
    </section>
  </div>
@endsection
