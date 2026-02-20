@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header orders-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="orders-header-actions">
      <button type="button" class="btn btn-secondary">Export CSV</button>
      <button type="button" class="btn btn-primary">Create Manual Order</button>
    </div>
  </div>

  <section class="orders-kpi-grid">
    @foreach ($metrics as $metric)
      <article class="orders-kpi-card">
        <span>{{ $metric['label'] }}</span>
        <strong>{{ $metric['value'] }}</strong>
        <small>{{ $metric['meta'] }}</small>
      </article>
    @endforeach
  </section>

  <section class="orders-hero-card mt-xl">
    <div>
      <p class="orders-hero-eyebrow">Fulfillment Command</p>
      <h3>Order Flow Control Tower</h3>
      <p>Track payment risk, dispatch bottlenecks, and delivery confidence before issues hit customer support.</p>
    </div>
    <div class="orders-hero-meta">
      <div>
        <span>Avg Processing Time</span>
        <strong>1h 28m</strong>
      </div>
      <div>
        <span>Dispatch SLA</span>
        <strong>89%</strong>
      </div>
      <div>
        <span>Return Risk</span>
        <strong>5.1%</strong>
      </div>
    </div>
  </section>

  <section class="orders-pipeline-grid mt-xl">
    @foreach ($pipeline as $lane)
      <article class="orders-pipeline-card">
        <span>{{ $lane['name'] }}</span>
        <strong>{{ $lane['count'] }}</strong>
        <small class="badge badge-{{ $lane['tone'] }}">Active</small>
      </article>
    @endforeach
  </section>

  <section class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Order Queue</h3>
      <span class="badge badge-info">{{ count($orders) }} monitored orders</span>
    </div>

    <div class="orders-filter-grid">
      <div class="form-group">
        <label class="form-label">Search</label>
        <input type="text" class="form-input" placeholder="Order ID, customer, location">
      </div>
      <div class="form-group">
        <label class="form-label">Status</label>
        <select class="form-select">
          <option>All Status</option>
          <option>Payment Review</option>
          <option>Ready to Dispatch</option>
          <option>In Transit</option>
          <option>Delivered</option>
          <option>Delayed</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Payment Type</label>
        <select class="form-select">
          <option>All Payments</option>
          <option>Paid</option>
          <option>COD</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Channel</label>
        <select class="form-select">
          <option>All Channels</option>
          <option>Website</option>
          <option>Messenger</option>
          <option>WhatsApp</option>
          <option>Instagram</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Sort By</label>
        <select class="form-select">
          <option>Newest First</option>
          <option>Highest Amount</option>
          <option>Highest SLA Risk</option>
          <option>Longest Pending</option>
        </select>
      </div>
    </div>

    <div class="orders-filter-actions">
      <button type="button" class="btn btn-primary btn-sm">Apply Filters</button>
      <button type="button" class="btn btn-ghost btn-sm">Reset</button>
      <span class="orders-filter-result">Showing {{ count($orders) }} of 142 orders</span>
    </div>

    <div class="table-container">
      <table class="table orders-table">
        <thead>
          <tr>
            <th>Order</th>
            <th>Customer</th>
            <th>Items</th>
            <th>Amount</th>
            <th>Payment</th>
            <th>Channel</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($orders as $order)
            @php
              $statusClass = match ($order['status']) {
                'Delivered' => 'badge-success',
                'In Transit' => 'badge-primary',
                'Ready to Dispatch' => 'badge-info',
                'Payment Review' => 'badge-warning',
                'Delayed' => 'badge-danger',
                default => 'badge-warning',
              };

              $paymentClass = $order['payment'] === 'Paid' ? 'badge-success' : 'badge-warning';
            @endphp
            <tr>
              <td>
                <div class="orders-order-cell">
                  <strong>{{ $order['id'] }}</strong>
                  <small>{{ $order['placed_at'] }}</small>
                </div>
              </td>
              <td>
                <div class="orders-customer-cell">
                  <span class="orders-customer-avatar">{{ strtoupper(substr($order['customer'], 0, 1)) }}</span>
                  <div>
                    <strong>{{ $order['customer'] }}</strong>
                    <small>{{ $order['location'] }}</small>
                  </div>
                </div>
              </td>
              <td>{{ $order['items'] }}</td>
              <td class="orders-cell-strong">{{ $order['amount'] }}</td>
              <td><span class="badge {{ $paymentClass }}">{{ $order['payment'] }}</span></td>
              <td><span class="badge badge-primary">{{ $order['channel'] }}</span></td>
              <td>
                <div class="orders-status-wrap">
                  <span class="badge {{ $statusClass }}">{{ $order['status'] }}</span>
                  <div class="orders-progress-track">
                    <span style="width: {{ $order['progress'] }}%"></span>
                  </div>
                </div>
              </td>
              <td>
                <div class="orders-table-actions">
                  <button type="button" class="btn btn-ghost btn-sm">View</button>
                  <button type="button" class="btn btn-secondary btn-sm">Update</button>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>
@endsection
