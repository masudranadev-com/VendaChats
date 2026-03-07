@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header dashboard-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>
  </div>

  <section
    id="adminDashboardSection"
    data-api-base-url="{{ $ordersApiBaseUrl }}"
    data-refresh-token="{{ $ordersRefreshToken }}"
    data-per-page="10"
  >
    <section class="card users-filters-card">
      <div class="card-header">
        <h3 class="card-title">Order Filters</h3>
        <span class="badge badge-info" data-dashboard-total-badge>Loading...</span>
      </div>

      <div class="users-filters-form">
        <div class="users-filter-grid">
          <div class="form-group">
            <label class="form-label" for="dashboard-status">Status</label>
            <select id="dashboard-status" class="form-select" data-dashboard-status>
              <option value="all">All status</option>
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
            <label class="form-label" for="dashboard-payment-type">Payment Type</label>
            <select id="dashboard-payment-type" class="form-select" data-dashboard-payment-type>
              <option value="all">All methods</option>
              <option value="cod">COD</option>
              <option value="bkash">Bkash</option>
              <option value="nagad">Nagad</option>
              <option value="card">Card</option>
              <option value="bank">Bank</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="dashboard-channel">Channel</label>
            <select id="dashboard-channel" class="form-select" data-dashboard-channel>
              <option value="all">All channels</option>
              <option value="facebook">Facebook</option>
              <option value="website">Website</option>
              <option value="whatsapp">WhatsApp</option>
              <option value="messenger">Messenger</option>
              <option value="instagram">Instagram</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="dashboard-sort-by">Sort By</label>
            <select id="dashboard-sort-by" class="form-select" data-dashboard-sort-by>
              <option value="newest_first">Newest First</option>
              <option value="oldest_first">Oldest First</option>
              <option value="highest_amount">Highest Amount</option>
              <option value="lowest_amount">Lowest Amount</option>
            </select>
          </div>
        </div>

        <div class="users-filter-actions">
          <button type="button" class="btn btn-primary" data-dashboard-apply>Apply Filters</button>
          <button type="button" class="btn btn-secondary" data-dashboard-reset>Reset</button>
          <div class="users-filter-result" data-dashboard-result>Loading orders...</div>
        </div>
      </div>
    </section>

    <section class="dashboard-kpi-grid mt-xl">
      <article class="dashboard-kpi-card is-primary">
        <span>Orders Today</span>
        <strong data-dashboard-orders-today-value>--</strong>
        <small data-dashboard-orders-today-meta>--</small>
      </article>
      <article class="dashboard-kpi-card is-success">
        <span>Gross Revenue</span>
        <strong data-dashboard-revenue-value>--</strong>
        <small data-dashboard-revenue-meta>--</small>
      </article>
      <article class="dashboard-kpi-card is-warning">
        <span>Pending Dispatch</span>
        <strong data-dashboard-pending-dispatch-value>--</strong>
        <small data-dashboard-pending-dispatch-meta>--</small>
      </article>
      <article class="dashboard-kpi-card is-info">
        <span>Success Rate</span>
        <strong data-dashboard-success-rate-value>--</strong>
        <small data-dashboard-success-rate-meta>--</small>
      </article>
    </section>

    <section class="dashboard-overview-card mt-xl">
      <div class="dashboard-overview-copy">
        <p class="dashboard-overview-eyebrow">Live Snapshot</p>
        <h3 data-dashboard-overview-headline>Loading operational summary...</h3>
        <p data-dashboard-overview-note>Please wait while latest order analytics are fetched.</p>
      </div>

      <div class="dashboard-overview-meta">
        <article class="dashboard-overview-meta-item">
          <span>Total Orders</span>
          <strong data-dashboard-total-orders-value>--</strong>
        </article>
        <article class="dashboard-overview-meta-item">
          <span>Pending Orders</span>
          <strong data-dashboard-pending-orders-value>--</strong>
        </article>
        <article class="dashboard-overview-meta-item">
          <span>Completed Orders</span>
          <strong data-dashboard-completed-orders-value>--</strong>
        </article>
        <article class="dashboard-overview-meta-item">
          <span>Rejected Orders</span>
          <strong data-dashboard-rejected-orders-value>--</strong>
        </article>
      </div>
    </section>

    <section class="card mt-xl dashboard-card">
      <div class="card-header">
        <div>
          <h3 class="card-title">Recent Orders</h3>
          <p class="dashboard-card-subtitle">Live list from order-history API.</p>
        </div>
      </div>

      <div class="table-container">
        <table class="table dashboard-orders-table">
          <thead>
            <tr>
              <th>Order</th>
              <th>Customer</th>
              <th>Address</th>
              <th>Qty</th>
              <th>Method</th>
              <th>Channel</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody data-dashboard-orders-tbody>
            <tr>
              <td colspan="7" class="users-empty">Loading orders...</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="users-table-footer" data-dashboard-pagination-wrap hidden>
        <p class="users-pagination-summary" data-dashboard-pagination-summary>Page 1 of 1</p>
        <nav class="users-pagination-controls" data-dashboard-pagination-controls aria-label="Dashboard orders pagination"></nav>
      </div>
    </section>
  </section>
@endsection
