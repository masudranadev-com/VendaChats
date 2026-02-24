@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header dashboard-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>
  </div>

  <section class="dashboard-kpi-grid">
    @foreach ($metrics as $metric)
      <article class="dashboard-kpi-card is-{{ $metric['tone'] }}">
        <span>{{ $metric['label'] }}</span>
        <strong>{{ $metric['value'] }}</strong>
        <small>{{ $metric['meta'] }}</small>
      </article>
    @endforeach
  </section>

  <section class="dashboard-overview-card mt-xl">
    <div class="dashboard-overview-copy">
      <p class="dashboard-overview-eyebrow">{{ $overview['eyebrow'] }}</p>
      <h3>{{ $overview['headline'] }}</h3>
      <p>{{ $overview['note'] }}</p>
    </div>

    <div class="dashboard-overview-meta">
      @foreach ($overview['highlights'] as $highlight)
        <article class="dashboard-overview-meta-item">
          <span>{{ $highlight['label'] }}</span>
          <strong>{{ $highlight['value'] }}</strong>
        </article>
      @endforeach
    </div>
  </section>

  <section class="dashboard-section-grid mt-xl">
    <article class="card dashboard-card">
      <div class="card-header">
        <div>
          <h3 class="card-title">Revenue Trend (Last 7 Days)</h3>
          <p class="dashboard-card-subtitle">Fixed weekly view for quick comparison.</p>
        </div>
      </div>

      @php
        $maxRevenue = max(array_column($revenueTrend, 'amount')) ?: 1;
      @endphp

      <div class="dashboard-revenue-bars">
        @foreach ($revenueTrend as $point)
          @php
            $height = max(10, (int) round(($point['amount'] / $maxRevenue) * 100));
          @endphp
          <article class="dashboard-revenue-bar">
            <div class="dashboard-revenue-track">
              <span style="height: {{ $height }}%"></span>
            </div>
            <strong>{{ $point['short_amount'] }}</strong>
            <small>{{ $point['day'] }}</small>
          </article>
        @endforeach
      </div>
    </article>

    <article class="card dashboard-card">
      <div class="card-header">
        <div>
          <h3 class="card-title">Top Selling Products</h3>
          <p class="dashboard-card-subtitle">Current leaders by total sales revenue.</p>
        </div>
      </div>

      <div class="dashboard-product-list">
        @foreach ($topProducts as $index => $product)
          <article class="dashboard-product-item">
            <span class="dashboard-product-rank">#{{ $index + 1 }}</span>
            <div class="dashboard-product-info">
              <strong>{{ $product['name'] }}</strong>
              <p>{{ $product['sold'] }} sold | {{ $product['unit_price'] }} each</p>
            </div>
            <strong class="dashboard-product-revenue">{{ $product['revenue'] }}</strong>
          </article>
        @endforeach
      </div>
    </article>
  </section>

  <section class="card mt-xl dashboard-card">
    <div class="card-header">
      <div>
        <h3 class="card-title">Recent Orders</h3>
        <p class="dashboard-card-subtitle">Latest incoming orders and current status.</p>
      </div>
      <span class="badge badge-info">{{ count($recentOrders) }} latest</span>
    </div>

    <div class="table-container">
      <table class="table dashboard-orders-table">
        <thead>
          <tr>
            <th>Order</th>
            <th>Customer</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Placed</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($recentOrders as $order)
            @php
              $statusClass = match ($order['status']) {
                'Delivered' => 'badge-success',
                'In Transit' => 'badge-primary',
                'Processing' => 'badge-info',
                'Payment Review' => 'badge-warning',
                default => 'badge-warning',
              };
            @endphp
            <tr>
              <td>
                <div class="dashboard-order-cell">
                  <strong>{{ $order['id'] }}</strong>
                </div>
              </td>
              <td>{{ $order['customer'] }}</td>
              <td class="dashboard-order-amount">{{ $order['amount'] }}</td>
              <td><span class="badge {{ $statusClass }}">{{ $order['status'] }}</span></td>
              <td>{{ $order['placed_at'] }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>
@endsection
