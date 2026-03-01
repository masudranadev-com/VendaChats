@extends('admin.master')

@section('title', $title)

@section('admin.content')

  {{-- -- PAGE HEADER -- --}}
  <div class="page-header products-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>
    <div class="products-header-actions">
      <a href="{{ route('admin.categories') }}" class="btn btn-secondary">Manage Categories</a>
      <a href="{{ route('admin.products.create') }}" class="btn btn-primary">+ Add Product</a>
    </div>
  </div>

  {{-- -- KPI STRIP -- --}}
  <section class="products-kpi-grid">
    <article class="products-kpi-card is-primary">
        <span>Total Products</span>
        <strong>286</strong>
        <small>+14 this month</small>
    </article>
    <article class="products-kpi-card is-success">
        <span>Live Products</span>
        <strong>249</strong>
        <small>37 need review</small>
    </article>
    <article class="products-kpi-card is-warning">
        <span>Low Stock Alert</span>
        <strong>18</strong>
        <small>Restock in 24h</small>
    </article>
    <article class="products-kpi-card is-info">
        <span>Total Visitor</span>
        <strong>249</strong>
        <small>+10% this month</small>
    </article>
  </section>


  {{-- -- PRODUCT CATALOG -- --}}
  <section
    class="card mt-xl"
    id="productsCatalogSection"
    data-api-base-url="{{ $productsApiBaseUrl }}"
    data-refresh-token="{{ $productsRefreshToken }}"
    data-per-page="5"
  >
    <div class="card-header">
      <h3 class="card-title">Product Catalog</h3>
      <div class="products-card-tools">
        <span class="badge badge-info" data-products-total-badge>Loading...</span>
      </div>
    </div>

    {{-- Filters --}}
    <div class="products-filter-grid products-filter-grid--5">
      <div class="form-group">
        <label class="form-label">Search</label>
        <input type="text" class="form-input" placeholder="Product name..." data-products-search>
      </div>
      <div class="form-group">
        <label class="form-label">Type</label>
        <select class="form-select" data-products-type>
          <option value="">All Types</option>
          <option value="physical">Physical</option>
          <option value="downloadable">Downloadable</option>
          <option value="subscription">Subscription</option>
          <option value="package">Package</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Category</label>
        <select class="form-select" data-products-category>
          <option value="">All Categories</option>
          <option>Apparel</option>
          <option>Electronics</option>
          <option>Footwear</option>
          <option>Accessories</option>
          <option>Digital</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Status</label>
        <select class="form-select" data-products-status>
          <option value="">All Status</option>
          <option>Active</option>
          <option>Draft</option>
          <option>Out of Stock</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Sort By</label>
        <select class="form-select" data-products-sort>
          <option value="latest">Latest Added</option>
          <option value="sales_desc">Highest Sales (7d)</option>
          <option value="stock_asc">Lowest Stock</option>
          <option value="price_desc">Price: High to Low</option>
          <option value="price_asc">Price: Low to High</option>
        </select>
      </div>
    </div>

    <div class="products-filter-actions">
      <button type="button" class="btn btn-primary btn-sm" data-products-apply>Apply Filter</button>
      <button type="button" class="btn btn-ghost btn-sm" data-products-reset>Reset</button>
      <span class="products-filter-result" data-products-result>Loading products...</span>
    </div>

    {{-- Table --}}
    <div class="table-container products-table-container">
      <table class="table products-catalog-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Stock / Access</th>
            <th>Performance <small style="font-weight:400; font-size:10px;">(7d)</small></th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody data-products-tbody>
          <tr>
            <td colspan="6">
              <div class="products-catalog-empty">
                <p>Loading products...</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    <div class="products-table-footer" data-products-pagination-wrap hidden>
      <p class="products-pagination-summary" data-products-pagination-summary>Page 1 of 1</p>
      <nav class="products-pagination-controls" data-products-pagination-controls aria-label="Products pagination"></nav>
    </div>

  </section>

  {{-- -- NEEDS ATTENTION TOGGLE -- --}}
  <button
    type="button"
    class="products-side-toggle"
    data-products-attention-toggle
    aria-controls="productsAttentionPanel"
    aria-expanded="false"
    aria-label="Open Needs Attention panel"
  >
    <span class="products-side-toggle-icon">&#9888;</span>
    <span class="products-side-toggle-text">Needs Attention</span>
    <span class="products-side-toggle-count">3</span>
  </button>

  <div class="products-side-backdrop" data-products-attention-backdrop aria-hidden="true"></div>

  <section class="card products-side-card products-side-panel" id="productsAttentionPanel" aria-hidden="true">
    <div class="card-header products-side-panel-header">
      <h3 class="card-title">Needs Attention</h3>
      <div class="products-side-panel-actions">
        <span class="badge badge-warning">3 alerts</span>
        <button type="button" class="products-side-close" data-products-attention-close aria-label="Close">&times;</button>
      </div>
    </div>

    <ul class="products-attention-list">
      <li>
          <strong>11 units left on Leather Office Backpack</strong>
          <p>Top wishlist item. Create urgent restock request.</p>
      </li>
      <li>
          <strong>Hoodie return rate increased to 6.2%</strong>
          <p>Check size chart and fabric details on product page.</p>
      </li>
      <li>
          <strong>3 products missing size variation</strong>
          <p>Publish size options to reduce drop-off on checkout.</p>
      </li>
    </ul>
  </section>

@endsection

