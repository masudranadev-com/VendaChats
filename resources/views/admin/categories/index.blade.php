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
      <button type="button" class="btn btn-primary" data-category-save-setup>Save Category Setup</button>
    </div>
  </div>

  <section class="settings-stats-grid" data-categories-metrics>
    <article class="settings-stat-card is-info">
      <span>Loading...</span>
      <strong>--</strong>
      <small>Please wait</small>
    </article>
  </section>

  <article class="card settings-panel mt-xl categories-create-panel" data-category-create-panel>
    <div class="card-header">
      <div>
        <h3 class="card-title">Add New Category</h3>
        <p class="settings-panel-subtitle">Demo design only. Inputs are static and not saved to database.</p>
      </div>
      <span class="badge badge-info">UI Demo</span>
    </div>

    <div class="settings-field-grid">
      <div class="form-group">
        <label class="form-label" for="categoryNameInput">Category Name</label>
        <input
          id="categoryNameInput"
          type="text"
          class="form-input"
          data-category-name-input
          placeholder="e.g. Beauty &amp; Personal Care"
        >
      </div>
      <div class="form-group">
        <label class="form-label" for="categorySlugInput">Category Slug</label>
        <input id="categorySlugInput" type="text" class="form-input" data-category-slug-input placeholder="beauty-personal-care">
      </div>
      <div class="form-group">
        <label class="form-label" for="categoryVisibilityInput">Visibility</label>
        <select id="categoryVisibilityInput" class="form-select" data-category-status-input>
          <option value="Active">Active</option>
          <option value="Draft">Draft</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label" for="categoryParentInput">Parent Category</label>
        <select id="categoryParentInput" class="form-select" data-category-parent-input>
          <option value="">None (Top level)</option>
        </select>
      </div>
    </div>

    <div class="form-group mt-md">
      <div class="categories-description-head">
        <label class="form-label" for="categoryDescriptionInput">Description</label>
        <button
          type="button"
          class="categories-ai-btn"
          data-category-ai-generate
          aria-label="Write description with AI"
        >
          <span class="categories-ai-icon">&#10024;</span>
          <span class="categories-ai-btn-text">AI Write</span>
        </button>
      </div>
      <textarea
        id="categoryDescriptionInput"
        class="form-textarea"
        data-category-description-input
        placeholder="Short category note for admin reference..."
      ></textarea>
      <small class="categories-ai-status" data-category-ai-status>Click AI Write to auto-generate a demo short description.</small>
    </div>

    <div class="settings-inline-actions">
      <button type="button" class="btn btn-primary btn-sm" data-category-add-button>+ Add Category</button>
    </div>
  </article>

  <article class="card settings-panel mt-xl categories-edit-panel" data-category-edit-panel aria-hidden="true">
    <div class="card-header">
      <div>
        <h3 class="card-title" data-category-edit-title>Edit Category</h3>
        <p class="settings-panel-subtitle">Edit mode (demo only). Values are prefilled from selected row.</p>
      </div>
      <span class="badge badge-warning">Editing Mode</span>
    </div>

    <div class="categories-edit-meta">
      <span class="badge badge-info" data-category-edit-products>Products: 0</span>
      <span class="badge badge-success" data-category-edit-share>Share: 0%</span>
      <span class="badge badge-primary" data-category-edit-updated>Updated: -</span>
    </div>

    <div class="settings-field-grid mt-md">
      <div class="form-group">
        <label class="form-label" for="editCategoryNameInput">Category Name</label>
        <input id="editCategoryNameInput" type="text" class="form-input" data-category-edit-name>
      </div>
      <div class="form-group">
        <label class="form-label" for="editCategorySlugInput">Category Slug</label>
        <input id="editCategorySlugInput" type="text" class="form-input" data-category-edit-slug>
      </div>
      <div class="form-group">
        <label class="form-label" for="editCategoryStatusInput">Visibility</label>
        <select id="editCategoryStatusInput" class="form-select" data-category-edit-status>
          <option value="Active">Active</option>
          <option value="Draft">Draft</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label" for="editCategoryParentInput">Parent Category</label>
        <select id="editCategoryParentInput" class="form-select" data-category-edit-parent>
          <option value="">None (Top level)</option>
        </select>
      </div>
    </div>

    <div class="form-group mt-md">
      <label class="form-label" for="editCategoryDescriptionInput">Description</label>
      <textarea id="editCategoryDescriptionInput" class="form-textarea" data-category-edit-description></textarea>
    </div>

    <div class="settings-inline-actions">
      <button type="button" class="btn btn-primary btn-sm" data-category-edit-save>Update Category</button>
      <button type="button" class="btn btn-secondary btn-sm" data-category-edit-cancel>Cancel Edit</button>
      <span class="badge badge-info">Demo UI only</span>
    </div>
  </article>

  <article class="card settings-panel mt-md">
    <div class="card-header">
      <div>
        <h3 class="card-title">All Categories</h3>
        <p class="settings-panel-subtitle">Complete category list from demo API data.</p>
      </div>
      <span class="badge badge-info" data-categories-total>0 total</span>
    </div>

    <div class="categories-delete-feedback" data-categories-delete-feedback aria-live="polite"></div>

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
            <th>Action</th>
          </tr>
        </thead>
        <tbody data-categories-table-body>
          <tr>
            <td colspan="7">Loading categories...</td>
          </tr>
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
    <span class="products-side-toggle-icon">&#10024;</span>
    <span class="products-side-toggle-text">AI Suggestions</span>
    <span class="products-side-toggle-count" data-suggestions-count>0</span>
  </button>

  <div class="products-side-backdrop" data-products-attention-backdrop aria-hidden="true"></div>

  <section class="card products-side-card products-side-panel" id="productsAttentionPanel" aria-hidden="true">
    <div class="card-header products-side-panel-header">
      <h3 class="card-title">Suggestions</h3>
      <div class="products-side-panel-actions">
        <span class="badge badge-info" data-suggestions-next-reset>Next reset -</span>
        <button type="button" class="products-side-close" data-products-attention-close aria-label="Close Suggestions panel">&times;</button>
      </div>
    </div>

    <ul class="products-attention-list" data-suggestions-list>
      <li>
        <strong>Loading suggestions...</strong>
        <p>Please wait.</p>
      </li>
    </ul>
  </section>
@endsection
