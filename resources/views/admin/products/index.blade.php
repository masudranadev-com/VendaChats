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
    @foreach ($metrics as $index => $metric)
      @php $kpiAccent = ['is-primary', 'is-success', 'is-warning', 'is-info'][$index] ?? ''; @endphp
      <article class="products-kpi-card {{ $kpiAccent }}">
        <span>{{ $metric['label'] }}</span>
        <strong>{{ $metric['value'] }}</strong>
        <small>{{ $metric['meta'] }}</small>
      </article>
    @endforeach
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
    <span class="products-side-toggle-count">{{ count($attentionItems) }}</span>
  </button>

  <div class="products-side-backdrop" data-products-attention-backdrop aria-hidden="true"></div>

  <section class="card products-side-card products-side-panel" id="productsAttentionPanel" aria-hidden="true">
    <div class="card-header products-side-panel-header">
      <h3 class="card-title">Needs Attention</h3>
      <div class="products-side-panel-actions">
        <span class="badge badge-warning">{{ count($attentionItems) }} alerts</span>
        <button type="button" class="products-side-close" data-products-attention-close aria-label="Close">&times;</button>
      </div>
    </div>

    <ul class="products-attention-list">
      @foreach ($attentionItems as $item)
        <li>
          <strong>{{ $item['title'] }}</strong>
          <p>{{ $item['note'] }}</p>
        </li>
      @endforeach
    </ul>

    <div class="products-divider"></div>

    <div class="card-header products-inline-header">
      <h3 class="card-title">Category Share</h3>
      <span class="badge badge-success">Sales mix</span>
    </div>

    <div class="products-category-list">
      @foreach ($categoryHealth as $category)
        <div class="products-category-item">
          <div class="flex-between">
            <span>{{ $category['name'] }}</span>
            <strong>{{ $category['share'] }}%</strong>
          </div>
          <div class="products-category-track">
            <span style="width: {{ $category['share'] }}%"></span>
          </div>
        </div>
      @endforeach
    </div>

    <div class="products-quick-actions">
      <button type="button" class="btn btn-primary btn-sm">Restock Planner</button>
      <button type="button" class="btn btn-secondary btn-sm">Price Bulk Update</button>
      <button type="button" class="btn btn-ghost btn-sm">Export Sheet</button>
    </div>
  </section>

@endsection

