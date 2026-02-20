@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header courier-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="courier-header-actions">
      <button type="button" class="btn btn-secondary">Run Health Check</button>
      <button type="button" class="btn btn-primary">Save All Changes</button>
    </div>
  </div>

  <section class="courier-kpi-grid">
    <article class="courier-kpi-card">
      <span>Active Courier</span>
      <strong>2</strong>
      <small>SteadFast + RedX</small>
    </article>
    <article class="courier-kpi-card">
      <span>API Health</span>
      <strong>91%</strong>
      <small>Last 24 hours</small>
    </article>
    <article class="courier-kpi-card">
      <span>Dispatch Success</span>
      <strong>97.4%</strong>
      <small>Last 7 days</small>
    </article>
    <article class="courier-kpi-card">
      <span>Avg Delivery ETA</span>
      <strong>2.8d</strong>
      <small>All zones</small>
    </article>
  </section>

  <div class="courier-layout mt-xl">
    <section class="card">
      <div class="card-header">
        <h3 class="card-title">Courier API Connection</h3>
        <span class="badge badge-info">Design only</span>
      </div>

      <div class="courier-provider-list">
        @foreach ($providers as $provider)
          <article class="courier-provider-card">
            <div class="courier-provider-head">
              <div>
                <h4>{{ $provider['name'] }}</h4>
                <p>Primary endpoint: {{ $provider['base_url'] }}</p>
              </div>
              <span class="badge {{ $provider['status_class'] }}">{{ $provider['status'] }}</span>
            </div>

            <form class="courier-provider-form">
              <div class="courier-provider-grid">
                <div class="form-group">
                  <label class="form-label">Base URL</label>
                  <input type="text" class="form-input" value="{{ $provider['base_url'] }}">
                </div>
                <div class="form-group">
                  <label class="form-label">API Key</label>
                  <input type="password" class="form-input" value="********************">
                </div>
                <div class="form-group">
                  <label class="form-label">API Secret</label>
                  <input type="password" class="form-input" value="********************">
                </div>
                <div class="form-group">
                  <label class="form-label">{{ $provider['merchant_field'] }}</label>
                  <input type="text" class="form-input" value="{{ $provider['merchant_value'] }}">
                </div>
                <div class="form-group">
                  <label class="form-label">Mode</label>
                  <select class="form-select">
                    <option {{ $provider['mode'] === 'Live' ? 'selected' : '' }}>Live</option>
                    <option {{ $provider['mode'] === 'Sandbox' ? 'selected' : '' }}>Sandbox</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Webhook URL</label>
                  <input type="text" class="form-input" value="{{ url('/webhook') }}">
                </div>
              </div>

              <div class="courier-provider-actions">
                <button type="button" class="btn btn-primary btn-sm">Test Connection</button>
                <button type="button" class="btn btn-secondary btn-sm">Sync Zones</button>
                <button type="button" class="btn btn-ghost btn-sm">Save Provider</button>
              </div>
            </form>
          </article>
        @endforeach
      </div>
    </section>

    <section class="card courier-side-card">
      <div class="card-header">
        <h3 class="card-title">Routing Rules</h3>
        <span class="badge badge-primary">Priority engine</span>
      </div>

      <div class="courier-rule-list">
        <label class="courier-rule-item">
          <input type="checkbox" checked>
          <span>Use cheapest courier by zone rate</span>
        </label>
        <label class="courier-rule-item">
          <input type="checkbox" checked>
          <span>Fallback to SteadFast if RedX API fails</span>
        </label>
        <label class="courier-rule-item">
          <input type="checkbox" checked>
          <span>Auto-assign remote area to SteadFast</span>
        </label>
        <label class="courier-rule-item">
          <input type="checkbox">
          <span>Require manual approval for COD above ৳10,000</span>
        </label>
      </div>

      <div class="courier-note">
        <strong>Basic Target:</strong> SteadFast Courier + RedX API connection.
        Configure credentials first, then run test and zone sync.
      </div>
    </section>
  </div>

  <section class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Zone Rate Matrix</h3>
      <span class="badge badge-success">Cost comparison</span>
    </div>

    <div class="table-container">
      <table class="table">
        <thead>
          <tr>
            <th>Zone</th>
            <th>SteadFast</th>
            <th>RedX</th>
            <th>Preferred</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($zones as $zone)
            @php
              $preferredClass = $zone['preferred'] === 'SteadFast' ? 'badge-primary' : 'badge-info';
            @endphp
            <tr>
              <td class="courier-cell-strong">{{ $zone['zone'] }}</td>
              <td>{{ $zone['steadfast'] }}</td>
              <td>{{ $zone['redx'] }}</td>
              <td><span class="badge {{ $preferredClass }}">{{ $zone['preferred'] }}</span></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>

  <section class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">API Activity Log</h3>
      <span class="badge badge-warning">Recent requests</span>
    </div>

    <div class="table-container">
      <table class="table">
        <thead>
          <tr>
            <th>Time</th>
            <th>Provider</th>
            <th>Event</th>
            <th>Status</th>
            <th>Request ID</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($logs as $log)
            @php
              $statusClass = $log['status'] === 'Success' ? 'badge-success' : 'badge-danger';
            @endphp
            <tr>
              <td>{{ $log['time'] }}</td>
              <td class="courier-cell-strong">{{ $log['provider'] }}</td>
              <td>{{ $log['event'] }}</td>
              <td><span class="badge {{ $statusClass }}">{{ $log['status'] }}</span></td>
              <td>{{ $log['request_id'] }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>
@endsection
