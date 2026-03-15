@extends('admin.shop-settings.website-content.layout')

@section('website-content-body')
  @php
    $liveSlideCount = collect($sliderItems)->where('status', 'Live')->count();
    $draftSlideCount = collect($sliderItems)->where('status', 'Draft')->count();
    $linkedProductCount = collect($sliderItems)->pluck('product_id')->unique()->count();
  @endphp

  <div class="settings-layout mt-md" data-slider-workspace>
    <section class="settings-main-column">
      <article class="card settings-panel">
        <div class="card-header">
          <div>
            <h3 class="card-title">Homepage slider queue</h3>
            <p class="settings-panel-subtitle">See the live order first, then edit or add new slides from the right-side editor.</p>
          </div>
          <span class="badge badge-primary" data-slider-count-badge>{{ count($sliderItems) }} slides</span>
        </div>

        <div class="settings-slider-spotlight">
          <div>
            <strong>Publishing rule</strong>
            <p>Lower priority shows first. Keep one clear message per slide so the homepage stays readable.</p>
          </div>

          <div class="settings-slider-meta">
            <span data-slider-live-count>Live: {{ $liveSlideCount }}</span>
            <span data-slider-draft-count>Draft: {{ $draftSlideCount }}</span>
            <span data-slider-product-count>Products: {{ $linkedProductCount }}</span>
          </div>
        </div>

        <div class="table-container mt-md">
          <table class="table">
            <thead>
              <tr>
                <th>Slide</th>
                <th>Product</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Updated</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody data-slider-table-body>
              @foreach ($sliderItems as $slide)
                @php
                  $statusClass = $slide['status'] === 'Live' ? 'badge-success' : 'badge-info';
                @endphp
                <tr
                  data-slider-row="{{ $slide['id'] }}"
                  data-slide-id="{{ $slide['id'] }}"
                  data-slide-title="{{ $slide['title'] }}"
                  data-slide-product-id="{{ $slide['product_id'] }}"
                  data-slide-product-name="{{ $slide['product_name'] }}"
                  data-slide-priority="{{ $slide['priority'] }}"
                  data-slide-status="{{ $slide['status'] }}"
                  data-slide-updated="{{ $slide['updated'] }}"
                >
                  <td class="settings-cell-strong" data-slider-cell="title">{{ $slide['title'] }}</td>
                  <td data-slider-cell="product">{{ $slide['product_name'] }}</td>
                  <td data-slider-cell="priority">#{{ $slide['priority'] }}</td>
                  <td data-slider-cell="status"><span class="badge {{ $statusClass }}">{{ $slide['status'] }}</span></td>
                  <td data-slider-cell="updated">{{ $slide['updated'] }}</td>
                  <td>
                    <div class="settings-offer-actions">
                      <button type="button" class="btn btn-primary btn-sm" data-slider-edit-btn>Edit</button>
                      <button type="button" class="btn btn-danger btn-sm" data-slider-remove-btn>Remove</button>
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
            <h3 class="card-title" data-slider-editor-title>Create slide</h3>
            <p class="settings-panel-subtitle">Use this side panel to add a new slide or load an existing row for editing.</p>
          </div>
          <span class="badge badge-success">Editor</span>
        </div>

        <input type="hidden" data-slider-mode value="create">
        <input type="hidden" data-slider-edit-id value="">

        <div class="settings-field-grid">
          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="websiteSliderTitle">Slide Title</label>
            <input id="websiteSliderTitle" type="text" class="form-input" data-slider-title-input placeholder="Summer launch hero">
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="websiteSliderProduct">Linked Product</label>
            <select id="websiteSliderProduct" class="form-select settings-coupon-dropdown" data-slider-product-input>
              <option value="">Select Product</option>
              @foreach ($sliderProducts as $product)
                <option value="{{ $product['id'] }}">{{ $product['name'] }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="websiteSliderPriority">Priority</label>
            <input id="websiteSliderPriority" type="number" min="1" class="form-input" data-slider-priority-input value="1">
          </div>

          <div class="form-group">
            <label class="form-label" for="websiteSliderStatus">Status</label>
            <select id="websiteSliderStatus" class="form-select settings-coupon-dropdown" data-slider-status-input>
              <option value="Live">Live</option>
              <option value="Draft" selected>Draft</option>
            </select>
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="websiteSliderNotes">Admin Notes</label>
            <textarea id="websiteSliderNotes" class="form-textarea" rows="4" data-slider-notes-input placeholder="What campaign is this slide for, and when should it go live?"></textarea>
          </div>
        </div>

        <div class="settings-inline-actions">
          <button type="button" class="btn btn-secondary btn-sm" data-slider-reset-btn>New Slide</button>
          <button type="button" class="btn btn-success btn-sm" data-slider-save-btn>Create Slide</button>
          <button type="button" class="btn btn-primary btn-sm" data-slider-preview-btn>Preview Order</button>
        </div>
      </article>

      <article class="card settings-panel mt-md">
        <div class="card-header">
          <div>
            <h3 class="card-title">Operator checklist</h3>
            <p class="settings-panel-subtitle">A short workflow so the slider stays easy to manage.</p>
          </div>
          <span class="badge badge-info">Guide</span>
        </div>

        <div style="display: grid; gap: 12px; color: var(--text-secondary); font-size: 13px;">
          <p style="margin: 0;">1. Add a short title that sales or support can recognize quickly.</p>
          <p style="margin: 0;">2. Link the slide to a real product so the team can trace the campaign owner.</p>
          <p style="margin: 0;">3. Keep only a few live slides at the top. Draft the rest for later campaigns.</p>
        </div>
      </article>
    </aside>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const workspace = document.querySelector('[data-slider-workspace]');
      if (!(workspace instanceof HTMLElement)) {
        return;
      }

      const tableBody = workspace.querySelector('[data-slider-table-body]');
      const modeInput = workspace.querySelector('[data-slider-mode]');
      const editIdInput = workspace.querySelector('[data-slider-edit-id]');
      const editorTitle = workspace.querySelector('[data-slider-editor-title]');
      const titleInput = workspace.querySelector('[data-slider-title-input]');
      const productInput = workspace.querySelector('[data-slider-product-input]');
      const priorityInput = workspace.querySelector('[data-slider-priority-input]');
      const statusInput = workspace.querySelector('[data-slider-status-input]');
      const notesInput = workspace.querySelector('[data-slider-notes-input]');
      const saveButton = workspace.querySelector('[data-slider-save-btn]');
      const resetButton = workspace.querySelector('[data-slider-reset-btn]');
      const previewButton = workspace.querySelector('[data-slider-preview-btn]');
      const countBadge = workspace.querySelector('[data-slider-count-badge]');
      const liveCount = workspace.querySelector('[data-slider-live-count]');
      const draftCount = workspace.querySelector('[data-slider-draft-count]');
      const productCount = workspace.querySelector('[data-slider-product-count]');

      if (!(tableBody instanceof HTMLElement)) {
        return;
      }

      function statusBadgeClass(status) {
        return status === 'Live' ? 'badge-success' : 'badge-info';
      }

      function createCell(text, className = '', dataKey = '') {
        const cell = document.createElement('td');
        if (className) {
          cell.className = className;
        }
        if (dataKey) {
          cell.dataset.sliderCell = dataKey;
        }
        cell.textContent = text;
        return cell;
      }

      function buildActionButton(label, className, dataKey) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = className;
        button.dataset[dataKey] = '';
        button.textContent = label;
        return button;
      }

      function buildRow(item) {
        const row = document.createElement('tr');
        row.dataset.sliderRow = item.id;
        row.dataset.slideId = item.id;
        row.dataset.slideTitle = item.title;
        row.dataset.slideProductId = item.product_id;
        row.dataset.slideProductName = item.product_name;
        row.dataset.slidePriority = String(item.priority);
        row.dataset.slideStatus = item.status;
        row.dataset.slideUpdated = item.updated;

        row.appendChild(createCell(item.title, 'settings-cell-strong', 'title'));
        row.appendChild(createCell(item.product_name, '', 'product'));
        row.appendChild(createCell(`#${item.priority}`, '', 'priority'));

        const statusCell = document.createElement('td');
        statusCell.dataset.sliderCell = 'status';
        const statusBadge = document.createElement('span');
        statusBadge.className = `badge ${statusBadgeClass(item.status)}`;
        statusBadge.textContent = item.status;
        statusCell.appendChild(statusBadge);
        row.appendChild(statusCell);

        row.appendChild(createCell(item.updated, '', 'updated'));

        const actionCell = document.createElement('td');
        const actions = document.createElement('div');
        actions.className = 'settings-offer-actions';
        actions.appendChild(buildActionButton('Edit', 'btn btn-primary btn-sm', 'sliderEditBtn'));
        actions.appendChild(buildActionButton('Remove', 'btn btn-danger btn-sm', 'sliderRemoveBtn'));
        actionCell.appendChild(actions);
        row.appendChild(actionCell);

        return row;
      }

      function sliderRows() {
        return Array.from(tableBody.querySelectorAll('[data-slider-row]'));
      }

      function rowById(id) {
        return sliderRows().find((row) => row.getAttribute('data-slider-row') === id) || null;
      }

      function resetEditor() {
        if (modeInput instanceof HTMLInputElement) {
          modeInput.value = 'create';
        }

        if (editIdInput instanceof HTMLInputElement) {
          editIdInput.value = '';
        }

        if (editorTitle instanceof HTMLElement) {
          editorTitle.textContent = 'Create slide';
        }

        if (saveButton instanceof HTMLButtonElement) {
          saveButton.textContent = 'Create Slide';
        }

        if (titleInput instanceof HTMLInputElement) {
          titleInput.value = '';
        }

        if (productInput instanceof HTMLSelectElement) {
          productInput.value = '';
        }

        if (priorityInput instanceof HTMLInputElement) {
          priorityInput.value = '1';
        }

        if (statusInput instanceof HTMLSelectElement) {
          statusInput.value = 'Draft';
        }

        if (notesInput instanceof HTMLTextAreaElement) {
          notesInput.value = '';
        }
      }

      function updateSummary() {
        const rows = sliderRows();
        const live = rows.filter((row) => row.dataset.slideStatus === 'Live').length;
        const draft = rows.filter((row) => row.dataset.slideStatus === 'Draft').length;
        const products = new Set(rows.map((row) => row.dataset.slideProductId || '').filter(Boolean)).size;

        if (countBadge instanceof HTMLElement) {
          countBadge.textContent = `${rows.length} slides`;
        }

        if (liveCount instanceof HTMLElement) {
          liveCount.textContent = `Live: ${live}`;
        }

        if (draftCount instanceof HTMLElement) {
          draftCount.textContent = `Draft: ${draft}`;
        }

        if (productCount instanceof HTMLElement) {
          productCount.textContent = `Products: ${products}`;
        }
      }

      function sortRows() {
        sliderRows()
          .sort((left, right) => Number(left.dataset.slidePriority || '0') - Number(right.dataset.slidePriority || '0'))
          .forEach((row) => tableBody.appendChild(row));
      }

      function loadRowIntoEditor(row) {
        if (!(row instanceof HTMLElement)) {
          return;
        }

        if (modeInput instanceof HTMLInputElement) {
          modeInput.value = 'edit';
        }

        if (editIdInput instanceof HTMLInputElement) {
          editIdInput.value = row.dataset.slideId || '';
        }

        if (editorTitle instanceof HTMLElement) {
          editorTitle.textContent = 'Edit slide';
        }

        if (saveButton instanceof HTMLButtonElement) {
          saveButton.textContent = 'Update Slide';
        }

        if (titleInput instanceof HTMLInputElement) {
          titleInput.value = row.dataset.slideTitle || '';
        }

        if (productInput instanceof HTMLSelectElement) {
          productInput.value = row.dataset.slideProductId || '';
        }

        if (priorityInput instanceof HTMLInputElement) {
          priorityInput.value = row.dataset.slidePriority || '1';
        }

        if (statusInput instanceof HTMLSelectElement) {
          statusInput.value = row.dataset.slideStatus || 'Draft';
        }
      }

      tableBody.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Element)) {
          return;
        }

        const editButton = target.closest('[data-slider-edit-btn]');
        if (editButton instanceof HTMLButtonElement) {
          loadRowIntoEditor(editButton.closest('[data-slider-row]'));
          return;
        }

        const removeButton = target.closest('[data-slider-remove-btn]');
        if (!(removeButton instanceof HTMLButtonElement)) {
          return;
        }

        const row = removeButton.closest('[data-slider-row]');
        if (!(row instanceof HTMLElement)) {
          return;
        }

        const slideTitle = row.dataset.slideTitle || 'this slide';
        const shouldRemove = window.confirm(`Remove ${slideTitle}?`);
        if (!shouldRemove) {
          return;
        }

        row.remove();
        updateSummary();

        if (typeof window.showWarning === 'function') {
          window.showWarning(`${slideTitle} removed from the homepage queue.`);
        }

        if (editIdInput instanceof HTMLInputElement && editIdInput.value === row.dataset.slideId) {
          resetEditor();
        }
      });

      resetButton?.addEventListener('click', resetEditor);

      previewButton?.addEventListener('click', () => {
        if (typeof window.showInfo === 'function') {
          window.showInfo('Slider order preview is ready.');
        }
      });

      saveButton?.addEventListener('click', () => {
        const title = titleInput instanceof HTMLInputElement ? titleInput.value.trim() : '';
        const productId = productInput instanceof HTMLSelectElement ? productInput.value : '';
        const productName = productInput instanceof HTMLSelectElement ? (productInput.selectedOptions[0]?.textContent || '') : '';
        const priority = priorityInput instanceof HTMLInputElement ? Math.max(1, Number(priorityInput.value || '1')) : 1;
        const status = statusInput instanceof HTMLSelectElement ? statusInput.value : 'Draft';
        const mode = modeInput instanceof HTMLInputElement ? modeInput.value : 'create';
        const editId = editIdInput instanceof HTMLInputElement ? editIdInput.value : '';

        if (!title) {
          if (typeof window.showError === 'function') {
            window.showError('Slide title is required.');
          }
          return;
        }

        if (!productId) {
          if (typeof window.showError === 'function') {
            window.showError('Select a product for this slide.');
          }
          return;
        }

        const item = {
          id: mode === 'edit' && editId ? editId : `slide_${Date.now()}`,
          title,
          product_id: productId,
          product_name: productName,
          priority,
          status,
          updated: 'Just now',
        };

        const nextRow = buildRow(item);
        const existingRow = mode === 'edit' && editId ? rowById(editId) : null;

        if (existingRow instanceof HTMLElement) {
          existingRow.replaceWith(nextRow);
        } else {
          tableBody.appendChild(nextRow);
        }

        sortRows();
        updateSummary();
        resetEditor();

        if (typeof window.showSuccess === 'function') {
          window.showSuccess(mode === 'edit' ? `${title} updated.` : `${title} added to the slider queue.`);
        }
      });

      updateSummary();
    });
  </script>
@endsection
