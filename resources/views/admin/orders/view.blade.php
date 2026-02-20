@extends('admin.master')

@section('title', $title)

@section('admin.content')
  @php
    $fraudTone = $fraud['is_flagged'] ? 'danger' : 'success';
    $fraudLabel = $fraud['is_flagged'] ? 'Potential Fraud' : 'Trusted Customer';
  @endphp

  <div class="page-header orders-page-header">
    <div>
      <h1 class="page-title">Order {{ $order['id'] }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="orders-header-actions">
      <a href="{{ route('admin.orders') }}" class="btn btn-secondary">Back to Orders</a>
      <a href="{{ route('admin.users.views', ['user_id' => $order['customer']['id']]) }}" class="btn btn-info">
        User Profile
      </a>
      <form
        action="{{ route('admin.orders.confirm', ['orderId' => $order['id']]) }}"
        method="POST"
        onsubmit="return confirm('Confirm this order and generate invoice now?');"
      >
        @csrf
        <button type="submit" class="btn btn-success">Confirm + Invoice</button>
      </form>
    </div>
  </div>

  <div class="orders-detail-grid">
    <section class="card orders-customer-card">
      <div class="card-header">
        <h3 class="card-title">Customer Details</h3>
        <span class="badge badge-info">{{ $order['customer']['id'] }}</span>
      </div>

      <div class="orders-customer-id">
        <span class="orders-customer-avatar">{{ strtoupper(substr($order['customer']['name'], 0, 1)) }}</span>
        <div>
          <strong>{{ $order['customer']['name'] }}</strong>
          <small>{{ $order['customer']['email'] }}</small>
        </div>
      </div>

      <div class="orders-detail-lines">
        <span>Phone: <strong>{{ $order['customer']['phone'] }}</strong></span>
        <span>Location: <strong>{{ $order['customer']['location'] }}</strong></span>
        <span>Address: <strong>{{ $order['customer']['address'] }}</strong></span>
      </div>

      <div class="orders-detail-tags">
        <span class="badge badge-primary">Payment: {{ $order['payment'] }}</span>
        <span class="badge badge-info">Channel: {{ $order['channel'] }}</span>
        <span class="badge badge-warning">{{ $order['shipping_method'] }}</span>
      </div>
    </section>

    <section class="card orders-fraud-card">
      <div class="card-header">
        <h3 class="card-title">Fraud Signal</h3>
        <span class="badge badge-{{ $fraudTone }}">{{ $fraudLabel }}</span>
      </div>

      <div class="orders-fraud-metric is-{{ $fraudTone }}">
        <span>Delivered but Not Received Claims</span>
        <strong>{{ $fraud['claims'] }}</strong>
      </div>

      <div class="orders-fraud-stats">
        <div>
          <span>Delivered Orders</span>
          <strong>{{ $fraud['delivered'] }}</strong>
        </div>
        <div>
          <span>Completed Orders</span>
          <strong>{{ $fraud['completed'] }}</strong>
        </div>
      </div>

      <p class="orders-fraud-note">
        @if ($fraud['is_flagged'])
          Customer has previous delivery disputes. Verify phone and location before dispatch.
        @else
          No suspicious delivery claims found in previous orders.
        @endif
      </p>
    </section>
  </div>

  <section class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Current Order Products</h3>
      <span class="badge badge-info">{{ count($order['products']) }} products</span>
    </div>

    <div class="table-container orders-products-table-wrap">
      <table class="table orders-products-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>SKU</th>
            <th>Variant</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Line Total</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($order['products'] as $item)
            <tr>
              <td>
                <div class="orders-line-product">
                  <span class="orders-line-product-thumb">
                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" loading="lazy">
                  </span>
                  <div>
                    <strong>{{ $item['name'] }}</strong>
                  </div>
                </div>
              </td>
              <td>{{ $item['sku'] }}</td>
              <td>{{ $item['variant'] }}</td>
              <td>{{ $item['qty'] }}</td>
              <td>BDT {{ number_format($item['unit_price']) }}</td>
              <td class="orders-cell-strong">BDT {{ number_format($item['qty'] * $item['unit_price']) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="orders-totals">
      <div>
        <span>Subtotal</span>
        <strong>BDT {{ number_format($order['totals']['subtotal']) }}</strong>
      </div>
      <div>
        <span>Shipping Fee</span>
        <strong>BDT {{ number_format($order['totals']['shipping_fee']) }}</strong>
      </div>
      <div>
        <span>Discount</span>
        <strong>- BDT {{ number_format($order['totals']['discount']) }}</strong>
      </div>
      <div class="orders-total-grand">
        <span>Grand Total</span>
        <strong>BDT {{ number_format($order['totals']['grand_total']) }}</strong>
      </div>
    </div>
  </section>

  <section class="card mt-xl" id="previousOrders">
    <div class="card-header">
      <h3 class="card-title">Previous Orders</h3>
      <span class="badge badge-primary">{{ count($order['previous_orders']) }} history</span>
    </div>

    <div class="table-container">
      <table class="table">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Date</th>
            <th>Status</th>
            <th>Amount</th>
            <th>Issue</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($order['previous_orders'] as $history)
            <tr>
              <td>{{ $history['id'] }}</td>
              <td>{{ $history['date'] }}</td>
              <td>
                <span class="badge {{ in_array($history['status'], ['Completed', 'Delivered'], true) ? 'badge-success' : 'badge-warning' }}">
                  {{ $history['status'] }}
                </span>
              </td>
              <td>BDT {{ number_format($history['amount']) }}</td>
              <td>
                <span class="badge {{ $history['issue'] === 'None' ? 'badge-success' : 'badge-danger' }}">
                  {{ $history['issue'] }}
                </span>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>
@endsection
