@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header products-page-header">
    <div>
      <h1 class="page-title">Add New Product</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="products-header-actions">
      <a href="{{ route('admin.products') }}" class="btn btn-secondary">Back to Products</a>
      <button type="submit" form="createProductForm" class="btn btn-primary">Save Product</button>
    </div>
  </div>

  <form
    id="createProductForm"
    class="products-create-layout"
    onsubmit="event.preventDefault(); if (window.showSuccess) { window.showSuccess('Product saved (demo preview).'); }"
  >
    <div class="products-create-main">
      <section class="card" style="padding-bottom: 80px">
        <div class="card-header">
          <h3 class="card-title">Basic Information</h3>
          <span class="badge badge-info">Required</span>
        </div>

        <div class="products-create-grid">
          <div class="form-group products-field-span-2">
            <label class="form-label" for="productName">Product Name</label>
            <input id="productName" class="form-input" type="text" placeholder="Premium Cotton T-Shirt" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="productSku">SKU</label>
            <input id="productSku" class="form-input" type="text" placeholder="SKU-TS-2109" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="productBarcode">Barcode</label>
            <input id="productBarcode" class="form-input" type="text" placeholder="8901234567890">
          </div>

          <div class="form-group">
            <label class="form-label" for="productCategory">Category</label>
            <input
              id="productCategory"
              name="category"
              class="form-input"
              type="text"
              list="productCategoryOptions"
              placeholder="Search and select category"
              autocomplete="off"
              required
            >
            <datalist id="productCategoryOptions">
              @foreach ($categories as $category)
                <option value="{{ $category }}"></option>
              @endforeach
            </datalist>
          </div>

          <div class="form-group">
            <label class="form-label" for="productBrand">Brand</label>
            <input
              id="productBrand"
              name="brand"
              class="form-input"
              type="text"
              list="productBrandOptions"
              placeholder="Search and select brand"
              autocomplete="off"
            >
            <datalist id="productBrandOptions">
              @foreach ($brands as $brand)
                <option value="{{ $brand }}"></option>
              @endforeach
            </datalist>
          </div>

          <div class="form-group products-field-span-2">
            <div class="products-field-label">
              <label class="form-label" for="productShortDescription">Short Description</label>
              <button
                type="button"
                class="products-ai-btn"
                data-product-ai-short
                aria-label="Write short description with AI"
                title="Add Product Name first"
                disabled
              >
                <span class="products-ai-icon">&#10024;</span>
                <span class="products-ai-btn-text">AI Write</span>
              </button>
            </div>
            <textarea
              id="productShortDescription"
              class="form-textarea"
              rows="3"
              maxlength="150"
              placeholder="One-line value proposition and key feature highlights."
              required
            ></textarea>
            <small class="products-short-counter" data-product-short-count>0/150 characters</small>
          </div>

          <div class="form-group products-field-span-2">
            <div class="products-field-label">
              <label class="form-label" for="productDescription">Full Description</label>
              <button
                type="button"
                class="products-ai-btn"
                data-product-ai-full
                aria-label="Write full description with AI"
                title="Add Product Name first"
                disabled
              >
                <span class="products-ai-icon">&#10024;</span>
                <span class="products-ai-btn-text">AI Write</span>
              </button>
            </div>
            <div
              id="productDescriptionEditor"
              class="products-rich-editor"
              data-placeholder="Write complete product details, materials, fit, and use cases."
            ></div>
            <textarea id="productDescription" class="form-textarea" rows="6" placeholder="Write complete product details, materials, fit, and use cases." hidden></textarea>
          </div>
        </div>
      </section>

      <section class="card">
        <div class="card-header">
          <h3 class="card-title">Media</h3>
          <span class="badge badge-primary">Image + Video</span>
        </div>

        <div class="products-upload-box">
          <div class="products-media-group">
            <h4>Main Product Cover</h4>
            <p class="products-upload-note">
              Required. Only one image allowed.
              Best cover image size: 1080 x 1080 (1:1 ratio). Max file size: less than 2MB.
            </p>
            <input
              id="productCoverInput"
              type="file"
              class="form-input"
              accept="image/*"
              data-product-cover-input
            >
            <div class="products-media-list mt-sm" data-product-cover-list>
              <div class="products-media-empty">No cover image selected yet.</div>
            </div>
          </div>

          <div class="products-media-divider"></div>

          <div class="form-group mt-md mb-0">
            <label class="form-label">Product Image Slider</label>
            <span class="badge badge-info" data-product-slider-status>Enabled</span>

            <div class="products-publish-options mt-sm">
              <label class="products-radio-item">
                <input
                  type="radio"
                  name="image_slider_status"
                  value="enabled"
                  data-product-slider-toggle
                  checked
                >
                <span>Enabled</span>
              </label>
              <label class="products-radio-item">
                <input
                  type="radio"
                  name="image_slider_status"
                  value="disabled"
                  data-product-slider-toggle
                >
                <span>Disabled</span>
              </label>
            </div>

            <small class="form-help">If disabled, the product page will show a single image instead of a slider.</small>
          </div>

          <div class="products-slider-config mt-md" data-product-slider-config>
            <div class="form-group mb-0">
              <label class="form-label">Slider Media Type</label>
              <div class="products-publish-options mt-sm">
                <label class="products-radio-item">
                  <input
                    type="radio"
                    name="slider_media_type"
                    value="image"
                    data-product-slider-media-type
                    checked
                  >
                  <span>Image</span>
                </label>
                <label class="products-radio-item">
                  <input
                    type="radio"
                    name="slider_media_type"
                    value="video"
                    data-product-slider-media-type
                  >
                  <span>Video</span>
                </label>
              </div>
              <small class="form-help">
                For slider images: best size 1600 x 900 (16:9), less than 2MB, max 7 images.
                For slider videos: less than 30MB.
              </small>
            </div>

            <div class="form-group mt-md mb-0">
              <label class="form-label" for="productSliderItemInput">Add Slider Item</label>
              <input
                id="productSliderItemInput"
                type="file"
                class="form-input"
                accept="image/*"
                data-product-slider-item-input
              >
              <small class="form-help">Upload one file at a time. You can remove each item one by one.</small>
            </div>

            <div class="products-media-list mt-sm" data-product-slider-list>
              <div class="products-media-empty">No slider items added yet.</div>
            </div>
            <small class="form-help">After upload, use Move Up or Move Down to set slider order.</small>
          </div>
        </div>
      </section>

      <section class="card">
        <div class="card-header">
          <h3 class="card-title">Pricing & Inventory</h3>
          <span class="badge badge-success">Commerce</span>
        </div>

        <div class="products-create-grid">
          <div class="form-group">
            <label class="form-label" for="productPrice">Selling Price (BDT)</label>
            <input id="productPrice" class="form-input" type="number" min="0" step="1" placeholder="1150" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="productComparePrice">Compare Price (BDT)</label>
            <input id="productComparePrice" class="form-input" type="number" min="0" step="1" placeholder="1390">
          </div>

          <div class="form-group">
            <label class="form-label" for="productCost">Cost Price (BDT)</label>
            <input id="productCost" class="form-input" type="number" min="0" step="1" placeholder="780">
          </div>

          <div class="form-group">
            <label class="form-label" for="productTax">Tax Class</label>
            <select id="productTax" class="form-select">
              <option>Standard VAT</option>
              <option>Reduced VAT</option>
              <option>Tax Exempt</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="productStock">Available Quantity</label>
            <input id="productStock" class="form-input" type="number" min="0" step="1" placeholder="120" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="productLowStock">Low Stock Alert At</label>
            <input id="productLowStock" class="form-input" type="number" min="1" step="1" placeholder="15">
          </div>

          <div class="form-group">
            <label class="form-label" for="productWeight">Weight (kg)</label>
            <input id="productWeight" class="form-input" type="number" min="0" step="0.01" placeholder="0.40">
          </div>

          <div class="form-group">
            <label class="form-label" for="productShippingProfile">Shipping Profile</label>
            <select id="productShippingProfile" class="form-select">
              @foreach ($shippingProfiles as $profile)
                <option value="{{ $profile }}">{{ $profile }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </section>

      <section class="card">
        <div class="card-header">
          <h3 class="card-title">Search & Discoverability</h3>
          <div class="products-card-tools">
            <button
              type="button"
              class="products-ai-btn"
              data-product-ai-seo
              aria-label="Write SEO fields with AI"
              title="Add Product Name, Short Description, and Full Description first"
              disabled
            >
              <span class="products-ai-icon">&#10024;</span>
              <span class="products-ai-btn-text">AI Write</span>
            </button>
            <span class="badge badge-warning">SEO</span>
          </div>
        </div>

        <div class="products-create-grid">
          <div class="form-group products-field-span-2">
            <label class="form-label" for="productSlug">Slug</label>
            <input id="productSlug" class="form-input" type="text" placeholder="premium-cotton-t-shirt">
          </div>

          <div class="form-group products-field-span-2">
            <label class="form-label" for="productMetaTitle">Meta Title</label>
            <input id="productMetaTitle" class="form-input" type="text" placeholder="Premium Cotton T-Shirt | A Metafy">
          </div>

          <div class="form-group products-field-span-2">
            <label class="form-label" for="productMetaDescription">Meta Description</label>
            <textarea id="productMetaDescription" class="form-textarea" rows="3" placeholder="Short search snippet for Google and marketplace indexing."></textarea>
          </div>

          <div class="form-group products-field-span-2">
            <label class="form-label" for="productTags">Tags</label>
            <input id="productTags" class="form-input" type="text" placeholder="cotton, tshirt, casual, summer">
            <small class="form-help">Use comma-separated keywords to improve search relevance.</small>
          </div>
        </div>
      </section>
    </div>

    <aside class="products-create-side">
      <section class="card">
        <div class="card-header">
          <h3 class="card-title">Publishing</h3>
          <span class="badge badge-success">Status</span>
        </div>

        <div class="products-publish-options">
          <label class="products-radio-item">
            <input type="radio" name="publish_state" checked>
            <span>Save as Draft</span>
          </label>
          <label class="products-radio-item">
            <input type="radio" name="publish_state">
            <span>Publish Immediately</span>
          </label>
          <label class="products-radio-item">
            <input type="radio" name="publish_state">
            <span>Schedule Publish</span>
          </label>
        </div>

        <div class="form-group mt-md">
          <label class="form-label" for="productScheduleAt">Schedule Date & Time</label>
          <input id="productScheduleAt" class="form-input" type="datetime-local">
        </div>
      </section>

      <section class="card mt-lg">
        <div class="card-header">
          <h3 class="card-title">Checklist</h3>
          <span class="badge badge-info">Before Publish</span>
        </div>

        <ul class="products-create-checklist">
          <li>At least 3 clear product images added</li>
          <li>Price, stock, and SKU are correct</li>
          <li>Category, brand, and shipping profile selected</li>
          <li>Short description is customer-ready</li>
          <li>Publishing status is selected</li>
        </ul>
      </section>
    </aside>
  </form>

  <div class="modal-overlay" id="productsMediaValidationModal" aria-hidden="true">
    <div class="modal products-media-dialog" role="dialog" aria-modal="true" aria-labelledby="productsMediaDialogTitle">
      <div class="modal-header">
        <h3 class="modal-title" id="productsMediaDialogTitle" data-media-dialog-title>Image Ratio Check</h3>
        <button type="button" class="modal-close" data-product-media-dialog-close aria-label="Close">x</button>
      </div>
      <div class="modal-body">
        <div class="products-media-dialog-alert">
          <span class="products-media-dialog-icon">!</span>
          <p class="products-media-dialog-message" data-media-dialog-message>
            The selected image size does not match the recommended ratio.
          </p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-product-media-dialog-cancel>Cancel Upload</button>
        <button type="button" class="btn btn-danger" data-product-media-dialog-confirm>Proceed Anyway</button>
      </div>
    </div>
  </div>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
  <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
@endsection
