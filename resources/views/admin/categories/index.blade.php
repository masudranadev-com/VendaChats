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
    </div>
  </div>

  <article class="card settings-panel mt-xl categories-create-panel" data-category-create-panel>
    <div class="card-header">
      <div>
        <h3 class="card-title">Add New Category</h3>
        <p class="settings-panel-subtitle">Create categories directly from live API.</p>
      </div>
      <span class="badge badge-success">Live API</span>
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
      <div class="form-group">
        <label class="form-label" for="categoryImageInput">Category Image</label>
        <input
          id="categoryImageInput"
          type="file"
          class="form-input"
          data-category-image-input
          accept="image/*"
        >
        <small class="categories-image-upload-hint">Upload an image under 2MB. Recommended: 1080 x 1080 px (1:1) in JPG/WEBP.</small>
        <div class="categories-image-upload-preview" data-category-image-preview>
          <span class="categories-image-upload-placeholder">No image selected</span>
        </div>
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
      <small class="categories-ai-status" data-category-ai-status>Click AI Write to auto-generate a category description.</small>
    </div>

    <div class="settings-inline-actions">
      <button type="button" class="btn btn-primary btn-sm" data-category-add-button>+ Add Category</button>
    </div>
  </article>

  <article class="card settings-panel mt-xl categories-edit-panel" data-category-edit-panel aria-hidden="true">
    <div class="card-header">
      <div>
        <h3 class="card-title" data-category-edit-title>Edit Category</h3>
        <p class="settings-panel-subtitle">Edit mode. Values are loaded from selected category.</p>
      </div>
      <span class="badge badge-warning">Editing</span>
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
      <div class="form-group">
        <label class="form-label" for="editCategoryImageInput">Category Image</label>
        <input
          id="editCategoryImageInput"
          type="file"
          class="form-input"
          data-category-edit-image
          accept="image/*"
        >
        <small class="categories-image-upload-hint">Upload an image under 2MB. Recommended: 1080 x 1080 px (1:1) in JPG/WEBP.</small>
        <div class="categories-image-upload-preview" data-category-edit-image-preview>
          <span class="categories-image-upload-placeholder">No image selected</span>
        </div>
      </div>
    </div>

    <div class="form-group mt-md">
      <label class="form-label" for="editCategoryDescriptionInput">Description</label>
      <textarea id="editCategoryDescriptionInput" class="form-textarea" data-category-edit-description></textarea>
    </div>

    <div class="settings-inline-actions">
      <button type="button" class="btn btn-primary btn-sm" data-category-edit-save>Update Category</button>
      <button type="button" class="btn btn-secondary btn-sm" data-category-edit-cancel>Cancel Edit</button>
    </div>
  </article>

  <article class="card settings-panel mt-md">
    <div class="card-header">
      <div>
        <h3 class="card-title">All Categories</h3>
        <p class="settings-panel-subtitle">Complete category list from categories API.</p>
      </div>
      <span class="badge badge-info" data-categories-total>0 total</span>
    </div>

    <div class="categories-delete-feedback" data-categories-delete-feedback aria-live="polite"></div>

    <div class="table-container categories-table-container">
      <table class="table categories-table">
        <thead>
          <tr>
            <th>Image</th>
            <th>Category</th>
            <th>Slug</th>
            <th>Products</th>
            <th>Category Share Snapshot</th>
            <th>Status</th>
            <th>Updated</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody
          data-categories-table-body
          data-api-base-url="{{ $categoriesApiBaseUrl }}"
          data-refresh-token="{{ $categoriesRefreshToken }}"
        >
          <tr>
            <td colspan="8">Loading categories...</td>
          </tr>
        </tbody>
      </table>
    </div>
  </article>

  <div class="modal-overlay" id="categoriesValidationModal" aria-hidden="true">
    <div class="modal categories-validation-modal" role="dialog" aria-modal="true" aria-labelledby="categoriesValidationModalTitle">
      <div class="modal-header categories-validation-modal-header">
        <div class="categories-validation-modal-title-wrap">
          <span class="categories-validation-modal-icon" aria-hidden="true">!</span>
          <div>
            <h3 class="modal-title categories-validation-modal-title" id="categoriesValidationModalTitle" data-category-validation-title>
              Category Image Error
            </h3>
            <p class="categories-validation-modal-subtitle">Please follow image upload rules.</p>
          </div>
        </div>
        <button type="button" class="modal-close categories-validation-modal-close" data-modal-close aria-label="Close">x</button>
      </div>
      <div class="modal-body">
        <p class="categories-validation-modal-message" data-category-validation-message>
          Please check your image and try again.
        </p>
        <ul class="categories-validation-modal-rules">
          <li>Allowed files: JPG, PNG, WEBP.</li>
          <li>Max upload size: less than 2MB.</li>
          <li>Minimum dimensions: 600 x 600 px.</li>
          <li>Best quality: 1080 x 1080 px (1:1 square).</li>
        </ul>
      </div>
      <div class="modal-footer categories-validation-modal-footer">
        <button type="button" class="btn btn-primary" data-modal-close data-category-validation-close>Got it</button>
      </div>
    </div>
  </div>
@endsection
