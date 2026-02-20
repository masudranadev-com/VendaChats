@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header settings-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="settings-header-actions">
      <a href="{{ route('admin.products') }}" class="btn btn-ghost">Back to Products</a>
      <button type="button" class="btn btn-primary">Save Category Setup</button>
    </div>
  </div>

  <section class="settings-stats-grid">
    @foreach ($metrics as $metric)
      @php
        $toneClass = match ($loop->index) {
          0 => 'is-primary',
          1 => 'is-info',
          2 => 'is-success',
          default => 'is-warning',
        };
      @endphp
      <article class="settings-stat-card {{ $toneClass }}">
        <span>{{ $metric['label'] }}</span>
        <strong>{{ $metric['value'] }}</strong>
        <small>{{ $metric['meta'] }}</small>
      </article>
    @endforeach
  </section>

  <article class="card settings-panel mt-xl">
    <div class="card-header">
      <div>
        <h3 class="card-title">Add New Category</h3>
        <p class="settings-panel-subtitle">Demo design only. Inputs are static and not saved to database.</p>
      </div>
      <span class="badge badge-info">UI Demo</span>
    </div>

    <div class="settings-field-grid">
      <div class="form-group">
        <label class="form-label">Category Name</label>
        <input type="text" class="form-input" placeholder="e.g. Beauty & Personal Care">
      </div>
      <div class="form-group">
        <label class="form-label">Category Slug</label>
        <input type="text" class="form-input" placeholder="beauty-personal-care">
      </div>
      <div class="form-group">
        <label class="form-label">Visibility</label>
        <select class="form-select">
          <option>Active</option>
          <option>Draft</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Parent Category</label>
        <select class="form-select">
          <option>None (Top level)</option>
          @foreach ($categories as $category)
            <option>{{ $category['name'] }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="form-group mt-md">
      <label class="form-label">Description</label>
      <textarea class="form-textarea" placeholder="Short category note for admin reference..."></textarea>
    </div>

    <div class="settings-inline-actions">
      <button type="button" class="btn btn-primary btn-sm">+ Add Category</button>
      <button type="button" class="btn btn-secondary btn-sm">Clear</button>
      <span class="badge badge-warning">No backend submit</span>
    </div>
  </article>

  <article class="card settings-panel mt-md">
    <div class="card-header">
      <div>
        <h3 class="card-title">All Categories</h3>
        <p class="settings-panel-subtitle">Complete category list from demo controller data.</p>
      </div>
      <span class="badge badge-info">{{ count($categories) }} total</span>
    </div>

    <div class="table-container categories-table-container">
      <table class="table categories-table">
        <thead>
          <tr>
            <th>Category</th>
            <th>Slug</th>
            <th>Products</th>
            <th>Category Share Snapshot</th>
            <th>Status</th>
            <th>Updated</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($categories as $category)
            @php
              $statusClass = $category['status'] === 'Active' ? 'badge-success' : 'badge-warning';
            @endphp
            <tr>
              <td class="settings-cell-strong">{{ $category['name'] }}</td>
              <td>{{ $category['slug'] }}</td>
              <td>{{ number_format($category['products']) }}</td>
              <td class="categories-share-cell">
                <span class="categories-share-value">{{ $category['share'] }}%</span>
                <div class="settings-category-track categories-share-track">
                  <span style="width: {{ $category['share'] }}%"></span>
                </div>
              </td>
              <td><span class="badge {{ $statusClass }}">{{ $category['status'] }}</span></td>
              <td>{{ $category['updated_at'] }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </article>

  <button
    type="button"
    class="products-side-toggle"
    data-products-attention-toggle
    aria-controls="productsAttentionPanel"
    aria-expanded="false"
    aria-label="Open Suggestions panel"
  >
    <span class="products-side-toggle-icon">&#9888;</span>
    <span class="products-side-toggle-text">Suggestions</span>
    <span class="products-side-toggle-count">{{ count($suggestions) }}</span>
  </button>

  <div class="products-side-backdrop" data-products-attention-backdrop aria-hidden="true"></div>

  <section class="card products-side-card products-side-panel" id="productsAttentionPanel" aria-hidden="true">
    <div class="card-header products-side-panel-header">
      <h3 class="card-title">Suggestions</h3>
      <div class="products-side-panel-actions">
        <span class="badge badge-warning">{{ count($suggestions) }} action items</span>
        <button type="button" class="products-side-close" data-products-attention-close aria-label="Close Suggestions panel">&times;</button>
      </div>
    </div>

    <ul class="products-attention-list">
      @foreach ($suggestions as $suggestion)
        <li>
          <strong>Category Action {{ $loop->iteration }}</strong>
          <p>{{ $suggestion }}</p>
        </li>
      @endforeach
    </ul>
  </section>
@endsection
