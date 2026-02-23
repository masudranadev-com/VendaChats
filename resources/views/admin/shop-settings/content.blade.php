@extends('admin.master')

@section('title', $title)

@section('admin.content')
  @php
    $defaultPolicy = $legalPages[0] ?? null;
  @endphp

  <div class="page-header settings-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="settings-header-actions">
      <button type="button" class="btn btn-secondary" data-content-preview-btn>Preview Storefront</button>
      <button type="button" class="btn btn-success" data-content-save-all-btn>Save All Content Settings</button>
    </div>
  </div>

  @include('admin.shop-settings.partials.tab-row')

  <section class="settings-section-intro">
    <h3>{{ $sectionHeading }}</h3>
    <p>{{ $sectionSubtitle }}</p>
  </section>

  <section class="settings-stats-grid">
    @foreach ($quickStats as $stat)
      <article class="settings-stat-card is-{{ $stat['tone'] }}">
        <span>{{ $stat['label'] }}</span>
        <strong>{{ $stat['value'] }}</strong>
        <small>{{ $stat['note'] }}</small>
      </article>
    @endforeach
  </section>

  <div class="settings-layout mt-xl" data-content-center>
    <section class="settings-main-column">
      <article class="card settings-panel" id="content-slider">
        <div class="card-header">
          <div>
            <h3 class="card-title">Homepage Slider Manager</h3>
            <p class="settings-panel-subtitle">Manage hero sliders for campaigns, seasonal launches, and important notices.</p>
          </div>
          <div class="settings-inline-actions mt-0">
            <button type="button" class="btn btn-success btn-sm" data-modal="contentSliderModal" data-slider-create-btn>Add Slide</button>
            <button type="button" class="btn btn-secondary btn-sm">Reorder Priority</button>
          </div>
        </div>

        <div class="settings-slider-spotlight">
          <div>
            <strong>Live slider workflow</strong>
            <p>Only one hero slider is shown at a time based on priority. Lower priority number appears first.</p>
          </div>
          <div class="settings-slider-meta">
            <span>Live: {{ collect($sliderItems)->where('status', 'Live')->count() }}</span>
            <span>Scheduled: {{ collect($sliderItems)->where('status', 'Scheduled')->count() }}</span>
            <span>Draft: {{ collect($sliderItems)->where('status', 'Draft')->count() }}</span>
          </div>
        </div>

        <div class="table-container mt-md">
          <table class="table">
            <thead>
              <tr>
                <th>Slide</th>
                <th>CTA</th>
                <th>Schedule</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Updated</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($sliderItems as $slide)
                @php
                  $slideStatusClass = match ($slide['status']) {
                    'Live' => 'badge-success',
                    'Scheduled' => 'badge-warning',
                    default => 'badge-info',
                  };
                @endphp
                <tr data-slider-row="{{ $slide['id'] }}">
                  <td class="settings-cell-strong">{{ $slide['title'] }}</td>
                  <td>{{ $slide['cta'] }}</td>
                  <td>{{ $slide['schedule'] }}</td>
                  <td>#{{ $slide['priority'] }}</td>
                  <td><span class="badge {{ $slideStatusClass }}">{{ $slide['status'] }}</span></td>
                  <td>{{ $slide['updated'] }}</td>
                  <td>
                    <div class="settings-offer-actions">
                      <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        data-modal="contentSliderModal"
                        data-slider-edit-btn
                        data-slide-id="{{ $slide['id'] }}"
                        data-slide-title="{{ $slide['title'] }}"
                        data-slide-headline="{{ $slide['headline'] }}"
                        data-slide-cta="{{ $slide['cta'] }}"
                        data-slide-url="{{ $slide['url'] }}"
                        data-slide-schedule="{{ $slide['schedule'] }}"
                        data-slide-priority="{{ $slide['priority'] }}"
                        data-slide-status="{{ $slide['status'] }}"
                      >
                        Edit
                      </button>
                      <button type="button" class="btn btn-danger btn-sm" data-slider-remove-btn data-slide-title="{{ $slide['title'] }}" data-slide-id="{{ $slide['id'] }}">Remove</button>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </article>

      <article class="card settings-panel mt-xl" id="content-policies" data-policy-editor>
        <div class="card-header">
          <div>
            <h3 class="card-title">Policy and Static Page Editor</h3>
            <p class="settings-panel-subtitle">Update Terms, Privacy, Refund, Shipping, About, and Contact page content with one editor.</p>
          </div>
          <span class="badge badge-primary">Policy Control</span>
        </div>

        <div class="settings-policy-layout">
          <div class="settings-policy-nav" role="tablist" aria-label="Policy pages">
            @foreach ($legalPages as $page)
              @php
                $policyBadgeClass = $page['status'] === 'Published' ? 'badge-success' : ($page['status'] === 'In Review' ? 'badge-warning' : 'badge-info');
              @endphp
              <button
                type="button"
                class="settings-policy-tab {{ $loop->first ? 'active' : '' }}"
                data-policy-tab="{{ $page['key'] }}"
                aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
              >
                <span>{{ $page['title'] }}</span>
                <small>Updated {{ $page['last_updated'] }}</small>
                <span class="badge {{ $policyBadgeClass }}">{{ $page['status'] }}</span>
              </button>
            @endforeach
          </div>

          <div class="settings-policy-editor-area">
            <div class="settings-policy-meta">
              <span class="badge badge-primary" data-policy-status-badge>{{ $defaultPolicy['status'] ?? 'Draft' }}</span>
              <span class="text-muted" data-policy-last-updated>Last updated {{ $defaultPolicy['last_updated'] ?? 'N/A' }}</span>
            </div>

            <input type="hidden" data-policy-key value="{{ $defaultPolicy['key'] ?? '' }}">

            <div class="settings-field-grid mt-md">
              <div class="form-group">
                <label class="form-label" for="policyTitle">Page Title</label>
                <input id="policyTitle" type="text" class="form-input" data-policy-title value="{{ $defaultPolicy['title'] ?? '' }}">
              </div>

              <div class="form-group">
                <label class="form-label" for="policySlug">Page URL</label>
                <input id="policySlug" type="text" class="form-input" data-policy-slug value="{{ $defaultPolicy['slug'] ?? '' }}">
              </div>

              <div class="form-group">
                <label class="form-label" for="policyStatus">Publish Status</label>
                <select id="policyStatus" class="form-select" data-policy-status>
                  <option {{ ($defaultPolicy['status'] ?? '') === 'Published' ? 'selected' : '' }}>Published</option>
                  <option {{ ($defaultPolicy['status'] ?? '') === 'In Review' ? 'selected' : '' }}>In Review</option>
                  <option {{ ($defaultPolicy['status'] ?? '') === 'Draft' ? 'selected' : '' }}>Draft</option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label" for="policyReviewCycle">Review Cycle</label>
                <select id="policyReviewCycle" class="form-select" data-policy-review-cycle>
                  <option {{ ($defaultPolicy['review_cycle'] ?? '') === 'Every 30 days' ? 'selected' : '' }}>Every 30 days</option>
                  <option {{ ($defaultPolicy['review_cycle'] ?? '') === 'Every 60 days' ? 'selected' : '' }}>Every 60 days</option>
                  <option {{ ($defaultPolicy['review_cycle'] ?? '') === 'Every 90 days' ? 'selected' : '' }}>Every 90 days</option>
                  <option {{ ($defaultPolicy['review_cycle'] ?? '') === 'Every 180 days' ? 'selected' : '' }}>Every 180 days</option>
                </select>
              </div>

              <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label" for="policySeoTitle">SEO Title</label>
                <input id="policySeoTitle" type="text" class="form-input" data-policy-seo-title value="{{ $defaultPolicy['seo_title'] ?? '' }}">
              </div>

              <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label" for="policyMetaDescription">SEO Description</label>
                <textarea id="policyMetaDescription" class="form-textarea" rows="3" data-policy-meta-description>{{ $defaultPolicy['meta_description'] ?? '' }}</textarea>
              </div>

              <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label" for="policyContent">Page Content</label>
                <textarea id="policyContent" class="form-textarea settings-policy-textarea" data-policy-content>{{ $defaultPolicy['content'] ?? '' }}</textarea>
              </div>

              <div class="form-group" style="grid-column: 1 / -1;">
                <label class="settings-policy-toggle">
                  <input type="checkbox" data-policy-show-footer {{ ($defaultPolicy['show_in_footer'] ?? false) ? 'checked' : '' }}>
                  <span>Show this page in storefront footer menu</span>
                </label>
              </div>
            </div>

            <div class="settings-inline-actions">
              <button type="button" class="btn btn-success btn-sm" data-policy-save-btn>Save Page Content</button>
              <button type="button" class="btn btn-secondary btn-sm" data-policy-preview-btn>Preview Current Page</button>
            </div>
          </div>
        </div>
      </article>

      <article class="card settings-panel mt-xl" id="content-contact">
        <div class="card-header">
          <div>
            <h3 class="card-title">Contact and Store Information</h3>
            <p class="settings-panel-subtitle">Control support contacts, address, support hours, and social links shown to customers.</p>
          </div>
          <span class="badge badge-info">Customer Facing</span>
        </div>

        <div class="settings-field-grid">
          <div class="form-group">
            <label class="form-label" for="contentSupportPhone">Support Phone</label>
            <input id="contentSupportPhone" type="text" class="form-input" value="{{ $contactInfo['support_phone'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="contentSupportWhatsapp">WhatsApp Number</label>
            <input id="contentSupportWhatsapp" type="text" class="form-input" value="{{ $contactInfo['support_whatsapp'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="contentSupportEmail">Support Email</label>
            <input id="contentSupportEmail" type="email" class="form-input" value="{{ $contactInfo['support_email'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="contentBusinessEmail">Business Email</label>
            <input id="contentBusinessEmail" type="email" class="form-input" value="{{ $contactInfo['business_email'] }}">
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="contentStoreAddress">Store Address</label>
            <input id="contentStoreAddress" type="text" class="form-input" value="{{ $contactInfo['store_address'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="contentSupportHours">Support Hours</label>
            <input id="contentSupportHours" type="text" class="form-input" value="{{ $contactInfo['support_hours'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="contentMapEmbed">Map URL</label>
            <input id="contentMapEmbed" type="text" class="form-input" value="{{ $contactInfo['map_embed'] }}">
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="contentContactNotice">Contact Page Notice</label>
            <textarea id="contentContactNotice" class="form-textarea" rows="3">{{ $contactInfo['contact_page_notice'] }}</textarea>
          </div>
        </div>

        <div class="card-header mt-md">
          <div>
            <h4 class="card-title">Social Profiles</h4>
            <p class="settings-panel-subtitle">Enable only active profiles for storefront footer and contact page.</p>
          </div>
          <button type="button" class="btn btn-success btn-sm" data-modal="contentSocialModal" data-social-create-btn>Add Social Link</button>
        </div>

        <div class="table-container">
          <table class="table">
            <thead>
              <tr>
                <th>Platform</th>
                <th>URL</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody data-social-table-body>
              @foreach ($socialLinks as $social)
                @php
                  $socialStatusClass = $social['status'] === 'Active' ? 'badge-success' : 'badge-warning';
                @endphp
                <tr>
                  <td class="settings-cell-strong">{{ $social['platform'] }}</td>
                  <td><a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer">{{ $social['url'] }}</a></td>
                  <td><span class="badge {{ $socialStatusClass }}">{{ $social['status'] }}</span></td>
                  <td>
                    <div class="settings-offer-actions">
                      <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        data-modal="contentSocialModal"
                        data-social-edit-btn
                        data-social-platform="{{ $social['platform'] }}"
                        data-social-url="{{ $social['url'] }}"
                        data-social-status="{{ $social['status'] }}"
                      >
                        Edit
                      </button>
                      <button type="button" class="btn btn-danger btn-sm" data-social-remove-btn data-social-platform="{{ $social['platform'] }}">Remove</button>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="settings-inline-actions">
          <button type="button" class="btn btn-success btn-sm" data-contact-save-btn>Save Contact Information</button>
        </div>
      </article>

      <article class="card settings-panel mt-xl" id="content-footer">
        <div class="card-header">
          <div>
            <h3 class="card-title">Footer and Trust Blocks</h3>
            <p class="settings-panel-subtitle">Control customer trust elements, announcement bar, and footer utility blocks.</p>
          </div>
          <span class="badge badge-primary">Storefront Blocks</span>
        </div>

        <div class="bot-settings-list">
          @foreach ($footerBlocks as $block)
            <label class="bot-setting-row">
              <div class="bot-setting-info">
                <h4>{{ $block['label'] }}</h4>
                <p>{{ $block['description'] }}</p>
              </div>
              <span class="bot-setting-state {{ $block['enabled'] ? 'on' : 'off' }}" data-footer-state-label>{{ $block['enabled'] ? 'On' : 'Off' }}</span>
              <span class="bot-switch">
                <input
                  type="checkbox"
                  class="bot-toggle-input"
                  data-footer-toggle
                  {{ $block['enabled'] ? 'checked' : '' }}
                >
                <span class="bot-switch-ui"></span>
              </span>
            </label>
          @endforeach
        </div>

        <div class="settings-field-grid mt-md">
          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="contentStoreTagline">Footer Tagline</label>
            <input id="contentStoreTagline" type="text" class="form-input" value="{{ $trustSettings['store_tagline'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="contentReturnBadge">Return Badge Text</label>
            <input id="contentReturnBadge" type="text" class="form-input" value="{{ $trustSettings['return_badge'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="contentShippingBadge">Shipping Badge Text</label>
            <input id="contentShippingBadge" type="text" class="form-input" value="{{ $trustSettings['shipping_badge'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="contentPaymentBadge">Payment Badge Text</label>
            <input id="contentPaymentBadge" type="text" class="form-input" value="{{ $trustSettings['payment_badge'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="contentAnnouncementBar">Announcement Bar Message</label>
            <input id="contentAnnouncementBar" type="text" class="form-input" value="{{ $trustSettings['announcement_bar'] }}">
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="contentCopyright">Copyright Line</label>
            <input id="contentCopyright" type="text" class="form-input" value="{{ $trustSettings['copyright'] }}">
          </div>
        </div>

        <div class="settings-inline-actions">
          <button type="button" class="btn btn-success btn-sm" data-footer-save-btn>Save Footer and Trust Settings</button>
        </div>
      </article>

      <article class="card settings-panel mt-xl" id="content-seo">
        <div class="card-header">
          <div>
            <h3 class="card-title">SEO Defaults and Scripts</h3>
            <p class="settings-panel-subtitle">Set default metadata and tracking scripts used across storefront pages.</p>
          </div>
          <span class="badge badge-info">Technical Content</span>
        </div>

        <div class="settings-field-grid">
          <div class="form-group">
            <label class="form-label" for="contentTitleTemplate">Meta Title Template</label>
            <input id="contentTitleTemplate" type="text" class="form-input" value="{{ $seoDefaults['title_template'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="contentRobotsMeta">Robots Meta</label>
            <input id="contentRobotsMeta" type="text" class="form-input" value="{{ $seoDefaults['robots_meta'] }}">
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="contentDefaultMetaDescription">Default Meta Description</label>
            <textarea id="contentDefaultMetaDescription" class="form-textarea" rows="3">{{ $seoDefaults['meta_description'] }}</textarea>
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="contentDefaultMetaKeywords">Default Meta Keywords</label>
            <input id="contentDefaultMetaKeywords" type="text" class="form-input" value="{{ $seoDefaults['meta_keywords'] }}">
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="contentOgImageUrl">Open Graph Image URL</label>
            <input id="contentOgImageUrl" type="text" class="form-input" value="{{ $seoDefaults['og_image_url'] }}">
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="contentHeaderScript">Header Script</label>
            <textarea id="contentHeaderScript" class="form-textarea" rows="3">{{ $seoDefaults['header_script'] }}</textarea>
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="contentFooterScript">Footer Script</label>
            <textarea id="contentFooterScript" class="form-textarea" rows="3">{{ $seoDefaults['footer_script'] }}</textarea>
          </div>
        </div>

        <div class="settings-inline-actions">
          <button type="button" class="btn btn-success btn-sm" data-seo-save-btn>Save SEO Defaults</button>
        </div>
      </article>
    </section>

    <section class="settings-side-column">
      <article class="card settings-panel">
        <div class="card-header">
          <h3 class="card-title">Page Navigation</h3>
          <span class="badge badge-info">Quick Jump</span>
        </div>

        <div class="settings-anchor-list">
          <a href="#content-slider" class="settings-anchor-item">1. Homepage Slider Manager</a>
          <a href="#content-policies" class="settings-anchor-item">2. Policy and Static Pages</a>
          <a href="#content-contact" class="settings-anchor-item">3. Contact and Store Information</a>
          <a href="#content-footer" class="settings-anchor-item">4. Footer and Trust Blocks</a>
          <a href="#content-seo" class="settings-anchor-item">5. SEO Defaults and Scripts</a>
        </div>
      </article>

      <article class="card settings-panel mt-xl">
        <div class="card-header">
          <h3 class="card-title">Publishing Checklist</h3>
          <span class="badge badge-primary">Before Save</span>
        </div>

        <ul class="settings-focus-list">
          @foreach ($checklist as $item)
            <li>{{ $item }}</li>
          @endforeach
        </ul>
      </article>

      @include('admin.shop-settings.partials.recent-activity')
    </section>
  </div>

  <div class="modal-overlay" id="contentSliderModal" aria-hidden="true">
    <div class="modal">
      <div class="modal-header">
        <h3 class="modal-title" data-slider-modal-title>Add Slider Item</h3>
        <button type="button" class="modal-close" data-modal-close aria-label="Close slider modal">x</button>
      </div>
      <div class="modal-body">
        <input type="hidden" data-slider-modal-mode value="create">
        <input type="hidden" data-slider-modal-id value="">

        <div class="settings-field-grid settings-modal-grid">
          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="sliderModalTitle">Slide Title</label>
            <input id="sliderModalTitle" type="text" class="form-input" data-slider-modal-title-input>
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="sliderModalHeadline">Headline / Message</label>
            <textarea id="sliderModalHeadline" class="form-textarea" rows="3" data-slider-modal-headline-input></textarea>
          </div>

          <div class="form-group">
            <label class="form-label" for="sliderModalCta">CTA Label</label>
            <input id="sliderModalCta" type="text" class="form-input" data-slider-modal-cta-input>
          </div>

          <div class="form-group">
            <label class="form-label" for="sliderModalUrl">Target URL</label>
            <input id="sliderModalUrl" type="text" class="form-input" data-slider-modal-url-input>
          </div>

          <div class="form-group">
            <label class="form-label" for="sliderModalSchedule">Schedule</label>
            <input id="sliderModalSchedule" type="text" class="form-input" data-slider-modal-schedule-input>
          </div>

          <div class="form-group">
            <label class="form-label" for="sliderModalPriority">Priority</label>
            <input id="sliderModalPriority" type="number" min="1" class="form-input" data-slider-modal-priority-input>
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="sliderModalStatus">Status</label>
            <select id="sliderModalStatus" class="form-select" data-slider-modal-status-input>
              <option>Live</option>
              <option>Scheduled</option>
              <option>Draft</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
        <button type="button" class="btn btn-success" data-slider-modal-save-btn>Save Slider Item</button>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="contentSocialModal" aria-hidden="true">
    <div class="modal">
      <div class="modal-header">
        <h3 class="modal-title" data-social-modal-title>Add Social Link</h3>
        <button type="button" class="modal-close" data-modal-close aria-label="Close social modal">x</button>
      </div>
      <div class="modal-body">
        <input type="hidden" data-social-modal-mode value="create">

        <div class="settings-field-grid settings-modal-grid">
          <div class="form-group">
            <label class="form-label" for="socialModalPlatform">Platform</label>
            <input id="socialModalPlatform" type="text" class="form-input" data-social-modal-platform-input>
          </div>

          <div class="form-group">
            <label class="form-label" for="socialModalStatus">Status</label>
            <select id="socialModalStatus" class="form-select" data-social-modal-status-input>
              <option>Active</option>
              <option>Draft</option>
            </select>
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="socialModalUrl">Profile URL</label>
            <input id="socialModalUrl" type="text" class="form-input" data-social-modal-url-input>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
        <button type="button" class="btn btn-success" data-social-modal-save-btn>Save Social Link</button>
      </div>
    </div>
  </div>

  <script type="application/json" id="contentPolicyJson">@json($legalPages)</script>
  <script type="application/json" id="contentSliderDefaultsJson">@json($sliderDefaults)</script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const contentCenter = document.querySelector('[data-content-center]');
      if (!contentCenter) {
        return;
      }

      const policyDataScript = document.getElementById('contentPolicyJson');
      const sliderDefaultsScript = document.getElementById('contentSliderDefaultsJson');
      const policies = policyDataScript ? JSON.parse(policyDataScript.textContent || '[]') : [];
      const sliderDefaults = sliderDefaultsScript ? JSON.parse(sliderDefaultsScript.textContent || '{}') : {};

      const policyButtons = Array.from(contentCenter.querySelectorAll('[data-policy-tab]'));
      const policyKeyInput = contentCenter.querySelector('[data-policy-key]');
      const policyTitleInput = contentCenter.querySelector('[data-policy-title]');
      const policySlugInput = contentCenter.querySelector('[data-policy-slug]');
      const policyStatusSelect = contentCenter.querySelector('[data-policy-status]');
      const policyReviewCycleSelect = contentCenter.querySelector('[data-policy-review-cycle]');
      const policySeoTitleInput = contentCenter.querySelector('[data-policy-seo-title]');
      const policyMetaDescriptionInput = contentCenter.querySelector('[data-policy-meta-description]');
      const policyContentInput = contentCenter.querySelector('[data-policy-content]');
      const policyFooterToggle = contentCenter.querySelector('[data-policy-show-footer]');
      const policyStatusBadge = contentCenter.querySelector('[data-policy-status-badge]');
      const policyLastUpdated = contentCenter.querySelector('[data-policy-last-updated]');
      const policySaveButton = contentCenter.querySelector('[data-policy-save-btn]');
      const policyPreviewButton = contentCenter.querySelector('[data-policy-preview-btn]');

      const sliderCreateButton = document.querySelector('[data-slider-create-btn]');
      const sliderEditButtons = Array.from(document.querySelectorAll('[data-slider-edit-btn]'));
      const sliderRemoveButtons = Array.from(document.querySelectorAll('[data-slider-remove-btn]'));
      const sliderModalModeInput = document.querySelector('[data-slider-modal-mode]');
      const sliderModalIdInput = document.querySelector('[data-slider-modal-id]');
      const sliderModalTitle = document.querySelector('[data-slider-modal-title]');
      const sliderModalTitleInput = document.querySelector('[data-slider-modal-title-input]');
      const sliderModalHeadlineInput = document.querySelector('[data-slider-modal-headline-input]');
      const sliderModalCtaInput = document.querySelector('[data-slider-modal-cta-input]');
      const sliderModalUrlInput = document.querySelector('[data-slider-modal-url-input]');
      const sliderModalScheduleInput = document.querySelector('[data-slider-modal-schedule-input]');
      const sliderModalPriorityInput = document.querySelector('[data-slider-modal-priority-input]');
      const sliderModalStatusInput = document.querySelector('[data-slider-modal-status-input]');
      const sliderModalSaveButton = document.querySelector('[data-slider-modal-save-btn]');

      const socialCreateButton = document.querySelector('[data-social-create-btn]');
      const socialEditButtons = Array.from(document.querySelectorAll('[data-social-edit-btn]'));
      const socialRemoveButtons = Array.from(document.querySelectorAll('[data-social-remove-btn]'));
      const socialModalModeInput = document.querySelector('[data-social-modal-mode]');
      const socialModalTitle = document.querySelector('[data-social-modal-title]');
      const socialModalPlatformInput = document.querySelector('[data-social-modal-platform-input]');
      const socialModalStatusInput = document.querySelector('[data-social-modal-status-input]');
      const socialModalUrlInput = document.querySelector('[data-social-modal-url-input]');
      const socialModalSaveButton = document.querySelector('[data-social-modal-save-btn]');

      const saveAllButton = document.querySelector('[data-content-save-all-btn]');
      const previewButton = document.querySelector('[data-content-preview-btn]');
      const contactSaveButton = document.querySelector('[data-contact-save-btn]');
      const footerSaveButton = document.querySelector('[data-footer-save-btn]');
      const seoSaveButton = document.querySelector('[data-seo-save-btn]');
      const footerToggles = Array.from(document.querySelectorAll('[data-footer-toggle]'));

      const policyMap = new Map();
      policies.forEach((policy) => {
        policyMap.set(policy.key, policy);
      });

      function policyBadgeClass(status) {
        if (status === 'Published') {
          return 'badge-success';
        }

        if (status === 'In Review') {
          return 'badge-warning';
        }

        return 'badge-info';
      }

      function activatePolicyButton(targetKey) {
        policyButtons.forEach((button) => {
          const active = button.dataset.policyTab === targetKey;
          button.classList.toggle('active', active);
          button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
      }

      function fillPolicyEditor(key) {
        const policy = policyMap.get(key);
        if (!policy) {
          return;
        }

        if (policyKeyInput instanceof HTMLInputElement) {
          policyKeyInput.value = policy.key;
        }

        if (policyTitleInput instanceof HTMLInputElement) {
          policyTitleInput.value = policy.title || '';
        }

        if (policySlugInput instanceof HTMLInputElement) {
          policySlugInput.value = policy.slug || '';
        }

        if (policyStatusSelect instanceof HTMLSelectElement) {
          policyStatusSelect.value = policy.status || 'Draft';
        }

        if (policyReviewCycleSelect instanceof HTMLSelectElement) {
          policyReviewCycleSelect.value = policy.review_cycle || 'Every 90 days';
        }

        if (policySeoTitleInput instanceof HTMLInputElement) {
          policySeoTitleInput.value = policy.seo_title || '';
        }

        if (policyMetaDescriptionInput instanceof HTMLTextAreaElement) {
          policyMetaDescriptionInput.value = policy.meta_description || '';
        }

        if (policyContentInput instanceof HTMLTextAreaElement) {
          policyContentInput.value = policy.content || '';
        }

        if (policyFooterToggle instanceof HTMLInputElement) {
          policyFooterToggle.checked = Boolean(policy.show_in_footer);
        }

        if (policyStatusBadge instanceof HTMLElement) {
          policyStatusBadge.textContent = policy.status || 'Draft';
          policyStatusBadge.className = `badge ${policyBadgeClass(policy.status || 'Draft')}`;
        }

        if (policyLastUpdated instanceof HTMLElement) {
          policyLastUpdated.textContent = `Last updated ${policy.last_updated || 'N/A'}`;
        }

        activatePolicyButton(policy.key);
      }

      function currentPolicy() {
        if (!(policyKeyInput instanceof HTMLInputElement)) {
          return null;
        }

        return policyMap.get(policyKeyInput.value) || null;
      }

      function openSliderCreate() {
        if (!(sliderModalModeInput instanceof HTMLInputElement)) {
          return;
        }

        sliderModalModeInput.value = 'create';

        if (sliderModalIdInput instanceof HTMLInputElement) {
          sliderModalIdInput.value = '';
        }

        if (sliderModalTitle instanceof HTMLElement) {
          sliderModalTitle.textContent = 'Add Slider Item';
        }

        if (sliderModalTitleInput instanceof HTMLInputElement) {
          sliderModalTitleInput.value = sliderDefaults.title || '';
        }

        if (sliderModalHeadlineInput instanceof HTMLTextAreaElement) {
          sliderModalHeadlineInput.value = sliderDefaults.headline || '';
        }

        if (sliderModalCtaInput instanceof HTMLInputElement) {
          sliderModalCtaInput.value = sliderDefaults.cta || '';
        }

        if (sliderModalUrlInput instanceof HTMLInputElement) {
          sliderModalUrlInput.value = sliderDefaults.url || '';
        }

        if (sliderModalScheduleInput instanceof HTMLInputElement) {
          sliderModalScheduleInput.value = sliderDefaults.schedule || 'Always On';
        }

        if (sliderModalPriorityInput instanceof HTMLInputElement) {
          sliderModalPriorityInput.value = String(sliderDefaults.priority || 1);
        }

        if (sliderModalStatusInput instanceof HTMLSelectElement) {
          sliderModalStatusInput.value = sliderDefaults.status || 'Draft';
        }
      }

      function openSliderEdit(button) {
        if (!(button instanceof HTMLButtonElement)) {
          return;
        }

        if (!(sliderModalModeInput instanceof HTMLInputElement)) {
          return;
        }

        sliderModalModeInput.value = 'edit';

        if (sliderModalIdInput instanceof HTMLInputElement) {
          sliderModalIdInput.value = String(button.dataset.slideId || '');
        }

        if (sliderModalTitle instanceof HTMLElement) {
          sliderModalTitle.textContent = 'Edit Slider Item';
        }

        if (sliderModalTitleInput instanceof HTMLInputElement) {
          sliderModalTitleInput.value = String(button.dataset.slideTitle || '');
        }

        if (sliderModalHeadlineInput instanceof HTMLTextAreaElement) {
          sliderModalHeadlineInput.value = String(button.dataset.slideHeadline || '');
        }

        if (sliderModalCtaInput instanceof HTMLInputElement) {
          sliderModalCtaInput.value = String(button.dataset.slideCta || '');
        }

        if (sliderModalUrlInput instanceof HTMLInputElement) {
          sliderModalUrlInput.value = String(button.dataset.slideUrl || '');
        }

        if (sliderModalScheduleInput instanceof HTMLInputElement) {
          sliderModalScheduleInput.value = String(button.dataset.slideSchedule || 'Always On');
        }

        if (sliderModalPriorityInput instanceof HTMLInputElement) {
          sliderModalPriorityInput.value = String(button.dataset.slidePriority || '1');
        }

        if (sliderModalStatusInput instanceof HTMLSelectElement) {
          sliderModalStatusInput.value = String(button.dataset.slideStatus || 'Draft');
        }
      }

      function openSocialCreate() {
        if (socialModalModeInput instanceof HTMLInputElement) {
          socialModalModeInput.value = 'create';
        }

        if (socialModalTitle instanceof HTMLElement) {
          socialModalTitle.textContent = 'Add Social Link';
        }

        if (socialModalPlatformInput instanceof HTMLInputElement) {
          socialModalPlatformInput.value = '';
        }

        if (socialModalStatusInput instanceof HTMLSelectElement) {
          socialModalStatusInput.value = 'Active';
        }

        if (socialModalUrlInput instanceof HTMLInputElement) {
          socialModalUrlInput.value = '';
        }
      }

      function openSocialEdit(button) {
        if (!(button instanceof HTMLButtonElement)) {
          return;
        }

        if (socialModalModeInput instanceof HTMLInputElement) {
          socialModalModeInput.value = 'edit';
        }

        if (socialModalTitle instanceof HTMLElement) {
          socialModalTitle.textContent = 'Edit Social Link';
        }

        if (socialModalPlatformInput instanceof HTMLInputElement) {
          socialModalPlatformInput.value = String(button.dataset.socialPlatform || '');
        }

        if (socialModalStatusInput instanceof HTMLSelectElement) {
          socialModalStatusInput.value = String(button.dataset.socialStatus || 'Active');
        }

        if (socialModalUrlInput instanceof HTMLInputElement) {
          socialModalUrlInput.value = String(button.dataset.socialUrl || '');
        }
      }

      policyButtons.forEach((button) => {
        button.addEventListener('click', () => {
          fillPolicyEditor(String(button.dataset.policyTab || ''));
        });
      });

      if (policyStatusSelect instanceof HTMLSelectElement) {
        policyStatusSelect.addEventListener('change', () => {
          if (policyStatusBadge instanceof HTMLElement) {
            const value = policyStatusSelect.value;
            policyStatusBadge.textContent = value;
            policyStatusBadge.className = `badge ${policyBadgeClass(value)}`;
          }
        });
      }

      if (policySaveButton instanceof HTMLButtonElement) {
        policySaveButton.addEventListener('click', () => {
          const selectedPolicy = currentPolicy();
          const policyName = selectedPolicy?.title || 'page';

          if (typeof window.showSuccess === 'function') {
            window.showSuccess(`${policyName} content saved (UI demo).`);
          }
        });
      }

      if (policyPreviewButton instanceof HTMLButtonElement) {
        policyPreviewButton.addEventListener('click', () => {
          const selectedPolicy = currentPolicy();
          const url = selectedPolicy?.slug || '/preview';

          if (typeof window.showInfo === 'function') {
            window.showInfo(`Preview ready for ${url}`);
          }
        });
      }

      if (sliderCreateButton instanceof HTMLButtonElement) {
        sliderCreateButton.addEventListener('click', openSliderCreate);
      }

      sliderEditButtons.forEach((button) => {
        button.addEventListener('click', () => openSliderEdit(button));
      });

      if (sliderModalSaveButton instanceof HTMLButtonElement) {
        sliderModalSaveButton.addEventListener('click', () => {
          const mode = sliderModalModeInput instanceof HTMLInputElement ? sliderModalModeInput.value : 'create';
          const title = sliderModalTitleInput instanceof HTMLInputElement ? sliderModalTitleInput.value.trim() : '';

          if (!title) {
            if (typeof window.showError === 'function') {
              window.showError('Slide title is required.');
            }
            return;
          }

          if (typeof window.showSuccess === 'function') {
            window.showSuccess(mode === 'edit' ? `Slider "${title}" updated (UI demo).` : `Slider "${title}" added (UI demo).`);
          }

          if (typeof window.closeAllModals === 'function') {
            window.closeAllModals();
          }
        });
      }

      sliderRemoveButtons.forEach((button) => {
        button.addEventListener('click', () => {
          const slideTitle = String(button.dataset.slideTitle || 'this slide');
          const shouldRemove = window.confirm(`Remove ${slideTitle}?`);
          if (!shouldRemove) {
            return;
          }

          const slideId = String(button.dataset.slideId || '');
          const row = slideId ? document.querySelector(`[data-slider-row="${slideId}"]`) : null;
          if (row instanceof HTMLElement) {
            row.remove();
          }

          if (typeof window.showWarning === 'function') {
            window.showWarning(`${slideTitle} removed from UI table.`);
          }
        });
      });

      if (socialCreateButton instanceof HTMLButtonElement) {
        socialCreateButton.addEventListener('click', openSocialCreate);
      }

      socialEditButtons.forEach((button) => {
        button.addEventListener('click', () => openSocialEdit(button));
      });

      if (socialModalSaveButton instanceof HTMLButtonElement) {
        socialModalSaveButton.addEventListener('click', () => {
          const platform = socialModalPlatformInput instanceof HTMLInputElement ? socialModalPlatformInput.value.trim() : '';
          if (!platform) {
            if (typeof window.showError === 'function') {
              window.showError('Platform name is required.');
            }
            return;
          }

          if (typeof window.showSuccess === 'function') {
            window.showSuccess(`${platform} social link saved (UI demo).`);
          }

          if (typeof window.closeAllModals === 'function') {
            window.closeAllModals();
          }
        });
      }

      socialRemoveButtons.forEach((button) => {
        button.addEventListener('click', () => {
          const platform = String(button.dataset.socialPlatform || 'this social profile');
          const shouldRemove = window.confirm(`Remove ${platform} from social links?`);
          if (!shouldRemove) {
            return;
          }

          const row = button.closest('tr');
          if (row instanceof HTMLElement) {
            row.remove();
          }

          if (typeof window.showWarning === 'function') {
            window.showWarning(`${platform} link removed from UI table.`);
          }
        });
      });

      footerToggles.forEach((toggle) => {
        toggle.addEventListener('change', () => {
          const row = toggle.closest('.bot-setting-row');
          if (!(row instanceof HTMLElement)) {
            return;
          }

          const stateLabel = row.querySelector('[data-footer-state-label]');
          if (!(stateLabel instanceof HTMLElement)) {
            return;
          }

          stateLabel.textContent = toggle.checked ? 'On' : 'Off';
          stateLabel.classList.toggle('on', toggle.checked);
          stateLabel.classList.toggle('off', !toggle.checked);
        });
      });

      if (contactSaveButton instanceof HTMLButtonElement) {
        contactSaveButton.addEventListener('click', () => {
          if (typeof window.showSuccess === 'function') {
            window.showSuccess('Contact and store information saved (UI demo).');
          }
        });
      }

      if (footerSaveButton instanceof HTMLButtonElement) {
        footerSaveButton.addEventListener('click', () => {
          if (typeof window.showSuccess === 'function') {
            window.showSuccess('Footer and trust settings saved (UI demo).');
          }
        });
      }

      if (seoSaveButton instanceof HTMLButtonElement) {
        seoSaveButton.addEventListener('click', () => {
          if (typeof window.showSuccess === 'function') {
            window.showSuccess('SEO defaults and scripts saved (UI demo).');
          }
        });
      }

      if (saveAllButton instanceof HTMLButtonElement) {
        saveAllButton.addEventListener('click', () => {
          if (typeof window.showSuccess === 'function') {
            window.showSuccess('All content settings saved (UI demo).');
          }
        });
      }

      if (previewButton instanceof HTMLButtonElement) {
        previewButton.addEventListener('click', () => {
          if (typeof window.showInfo === 'function') {
            window.showInfo('Storefront preview opened (UI demo).');
          }
        });
      }

      const firstPolicyKey = policies[0]?.key || '';
      if (firstPolicyKey) {
        fillPolicyEditor(firstPolicyKey);
      }

      openSliderCreate();
      openSocialCreate();
    });
  </script>
@endsection
