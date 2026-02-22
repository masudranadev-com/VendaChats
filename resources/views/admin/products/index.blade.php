@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header products-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="products-header-actions">
      <a href="{{ route('admin.categories') }}" class="btn btn-secondary">Add Category</a>
      <a href="{{ route('admin.products.create') }}" class="btn btn-primary">+ Add New Product</a>
    </div>
  </div>

  <section class="products-kpi-grid">
    @foreach ($metrics as $metric)
      <article class="products-kpi-card">
        <span>{{ $metric['label'] }}</span>
        <strong>{{ $metric['value'] }}</strong>
        <small>{{ $metric['meta'] }}</small>
      </article>
    @endforeach
  </section>

  <section class="products-hero-card mt-xl">
    <div>
      <p class="products-hero-eyebrow">Catalog Pulse</p>
      <h3>Easy Product Control Panel</h3>
      <p>Review inventory, identify low stock, and launch product actions without confusion.</p>
    </div>
    <div class="products-hero-meta">
      <div>
        <span>Sync Status</span>
        <strong>Healthy</strong>
      </div>
      <div>
        <span>Last Update</span>
        <strong>12m ago</strong>
      </div>
      <div>
        <span>Pending Edits</span>
        <strong>7</strong>
      </div>
    </div>
  </section>

  <section class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Product List</h3>
      <span class="badge badge-info">Demo Data</span>
    </div>

    <div class="products-filter-grid">
      <div class="form-group">
        <label class="form-label">Search Product</label>
        <input type="text" class="form-input" placeholder="Search by name or SKU">
      </div>
      <div class="form-group">
        <label class="form-label">Category</label>
        <select class="form-select">
          <option>All Categories</option>
          <option>Apparel</option>
          <option>Electronics</option>
          <option>Footwear</option>
          <option>Accessories</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Stock State</label>
        <select class="form-select">
          <option>All</option>
          <option>In Stock</option>
          <option>Low Stock</option>
          <option>Critical</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Sort By</label>
        <select class="form-select">
          <option>Latest Updated</option>
          <option>Highest Sales (7d)</option>
          <option>Lowest Stock</option>
          <option>Price: High to Low</option>
        </select>
      </div>
    </div>

    <div class="products-filter-actions">
      <button type="button" class="btn btn-primary btn-sm">Apply Filter</button>
      <button type="button" class="btn btn-ghost btn-sm">Reset</button>
      <span class="products-filter-result">
        @if ($products->count() > 0)
          Showing {{ $products->firstItem() }}-{{ $products->lastItem() }} of {{ $products->total() }} products
        @else
          Showing 0 products
        @endif
      </span>
    </div>

    <div class="table-container products-table-container">
      <table class="table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Visitors</th>
            <th>Sales (7d)</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($products as $product)
            @php
              $stockClass = match ($product['stock_label']) {
                'Critical' => 'badge-danger',
                'Low Stock' => 'badge-warning',
                default => 'badge-success',
              };

              $statusClass = match ($product['status']) {
                'Draft' => 'badge-warning',
                default => 'badge-primary',
              };
            @endphp
            <tr>
              <td>
                <div class="products-product-cell">
                  @if (!empty($product['image']))
                    <span class="products-product-thumb">
                      <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy">
                    </span>
                  @else
                    <span class="products-product-thumb">{{ strtoupper(substr($product['name'], 0, 1)) }}</span>
                  @endif
                  <div>
                    <strong title="{{ $product['name'] }}">{{ \Illuminate\Support\Str::limit($product['name'], 15, '...') }}</strong>
                    <small>{{ $product['sku'] }}</small>
                  </div>
                </div>
              </td>
              <td>{{ $product['category'] }}</td>
              <td class="products-cell-strong">{{ $product['price'] }}</td>
              <td>
                <div class="products-stock-wrap">
                  <span class="badge {{ $stockClass }}">{{ $product['stock_label'] }}</span>
                  <div class="products-stock-track">
                    <span style="width: {{ $product['stock'] }}%"></span>
                  </div>
                </div>
              </td>
              <td>{{ number_format($product['visitors']) }}</td>
              <td>{{ number_format($product['sales']) }}</td>
              <td><span class="badge {{ $statusClass }}">{{ $product['status'] }}</span></td>
              <td>
                <div class="products-table-actions">
                  <button type="button" class="btn btn-ghost btn-sm">View</button>
                  <button type="button" class="btn btn-secondary btn-sm">Edit</button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center">No products found for this page.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

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
        <button type="button" class="products-side-close" data-products-attention-close aria-label="Close Needs Attention panel">&times;</button>
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
      <button type="button" class="btn btn-ghost btn-sm">Export Product Sheet</button>
    </div>
  </section>
@endsection
