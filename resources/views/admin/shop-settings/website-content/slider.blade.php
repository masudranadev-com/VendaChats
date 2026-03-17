@extends('admin.shop-settings.website-content.layout')

@section('website-content-body')
  <div
    class="settings-layout settings-layout-single mt-md"
    data-slider-manager
    data-api-base-url="{{ $slidersApiBaseUrl }}"
    data-refresh-token="{{ $slidersRefreshToken }}"
  >
    <section class="settings-main-column">
      <article class="card settings-panel">
        <div class="card-header">
          <div>
            <h3 class="card-title">Homepage sliders</h3>
            <p class="settings-panel-subtitle">Keep the homepage slider queue short, product-linked, and easy to update from one modal.</p>
          </div>

          <div class="settings-inline-actions mt-0">
            <span class="badge badge-primary" data-slider-count-badge>Loading...</span>
            <button type="button" class="btn btn-success btn-sm" data-slider-open-create>Add Slider</button>
          </div>
        </div>

        <div class="settings-slider-spotlight">
          <div>
            <strong>Queue rule</strong>
            <p>Lower priority number shows first. Keep each slider focused on one clear product message.</p>
          </div>

          <div class="settings-slider-meta">
            <span data-slider-live-count>Live: --</span>
            <span data-slider-draft-count>Draft: --</span>
            <span data-slider-product-count>Products: --</span>
          </div>
        </div>

        <div class="table-container mt-md">
          <table class="table">
            <thead>
              <tr>
                <th>Slider</th>
                <th>Linked Product</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Updated</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody data-slider-table-body>
              <tr>
                <td colspan="6">Loading sliders...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>
    </section>
  </div>

  <div class="modal-overlay" id="websiteSliderModal" aria-hidden="true">
    <div class="modal settings-coupon-modal" role="dialog" aria-modal="true" aria-labelledby="websiteSliderModalTitle">
      <div class="modal-header">
        <h3 class="modal-title" id="websiteSliderModalTitle" data-slider-modal-title>Create Slider</h3>
        <button type="button" class="modal-close" data-modal-close aria-label="Close slider modal">x</button>
      </div>

      <div class="modal-body">
        <input type="hidden" data-slider-modal-mode value="create">
        <input type="hidden" data-slider-modal-id value="">

        <div class="settings-field-grid settings-modal-grid">
          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="websiteSliderTitle">Slider Title</label>
            <input
              id="websiteSliderTitle"
              type="text"
              class="form-input"
              maxlength="120"
              data-slider-title-input
              placeholder="Summer launch hero"
            >
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="websiteSliderProduct">Linked Product</label>
            <select id="websiteSliderProduct" class="form-select settings-coupon-dropdown" data-slider-product-input>
              <option value="">Select product</option>
            </select>
            <small class="form-help">Choose the product that should receive traffic from this homepage slider.</small>
          </div>

          <div class="form-group">
            <label class="form-label" for="websiteSliderPriority">Priority</label>
            <input id="websiteSliderPriority" type="number" min="1" max="500" class="form-input" data-slider-priority-input>
          </div>

          <div class="form-group">
            <label class="form-label" for="websiteSliderStatus">Status</label>
            <select id="websiteSliderStatus" class="form-select settings-coupon-dropdown" data-slider-status-input>
              <option value="Live">Live</option>
              <option value="Draft">Draft</option>
            </select>
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label class="form-label" for="websiteSliderNotes">Admin Notes</label>
            <textarea
              id="websiteSliderNotes"
              class="form-textarea"
              rows="4"
              maxlength="1000"
              data-slider-notes-input
              placeholder="Internal notes for campaign timing, headline idea, or publishing context."
            ></textarea>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-slider-restore-btn>Restore</button>
        <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
        <button type="button" class="btn btn-success" data-slider-save-btn>Create Slider</button>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="websiteSliderDeleteModal" aria-hidden="true">
    <div class="modal settings-coupon-modal" role="dialog" aria-modal="true" aria-labelledby="websiteSliderDeleteModalTitle">
      <div class="modal-header">
        <h3 class="modal-title" id="websiteSliderDeleteModalTitle">Delete Slider</h3>
        <button type="button" class="modal-close" data-modal-close aria-label="Close delete slider modal">x</button>
      </div>

      <div class="modal-body">
        <div class="settings-content-card">
          <div class="settings-content-head">
            <strong>Permanent Delete</strong>
            <span class="badge badge-danger">Cannot Undo</span>
          </div>
          <p class="mb-0">
            <strong data-slider-delete-title>Slider</strong> will be removed from the homepage queue permanently.
          </p>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
        <button type="button" class="btn btn-danger" data-slider-delete-confirm-btn>Delete Slider</button>
      </div>
    </div>
  </div>

  <script type="application/json" id="websiteSliderDefaultsJson">@json($sliderDefaults)</script>
  <script type="application/json" id="websiteSliderItemsJson">@json($sliderItems)</script>
  <script type="application/json" id="websiteSliderProductsJson">@json($sliderProducts)</script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const manager = document.querySelector('[data-slider-manager]');
      if (!(manager instanceof HTMLElement)) {
        return;
      }

      const apiBaseUrl = String(manager.dataset.apiBaseUrl || '').trim();
      const refreshToken = String(manager.dataset.refreshToken || window.API?.getToken?.() || '').trim();

      const defaultsScript = document.getElementById('websiteSliderDefaultsJson');
      const itemsScript = document.getElementById('websiteSliderItemsJson');
      const productsScript = document.getElementById('websiteSliderProductsJson');

      let sliderDefaults = defaultsScript ? JSON.parse(defaultsScript.textContent || '{}') : {};
      let sliders = itemsScript ? JSON.parse(itemsScript.textContent || '[]') : [];
      let products = productsScript ? JSON.parse(productsScript.textContent || '[]') : [];

      const stats = Array.from(document.querySelectorAll('[data-content-stat]'));
      const countBadge = document.querySelector('[data-slider-count-badge]');
      const liveCount = document.querySelector('[data-slider-live-count]');
      const draftCount = document.querySelector('[data-slider-draft-count]');
      const productCount = document.querySelector('[data-slider-product-count]');
      const tableBody = document.querySelector('[data-slider-table-body]');
      const createButtons = Array.from(document.querySelectorAll('[data-slider-open-create]'));

      const modal = document.getElementById('websiteSliderModal');
      const deleteModal = document.getElementById('websiteSliderDeleteModal');
      const modalTitle = document.querySelector('[data-slider-modal-title]');
      const modalModeInput = document.querySelector('[data-slider-modal-mode]');
      const modalIdInput = document.querySelector('[data-slider-modal-id]');
      const titleInput = document.querySelector('[data-slider-title-input]');
      const productInput = document.querySelector('[data-slider-product-input]');
      const priorityInput = document.querySelector('[data-slider-priority-input]');
      const statusInput = document.querySelector('[data-slider-status-input]');
      const notesInput = document.querySelector('[data-slider-notes-input]');
      const restoreButton = document.querySelector('[data-slider-restore-btn]');
      const saveButton = document.querySelector('[data-slider-save-btn]');
      const deleteTitleNode = document.querySelector('[data-slider-delete-title]');
      const deleteConfirmButton = document.querySelector('[data-slider-delete-confirm-btn]');

      if (
        !(countBadge instanceof HTMLElement) ||
        !(liveCount instanceof HTMLElement) ||
        !(draftCount instanceof HTMLElement) ||
        !(productCount instanceof HTMLElement) ||
        !(tableBody instanceof HTMLElement) ||
        !(modal instanceof HTMLElement) ||
        !(deleteModal instanceof HTMLElement) ||
        !(modalTitle instanceof HTMLElement) ||
        !(modalModeInput instanceof HTMLInputElement) ||
        !(modalIdInput instanceof HTMLInputElement) ||
        !(titleInput instanceof HTMLInputElement) ||
        !(productInput instanceof HTMLSelectElement) ||
        !(priorityInput instanceof HTMLInputElement) ||
        !(statusInput instanceof HTMLSelectElement) ||
        !(notesInput instanceof HTMLTextAreaElement) ||
        !(restoreButton instanceof HTMLButtonElement) ||
        !(saveButton instanceof HTMLButtonElement) ||
        !(deleteTitleNode instanceof HTMLElement) ||
        !(deleteConfirmButton instanceof HTMLButtonElement)
      ) {
        return;
      }

      const sliderMap = new Map();
      let loadedSliderState = null;
      let pendingDeleteSlider = null;

      const showError = (message) => {
        if (typeof window.showError === 'function') {
          window.showError(message);
          return;
        }

        window.alert(message);
      };

      const showSuccess = (message) => {
        if (typeof window.showSuccess === 'function') {
          window.showSuccess(message);
          return;
        }

        window.alert(message);
      };

      function syncSliderMap() {
        sliderMap.clear();
        sliders.forEach((slider) => {
          if (slider && typeof slider === 'object' && slider.id) {
            sliderMap.set(String(slider.id), slider);
          }
        });
      }

      function normalizeInt(value, fallback = 0) {
        const parsed = Number.parseInt(String(value ?? '').trim(), 10);
        return Number.isFinite(parsed) ? parsed : fallback;
      }

      function normalizeSliderState(raw = {}) {
        const source = raw && typeof raw === 'object' ? raw : {};
        const defaults = sliderDefaults && typeof sliderDefaults === 'object' ? sliderDefaults : {};
        const normalizedStatus = ['Live', 'Draft'].includes(String(source.status || ''))
          ? String(source.status)
          : (['Live', 'Draft'].includes(String(defaults.status || '')) ? String(defaults.status) : 'Draft');

        return {
          id: source.id ? String(source.id) : '',
          title: String(source.title || defaults.title || '').trim(),
          product_id: Math.max(0, normalizeInt(source.product_id, normalizeInt(defaults.product_id, 0))),
          notes: String(source.notes || defaults.notes || '').trim(),
          priority: Math.max(1, normalizeInt(source.priority, normalizeInt(defaults.priority, 1))),
          status: normalizedStatus,
        };
      }

      function renderStats(summary = {}) {
        const values = {
          live_slides: Math.max(0, normalizeInt(summary.live_slides, 0)),
          draft_slides: Math.max(0, normalizeInt(summary.draft_slides, 0)),
          linked_products: Math.max(0, normalizeInt(summary.linked_products, 0)),
          queue_size: Math.max(0, normalizeInt(summary.queue_size, 0)),
        };

        stats.forEach((card) => {
          const statKey = String(card.getAttribute('data-content-stat') || '').trim();
          const valueNode = card.querySelector('[data-content-stat-value]');
          if (!statKey || !(valueNode instanceof HTMLElement) || !(statKey in values)) {
            return;
          }

          valueNode.textContent = String(values[statKey]);
        });

        liveCount.textContent = `Live: ${values.live_slides}`;
        draftCount.textContent = `Draft: ${values.draft_slides}`;
        productCount.textContent = `Products: ${values.linked_products}`;
      }

      function renderCountBadge() {
        const total = Array.isArray(sliders) ? sliders.length : 0;
        countBadge.textContent = `${total} ${total === 1 ? 'slider' : 'sliders'}`;
      }

      function renderProducts(selectedProductID = 0) {
        const selectedID = Math.max(0, normalizeInt(selectedProductID, 0));
        productInput.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = Array.isArray(products) && products.length > 0
          ? 'Select product'
          : 'No products available';
        productInput.appendChild(placeholder);

        products.forEach((product) => {
          const option = document.createElement('option');
          option.value = String(product.id ?? '');
          option.textContent = String(product.label || '');
          option.selected = selectedID > 0 && normalizeInt(product.id, 0) === selectedID;
          productInput.appendChild(option);
        });
      }

      function renderTableMessage(message) {
        tableBody.innerHTML = '';
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 6;
        cell.textContent = message;
        row.appendChild(cell);
        tableBody.appendChild(row);
      }

      function appendCell(row, text, className = '') {
        const cell = document.createElement('td');
        if (className) {
          cell.className = className;
        }

        cell.textContent = String(text ?? '');
        row.appendChild(cell);
        return cell;
      }

      function statusBadgeClass(status) {
        return status === 'Live' ? 'badge-success' : 'badge-info';
      }

      function buildSliderRow(slider) {
        const row = document.createElement('tr');
        row.dataset.sliderRow = String(slider.id || '');
        row.dataset.slideId = String(slider.id || '');
        row.dataset.slideTitle = String(slider.title || '');
        row.dataset.slideProductId = String(slider.product_id || '');
        row.dataset.slideNotes = String(slider.notes || '');
        row.dataset.slidePriority = String(slider.priority || '');
        row.dataset.slideStatus = String(slider.status || '');

        appendCell(row, slider.title || '', 'settings-cell-strong');
        appendCell(row, slider.product_name || '');
        appendCell(row, `#${slider.priority || 0}`);

        const statusCell = document.createElement('td');
        const badge = document.createElement('span');
        badge.className = `badge ${statusBadgeClass(slider.status)}`;
        badge.textContent = String(slider.status || '');
        statusCell.appendChild(badge);
        row.appendChild(statusCell);

        appendCell(row, slider.updated || '');

        const actionsCell = document.createElement('td');
        const actions = document.createElement('div');
        actions.className = 'settings-offer-actions';

        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.className = 'btn btn-primary btn-sm';
        editButton.dataset.sliderEditBtn = '1';
        editButton.dataset.sliderId = String(slider.id || '');
        editButton.textContent = 'Edit';

        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.className = 'btn btn-danger btn-sm';
        deleteButton.dataset.sliderDeleteBtn = '1';
        deleteButton.dataset.sliderId = String(slider.id || '');
        deleteButton.dataset.sliderTitle = String(slider.title || '');
        deleteButton.textContent = 'Delete';

        actions.append(editButton, deleteButton);
        actionsCell.appendChild(actions);
        row.appendChild(actionsCell);

        return row;
      }

      function renderTable() {
        if (!Array.isArray(sliders) || sliders.length === 0) {
          renderTableMessage('No homepage sliders found yet.');
          return;
        }

        tableBody.innerHTML = '';
        sliders.forEach((slider) => {
          tableBody.appendChild(buildSliderRow(slider));
        });
      }

      function showEditorModal() {
        if (typeof window.openModal === 'function') {
          window.openModal('websiteSliderModal');
        } else {
          modal.classList.add('active');
          document.body.style.overflow = 'hidden';
        }

        window.setTimeout(() => {
          titleInput.focus();
          titleInput.select();
        }, 0);
      }

      function hideEditorModal() {
        if (typeof window.closeAllModals === 'function') {
          window.closeAllModals();
        } else {
          modal.classList.remove('active');
          document.body.style.overflow = '';
        }
      }

      function showDeleteModal() {
        if (typeof window.openModal === 'function') {
          window.openModal('websiteSliderDeleteModal');
          return;
        }

        deleteModal.classList.add('active');
        document.body.style.overflow = 'hidden';
      }

      function hideDeleteModal() {
        deleteModal.classList.remove('active');
        if (document.querySelector('.modal-overlay.active')) {
          document.body.style.overflow = 'hidden';
          return;
        }

        document.body.style.overflow = '';
      }

      function setModalMode(mode) {
        const editMode = mode === 'edit';
        modalModeInput.value = editMode ? 'edit' : 'create';
        modalTitle.textContent = editMode ? 'Edit Slider' : 'Create Slider';
        saveButton.textContent = editMode ? 'Update Slider' : 'Create Slider';
        saveButton.classList.toggle('btn-primary', editMode);
        saveButton.classList.toggle('btn-success', !editMode);
      }

      function fillForm(state) {
        const slider = normalizeSliderState(state);
        modalIdInput.value = slider.id;
        titleInput.value = slider.title;
        renderProducts(slider.product_id);
        productInput.value = slider.product_id > 0 ? String(slider.product_id) : '';
        priorityInput.value = String(slider.priority);
        statusInput.value = slider.status;
        notesInput.value = slider.notes;
        saveButton.disabled = false;
        restoreButton.disabled = false;
      }

      function openCreateModal() {
        loadedSliderState = normalizeSliderState(sliderDefaults);
        setModalMode('create');
        fillForm(loadedSliderState);
        showEditorModal();
      }

      function openEditModal(button) {
        if (!(button instanceof HTMLButtonElement)) {
          return;
        }

        const sliderID = String(button.dataset.sliderId || '').trim();
        const slider = sliderMap.get(sliderID);
        if (!slider) {
          showError('Slider details are unavailable for editing.');
          return;
        }

        loadedSliderState = normalizeSliderState(slider);
        setModalMode('edit');
        fillForm(loadedSliderState);
        showEditorModal();
      }

      function setPendingDeleteSlider(slider) {
        const source = slider && typeof slider === 'object' ? slider : {};
        pendingDeleteSlider = {
          id: String(source.id || '').trim(),
          title: String(source.title || 'this slider').trim() || 'this slider',
        };

        deleteTitleNode.textContent = pendingDeleteSlider.title;
        deleteConfirmButton.disabled = false;
        deleteConfirmButton.textContent = 'Delete Slider';
      }

      function openDeleteSliderModal(button) {
        if (!(button instanceof HTMLButtonElement)) {
          return;
        }

        const sliderID = String(button.dataset.sliderId || '').trim();
        const fallbackTitle = String(button.dataset.sliderTitle || 'this slider').trim() || 'this slider';
        if (!sliderID) {
          showError('Slider details are unavailable for delete action.');
          return;
        }

        setPendingDeleteSlider(sliderMap.get(sliderID) || { id: sliderID, title: fallbackTitle });
        showDeleteModal();
      }

      function restoreLoadedState() {
        fillForm(loadedSliderState || sliderDefaults);

        if (typeof window.showInfo === 'function') {
          window.showInfo('Slider form restored.');
        }
      }

      function currentFormState() {
        return normalizeSliderState({
          id: modalIdInput.value,
          title: titleInput.value,
          product_id: productInput.value,
          notes: notesInput.value,
          priority: priorityInput.value,
          status: statusInput.value,
        });
      }

      function toApiPayload(state) {
        return {
          title: state.title,
          product_id: Math.max(0, normalizeInt(state.product_id, 0)),
          notes: state.notes,
          priority: Math.max(1, normalizeInt(state.priority, 1)),
          status: state.status,
        };
      }

      function applyPayload(payload = {}, options = {}) {
        if (payload && typeof payload.defaults === 'object') {
          sliderDefaults = payload.defaults;
        }

        sliders = Array.isArray(payload?.sliders) ? payload.sliders : [];
        products = Array.isArray(payload?.products) ? payload.products : [];

        syncSliderMap();
        renderStats(payload?.stats && typeof payload.stats === 'object' ? payload.stats : {});
        renderCountBadge();
        renderTable();

        if (options.resetForm !== false || !loadedSliderState) {
          loadedSliderState = normalizeSliderState(sliderDefaults);
          setModalMode('create');
        } else if (loadedSliderState.id) {
          const refreshedSlider = sliderMap.get(String(loadedSliderState.id));
          loadedSliderState = refreshedSlider
            ? normalizeSliderState(refreshedSlider)
            : normalizeSliderState(sliderDefaults);
        }

        fillForm(loadedSliderState || sliderDefaults);
      }

      async function fetchSliderData(options = {}) {
        if (!apiBaseUrl || !refreshToken || !window.API?.Admin?.WebsiteSliders?.get) {
          renderTableMessage('Slider API is not configured.');
          countBadge.textContent = 'Unavailable';
          if (options.showErrors !== false) {
            showError('Slider API is not configured.');
          }
          return false;
        }

        if (options.showLoading !== false) {
          renderTableMessage('Loading sliders...');
          countBadge.textContent = 'Loading...';
        }

        try {
          const response = await window.API.Admin.WebsiteSliders.get({
            apiBaseUrl,
            refreshToken,
          });

          applyPayload(response, options);
          return true;
        } catch (error) {
          if (!Array.isArray(sliders) || sliders.length === 0) {
            renderTableMessage('Unable to load sliders right now.');
          }

          if (options.showErrors !== false) {
            showError(error?.message || 'Unable to load sliders right now.');
          }

          return false;
        }
      }

      createButtons.forEach((button) => {
        button.addEventListener('click', openCreateModal);
      });

      tableBody.addEventListener('click', (event) => {
        const target = event.target instanceof HTMLElement ? event.target : null;
        if (!(target instanceof HTMLElement)) {
          return;
        }

        const editButton = target.closest('[data-slider-edit-btn]');
        if (editButton instanceof HTMLButtonElement) {
          openEditModal(editButton);
          return;
        }

        const deleteButton = target.closest('[data-slider-delete-btn]');
        if (deleteButton instanceof HTMLButtonElement) {
          openDeleteSliderModal(deleteButton);
        }
      });

      restoreButton.addEventListener('click', restoreLoadedState);

      saveButton.addEventListener('click', async () => {
        const formState = currentFormState();
        fillForm(formState);

        if (!Array.isArray(products) || products.length === 0) {
          showError('Create a product first, then link it to a homepage slider.');
          return;
        }

        if (!apiBaseUrl || !refreshToken || !window.API?.Admin?.WebsiteSliders?.save) {
          showError('Slider API is not configured.');
          return;
        }

        const action = modalModeInput.value === 'edit' ? 'edit' : 'create';
        const sliderID = action === 'edit' ? modalIdInput.value : '';

        saveButton.disabled = true;
        restoreButton.disabled = true;
        saveButton.textContent = action === 'edit' ? 'Updating...' : 'Creating...';

        try {
          const response = await window.API.Admin.WebsiteSliders.save({
            apiBaseUrl,
            refreshToken,
            payload: toApiPayload(formState),
            sliderId: sliderID !== '' ? sliderID : null,
          });

          showSuccess(response?.message || `Slider ${formState.title || 'item'} saved successfully.`);
          hideEditorModal();
          await fetchSliderData({ resetForm: true, showErrors: false, showLoading: false });
        } catch (error) {
          showError(error?.message || 'Unable to save the slider.');
          saveButton.disabled = false;
          restoreButton.disabled = false;
          setModalMode(action);
        }
      });

      deleteConfirmButton.addEventListener('click', async () => {
        const sliderID = String(pendingDeleteSlider?.id || '').trim();
        const sliderTitle = String(pendingDeleteSlider?.title || 'this slider').trim() || 'this slider';

        if (!sliderID) {
          showError('Slider details are unavailable for delete action.');
          return;
        }

        if (!apiBaseUrl || !refreshToken || !window.API?.Admin?.WebsiteSliders?.remove) {
          showError('Slider API is not configured.');
          return;
        }

        deleteConfirmButton.disabled = true;
        deleteConfirmButton.textContent = 'Deleting...';

        try {
          const response = await window.API.Admin.WebsiteSliders.remove({
            apiBaseUrl,
            refreshToken,
            sliderId: sliderID,
          });

          pendingDeleteSlider = null;
          hideDeleteModal();
          showSuccess(response?.message || `${sliderTitle} deleted successfully.`);
          await fetchSliderData({ resetForm: true, showErrors: false, showLoading: false });
        } catch (error) {
          showError(error?.message || 'Unable to delete the slider.');
          deleteConfirmButton.disabled = false;
          deleteConfirmButton.textContent = 'Delete Slider';
        }
      });

      syncSliderMap();
      loadedSliderState = normalizeSliderState(sliderDefaults);
      setModalMode('create');
      renderStats({});
      renderCountBadge();
      renderTableMessage('Loading sliders...');
      fillForm(loadedSliderState);
      fetchSliderData({ resetForm: true, showErrors: false });
    });
  </script>
@endsection
