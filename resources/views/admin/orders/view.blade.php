@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div
    class="ui-loading-surface is-loading"
    data-order-details-page
    data-api-base-url="{{ $ordersApiBaseUrl ?? '' }}"
    data-refresh-token="{{ $ordersRefreshToken ?? '' }}"
    data-order-id="{{ $orderId }}"
  >
    <div class="ui-section-loader" data-ui-loader>
      <span class="ui-section-loader-spinner" aria-hidden="true"></span>
      <div class="ui-section-loader-copy">
        <strong>Loading order details...</strong>
        <span>Fetching fraud signals, history, and line items.</span>
      </div>
    </div>
  <div class="page-header orders-page-header">
    <div>
      <h1 class="page-title" data-order-details-order-title>Order {{ $orderId }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="orders-header-actions">
      <a href="{{ route('admin.orders') }}" class="btn btn-secondary">Back to Orders</a>
      <a href="{{ route('admin.users.views') }}" class="btn btn-info" data-order-details-user-profile-link data-user-profile-base-url="{{ route('admin.users.views') }}">
        User Profile
      </a>
      @if ($invoiceEnabled ?? true)
        <form
          action="{{ route('admin.orders.confirm', ['orderId' => $orderId]) }}"
          method="POST"
          onsubmit="return confirm('Confirm this order and generate invoice now?');"
        >
          @csrf
          <button type="submit" class="btn btn-success">Confirm + Invoice</button>
        </form>
      @else
        <span class="badge badge-danger">Invoice disabled for this order status.</span>
      @endif
    </div>
  </div>

  <div class="orders-confirm-message" data-order-details-fetch-message hidden></div>

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
        <span class="badge badge-info" data-order-details-customer-id>--</span>
      </div>

      <div class="orders-customer-id">
        <span class="orders-customer-avatar" data-order-details-avatar-wrap>-</span>
        <div>
          <strong data-order-details-name>--</strong>
          <small data-order-details-email>--</small>
        </div>
      </div>

      <div class="orders-detail-lines">
        <span>Phone: <strong data-order-details-phone>--</strong></span>
        <span>Address: <strong data-order-details-address>--</strong></span>
      </div>

      <div class="orders-detail-tags">
        <span class="badge badge-primary" data-order-details-method>Payment: --</span>
        <span class="badge badge-info" data-order-details-channel>Channel: --</span>
      </div>
    </section>

    <section class="card orders-fraud-card">
      <div class="card-header">
        <h3 class="card-title">Fraud Signal</h3>
      </div>

      <div class="orders-fraud-stats">
        <div>
          <span>Total Order</span>
          <strong data-order-details-fraud-total-order>0</strong>
        </div>
        <div>
          <span>Total Cancelled</span>
          <strong data-order-details-fraud-total-cancelled>0</strong>
        </div>
        <div>
          <span>COD Cancelled</span>
          <strong data-order-details-fraud-cod-cancelled>0</strong>
        </div>
        <div>
          <span>Success Order</span>
          <strong data-order-details-fraud-success-order>0</strong>
        </div>
      </div>
    </section>
  </div>

  <section class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Coupon / Manual Discount</h3>
      <span class="badge badge-warning">Not Applied</span>
    </div>

    <form action="{{ route('admin.orders.discount.apply', ['orderId' => $orderId]) }}" method="POST">
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
            value="{{ old('coupon_code', '') }}"
          >
        </div>
        <div class="form-group">
          <label class="form-label" for="discount_type">Discount Type</label>
          <select id="discount_type" name="discount_type" class="form-select">
            <option value="fixed" {{ old('discount_type', 'fixed') === 'fixed' ? 'selected' : '' }}>
              Fixed Amount (BDT)
            </option>
            <option value="percent" {{ old('discount_type', 'fixed') === 'percent' ? 'selected' : '' }}>
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
            value="{{ old('discount_value', '') }}"
            required
          >
        </div>
      </div>

      <div class="orders-discount-actions">
        <button type="submit" class="btn btn-primary">Apply Discount</button>
      </div>
    </form>


    <p class="orders-discount-help">
      Tip: use Percentage for coupon campaigns and Fixed Amount for direct negotiation discounts.
    </p>
  </section>

  <section class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Current Order Products</h3>
      <span class="badge badge-info" data-order-details-products-count>0 products</span>
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
<tbody data-order-details-products-body>
  <tr>
    <td colspan="6" class="users-empty"><span class="ui-skeleton-line is-lg"></span><span class="ui-skeleton-line is-sm"></span></td>
  </tr>
</tbody>
      </table>
    </div>

    <div class="orders-totals">
      <div>
        <span>Subtotal</span>
        <strong data-order-details-total-subtotal>BDT 0</strong>
      </div>
      <div>
        <span>Delivery Charge</span>
        <strong data-order-details-total-shipping>BDT 0</strong>
      </div>
      <div>
        <span>Discount</span>
        <strong data-order-details-total-discount>- BDT 0</strong>
      </div>
      <div class="orders-total-grand">
        <span>Grand Total</span>
        <strong data-order-details-total-grand>BDT 0</strong>
      </div>
    </div>
  </section>

  <section class="card mt-xl" id="previousOrders">
    <div class="card-header">
      <h3 class="card-title">Previous Orders</h3>
      <span class="badge badge-primary" data-order-details-history-count>0 history</span>
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
<tbody data-order-details-history-body>
  <tr>
    <td colspan="5" class="users-empty"><span class="ui-skeleton-line is-lg"></span><span class="ui-skeleton-line is-sm"></span></td>
  </tr>
</tbody>
      </table>
    </div>
  </section>
  </div>
@endsection
