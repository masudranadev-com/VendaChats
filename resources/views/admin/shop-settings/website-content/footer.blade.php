@extends('admin.shop-settings.website-content.layout')

@section('website-content-body')
  <div class="settings-layout mt-md" data-footer-workspace>
    <section class="settings-main-column">
      <article class="card settings-panel">
        <div class="card-header">
          <div>
            <h3 class="card-title">Footer and trust content</h3>
            <p class="settings-panel-subtitle">Edit the customer-facing footer copy, trust badges, and global announcement from one form.</p>
          </div>
          <span class="badge badge-primary">Storefront Blocks</span>
        </div>

        <div class="settings-field-grid">
          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="websiteFooterTagline">Footer Tagline</label>
            <input id="websiteFooterTagline" type="text" class="form-input" value="{{ $trustSettings['store_tagline'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="websiteReturnBadge">Return Badge</label>
            <input id="websiteReturnBadge" type="text" class="form-input" value="{{ $trustSettings['return_badge'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="websiteShippingBadge">Shipping Badge</label>
            <input id="websiteShippingBadge" type="text" class="form-input" value="{{ $trustSettings['shipping_badge'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="websitePaymentBadge">Payment Badge</label>
            <input id="websitePaymentBadge" type="text" class="form-input" value="{{ $trustSettings['payment_badge'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="websiteAnnouncementBar">Announcement Bar</label>
            <input id="websiteAnnouncementBar" type="text" class="form-input" value="{{ $trustSettings['announcement_bar'] }}">
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="websiteCopyrightLine">Copyright Line</label>
            <input id="websiteCopyrightLine" type="text" class="form-input" value="{{ $trustSettings['copyright'] }}">
          </div>
        </div>

        <div class="settings-inline-actions">
          <button type="button" class="btn btn-success btn-sm" data-footer-save-btn>Save Footer Content</button>
          <button type="button" class="btn btn-primary btn-sm" data-footer-preview-btn>Preview Footer</button>
        </div>
      </article>

      <article class="card settings-panel mt-md">
        <div class="card-header">
          <div>
            <h3 class="card-title">SEO defaults and scripts</h3>
            <p class="settings-panel-subtitle">Keep your global metadata and tracking snippets together so technical content is easy to review.</p>
          </div>
          <span class="badge badge-info">Technical Content</span>
        </div>

        <div class="settings-field-grid">
          <div class="form-group">
            <label class="form-label" for="websiteTitleTemplate">Meta Title Template</label>
            <input id="websiteTitleTemplate" type="text" class="form-input" value="{{ $seoDefaults['title_template'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="websiteRobotsMeta">Robots Meta</label>
            <input id="websiteRobotsMeta" type="text" class="form-input" value="{{ $seoDefaults['robots_meta'] }}">
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="websiteMetaDescription">Default Meta Description</label>
            <textarea id="websiteMetaDescription" class="form-textarea" rows="3">{{ $seoDefaults['meta_description'] }}</textarea>
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="websiteMetaKeywords">Default Meta Keywords</label>
            <input id="websiteMetaKeywords" type="text" class="form-input" value="{{ $seoDefaults['meta_keywords'] }}">
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="websiteOgImage">Open Graph Image URL</label>
            <input id="websiteOgImage" type="text" class="form-input" value="{{ $seoDefaults['og_image_url'] }}">
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="websiteHeaderScript">Header Script</label>
            <textarea id="websiteHeaderScript" class="form-textarea" rows="3">{{ $seoDefaults['header_script'] }}</textarea>
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="websiteFooterScript">Footer Script</label>
            <textarea id="websiteFooterScript" class="form-textarea" rows="3">{{ $seoDefaults['footer_script'] }}</textarea>
          </div>
        </div>

        <div class="settings-inline-actions">
          <button type="button" class="btn btn-success btn-sm" data-seo-save-btn>Save SEO Defaults</button>
        </div>
      </article>
    </section>

    <aside class="settings-side-column">
      <article class="card settings-panel">
        <div class="card-header">
          <div>
            <h3 class="card-title">Footer preview notes</h3>
            <p class="settings-panel-subtitle">A quick reference for what the customer sees first in the footer area.</p>
          </div>
          <span class="badge badge-success">Preview</span>
        </div>

        <div class="settings-slider-spotlight" style="margin-top: 0;">
          <div>
            <strong>{{ $trustSettings['store_tagline'] }}</strong>
            <p>{{ $trustSettings['announcement_bar'] }}</p>
          </div>
        </div>

        <div style="display: grid; gap: 8px; margin-top: 14px;">
          <span class="badge badge-success">{{ $trustSettings['return_badge'] }}</span>
          <span class="badge badge-info">{{ $trustSettings['shipping_badge'] }}</span>
          <span class="badge badge-primary">{{ $trustSettings['payment_badge'] }}</span>
        </div>
      </article>

      <article class="card settings-panel mt-md">
        <div class="card-header">
          <div>
            <h3 class="card-title">Review checklist</h3>
            <p class="settings-panel-subtitle">Use this before publishing footer or script changes.</p>
          </div>
          <span class="badge badge-warning">Checklist</span>
        </div>

        <div style="display: grid; gap: 12px; color: var(--text-secondary); font-size: 13px;">
          <p style="margin: 0;">1. Keep footer tagline short enough for mobile screens.</p>
          <p style="margin: 0;">2. Confirm the announcement bar still matches the current campaign or offer.</p>
          <p style="margin: 0;">3. Review scripts carefully before saving so tracking tags stay valid.</p>
        </div>
      </article>
    </aside>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const workspace = document.querySelector('[data-footer-workspace]');
      if (!(workspace instanceof HTMLElement)) {
        return;
      }

      const footerSaveButton = workspace.querySelector('[data-footer-save-btn]');
      const footerPreviewButton = workspace.querySelector('[data-footer-preview-btn]');
      const seoSaveButton = workspace.querySelector('[data-seo-save-btn]');

      footerSaveButton?.addEventListener('click', () => {
        if (typeof window.showSuccess === 'function') {
          window.showSuccess('Footer content saved.');
        }
      });

      footerPreviewButton?.addEventListener('click', () => {
        if (typeof window.showInfo === 'function') {
          window.showInfo('Footer preview is ready.');
        }
      });

      seoSaveButton?.addEventListener('click', () => {
        if (typeof window.showSuccess === 'function') {
          window.showSuccess('SEO defaults saved.');
        }
      });
    });
  </script>
@endsection
