@extends('admin.shop-settings.website-content.layout')

@section('website-content-body')
  @php
    $defaultPage = $legalPages[0] ?? null;
  @endphp

  <div class="settings-layout settings-layout-single mt-md" data-policy-editor-root>
    <section class="settings-main-column">
      <article class="card settings-panel">
        <div class="card-header">
          <div>
            <h3 class="card-title">Policy and static page editor</h3>
            <p class="settings-panel-subtitle">Switch pages from the left, then edit title, SEO, and body content from one workspace.</p>
          </div>

          <div class="settings-inline-actions mt-0">
            <button type="button" class="btn btn-secondary btn-sm" data-policy-source-toggle-btn>HTML / Code Mode</button>
            <button type="button" class="btn btn-primary btn-sm" data-policy-preview-btn>Preview Page</button>
            <button type="button" class="btn btn-success btn-sm" data-policy-save-btn>Save Page</button>
          </div>
        </div>

        <div class="settings-policy-layout">
          <div class="settings-policy-nav" role="tablist" aria-label="Website page navigation">
            @foreach ($legalPages as $page)
              @php
                $statusClass = $page['status'] === 'Published' ? 'badge-success' : 'badge-info';
              @endphp
              <button
                type="button"
                class="settings-policy-tab {{ $loop->first ? 'active' : '' }}"
                data-policy-tab="{{ $page['key'] }}"
                aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
              >
                <span data-policy-tab-title>{{ $page['title'] }}</span>
                <small data-policy-tab-updated>Updated {{ $page['last_updated'] }}</small>
                <span class="badge {{ $statusClass }}" data-policy-tab-status>{{ $page['status'] }}</span>
              </button>
            @endforeach
          </div>

          <div class="settings-policy-editor-area">
            <div class="settings-policy-meta">
              <span class="badge badge-success" data-policy-status-badge>{{ $defaultPage['status'] ?? 'Draft' }}</span>
              <span class="text-muted" data-policy-last-updated>Last updated {{ $defaultPage['last_updated'] ?? 'N/A' }}</span>
              <span class="text-muted" data-policy-review-cycle>{{ $defaultPage['review_cycle'] ?? 'No review cycle' }}</span>
            </div>

            <input type="hidden" data-policy-key value="{{ $defaultPage['key'] ?? '' }}">

            <div class="settings-field-grid mt-md">
              <div class="form-group">
                <label class="form-label" for="websitePolicyTitle">Page Title</label>
                <input id="websitePolicyTitle" type="text" class="form-input" data-policy-title value="{{ $defaultPage['title'] ?? '' }}">
              </div>

              <div class="form-group">
                <label class="form-label" for="websitePolicySlug">Page URL</label>
                <input id="websitePolicySlug" type="text" class="form-input" data-policy-slug value="{{ $defaultPage['slug'] ?? '' }}">
              </div>

              <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label" for="websitePolicySeoTitle">SEO Title</label>
                <input id="websitePolicySeoTitle" type="text" class="form-input" data-policy-seo-title value="{{ $defaultPage['seo_title'] ?? '' }}">
              </div>

              <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label" for="websitePolicyMetaDescription">SEO Description</label>
                <textarea id="websitePolicyMetaDescription" class="form-textarea" rows="3" data-policy-meta-description>{{ $defaultPage['meta_description'] ?? '' }}</textarea>
              </div>

              <div class="form-group" style="grid-column: 1 / -1;">
                <label class="settings-policy-toggle">
                  <input type="checkbox" data-policy-show-footer {{ ! empty($defaultPage['show_in_footer']) ? 'checked' : '' }}>
                  <span>Show this page in footer navigation</span>
                </label>
              </div>

              <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label" for="websitePolicyContent">Page Content</label>
                <textarea
                  id="websitePolicyContent"
                  class="form-textarea settings-policy-textarea"
                  data-policy-content
                  data-ckeditor
                >{{ $defaultPage['content'] ?? '' }}</textarea>
                <textarea
                  id="websitePolicyContentSource"
                  class="form-textarea settings-policy-textarea mt-sm"
                  data-policy-content-source
                  style="display: none; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;"
                ></textarea>
              </div>
            </div>
          </div>
        </div>
      </article>
    </section>
  </div>

  <script type="application/json" id="websiteContentPoliciesJson">@json($legalPages)</script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const root = document.querySelector('[data-policy-editor-root]');
      const dataScript = document.getElementById('websiteContentPoliciesJson');

      if (!(root instanceof HTMLElement) || !(dataScript instanceof HTMLScriptElement)) {
        return;
      }

      let policies = [];
      try {
        policies = JSON.parse(dataScript.textContent || '[]');
      } catch (error) {
        policies = [];
      }

      const policyButtons = Array.from(root.querySelectorAll('[data-policy-tab]'));
      const policyKeyInput = root.querySelector('[data-policy-key]');
      const policyTitleInput = root.querySelector('[data-policy-title]');
      const policySlugInput = root.querySelector('[data-policy-slug]');
      const policySeoTitleInput = root.querySelector('[data-policy-seo-title]');
      const policyMetaDescriptionInput = root.querySelector('[data-policy-meta-description]');
      const policyContentInput = root.querySelector('[data-policy-content]');
      const policySourceTextarea = root.querySelector('[data-policy-content-source]');
      const policyFooterToggle = root.querySelector('[data-policy-show-footer]');
      const policyStatusBadge = root.querySelector('[data-policy-status-badge]');
      const policyLastUpdated = root.querySelector('[data-policy-last-updated]');
      const policyReviewCycle = root.querySelector('[data-policy-review-cycle]');
      const sourceToggleButton = root.querySelector('[data-policy-source-toggle-btn]');
      const previewButton = root.querySelector('[data-policy-preview-btn]');
      const saveButton = root.querySelector('[data-policy-save-btn]');

      const policyMap = new Map();
      policies.forEach((policy) => {
        policyMap.set(policy.key, policy);
      });

      let sourceMode = false;

      function currentPolicy() {
        if (!(policyKeyInput instanceof HTMLInputElement)) {
          return null;
        }

        return policyMap.get(policyKeyInput.value) || null;
      }

      function editorElement() {
        if (!(policyContentInput instanceof HTMLTextAreaElement)) {
          return null;
        }

        if (!window.AdminCkeditor || typeof window.AdminCkeditor.getInstance !== 'function') {
          return null;
        }

        const instance = window.AdminCkeditor.getInstance(policyContentInput);
        const element = instance?.editor?.ui?.view?.element;
        return element instanceof HTMLElement ? element : null;
      }

      function getEditorData() {
        if (!(policyContentInput instanceof HTMLTextAreaElement)) {
          return '';
        }

        if (window.AdminCkeditor && typeof window.AdminCkeditor.getData === 'function') {
          return window.AdminCkeditor.getData(policyContentInput);
        }

        return policyContentInput.value;
      }

      function setEditorData(value) {
        if (!(policyContentInput instanceof HTMLTextAreaElement)) {
          return;
        }

        if (window.AdminCkeditor && typeof window.AdminCkeditor.setData === 'function') {
          window.AdminCkeditor.setData(policyContentInput, value);
          return;
        }

        policyContentInput.value = value;
      }

      function statusClass(status) {
        return status === 'Published' ? 'badge-success' : 'badge-info';
      }

      function activateButton(targetKey) {
        policyButtons.forEach((button) => {
          const isActive = button.dataset.policyTab === targetKey;
          button.classList.toggle('active', isActive);
          button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
      }

      function setSourceMode(enabled) {
        if (!(policySourceTextarea instanceof HTMLTextAreaElement) || !(sourceToggleButton instanceof HTMLButtonElement)) {
          return;
        }

        sourceMode = enabled;
        const richEditor = editorElement();

        if (enabled) {
          policySourceTextarea.value = getEditorData();
          policySourceTextarea.style.display = 'block';

          if (richEditor instanceof HTMLElement) {
            richEditor.style.display = 'none';
          }

          sourceToggleButton.textContent = 'Visual Mode';
          return;
        }

        setEditorData(policySourceTextarea.value);
        policySourceTextarea.style.display = 'none';

        if (richEditor instanceof HTMLElement) {
          richEditor.style.display = '';
        }

        sourceToggleButton.textContent = 'HTML / Code Mode';
      }

      function fillPolicy(key) {
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

        if (policySeoTitleInput instanceof HTMLInputElement) {
          policySeoTitleInput.value = policy.seo_title || '';
        }

        if (policyMetaDescriptionInput instanceof HTMLTextAreaElement) {
          policyMetaDescriptionInput.value = policy.meta_description || '';
        }

        setEditorData(policy.content || '');

        if (policySourceTextarea instanceof HTMLTextAreaElement) {
          policySourceTextarea.value = policy.content || '';
        }

        if (policyFooterToggle instanceof HTMLInputElement) {
          policyFooterToggle.checked = Boolean(policy.show_in_footer);
        }

        if (policyStatusBadge instanceof HTMLElement) {
          policyStatusBadge.textContent = policy.status || 'Draft';
          policyStatusBadge.className = `badge ${statusClass(policy.status || 'Draft')}`;
        }

        if (policyLastUpdated instanceof HTMLElement) {
          policyLastUpdated.textContent = `Last updated ${policy.last_updated || 'N/A'}`;
        }

        if (policyReviewCycle instanceof HTMLElement) {
          policyReviewCycle.textContent = policy.review_cycle || 'No review cycle';
        }

        activateButton(policy.key);
      }

      function syncPolicyToSidebar(policy) {
        const button = policyButtons.find((item) => item.dataset.policyTab === policy.key);
        if (!(button instanceof HTMLElement)) {
          return;
        }

        const title = button.querySelector('[data-policy-tab-title]');
        const updated = button.querySelector('[data-policy-tab-updated]');
        const status = button.querySelector('[data-policy-tab-status]');

        if (title instanceof HTMLElement) {
          title.textContent = policy.title || 'Untitled Page';
        }

        if (updated instanceof HTMLElement) {
          updated.textContent = `Updated ${policy.last_updated || 'N/A'}`;
        }

        if (status instanceof HTMLElement) {
          status.textContent = policy.status || 'Draft';
          status.className = `badge ${statusClass(policy.status || 'Draft')}`;
        }
      }

      policyButtons.forEach((button) => {
        button.addEventListener('click', () => {
          if (sourceMode) {
            setSourceMode(false);
          }

          fillPolicy(button.dataset.policyTab || '');
        });
      });

      sourceToggleButton?.addEventListener('click', () => {
        setSourceMode(!sourceMode);
      });

      previewButton?.addEventListener('click', () => {
        const policy = currentPolicy();
        if (!policy) {
          return;
        }

        if (typeof window.showInfo === 'function') {
          window.showInfo(`Preview ready for ${policy.slug || '/'}`);
        }
      });

      saveButton?.addEventListener('click', () => {
        const policy = currentPolicy();
        if (!policy) {
          return;
        }

        if (sourceMode) {
          setSourceMode(false);
        }

        policy.title = policyTitleInput instanceof HTMLInputElement ? policyTitleInput.value.trim() : policy.title;
        policy.slug = policySlugInput instanceof HTMLInputElement ? policySlugInput.value.trim() : policy.slug;
        policy.seo_title = policySeoTitleInput instanceof HTMLInputElement ? policySeoTitleInput.value.trim() : policy.seo_title;
        policy.meta_description = policyMetaDescriptionInput instanceof HTMLTextAreaElement ? policyMetaDescriptionInput.value.trim() : policy.meta_description;
        policy.content = getEditorData();
        policy.show_in_footer = policyFooterToggle instanceof HTMLInputElement ? policyFooterToggle.checked : policy.show_in_footer;
        policy.status = 'Published';
        policy.last_updated = 'Just now';

        syncPolicyToSidebar(policy);
        fillPolicy(policy.key);

        if (typeof window.showSuccess === 'function') {
          window.showSuccess(`${policy.title || 'Page'} saved.`);
        }
      });

      const firstKey = policies[0]?.key || '';
      if (firstKey) {
        fillPolicy(firstKey);
      }
    });
  </script>
@endsection
