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
      <a href="{{ route('admin.order-call') }}" class="btn btn-info">Call Voice Setup</a>
      <a href="{{ route('admin.categories') }}" class="btn btn-secondary">Manage Categories</a>
      <a href="{{ route('admin.products.create') }}" class="btn btn-primary">+ Add Product</a>
    </div>
  </div>

  {{-- -- KPI STRIP -- --}}
  <section class="products-kpi-grid" data-products-kpi-grid>
    <article class="products-kpi-card is-primary">
        <span>Total Products</span>
        <strong>--</strong>
    </article>
    <article class="products-kpi-card is-success">
        <span>Live Products</span>
        <strong>--</strong>
    </article>
    <article class="products-kpi-card is-warning">
        <span>Low Stock Alert</span>
        <strong>--</strong>
    </article>
    <article class="products-kpi-card is-info">
        <span>Total Visitor</span>
        <strong>--</strong>
    </article>
  </section>

  {{-- -- PRODUCT CATALOG -- --}}
  <section
    class="card mt-xl"
    id="productsCatalogSection"
    data-api-base-url="{{ $productsApiBaseUrl }}"
    data-refresh-token="{{ $productsRefreshToken }}"
    data-per-page="10"
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
        <input type="text" class="form-input" placeholder="Product name or code..." data-products-search>
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
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Status</label>
        <select class="form-select" data-products-status>
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="draft">Draft</option>
          <option value="out_of_stock">Out of Stock</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Sort By</label>
        <select class="form-select" data-products-sort>
          <option value="latest">Latest Added</option>
          <option value="low_stock">Lowest Stock</option>
          <option value="price_high_to_low">Price: High to Low</option>
          <option value="price_low_to_high">Price: Low to High</option>
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
            <th>Voice</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody data-products-tbody>
          <tr>
            <td colspan="7">
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
  {{-- <button
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
  </button> --}}

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

  <div class="modal-overlay" id="productsDeleteConfirmModal" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="productsDeleteConfirmTitle">
      <div class="modal-header">
        <h3 class="modal-title" id="productsDeleteConfirmTitle">Delete Product</h3>
        <button type="button" class="modal-close" data-modal-close aria-label="Close">x</button>
      </div>
      <div class="modal-body">
        <p>
          Are you sure you want to delete
          <strong data-products-delete-name>this product</strong>?
          This action cannot be undone.
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
        <button type="button" class="btn btn-danger" data-products-delete-confirm>Delete Product</button>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="productsVoiceReadyModal" aria-hidden="true">
    <div class="modal products-voice-modal" role="dialog" aria-modal="true" aria-labelledby="productsVoiceReadyTitle">
      <div class="modal-header products-voice-modal-header">
        <div>
          <span class="products-voice-modal-kicker">Voice Ready</span>
          <h3 class="modal-title" id="productsVoiceReadyTitle" data-products-voice-ready-product>Product Voice Preview</h3>
        </div>
        <button type="button" class="modal-close" data-modal-close aria-label="Close">x</button>
      </div>
      <div class="modal-body products-voice-modal-body">
        <div class="products-voice-modal-topline">
          <span class="products-voice-modal-pill is-ready" data-products-voice-ready-language>English</span>
          <span class="products-voice-modal-topline-text" data-products-voice-ready-duration>00:32 total</span>
        </div>

        <div class="products-voice-player-card" data-products-voice-player-card>
          <button type="button" class="products-voice-player-toggle" data-products-voice-play-toggle aria-label="Play voice preview" aria-pressed="false">
            <span data-products-voice-play-icon>&gt;</span>
          </button>

          <div class="products-voice-player-content">
            <div class="products-voice-waveform" data-products-voice-waveform aria-hidden="true">
              @for ($i = 0; $i < 16; $i++)
                <span style="--voice-bar: {{ ($i % 6) + 2 }};"></span>
              @endfor
            </div>
            <div class="products-voice-player-copy">
              <strong data-products-voice-ready-title>Voice title</strong>
              <p>Preview the current call voice, update the title, and send it back to queue.</p>
            </div>
          </div>

          <div class="products-voice-player-timing">
            <strong data-products-voice-current-time>00:00</strong>
            <span data-products-voice-total-time>00:06</span>
          </div>

          <audio data-products-voice-audio preload="none"></audio>
        </div>

        <div class="products-voice-editor-card">
          <label class="form-label" for="productsVoiceTitleInput">Voice Title</label>
          <input type="text" class="form-input" id="productsVoiceTitleInput" data-products-voice-title-input placeholder="Enter voice title">
          <p class="products-voice-editor-note">Saving this title will place the voice back into the creation queue.</p>
          <div class="products-voice-editor-action">
            <button type="button" class="btn btn-primary" data-products-voice-save>Save &amp; Queue</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="productsVoiceQueueModal" aria-hidden="true">
    <div class="modal products-voice-modal products-voice-modal--queue" role="dialog" aria-modal="true" aria-labelledby="productsVoiceQueueTitle">
      <div class="modal-header products-voice-modal-header">
        <div>
          <span class="products-voice-modal-kicker">Voice Queue</span>
          <h3 class="modal-title" id="productsVoiceQueueTitle" data-products-voice-queue-product>Voice is being prepared</h3>
        </div>
        <button type="button" class="modal-close" data-modal-close aria-label="Close">x</button>
      </div>
      <div class="modal-body products-voice-modal-body">
        <div class="products-voice-modal-topline">
          <span class="products-voice-modal-pill is-queue">Creating</span>
          <span class="products-voice-modal-topline-text" data-products-voice-queue-position>Queue slot #1</span>
        </div>

        <div class="products-voice-queue-card">
          <div class="products-voice-queue-copy">
            <strong data-products-voice-queue-title>Voice title</strong>
            <p>The voice is still generating. This preview updates live while the queue is running.</p>
          </div>

          <div class="products-voice-queue-clock">
            <span>Already creating for</span>
            <strong data-products-voice-queue-elapsed>00m 00s</strong>
            <small data-products-voice-queue-eta>About 3 min left</small>
          </div>

          <div class="products-voice-queue-loader" aria-hidden="true">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
          </div>

          <div class="products-voice-queue-progress">
            <span class="products-voice-queue-progress-fill" data-products-voice-queue-progress></span>
          </div>

          <div class="products-voice-queue-meta">
            <article>
              <span>Language</span>
              <strong data-products-voice-queue-language>English</strong>
            </article>
            <article>
              <span>Status</span>
              <strong>Generating</strong>
            </article>
            <article>
              <span>Output</span>
              <strong>Call confirmation</strong>
            </article>
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection
