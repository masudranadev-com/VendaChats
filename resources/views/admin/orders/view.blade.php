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

  @if (session('success'))
    <div class="orders-confirm-message">
      <span class="badge badge-success">{{ session('success') }}</span>
    </div>
  @endif

  @if ($errors->any())
    <div class="orders-confirm-message">
      @foreach ($errors->all() as $error)
        <span class="badge badge-danger">{{ $error }}</span>
      @endforeach
    </div>
  @endif

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
      <h3 class="card-title">Coupon / Manual Discount</h3>
      @if ($order['manual_discount']['is_applied'] ?? false)
        <span class="badge badge-success">Applied</span>
      @else
        <span class="badge badge-warning">Not Applied</span>
      @endif
    </div>

    <form action="{{ route('admin.orders.discount.apply', ['orderId' => $order['id']]) }}" method="POST">
      @csrf
      <div class="orders-discount-grid">
        <div class="form-group">
          <label class="form-label" for="coupon_code">Coupon Code (Optional)</label>
          <input
            id="coupon_code"
            type="text"
            name="coupon_code"
            class="form-input"
            placeholder="EID25, VIP-150..."
            value="{{ old('coupon_code', $order['manual_discount']['coupon_code'] ?? '') }}"
          >
        </div>
        <div class="form-group">
          <label class="form-label" for="discount_type">Discount Type</label>
          <select id="discount_type" name="discount_type" class="form-select">
            <option value="fixed" {{ old('discount_type', $order['manual_discount']['type'] ?? 'fixed') === 'fixed' ? 'selected' : '' }}>
              Fixed Amount (BDT)
            </option>
            <option value="percent" {{ old('discount_type', $order['manual_discount']['type'] ?? 'fixed') === 'percent' ? 'selected' : '' }}>
              Percentage (%)
            </option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="discount_value">Discount Value</label>
          <input
            id="discount_value"
            type="number"
            name="discount_value"
            class="form-input"
            min="0.01"
            step="0.01"
            placeholder="100 or 10 for 10%"
            value="{{ old('discount_value', ($order['manual_discount']['value'] ?? 0) > 0 ? $order['manual_discount']['value'] : '') }}"
            required
          >
        </div>
      </div>

      <div class="orders-discount-actions">
        <button type="submit" class="btn btn-primary">Apply Discount</button>
      </div>
    </form>

    @if ($order['manual_discount']['is_applied'] ?? false)
      <div class="orders-discount-summary">
        <p>
          Manual discount active:
          <strong>- BDT {{ number_format($order['manual_discount']['amount']) }}</strong>
          @if (($order['manual_discount']['coupon_code'] ?? null) !== null)
            using coupon <strong>{{ $order['manual_discount']['coupon_code'] }}</strong>.
          @endif
        </p>
        <form
          action="{{ route('admin.orders.discount.remove', ['orderId' => $order['id']]) }}"
          method="POST"
          onsubmit="return confirm('Remove manual discount from this order?');"
        >
          @csrf
          <button type="submit" class="btn btn-secondary btn-sm">Remove Manual Discount</button>
        </form>
      </div>
    @endif

    <p class="orders-discount-help">
      Tip: use Percentage for coupon campaigns and Fixed Amount for direct negotiation discounts.
    </p>
  </section>

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
        @if (($order['manual_discount']['amount'] ?? 0) > 0)
          <small class="orders-discount-inline-note">Includes manual discount: - BDT {{ number_format($order['manual_discount']['amount']) }}</small>
        @endif
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
