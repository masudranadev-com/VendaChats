@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header orders-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="orders-header-actions">
      <button type="button" class="btn btn-primary" data-modal="ordersManualOrderModal">Create Manual Order</button>
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
        <a href="" class="text-{{ $lane['tone'] }}">{{ $lane['count'] }}</a>
      </article>
    @endforeach
  </section>

  <section class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Order Queue</h3>
      <span class="badge badge-info" data-orders-count data-initial-count="{{ count($orders) }}">
        {{ count($orders) }} monitored orders
      </span>
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
      <span class="orders-filter-result" data-orders-filter-result data-universe-count="142">
        Showing {{ count($orders) }} of 142 orders
      </span>
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
        <tbody data-orders-table-body>
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
                  <a href="{{ route('admin.orders.view', ['orderId' => $order['id']]) }}" class="btn btn-info btn-sm">
                    View
                  </a>
                  <form
                    action="{{ route('admin.orders.confirm', ['orderId' => $order['id']]) }}"
                    method="POST"
                    onsubmit="return confirm('Confirm this order and generate invoice now?');"
                  >
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">Confirm</button>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>

  <div class="modal-overlay" id="ordersManualOrderModal" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="ordersManualOrderTitle">
      <div class="modal-header">
        <h3 class="modal-title" id="ordersManualOrderTitle">Create Manual Order</h3>
        <button type="button" class="modal-close" data-modal-close aria-label="Close">x</button>
      </div>

      <form data-manual-order-form>
        <div class="modal-body">
          <div class="orders-manual-form-grid">
            <div class="form-group orders-manual-span-2">
              <label class="form-label">Assign User</label>
              <div class="orders-manual-user-type" role="radiogroup" aria-label="Assign user mode">
                <label class="orders-manual-choice">
                  <input type="radio" name="customer_mode" value="old" checked>
                  <span>Old User</span>
                </label>
                <label class="orders-manual-choice">
                  <input type="radio" name="customer_mode" value="new">
                  <span>New User</span>
                </label>
              </div>
            </div>

            <div class="form-group orders-manual-span-2" data-manual-existing-user-wrap>
              <label class="form-label" for="manualExistingUserSearch">Search Existing User</label>
              <input
                id="manualExistingUserSearch"
                class="form-input"
                type="text"
                data-manual-existing-user-search
                placeholder="Search by name, email, or phone number"
                autocomplete="off"
              >
              <div class="orders-manual-user-results hidden" data-manual-existing-user-results></div>
              <small class="orders-manual-selected-user" data-manual-selected-user>No user selected.</small>
            </div>

            <div class="form-group">
              <label class="form-label" for="manualCustomerName">Customer Name</label>
              <input id="manualCustomerName" class="form-input" type="text" name="customer_name" data-manual-user-name required>
            </div>
            <div class="form-group">
              <label class="form-label" for="manualCustomerEmail">Customer Email</label>
              <input id="manualCustomerEmail" class="form-input" type="email" name="customer_email" data-manual-user-email required>
            </div>
            <div class="form-group">
              <label class="form-label" for="manualCustomerPhone">Phone Number</label>
              <input id="manualCustomerPhone" class="form-input" type="tel" name="customer_phone" data-manual-user-phone required>
            </div>
            <div class="form-group">
              <label class="form-label" for="manualLocation">Location</label>
              <input id="manualLocation" class="form-input" type="text" name="location" data-manual-user-location required>
            </div>

            <div class="form-group orders-manual-span-2">
              <label class="form-label" for="manualProductSearch">Search Products</label>
              <input
                id="manualProductSearch"
                class="form-input"
                type="text"
                data-manual-product-search
                placeholder="Search by product title or SKU"
                autocomplete="off"
              >
              <div class="orders-manual-user-results orders-manual-product-results hidden" data-manual-product-results></div>
              <small class="orders-manual-selected-user" data-manual-products-count>No products selected.</small>
              <div class="orders-manual-products-selected" data-manual-products-selected></div>
            </div>

            <div class="form-group">
              <label class="form-label" for="manualCouponCode">Coupon Code (Optional)</label>
              <input id="manualCouponCode" class="form-input" type="text" name="coupon_code" data-manual-coupon placeholder="EID25, VIP150">
            </div>
            <div class="form-group">
              <label class="form-label" for="manualDiscountType">Discount Type</label>
              <select id="manualDiscountType" class="form-select" name="discount_type" data-manual-discount-type>
                <option value="fixed">Fixed Amount (BDT)</option>
                <option value="percent">Percentage (%)</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="manualDiscountValue">Discount Value</label>
              <input id="manualDiscountValue" class="form-input" type="number" name="discount_value" min="0" step="0.01" value="0" data-manual-discount-value>
            </div>

            <div class="form-group">
              <label class="form-label" for="manualSubtotal">Subtotal (Auto)</label>
              <input id="manualSubtotal" class="form-input" type="number" name="subtotal" value="0" readonly>
            </div>
            <div class="form-group">
              <label class="form-label" for="manualItemCount">Items (Auto)</label>
              <input id="manualItemCount" class="form-input" type="number" name="items" value="0" readonly>
            </div>
            <div class="form-group">
              <label class="form-label" for="manualAmount">Grand Total (BDT, Auto)</label>
              <input id="manualAmount" class="form-input" type="number" name="amount" value="0" readonly>
            </div>
            <div class="form-group orders-manual-span-2">
              <small class="orders-manual-discount-preview" data-manual-discount-preview>No discount applied.</small>
            </div>
            <div class="form-group">
              <label class="form-label" for="manualPayment">Payment</label>
              <select id="manualPayment" class="form-select" name="payment" required>
                <option value="COD">COD</option>
                <option value="Paid">Paid</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="manualChannel">Channel</label>
              <select id="manualChannel" class="form-select" name="channel" required>
                <option value="Website">Website</option>
                <option value="Messenger">Messenger</option>
                <option value="WhatsApp">WhatsApp</option>
                <option value="Instagram">Instagram</option>
                <option value="Manual Entry">Manual Entry</option>
              </select>
            </div>
          </div>
          <p class="orders-manual-form-note">
            Select an old user or add a new user profile, then submit. This is frontend demo only (no backend save).
          </p>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
          <button type="button" class="btn btn-secondary" data-manual-order-reset>Reset</button>
          <button type="submit" class="btn btn-primary">Add Order</button>
        </div>
      </form>
    </div>
  </div>
@endsection
