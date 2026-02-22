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

          <div class="form-group products-field-span-2">
            <label class="form-label" for="productCategory">Category</label>
            <select
              id="productCategory"
              name="category"
              class="form-select"
              required
            >
              <option value="" selected disabled>Select category</option>
              @foreach ($categories as $category)
                <option value="{{ $category }}">{{ $category }}</option>
              @endforeach
            </select>
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
                  <span>Video Upload</span>
                </label>
                <label class="products-radio-item">
                  <input
                    type="radio"
                    name="slider_media_type"
                    value="youtube"
                    data-product-slider-media-type
                  >
                  <span>YouTube</span>
                </label>
              </div>
              <small class="form-help">
                For slider images: best size 1600 x 900 (16:9), less than 2MB, max 7 images.
                For video uploads: less than 30MB.
                You can also add YouTube links.
              </small>
            </div>

            <div class="form-group mt-md mb-0" data-product-slider-upload-group>
              <label class="form-label" for="productSliderItemInput">Upload Slider Item</label>
              <input
                id="productSliderItemInput"
                type="file"
                class="form-input"
                accept="image/*"
                data-product-slider-item-input
              >
              <small class="form-help">Upload one file at a time. You can remove each item one by one.</small>
            </div>

            <div class="form-group mt-md mb-0 hidden" data-product-slider-youtube-group>
              <label class="form-label" for="productSliderYoutubeInput">YouTube URL</label>
              <input
                id="productSliderYoutubeInput"
                type="url"
                class="form-input"
                placeholder="https://www.youtube.com/watch?v=XXXXXXXXXXX"
                data-product-slider-youtube-input
              >
              <button type="button" class="btn btn-primary btn-sm mt-sm" data-product-slider-youtube-add>Add YouTube Video</button>
              <small class="form-help">Supported links: youtu.be, youtube.com/watch, youtube.com/shorts, youtube.com/embed.</small>
            </div>

            <div class="products-media-list mt-sm" data-product-slider-list>
              <div class="products-media-empty">No slider items added yet.</div>
            </div>
            <small class="form-help">After adding, use Move Up or Move Down to set slider order.</small>
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
            <label class="form-label" for="productPrice">Product Price (BDT)</label>
            <input
              id="productPrice"
              name="product_price"
              class="form-input"
              type="number"
              min="0"
              step="0.01"
              placeholder="1150"
              required
            >
          </div>

          <div class="form-group">
            <label class="form-label" for="bargainingPrice">Bargaining Price (BDT)</label>
            <input
              id="bargainingPrice"
              name="bargaining_price"
              class="form-input"
              type="number"
              min="0"
              step="0.01"
              placeholder="1050"
            >
          </div>

          <div class="form-group">
            <label class="form-label" for="discountOfferType">Discount Offer Type</label>
            <select id="discountOfferType" name="discount_offer_type" class="form-select">
              <option value="fixed">Fixed</option>
              <option value="percentage">Percentage</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="discountOffer">Discount Offer</label>
            <input
              id="discountOffer"
              name="discount_offer"
              class="form-input"
              type="number"
              min="0"
              step="0.01"
              placeholder="100 or 10"
            >
          </div>

          <div class="form-group">
            <label class="form-label" for="discountOfferDuration">Discount Offer Date & Time / Lifetime</label>
            <select id="discountOfferDuration" name="discount_offer_duration" class="form-select" data-discount-offer-duration>
              <option value="lifetime" selected>Lifetime</option>
              <option value="date_time">Date & Time</option>
            </select>
          </div>

          <div class="form-group hidden" data-discount-offer-datetime>
            <label class="form-label" for="discountOfferStartAt">Discount Offer Start Date & Time</label>
            <input
              id="discountOfferStartAt"
              name="discount_offer_start_at"
              class="form-input"
              type="datetime-local"
              disabled
            >
          </div>

          <div class="form-group hidden" data-discount-offer-datetime>
            <label class="form-label" for="discountOfferEndAt">Discount Offer End Date & Time</label>
            <input
              id="discountOfferEndAt"
              name="discount_offer_end_at"
              class="form-input"
              type="datetime-local"
              disabled
            >
          </div>

          <div class="form-group">
            <label class="form-label" for="productStock">Available Quantity</label>
            <input
              id="productStock"
              name="available_quantity"
              class="form-input"
              type="number"
              min="0"
              step="1"
              placeholder="120"
              required
            >
          </div>

          <div class="form-group">
            <label class="form-label" for="productLowStock">Low Stock Alert At</label>
            <input
              id="productLowStock"
              name="low_stock_alert_at"
              class="form-input"
              type="number"
              min="1"
              step="1"
              placeholder="15"
            >
          </div>

          <div class="form-group">
            <label class="form-label" for="productWeight">Weight (kg)</label>
            <input
              id="productWeight"
              name="weight_kg"
              class="form-input"
              type="number"
              min="0"
              step="0.01"
              placeholder="0.40"
            >
          </div>

          <div class="form-group">
            <label class="form-label" for="productShippingProfile">Shipping Profile</label>
            <select id="productShippingProfile" name="shipping_profile" class="form-select">
              @foreach ($shippingProfiles as $profile)
                <option value="{{ $profile }}">{{ $profile }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="productColors">Color</label>
            <input
              id="productColors"
              name="colors"
              class="form-input"
              type="text"
              placeholder="Black, White, Blue"
            >
          </div>

          <div class="form-group">
            <label class="form-label" for="productSizes">Size</label>
            <input
              id="productSizes"
              name="sizes"
              class="form-input"
              type="text"
              placeholder="S, M, L, XL"
            >
          </div>

          <div class="form-group">
            <label class="form-label" for="productCatalogTags">Tags</label>
            <input
              id="productCatalogTags"
              name="catalog_tags"
              class="form-input"
              type="text"
              placeholder="new-arrival, casual, bestseller"
            >
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
            <label class="form-label" for="productTags">SEO Tags</label>
            <input id="productTags" name="seo_tags" class="form-input" type="text" placeholder="cotton, tshirt, casual, summer">
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
