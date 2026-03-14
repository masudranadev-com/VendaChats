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
    <article class="orders-kpi-card">
      <span>Orders Today</span>
      <strong data-orders-kpi-orders-today-value>--</strong>
      <small data-orders-kpi-orders-today-meta>--</small>
    </article>
    <article class="orders-kpi-card">
      <span>Gross Revenue</span>
      <strong data-orders-kpi-gross-revenue-value>--</strong>
      <small data-orders-kpi-gross-revenue-meta>--</small>
    </article>
    <article class="orders-kpi-card">
      <span>Pending Dispatch</span>
      <strong data-orders-kpi-pending-dispatch-value>--</strong>
      <small data-orders-kpi-pending-dispatch-meta>--</small>
    </article>
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
        <strong data-orders-hero-processing-time>--</strong>
      </div>
      <div>
        <span>Dispatch SLA</span>
        <strong data-orders-hero-dispatch-sla>--</strong>
      </div>
      <div>
        <span>Return Risk</span>
        <strong data-orders-hero-return-risk>--</strong>
      </div>
    </div>
  </section>

  <section class="orders-pipeline-grid mt-xl">
    <article class="orders-pipeline-card">
      <span>Total Order</span>
      <a href="javascript:void(0);" class="text-primary" data-orders-pipeline-total>--</a>
    </article>
    <article class="orders-pipeline-card">
      <span>Rejected Order</span>
      <a href="javascript:void(0);" class="text-danger" data-orders-pipeline-rejected>--</a>
    </article>
    <article class="orders-pipeline-card">
      <span>Pending Order</span>
      <a href="javascript:void(0);" class="text-warning" data-orders-pipeline-pending>--</a>
    </article>
    <article class="orders-pipeline-card">
      <span>Completed Order</span>
      <a href="javascript:void(0);" class="text-success" data-orders-pipeline-completed>--</a>
    </article>
  </section>

  <section
    class="card mt-xl"
    id="ordersCatalogSection"
    data-api-base-url="{{ $ordersApiBaseUrl }}"
    data-refresh-token="{{ $ordersRefreshToken }}"
    data-order-view-url-template="{{ route('admin.orders.view', ['orderId' => '__ORDER_ID__']) }}"
    data-order-invoice-url-template="{{ route('admin.orders.invoice', ['orderId' => '__ORDER_ID__']) }}"
    data-per-page="10"
  >
    <div class="card-header">
      <h3 class="card-title">Order Queue</h3>
      <span class="badge badge-info" data-orders-count data-initial-count="{{ $orders->total() }}">
        -- monitored orders
      </span>
    </div>

    <div class="orders-filter-grid">
      <div class="form-group">
        <label class="form-label">Search</label>
        <input type="text" class="form-input" placeholder="Order ID, customer, location" data-orders-search>
      </div>
      <div class="form-group">
        <label class="form-label">Status</label>
        <select class="form-select" data-orders-status>
          <option value="all">All Status</option>
          <option value="waiting_for_payment">Waiting for Payment</option>
          <option value="waiting_for_call">Waiting for Call</option>
          <option value="waiting_for_confirmation">Waiting for Confirmation</option>
          <option value="ready_to_dispatch">Ready to Dispatch</option>
          <option value="in_transit">In Transit</option>
          <option value="success">Success</option>
          <option value="cancel_on_called">Cancel on Called</option>
          <option value="cancel_on_confirmation">Cancel on Confirmation</option>
          <option value="cancel_on_dispatch">Cancel on Dispatch</option>
          <option value="cancel_on_delivered">Cancel on Delivered</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Payment Type</label>
        <select class="form-select" data-orders-payment-type>
          <option value="all">All Payments</option>
          <option value="cod">COD</option>
          <option value="bkash">Bkash</option>
          <option value="nagad">Nagad</option>
          <option value="card">Card</option>
          <option value="bank">Bank</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Channel</label>
        <select class="form-select" data-orders-channel>
          <option value="all">All Channels</option>
          <option value="facebook">Facebook</option>
          <option value="website">Website</option>
          <option value="messenger">Messenger</option>
          <option value="whatsapp">WhatsApp</option>
          <option value="instagram">Instagram</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Sort By</label>
        <select class="form-select" data-orders-sort-by>
          <option value="newest_first">Newest First</option>
          <option value="oldest_first">Oldest First</option>
          <option value="highest_amount">Highest Amount</option>
          <option value="lowest_amount">Lowest Amount</option>
        </select>
      </div>
    </div>

    <div class="orders-filter-actions">
      <button type="button" class="btn btn-primary btn-sm" data-orders-apply>Apply Filters</button>
      <button type="button" class="btn btn-ghost btn-sm" data-orders-reset>Reset</button>
      <span class="orders-filter-result" data-orders-filter-result data-universe-count="{{ $orders->total() }}">
        Loading orders...
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
          <tr>
            <td colspan="8" class="users-empty">Loading orders...</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="orders-table-footer" data-orders-pagination-wrap hidden>
      <p class="orders-pagination-summary" data-orders-pagination-summary>Page 1 of 1</p>
      <nav class="orders-pagination-controls" data-orders-pagination-controls aria-label="Orders pagination"></nav>
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

  <div class="modal-overlay" id="ordersActionModal" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="ordersActionModalTitle">
      <div class="modal-header">
        <h3 class="modal-title" id="ordersActionModalTitle" data-orders-action-title>Order Action</h3>
        <button type="button" class="modal-close" data-modal-close aria-label="Close">x</button>
      </div>
      <div class="modal-body">
        <p data-orders-action-message>Review this order before taking action.</p>
        <p><strong>Order ID:</strong> <span data-orders-action-order-id>--</span></p>
        <p><strong>Status:</strong> <span data-orders-action-status>--</span></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-modal-close>Close</button>
        <button type="button" class="btn btn-primary" data-orders-action-confirm hidden>Confirm</button>
      </div>
    </div>
  </div>
@endsection
