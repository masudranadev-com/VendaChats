@extends('admin.master')

@section('title', $title)

@section('admin.content')
  @php
    $activeCampaigns = [
      [
        'name' => 'Weekend Drop Recovery',
        'product' => 'Premium Cotton T-Shirt',
        'status' => 'Live',
        'budget' => '$120',
        'reach' => '12,450',
        'conversion' => '4.8%',
        'progress' => 72,
      ],
      [
        'name' => 'Back In Stock Push',
        'product' => 'Smart Casual Hoodie',
        'status' => 'Scheduled',
        'budget' => '$90',
        'reach' => '8,130',
        'conversion' => '3.9%',
        'progress' => 38,
      ],
      [
        'name' => 'High Intent Retarget',
        'product' => 'Wireless Earbuds Pro',
        'status' => 'Draft',
        'budget' => '$160',
        'reach' => '15,700',
        'conversion' => '5.2%',
        'progress' => 18,
      ],
    ];

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

    <div class="campaign-header-actions">
      <button type="button" class="btn btn-secondary">Duplicate Last Campaign</button>
      <button type="button" class="btn btn-primary">Create Campaign</button>
    </div>
  </div>

  <section class="campaign-metrics">
    <article class="campaign-metric-card">
      <span>Total Campaigns</span>
      <strong>18</strong>
      <small>+3 this month</small>
    </article>
    <article class="campaign-metric-card">
      <span>Live Right Now</span>
      <strong>4</strong>
      <small>2 scheduled today</small>
    </article>
    <article class="campaign-metric-card">
      <span>Average Conversion</span>
      <strong>4.6%</strong>
      <small>+0.8% vs last week</small>
    </article>
    <article class="campaign-metric-card">
      <span>Revenue From Campaigns</span>
      <strong>$12,940</strong>
      <small>Last 30 days</small>
    </article>
  </section>

  <div class="campaign-layout mt-xl">
    <section class="card campaign-form-card">
      <div class="card-header">
        <h3 class="card-title">Campaign Builder (UI)</h3>
        <span class="badge badge-info">Design Only</span>
      </div>

      <div class="campaign-form-grid">
        <div class="form-group">
          <label class="form-label">Campaign Name</label>
          <input type="text" class="form-input" placeholder="Example: Summer Conversion Push">
        </div>

        <div class="form-group">
          <label class="form-label">Product Focus</label>
          <select class="form-select">
            <option>Premium Cotton T-Shirt</option>
            <option>AirFlex Running Shoes</option>
            <option>Wireless Earbuds Pro</option>
            <option>Smart Casual Hoodie</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Audience Type</label>
          <select class="form-select">
            <option>Price-sensitive</option>
            <option>Quality-focused</option>
            <option>Repeat Buyers</option>
            <option>Cold Audience</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Daily Budget</label>
          <input type="text" class="form-input" placeholder="$120">
        </div>
      </div>

      <div class="campaign-chip-row">
        <span class="campaign-chip active">Facebook Feed</span>
        <span class="campaign-chip active">Messenger</span>
        <span class="campaign-chip">WhatsApp</span>
        <span class="campaign-chip">Instagram Stories</span>
      </div>

      <div class="page-actions">
        <button type="button" class="btn btn-primary">Save Draft</button>
        <button type="button" class="btn btn-secondary">Preview Flow</button>
      </div>
    </section>

    <section class="card campaign-side-card">
      <div class="card-header">
        <h3 class="card-title">Today's Schedule</h3>
        <span class="badge badge-primary">Auto Queue</span>
      </div>

      <ul class="campaign-schedule-list">
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
      <span class="badge badge-success">{{ count($activeCampaigns) }} running cards</span>
    </div>

    <div class="campaign-board">
      @foreach ($activeCampaigns as $campaign)
        @php
          $statusClass = match (strtolower($campaign['status'])) {
            'live' => 'campaign-status-live',
            'scheduled' => 'campaign-status-scheduled',
            default => 'campaign-status-draft',
          };
        @endphp
        <article class="campaign-card">
          <div class="campaign-card-top">
            <div>
              <h4>{{ $campaign['name'] }}</h4>
              <p>{{ $campaign['product'] }}</p>
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
      @endforeach
    </div>
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
