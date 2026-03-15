@extends('admin.shop-settings.website-content.layout')

@section('website-content-body')
  @php
    $activeSocialLinks = collect($socialLinks)->where('status', 'Active')->count();
  @endphp

  <div class="settings-layout mt-md" data-contact-workspace>
    <section class="settings-main-column">
      <article class="card settings-panel">
        <div class="card-header">
          <div>
            <h3 class="card-title">Customer contact details</h3>
            <p class="settings-panel-subtitle">Keep the core support information in one form so the storefront and team stay aligned.</p>
          </div>
          <span class="badge badge-info">Customer Facing</span>
        </div>

        <div class="settings-field-grid">
          <div class="form-group">
            <label class="form-label" for="websiteSupportPhone">Support Phone</label>
            <input id="websiteSupportPhone" type="text" class="form-input" value="{{ $contactInfo['support_phone'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="websiteSupportWhatsapp">WhatsApp Number</label>
            <input id="websiteSupportWhatsapp" type="text" class="form-input" value="{{ $contactInfo['support_whatsapp'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="websiteSupportEmail">Support Email</label>
            <input id="websiteSupportEmail" type="email" class="form-input" value="{{ $contactInfo['support_email'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="websiteBusinessEmail">Business Email</label>
            <input id="websiteBusinessEmail" type="email" class="form-input" value="{{ $contactInfo['business_email'] }}">
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="websiteStoreAddress">Store Address</label>
            <input id="websiteStoreAddress" type="text" class="form-input" value="{{ $contactInfo['store_address'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="websiteSupportHours">Support Hours</label>
            <input id="websiteSupportHours" type="text" class="form-input" value="{{ $contactInfo['support_hours'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="websiteMapUrl">Map URL</label>
            <input id="websiteMapUrl" type="text" class="form-input" value="{{ $contactInfo['map_embed'] }}">
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="websiteContactNotice">Contact Page Notice</label>
            <textarea id="websiteContactNotice" class="form-textarea" rows="3">{{ $contactInfo['contact_page_notice'] }}</textarea>
          </div>
        </div>

        <div class="settings-inline-actions">
          <button type="button" class="btn btn-success btn-sm" data-contact-save-btn>Save Contact Details</button>
        </div>
      </article>

      <article class="card settings-panel mt-md">
        <div class="card-header">
          <div>
            <h3 class="card-title">Social profile list</h3>
            <p class="settings-panel-subtitle">Edit storefront social links from the side panel and keep the visible channels clean.</p>
          </div>
          <span class="badge badge-primary" data-social-count-badge>{{ count($socialLinks) }} profiles</span>
        </div>

        <div class="settings-slider-spotlight">
          <div>
            <strong>Visibility summary</strong>
            <p>Only active social profiles should be shown to customers. Draft links can stay here until the brand team is ready.</p>
          </div>

          <div class="settings-slider-meta">
            <span data-social-active-count>Active: {{ $activeSocialLinks }}</span>
            <span data-social-draft-count>Draft: {{ count($socialLinks) - $activeSocialLinks }}</span>
          </div>
        </div>

        <div class="table-container mt-md">
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
                  $statusClass = $social['status'] === 'Active' ? 'badge-success' : 'badge-warning';
                @endphp
                <tr
                  data-social-row="{{ $social['platform'] }}"
                  data-social-platform="{{ $social['platform'] }}"
                  data-social-url="{{ $social['url'] }}"
                  data-social-status="{{ $social['status'] }}"
                >
                  <td class="settings-cell-strong" data-social-cell="platform">{{ $social['platform'] }}</td>
                  <td data-social-cell="url"><a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer">{{ $social['url'] }}</a></td>
                  <td data-social-cell="status"><span class="badge {{ $statusClass }}">{{ $social['status'] }}</span></td>
                  <td>
                    <div class="settings-offer-actions">
                      <button type="button" class="btn btn-primary btn-sm" data-social-edit-btn>Edit</button>
                      <button type="button" class="btn btn-danger btn-sm" data-social-remove-btn>Remove</button>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </article>
    </section>

    <aside class="settings-side-column">
      <article class="card settings-panel">
        <div class="card-header">
          <div>
            <h3 class="card-title" data-social-editor-title>Add social link</h3>
            <p class="settings-panel-subtitle">Use this editor for both new links and quick corrections to existing rows.</p>
          </div>
          <span class="badge badge-success">Editor</span>
        </div>

        <input type="hidden" data-social-mode value="create">
        <input type="hidden" data-social-original-platform value="">

        <div class="settings-field-grid">
          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="websiteSocialPlatform">Platform</label>
            <input id="websiteSocialPlatform" type="text" class="form-input" data-social-platform-input placeholder="Facebook">
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="websiteSocialUrl">Profile URL</label>
            <input id="websiteSocialUrl" type="text" class="form-input" data-social-url-input placeholder="https://facebook.com/yourbrand">
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="websiteSocialStatus">Status</label>
            <select id="websiteSocialStatus" class="form-select settings-coupon-dropdown" data-social-status-input>
              <option value="Active">Active</option>
              <option value="Draft">Draft</option>
            </select>
          </div>
        </div>

        <div class="settings-inline-actions">
          <button type="button" class="btn btn-secondary btn-sm" data-social-reset-btn>New Link</button>
          <button type="button" class="btn btn-success btn-sm" data-social-save-btn>Save Link</button>
        </div>
      </article>

      <article class="card settings-panel mt-md">
        <div class="card-header">
          <div>
            <h3 class="card-title">Support notes</h3>
            <p class="settings-panel-subtitle">Keep the contact page simple for customers and support agents.</p>
          </div>
          <span class="badge badge-warning">Tips</span>
        </div>

        <div style="display: grid; gap: 12px; color: var(--text-secondary); font-size: 13px;">
          <p style="margin: 0;">1. Use the same phone and WhatsApp numbers your team answers daily.</p>
          <p style="margin: 0;">2. Keep only the social channels that actually respond to customer messages.</p>
          <p style="margin: 0;">3. Update support hours first when your holiday or campaign schedule changes.</p>
        </div>
      </article>
    </aside>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const workspace = document.querySelector('[data-contact-workspace]');
      if (!(workspace instanceof HTMLElement)) {
        return;
      }

      const contactSaveButton = workspace.querySelector('[data-contact-save-btn]');
      const tableBody = workspace.querySelector('[data-social-table-body]');
      const modeInput = workspace.querySelector('[data-social-mode]');
      const originalPlatformInput = workspace.querySelector('[data-social-original-platform]');
      const editorTitle = workspace.querySelector('[data-social-editor-title]');
      const platformInput = workspace.querySelector('[data-social-platform-input]');
      const urlInput = workspace.querySelector('[data-social-url-input]');
      const statusInput = workspace.querySelector('[data-social-status-input]');
      const saveButton = workspace.querySelector('[data-social-save-btn]');
      const resetButton = workspace.querySelector('[data-social-reset-btn]');
      const countBadge = workspace.querySelector('[data-social-count-badge]');
      const activeCount = workspace.querySelector('[data-social-active-count]');
      const draftCount = workspace.querySelector('[data-social-draft-count]');

      if (!(tableBody instanceof HTMLElement)) {
        return;
      }

      function statusClass(status) {
        return status === 'Active' ? 'badge-success' : 'badge-warning';
      }

      function socialRows() {
        return Array.from(tableBody.querySelectorAll('[data-social-row]'));
      }

      function rowByPlatform(platform) {
        return socialRows().find((row) => row.getAttribute('data-social-platform') === platform) || null;
      }

      function buildSocialRow(item) {
        const row = document.createElement('tr');
        row.dataset.socialRow = item.platform;
        row.dataset.socialPlatform = item.platform;
        row.dataset.socialUrl = item.url;
        row.dataset.socialStatus = item.status;

        const platformCell = document.createElement('td');
        platformCell.className = 'settings-cell-strong';
        platformCell.dataset.socialCell = 'platform';
        platformCell.textContent = item.platform;

        const urlCell = document.createElement('td');
        urlCell.dataset.socialCell = 'url';
        const link = document.createElement('a');
        link.href = item.url;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        link.textContent = item.url;
        urlCell.appendChild(link);

        const statusCell = document.createElement('td');
        statusCell.dataset.socialCell = 'status';
        const badge = document.createElement('span');
        badge.className = `badge ${statusClass(item.status)}`;
        badge.textContent = item.status;
        statusCell.appendChild(badge);

        const actionCell = document.createElement('td');
        const actions = document.createElement('div');
        actions.className = 'settings-offer-actions';

        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.className = 'btn btn-primary btn-sm';
        editButton.dataset.socialEditBtn = '';
        editButton.textContent = 'Edit';

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'btn btn-danger btn-sm';
        removeButton.dataset.socialRemoveBtn = '';
        removeButton.textContent = 'Remove';

        actions.appendChild(editButton);
        actions.appendChild(removeButton);
        actionCell.appendChild(actions);

        row.appendChild(platformCell);
        row.appendChild(urlCell);
        row.appendChild(statusCell);
        row.appendChild(actionCell);

        return row;
      }

      function resetEditor() {
        if (modeInput instanceof HTMLInputElement) {
          modeInput.value = 'create';
        }

        if (originalPlatformInput instanceof HTMLInputElement) {
          originalPlatformInput.value = '';
        }

        if (editorTitle instanceof HTMLElement) {
          editorTitle.textContent = 'Add social link';
        }

        if (saveButton instanceof HTMLButtonElement) {
          saveButton.textContent = 'Save Link';
        }

        if (platformInput instanceof HTMLInputElement) {
          platformInput.value = '';
        }

        if (urlInput instanceof HTMLInputElement) {
          urlInput.value = '';
        }

        if (statusInput instanceof HTMLSelectElement) {
          statusInput.value = 'Active';
        }
      }

      function updateSummary() {
        const rows = socialRows();
        const active = rows.filter((row) => row.dataset.socialStatus === 'Active').length;
        const draft = rows.length - active;

        if (countBadge instanceof HTMLElement) {
          countBadge.textContent = `${rows.length} profiles`;
        }

        if (activeCount instanceof HTMLElement) {
          activeCount.textContent = `Active: ${active}`;
        }

        if (draftCount instanceof HTMLElement) {
          draftCount.textContent = `Draft: ${draft}`;
        }
      }

      function loadRowIntoEditor(row) {
        if (!(row instanceof HTMLElement)) {
          return;
        }

        if (modeInput instanceof HTMLInputElement) {
          modeInput.value = 'edit';
        }

        if (originalPlatformInput instanceof HTMLInputElement) {
          originalPlatformInput.value = row.dataset.socialPlatform || '';
        }

        if (editorTitle instanceof HTMLElement) {
          editorTitle.textContent = 'Edit social link';
        }

        if (saveButton instanceof HTMLButtonElement) {
          saveButton.textContent = 'Update Link';
        }

        if (platformInput instanceof HTMLInputElement) {
          platformInput.value = row.dataset.socialPlatform || '';
        }

        if (urlInput instanceof HTMLInputElement) {
          urlInput.value = row.dataset.socialUrl || '';
        }

        if (statusInput instanceof HTMLSelectElement) {
          statusInput.value = row.dataset.socialStatus || 'Active';
        }
      }

      contactSaveButton?.addEventListener('click', () => {
        if (typeof window.showSuccess === 'function') {
          window.showSuccess('Contact information saved.');
        }
      });

      tableBody.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Element)) {
          return;
        }

        const editButton = target.closest('[data-social-edit-btn]');
        if (editButton instanceof HTMLButtonElement) {
          loadRowIntoEditor(editButton.closest('[data-social-row]'));
          return;
        }

        const removeButton = target.closest('[data-social-remove-btn]');
        if (!(removeButton instanceof HTMLButtonElement)) {
          return;
        }

        const row = removeButton.closest('[data-social-row]');
        if (!(row instanceof HTMLElement)) {
          return;
        }

        const platform = row.dataset.socialPlatform || 'this social link';
        const shouldRemove = window.confirm(`Remove ${platform}?`);
        if (!shouldRemove) {
          return;
        }

        row.remove();
        updateSummary();

        if (typeof window.showWarning === 'function') {
          window.showWarning(`${platform} removed from the contact page.`);
        }

        if (originalPlatformInput instanceof HTMLInputElement && originalPlatformInput.value === platform) {
          resetEditor();
        }
      });

      resetButton?.addEventListener('click', resetEditor);

      saveButton?.addEventListener('click', () => {
        const platform = platformInput instanceof HTMLInputElement ? platformInput.value.trim() : '';
        const url = urlInput instanceof HTMLInputElement ? urlInput.value.trim() : '';
        const status = statusInput instanceof HTMLSelectElement ? statusInput.value : 'Active';
        const mode = modeInput instanceof HTMLInputElement ? modeInput.value : 'create';
        const originalPlatform = originalPlatformInput instanceof HTMLInputElement ? originalPlatformInput.value : '';

        if (!platform) {
          if (typeof window.showError === 'function') {
            window.showError('Platform name is required.');
          }
          return;
        }

        if (!url) {
          if (typeof window.showError === 'function') {
            window.showError('Profile URL is required.');
          }
          return;
        }

        const item = { platform, url, status };
        const existingRow = mode === 'edit' && originalPlatform ? rowByPlatform(originalPlatform) : null;

        if (existingRow instanceof HTMLElement) {
          existingRow.replaceWith(buildSocialRow(item));
        } else {
          tableBody.appendChild(buildSocialRow(item));
        }

        updateSummary();
        resetEditor();

        if (typeof window.showSuccess === 'function') {
          window.showSuccess(mode === 'edit' ? `${platform} updated.` : `${platform} added to social links.`);
        }
      });

      updateSummary();
    });
  </script>
@endsection
