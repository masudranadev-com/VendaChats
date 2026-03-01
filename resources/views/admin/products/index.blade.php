@extends('admin.master')

@section('title', $title)

@section('admin.content')

  {{-- ══ PAGE HEADER ══ --}}
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

  {{-- ══ KPI STRIP ══ --}}
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

  {{-- ══ PRODUCT CATALOG ══ --}}
  <section class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Product Catalog</h3>
      <div class="products-card-tools">
        <span class="badge badge-info">{{ $products->total() }} Products</span>
      </div>
    </div>

    {{-- Filters --}}
    <div class="products-filter-grid products-filter-grid--5">
      <div class="form-group">
        <label class="form-label">Search</label>
        <input type="text" class="form-input" placeholder="Product name...">
      </div>
      <div class="form-group">
        <label class="form-label">Type</label>
        <select class="form-select">
          <option value="">All Types</option>
          <option value="physical">📦 Physical</option>
          <option value="downloadable">⬇️ Downloadable</option>
          <option value="subscription">🔄 Subscription</option>
          <option value="package">📦 Package</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Category</label>
        <select class="form-select">
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
        <select class="form-select">
          <option value="">All Status</option>
          <option>Active</option>
          <option>Draft</option>
          <option>Out of Stock</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Sort By</label>
        <select class="form-select">
          <option>Latest Added</option>
          <option>Highest Sales (7d)</option>
          <option>Lowest Stock</option>
          <option>Price: High to Low</option>
          <option>Price: Low to High</option>
        </select>
      </div>
    </div>

    <div class="products-filter-actions">
      <button type="button" class="btn btn-primary btn-sm">Apply Filter</button>
      <button type="button" class="btn btn-ghost btn-sm">Reset</button>
      <span class="products-filter-result">
        @if ($products->count() > 0)
          Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }} products
        @else
          No products found
        @endif
      </span>
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
        <tbody>
          @forelse ($products as $product)
            @php
              $type       = $product['product_type'] ?? 'physical';
              $typeLabels = ['physical' => 'Physical', 'downloadable' => 'Downloadable', 'subscription' => 'Subscription', 'package' => 'Package'];
              $typeIcons  = ['physical' => '📦', 'downloadable' => '⬇️', 'subscription' => '🔄', 'package' => '📦'];
              $typeCss    = ['physical' => 'products-type-tag--physical', 'downloadable' => 'products-type-tag--downloadable', 'subscription' => 'products-type-tag--subscription', 'package' => 'products-type-tag--package'];

              $stockCss   = match ($product['stock_label'] ?? '') {
                'Critical'  => 'badge-danger',
                'Low Stock' => 'badge-warning',
                default     => 'badge-success',
              };

              $statusCss  = match ($product['status']) {
                'Active'    => 'badge-success',
                'Draft'     => 'badge-warning',
                default     => 'badge-info',
              };

              $initial = strtoupper(substr($product['name'], 0, 1));
            @endphp
            <tr>

              {{-- ── Product ── --}}
              <td>
                <div class="products-catalog-cell">
                  @if (!empty($product['image']))
                    <span class="products-catalog-thumb">
                      <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy">
                    </span>
                  @else
                    <span class="products-catalog-thumb products-catalog-thumb--initial products-catalog-thumb--{{ $type }}">
                      {{ $initial }}
                    </span>
                  @endif

                  <div class="products-catalog-meta">
                    <strong class="products-catalog-name" title="{{ $product['name'] }}">
                      {{ \Illuminate\Support\Str::limit($product['name'], 24, '…') }}
                    </strong>
                    <div class="products-catalog-tags">
                      <span class="products-type-tag {{ $typeCss[$type] ?? '' }}">
                        {{ $typeIcons[$type] ?? '📦' }} {{ $typeLabels[$type] ?? 'Physical' }}
                      </span>
                      <span class="products-catalog-category">{{ $product['category'] }}</span>
                    </div>
                  </div>
                </div>
              </td>

              {{-- ── Price ── --}}
              <td>
                <div class="products-catalog-price">
                  <strong>{{ $product['price'] }}</strong>
                  @if (!empty($product['has_discount']))
                    <div class="products-catalog-price-sub">
                      <del class="products-catalog-original">{{ $product['original_price'] ?? '' }}</del>
                      <span class="products-catalog-discount-badge">{{ $product['discount_label'] ?? '' }}</span>
                    </div>
                  @endif
                </div>
              </td>

              {{-- ── Stock / Access ── --}}
              <td>
                @if ($type === 'physical')
                  @if (!empty($product['has_variants']))
                    {{-- Product with variants --}}
                    <div class="products-catalog-stock">
                      <div class="products-catalog-stock-top">
                        <span class="products-catalog-stock-num">{{ number_format($product['total_stock'] ?? 0) }} units total</span>
                        <span class="badge badge-xs {{ $stockCss }}">{{ $product['stock_label'] ?? 'In Stock' }}</span>
                      </div>
                      <div class="products-catalog-variants-info">
                        <span class="products-catalog-variants-badge">
                          🎨 {{ $product['variant_count'] ?? 0 }} Variant{{ ($product['variant_count'] ?? 0) > 1 ? 's' : '' }}
                        </span>
                      </div>
                    </div>
                  @else
                    {{-- Simple product without variants --}}
                    <div class="products-catalog-stock">
                      <div class="products-catalog-stock-top">
                        <span class="products-catalog-stock-num">{{ number_format($product['stock'] ?? 0) }} units</span>
                        <span class="badge badge-xs {{ $stockCss }}">{{ $product['stock_label'] ?? 'In Stock' }}</span>
                      </div>
                      <div class="products-catalog-progress">
                        <div class="products-catalog-progress-fill {{ $stockCss }}" style="width: {{ min($product['stock'] ?? 0, 100) }}%"></div>
                      </div>
                    </div>
                  @endif

                @elseif ($type === 'downloadable')
                  <div class="products-catalog-access">
                    <span class="products-catalog-access-label products-catalog-access-label--digital">
                      ∞ Unlimited
                    </span>
                    <small class="products-catalog-access-note">No stock tracking — digital delivery</small>
                  </div>

                @elseif ($type === 'subscription')
                  <div class="products-catalog-access">
                    <span class="products-catalog-access-label products-catalog-access-label--slots">
                      {{ $product['subscription_slots'] ?? 0 }} Slots available
                    </span>
                    <small class="products-catalog-access-note">Seller managed · Auto-assigned per order</small>
                  </div>

                @elseif ($type === 'package')
                  <div class="products-catalog-access">
                    <span class="products-catalog-access-label products-catalog-access-label--package">
                      {{ $product['package_facilities'] ?? 0 }} Facilities included
                    </span>
                    <small class="products-catalog-access-note">Bundle package · Multiple facilities</small>
                  </div>
                @endif
              </td>

              {{-- ── Performance ── --}}
              <td>
                <div class="products-catalog-perf">
                  <strong>{{ number_format($product['sales']) }} sales</strong>
                  <small>{{ number_format($product['visitors']) }} visitors</small>
                </div>
              </td>

              {{-- ── Status ── --}}
              <td>
                <span class="badge {{ $statusCss }}">{{ $product['status'] }}</span>
              </td>

              {{-- ── Actions ── --}}
              <td>
                <div class="products-table-actions">
                  <button type="button" class="btn btn-ghost btn-sm">View</button>
                  <a href="{{ route('admin.products.create') }}" class="btn btn-secondary btn-sm">Edit</a>
                </div>
              </td>

            </tr>
          @empty
            <tr>
              <td colspan="6">
                <div class="products-catalog-empty">
                  <span class="products-catalog-empty-icon">📦</span>
                  <p>No products found.</p>
                  <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">+ Add First Product</a>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    @if ($products->hasPages())
      <div class="products-table-footer">
        <p class="products-pagination-summary">
          Page {{ $products->currentPage() }} of {{ $products->lastPage() }}
        </p>
        <nav class="products-pagination-controls" aria-label="Products pagination">
          @if ($products->onFirstPage())
            <span class="products-page-btn is-disabled" aria-disabled="true">Prev</span>
          @else
            <a href="{{ $products->previousPageUrl() }}" class="products-page-btn">Prev</a>
          @endif

          @for ($page = 1; $page <= $products->lastPage(); $page++)
            @if ($page === $products->currentPage())
              <span class="products-page-btn is-active" aria-current="page">{{ $page }}</span>
            @else
              <a href="{{ $products->url($page) }}" class="products-page-btn">{{ $page }}</a>
            @endif
          @endfor

          @if ($products->hasMorePages())
            <a href="{{ $products->nextPageUrl() }}" class="products-page-btn">Next</a>
          @else
            <span class="products-page-btn is-disabled" aria-disabled="true">Next</span>
          @endif
        </nav>
      </div>
    @endif
  </section>

  {{-- ══ NEEDS ATTENTION TOGGLE ══ --}}
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
