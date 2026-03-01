@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header products-page-header">
    <div>
      <h1 class="page-title">{{ $formMode === 'edit' ? 'Edit Product' : 'Add New Product' }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="products-header-actions">
      <a href="{{ route('admin.products') }}" class="btn btn-secondary">Back to Products</a>
      <button type="submit" form="createProductForm" class="btn btn-primary">
        {{ $formMode === 'edit' ? 'Update Product' : 'Save Product' }}
      </button>
    </div>
  </div>

  <form
    id="createProductForm"
    class="products-create-layout"
    data-form-mode="{{ $formMode }}"
    data-product-id="{{ $productId }}"
    data-api-base-url="{{ $backendApiBaseUrl }}"
    data-refresh-token="{{ $refreshToken }}"
    data-dev-autofill="{{ $enableDevAutofill ? '1' : '0' }}"
  >
    <div class="products-create-main">

      {{-- ══ 1. PRODUCT TYPE ══ --}}
      <section class="card">
        <div class="card-header">
          <h3 class="card-title">Product Type</h3>
          <span class="badge badge-info">Required</span>
        </div>

        <div class="products-type-selector">
          <label class="products-type-card is-active" data-product-type-card>
            <input type="radio" name="product_type" value="physical" checked>
            <span class="products-type-card-icon">📦</span>
            <strong class="products-type-card-title">Physical</strong>
            <span class="products-type-card-desc">Tangible product shipped to the customer with inventory & shipping management</span>
            <span class="products-type-card-check" aria-hidden="true">✓</span>
          </label>

          <label class="products-type-card" data-product-type-card>
            <input type="radio" name="product_type" value="downloadable">
            <span class="products-type-card-icon">⬇️</span>
            <strong class="products-type-card-title">Downloadable</strong>
            <span class="products-type-card-desc">Digital file delivered via Google Drive — no shipping or inventory needed</span>
            <span class="products-type-card-check" aria-hidden="true">✓</span>
          </label>

          <label class="products-type-card" data-product-type-card>
            <input type="radio" name="product_type" value="subscription">
            <span class="products-type-card-icon">🔄</span>
            <strong class="products-type-card-title">Subscription</strong>
            <span class="products-type-card-desc">Recurring access plan — manage multiple credential slots for buyers</span>
            <span class="products-type-card-check" aria-hidden="true">✓</span>
          </label>
        </div>
      </section>

      {{-- ══ 2. BASIC INFORMATION ══ --}}
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

          <div class="form-group products-field-span-2">
            <label class="form-label" for="productCategory">Category</label>
            <select id="productCategory" name="category" class="form-select" required>
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

      {{-- ══ 3. MEDIA ══ --}}
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
            <label class="form-label">Product Slider</label>
            <span class="badge badge-info" data-product-slider-status>Enabled</span>

            <div class="products-publish-options mt-sm">
              <label class="products-radio-item">
                <input type="radio" name="image_slider_status" value="enabled" data-product-slider-toggle checked>
                <span>Enabled</span>
              </label>
              <label class="products-radio-item">
                <input type="radio" name="image_slider_status" value="disabled" data-product-slider-toggle>
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
                  <input type="radio" name="slider_media_type" value="image" data-product-slider-media-type checked>
                  <span>Image</span>
                </label>
                <label class="products-radio-item">
                  <input type="radio" name="slider_media_type" value="video" data-product-slider-media-type>
                  <span>Video Upload</span>
                </label>
                <label class="products-radio-item">
                  <input type="radio" name="slider_media_type" value="youtube" data-product-slider-media-type>
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

      {{-- ══ 4. DOWNLOADABLE DETAILS (shown for downloadable type only) ══ --}}
      <section class="card hidden" data-product-type-section="downloadable">
        <div class="card-header">
          <h3 class="card-title">Download File</h3>
          <span class="badge badge-primary">Google Drive</span>
        </div>

        <div class="products-downloadable-box">

          {{-- Link access type toggle --}}
          <div class="form-group mb-0">
            <label class="form-label">File Access Type</label>
            <small class="form-help" style="margin-bottom: 10px; display: block;">
              Choose how customers will access the file after purchase.
            </small>
            <div class="products-link-type-selector">
              <label class="products-link-type-option is-active" data-drive-link-type-card>
                <input type="radio" name="drive_link_type" value="public" checked data-drive-link-type>
                <span class="products-link-type-icon">🌐</span>
                <span class="products-link-type-text">
                  <strong>Public Link</strong>
                  <small>Anyone with the link can access — customers get it automatically</small>
                </span>
                <span class="products-link-type-check" aria-hidden="true">✓</span>
              </label>
              <label class="products-link-type-option" data-drive-link-type-card>
                <input type="radio" name="drive_link_type" value="private" data-drive-link-type>
                <span class="products-link-type-icon">🔒</span>
                <span class="products-link-type-text">
                  <strong>Private Link</strong>
                  <small>Restricted — you manually grant access to each customer</small>
                </span>
                <span class="products-link-type-check" aria-hidden="true">✓</span>
              </label>
            </div>
          </div>

          {{-- Public notice --}}
          <div class="products-downloadable-notice" data-drive-public-info>
            <span class="products-downloadable-notice-icon">ℹ</span>
            <div>
              <strong>How to set up your public link:</strong>
              Open your file in Google Drive → click <strong>Share</strong> (top-right) →
              under "General access" select <em>"Anyone with the link"</em> → set role to <strong>Viewer</strong> → click <strong>Copy link</strong> and paste it below.
            </div>
          </div>

          {{-- Private notice --}}
          <div class="products-downloadable-notice products-downloadable-notice--private hidden" data-drive-private-info>
            <span class="products-downloadable-notice-icon products-downloadable-notice-icon--lock">🔒</span>
            <div>
              <strong>Private file — you will need to grant access manually after each sale.</strong>
              <p style="margin: 8px 0 6px;">Follow these steps for every new customer order:</p>
              <ol class="products-drive-steps">
                <li>Open your file in <strong>Google Drive</strong></li>
                <li>Click <strong>Share</strong> in the top-right corner</li>
                <li>In the "Add people and groups" field, type the customer's <strong>Gmail address</strong> (found in their order details)</li>
                <li>Set the permission to <strong>Viewer</strong></li>
                <li>Uncheck <em>"Notify people"</em> if you prefer to inform them yourself, or leave it checked to auto-send an email</li>
                <li>Click <strong>Share</strong> — the customer can now access the file</li>
              </ol>
              <small>💡 Tip: Keep this tab open alongside your orders so you can quickly grant access after each sale.</small>
            </div>
          </div>

          {{-- Link input --}}
          <div class="products-create-grid">
            <div class="form-group products-field-span-2">
              <label class="form-label" for="productDriveLink">Google Drive Link</label>
              <input
                id="productDriveLink"
                name="drive_link"
                class="form-input"
                type="url"
                placeholder="https://drive.google.com/file/d/XXXXXXXXXXXXXXXXXXXX/view?usp=sharing"
              >
              <small class="form-help" data-drive-link-help-public>
                Make sure sharing is set to <strong>"Anyone with the link"</strong> before pasting.
              </small>
              <small class="form-help hidden" data-drive-link-help-private>
                Paste the private file link. Customers cannot open it until you manually grant them access via Google Drive.
              </small>
            </div>

            <div class="form-group products-field-span-2">
              <label class="form-label" for="productDriveNotes">
                Access Instructions
                <span class="products-type-optional">— optional</span>
              </label>
              <textarea
                id="productDriveNotes"
                name="drive_notes"
                class="form-textarea"
                rows="3"
                placeholder="E.g. Download the ZIP, extract it, and follow the README inside."
              ></textarea>
              <small class="form-help">Any special steps the buyer needs to follow after accessing the file.</small>
            </div>
          </div>
        </div>
      </section>

      {{-- ══ 5. SUBSCRIPTION SLOTS (shown for subscription type only) ══ --}}
      <section class="card hidden" data-product-type-section="subscription">
        <div class="card-header">
          <h3 class="card-title">Subscription Slots</h3>
          <span class="badge badge-warning">Credentials</span>
        </div>

        {{-- Load balancer explanation --}}
        <div class="products-subscription-how">
          <div class="products-subscription-how-header">
            <span class="products-subscription-how-icon">⚡</span>
            <div>
              <strong>Smart Auto-Assignment</strong>
              <span class="badge badge-success" style="margin-left: 8px; font-size: 10px;">Load Balancer</span>
            </div>
          </div>
          <p class="products-subscription-how-desc">
            When you add <strong>multiple subscription slots</strong>, each new customer order is automatically assigned to the slot with the <strong>fewest active users</strong> — keeping the load balanced evenly across all your accounts.
          </p>
          <div class="products-subscription-how-visual">
            <div class="products-subscription-slot-demo">
              <span>Slot 1</span>
              <div class="products-subscription-slot-bar">
                <div class="products-subscription-slot-fill" style="width: 75%"></div>
              </div>
              <small>4 users</small>
            </div>
            <div class="products-subscription-slot-demo">
              <span>Slot 2</span>
              <div class="products-subscription-slot-bar">
                <div class="products-subscription-slot-fill" style="width: 40%"></div>
              </div>
              <small>2 users</small>
            </div>
            <div class="products-subscription-slot-demo products-subscription-slot-demo--next">
              <span>Slot 3</span>
              <div class="products-subscription-slot-bar">
                <div class="products-subscription-slot-fill" style="width: 0%"></div>
              </div>
              <small>← Next customer</small>
            </div>
          </div>
          <p class="products-subscription-how-note">
            This is informational — dynamic assignment will be handled automatically by the system when orders are placed. For now, add all available slots below.
          </p>
        </div>

        {{-- Credential note --}}
        <div class="products-subscription-notice">
          <span class="products-subscription-notice-icon">🔒</span>
          <div>
            Each slot holds the login credentials for that account. <strong>Leave any field empty if it is not available</strong> — it will be marked accordingly in the order details.
          </div>
        </div>

        <div class="products-subscription-list" id="subscriptionList" data-subscription-list>
          <div class="products-subscription-empty">
            No subscription slots yet. Click <strong>Add Subscription</strong> below to get started.
          </div>
        </div>

        <div class="products-subscription-footer">
          <button type="button" class="btn btn-primary" data-subscription-add>
            + Add Subscription
          </button>
          <span class="products-subscription-count hidden" data-subscription-count></span>
        </div>
      </section>

      {{-- ══ 6. PRICING & INVENTORY ══ --}}
      <section class="card">
        <div class="card-header">
          <h3 class="card-title">Pricing & Inventory</h3>
          <span class="badge badge-success">Commerce</span>
        </div>

        <div class="products-create-grid">
          {{-- Pricing fields for non-physical products (downloadable, subscription, package) --}}
          <div class="form-group hidden" data-non-physical-only>
            <label class="form-label" for="productPrice">
              <span class="products-label-with-icon">
                💵 Product Price (BDT)
              </span>
            </label>
            <input
              id="productPrice"
              name="product_price"
              class="form-input"
              type="number"
              min="0"
              step="0.01"
              placeholder="e.g. 850"
            >
          </div>

          <div class="form-group hidden" data-non-physical-only>
            <label class="form-label" for="bargainingPrice">
              <span class="products-label-with-icon">
                💸 Bargaining Price (BDT)
              </span>
            </label>
            <input
              id="bargainingPrice"
              name="bargaining_price"
              class="form-input"
              type="number"
              min="0"
              step="0.01"
              placeholder="e.g. 750"
            >
          </div>

          {{-- Discount duration comes first — controls what fields are visible --}}
          <div class="form-group products-field-span-2">
            <label class="form-label" for="discountOfferDuration">Discount Offer</label>
            <select id="discountOfferDuration" name="discount_offer_duration" class="form-select" data-discount-offer-duration>
              <option value="none" selected>None</option>
              <option value="lifetime">Lifetime</option>
              <option value="date_time">Date & Time</option>
            </select>
            <small class="form-help">Select <strong>Lifetime</strong> or <strong>Date & Time</strong> to configure a discount.</small>
          </div>

          {{-- Shown only when Lifetime or Date & Time is selected --}}
          <div class="form-group hidden" data-discount-offer-fields>
            <label class="form-label" for="discountOfferType">Discount Type</label>
            <select id="discountOfferType" name="discount_offer_type" class="form-select">
              <option value="fixed">Fixed (BDT)</option>
              <option value="percentage">Percentage (%)</option>
            </select>
          </div>

          <div class="form-group hidden" data-discount-offer-fields>
            <label class="form-label" for="discountOffer">Discount Amount</label>
            <input
              id="discountOffer"
              name="discount_offer"
              class="form-input"
              type="number"
              min="0"
              step="0.01"
              placeholder="100 or 10%"
            >
          </div>

          {{-- Shown only when Date & Time is selected --}}
          <div class="form-group hidden" data-discount-offer-datetime>
            <label class="form-label" for="discountOfferStartAt">Discount Start Date & Time</label>
            <input
              id="discountOfferStartAt"
              name="discount_offer_start_at"
              class="form-input"
              type="datetime-local"
              disabled
            >
          </div>

          <div class="form-group hidden" data-discount-offer-datetime>
            <label class="form-label" for="discountOfferEndAt">Discount End Date & Time</label>
            <input
              id="discountOfferEndAt"
              name="discount_offer_end_at"
              class="form-input"
              type="datetime-local"
              disabled
            >
          </div>

          {{-- Physical only: Shipping --}}
          <div class="form-group" data-physical-only>
            <label class="form-label" for="productShippingProfile">Shipping Profile</label>
            <select id="productShippingProfile" name="shipping_profile" class="form-select">
              @foreach ($shippingProfiles as $profile)
                <option value="{{ $profile }}">{{ $profile }}</option>
              @endforeach
            </select>
          </div>

          {{-- Always visible --}}
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

      {{-- ══ 7. PRODUCT VARIANTS & INVENTORY (Physical products only) ══ --}}
      <section class="card hidden" data-physical-only>
        <div class="card-header">
          <h3 class="card-title">Product Variants & Inventory</h3>
          <span class="badge badge-info">Stock Management</span>
        </div>

        <div class="products-variants-container">
          {{-- Has Variants Toggle --}}
          <div class="products-variant-toggle-wrapper">
            <label class="form-label" style="margin-bottom: 12px; display: block;">Does this product have variants?</label>
            <div class="products-variant-toggle-group">
              <label class="products-variant-toggle-card is-active" data-variant-toggle-card>
                <input type="radio" name="has_variants" value="no" checked data-has-variants-toggle>
                <div class="products-variant-toggle-content">
                  <span class="products-variant-toggle-icon">📦</span>
                  <div class="products-variant-toggle-text">
                    <strong>No Variants</strong>
                    <small>Single product with one quantity</small>
                  </div>
                  <span class="products-variant-toggle-check">✓</span>
                </div>
              </label>

              <label class="products-variant-toggle-card" data-variant-toggle-card>
                <input type="radio" name="has_variants" value="yes" data-has-variants-toggle>
                <div class="products-variant-toggle-content">
                  <span class="products-variant-toggle-icon">🎨</span>
                  <div class="products-variant-toggle-text">
                    <strong>Has Variants</strong>
                    <small>Multiple colors, sizes, or combinations</small>
                  </div>
                  <span class="products-variant-toggle-check">✓</span>
                </div>
              </label>
            </div>
          </div>

          {{-- No Variants: Simple Quantity Fields --}}
          <div class="products-simple-inventory" data-simple-inventory>
            <div class="products-create-grid" style="margin-top: 24px;">
              <div class="form-group">
                <label class="form-label" for="simplePrice">
                  <span class="products-label-with-icon">
                    💵 Product Price (BDT)
                  </span>
                </label>
                <input
                  id="simplePrice"
                  name="simple_price"
                  class="form-input"
                  type="number"
                  min="0"
                  step="0.01"
                  placeholder="e.g. 1150"
                >
              </div>

              <div class="form-group">
                <label class="form-label" for="simpleBargainingPrice">
                  <span class="products-label-with-icon">
                    💸 Bargaining Price (BDT)
                  </span>
                </label>
                <input
                  id="simpleBargainingPrice"
                  name="simple_bargaining_price"
                  class="form-input"
                  type="number"
                  min="0"
                  step="0.01"
                  placeholder="e.g. 1050"
                >
              </div>

              <div class="form-group">
                <label class="form-label" for="simpleQuantity">
                  <span class="products-label-with-icon">
                    📦 Available Quantity
                  </span>
                </label>
                <input
                  id="simpleQuantity"
                  name="simple_quantity"
                  class="form-input"
                  type="number"
                  min="0"
                  step="1"
                  placeholder="e.g. 120"
                >
              </div>

              <div class="form-group">
                <label class="form-label" for="simpleAlertQty">
                  <span class="products-label-with-icon">
                    🔔 Low Stock Alert At
                  </span>
                </label>
                <input
                  id="simpleAlertQty"
                  name="simple_alert_qty"
                  class="form-input"
                  type="number"
                  min="1"
                  step="1"
                  placeholder="e.g. 10"
                >
              </div>

              <div class="form-group">
                <label class="form-label" for="simpleWeight">
                  <span class="products-label-with-icon">
                    ⚖️ Weight (kg)
                  </span>
                </label>
                <input
                  id="simpleWeight"
                  name="simple_weight"
                  class="form-input"
                  type="number"
                  min="0"
                  step="0.01"
                  placeholder="e.g. 0.40"
                >
              </div>
            </div>
          </div>

          {{-- Has Variants: Variant Management --}}
          <div class="products-variant-management hidden" data-variant-management>
            <div class="products-variant-list" data-variant-list>
              <div class="products-variant-empty-state">
                <span class="products-variant-empty-icon">📦</span>
                <p>No variants added yet</p>
                <small>Click the button below to add your first variant</small>
              </div>
            </div>

            <div class="products-variant-add-section">
              <button type="button" class="btn btn-primary" data-add-variant-btn>
                <span style="font-size: 16px; margin-right: 4px;">+</span> Add Variant
              </button>
            </div>
          </div>
        </div>
      </section>

      {{-- ══ 8. SEO ══ --}}
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

    {{-- ══ SIDEBAR ══ --}}
    <aside class="products-create-side">

      {{-- Publishing --}}
      <section class="card">
        <div class="card-header">
          <h3 class="card-title">Publishing</h3>
          <span class="badge badge-success">Status</span>
        </div>

        <div class="products-publish-options">
          <label class="products-radio-item">
            <input type="radio" name="publish_state" value="draft">
            <span>Save as Draft</span>
          </label>
          <label class="products-radio-item">
            <input type="radio" name="publish_state" value="immediately" checked>
            <span>Publish Immediately</span>
          </label>
          <label class="products-radio-item">
            <input type="radio" name="publish_state" value="scheduled">
            <span>Schedule Publish</span>
          </label>
        </div>

        <div class="form-group mt-md">
          <label class="form-label" for="productScheduleAt">Schedule Date & Time</label>
          <input id="productScheduleAt" class="form-input" type="datetime-local">
        </div>
      </section>

      {{-- Type-specific Checklist --}}
      <section class="card">
        <div class="card-header">
          <h3 class="card-title">Type Checklist</h3>
          <span class="badge" id="productTypeInfoBadge" data-product-type-badge>Physical</span>
        </div>

        <div data-product-type-checklist></div>
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
