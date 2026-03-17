@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header settings-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>
  </div>

  <section class="settings-stats-grid">
    @foreach ($quickStats as $stat)
      <article class="settings-stat-card is-{{ $stat['tone'] }}" data-offers-stat="{{ $stat['key'] ?? '' }}">
        <span>{{ $stat['label'] }}</span>
        <strong data-offers-stat-value>{{ $stat['value'] }}</strong>
        <small>{{ $stat['note'] }}</small>
      </article>
    @endforeach
  </section>

  <div
    class="settings-layout settings-layout-single mt-md"
    data-coupon-manager
    data-api-base-url="{{ $offersApiBaseUrl }}"
    data-refresh-token="{{ $offersRefreshToken }}"
  >
    <section class="settings-main-column">
      <article class="card settings-panel">
        <div class="card-header">
          <div>
            <h3 class="card-title">Coupon Code List</h3>
            <p class="settings-panel-subtitle">Manage all coupons from one simple table.</p>
          </div>
          <div class="settings-inline-actions mt-0">
            <span class="badge badge-info" data-coupon-count-badge>Loading...</span>
            <button type="button" class="btn btn-success btn-sm" data-coupon-open-create>Add Coupon</button>
          </div>
        </div>

        <div class="table-container">
          <table class="table">
            <thead>
              <tr>
                <th>Code</th>
                <th>Type</th>
                <th>Value</th>
                <th>Minimum Order</th>
                <th>Usage</th>
                <th>Status</th>
                <th>Validity</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody data-coupon-table-body>
              <tr>
                <td colspan="8">Loading coupons...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>
    </section>
  </div>

  <div class="modal-overlay" id="offersCouponModal" aria-hidden="true">
    <div class="modal settings-coupon-modal" role="dialog" aria-modal="true" aria-labelledby="offersCouponModalTitle">
      <div class="modal-header">
        <h3 class="modal-title" id="offersCouponModalTitle" data-coupon-modal-title>Create Coupon Code</h3>
        <button type="button" class="modal-close" data-modal-close aria-label="Close coupon modal">x</button>
      </div>
      <div class="modal-body">
        <input type="hidden" data-coupon-modal-mode value="create">
        <input type="hidden" data-coupon-modal-id value="">

        <div class="settings-field-grid settings-modal-grid">
          <div class="settings-content-card" style="grid-column: 1 / -1;">
            <div class="settings-content-head">
              <strong>Coupon Preview</strong>
              <span class="badge badge-info" data-coupon-type-preview>Percentage</span>
            </div>
            <p data-coupon-preview-line>WELCOME10 gives 10% off up to BDT 250.</p>
            <small>Preview updates automatically while typing.</small>
          </div>

          <div class="form-group">
            <label class="form-label" for="couponCodeInput">Coupon Code</label>
            <input
              id="couponCodeInput"
              type="text"
              class="form-input"
              value="{{ $couponDefaults['code'] }}"
              maxlength="24"
              data-coupon-code-input
            >
            <small class="form-help">Use uppercase letters and numbers only.</small>
          </div>

          <div class="form-group">
            <label class="form-label">Discount Type</label>
            <div class="settings-coupon-type-switch">
              <label class="settings-coupon-type-option">
                <input
                  type="radio"
                  name="coupon_discount_type"
                  value="flat"
                  data-coupon-discount-type
                  {{ $couponDefaults['discount_type'] === 'flat' ? 'checked' : '' }}
                >
                Flat
              </label>
              <label class="settings-coupon-type-option">
                <input
                  type="radio"
                  name="coupon_discount_type"
                  value="percentage"
                  data-coupon-discount-type
                  {{ $couponDefaults['discount_type'] === 'percentage' ? 'checked' : '' }}
                >
                Percentage
              </label>
            </div>
          </div>

          <div class="form-group {{ $couponDefaults['discount_type'] === 'flat' ? '' : 'hidden' }}" data-coupon-flat-wrap>
            <label class="form-label" for="couponFlatValue">Flat Discount (BDT)</label>
            <input id="couponFlatValue" type="number" min="1" class="form-input" value="{{ $couponDefaults['flat_value'] }}" data-coupon-flat-value>
            <small class="form-help">Example: 120 means BDT 120 off.</small>
          </div>

          <div class="form-group {{ $couponDefaults['discount_type'] === 'percentage' ? '' : 'hidden' }}" data-coupon-percentage-wrap>
            <label class="form-label" for="couponPercentageValue">Percentage Discount (%)</label>
            <input id="couponPercentageValue" type="number" min="1" max="90" class="form-input" value="{{ $couponDefaults['percentage_value'] }}" data-coupon-percentage-value>
            <small class="form-help">Keep percentage between 1 and 90.</small>
          </div>

          <div class="form-group {{ $couponDefaults['discount_type'] === 'percentage' ? '' : 'hidden' }}" data-coupon-max-wrap>
            <label class="form-label" for="couponMaxDiscount">Max Discount Cap (BDT)</label>
            <input id="couponMaxDiscount" type="number" min="0" class="form-input" value="{{ $couponDefaults['max_discount'] }}" data-coupon-max-discount>
            <small class="form-help">Use a cap for percentage discounts to control high-value orders.</small>
          </div>

          <div class="form-group">
            <label class="form-label" for="couponMinOrder">Minimum Order (BDT)</label>
            <input id="couponMinOrder" type="number" min="0" class="form-input" value="{{ $couponDefaults['minimum_order'] }}" data-coupon-minimum-order>
          </div>

          <div class="form-group">
            <label class="form-label" for="couponUsageLimit">Total Usage Limit</label>
            <input id="couponUsageLimit" type="number" min="1" class="form-input" value="{{ $couponDefaults['usage_limit'] }}" data-coupon-usage-limit>
          </div>

          <div class="form-group">
            <label class="form-label" for="couponPerUserLimit">Per User Limit</label>
            <input id="couponPerUserLimit" type="number" min="1" class="form-input" value="{{ $couponDefaults['per_user_limit'] }}" data-coupon-per-user-limit>
          </div>

          <div class="form-group">
            <label class="form-label" for="couponStartAt">Start Date & Time</label>
            <input id="couponStartAt" type="datetime-local" class="form-input" value="{{ $couponDefaults['start_at'] }}" data-coupon-start-at>
          </div>

          <div class="form-group">
            <label class="form-label" for="couponEndAt">End Date & Time</label>
            <input id="couponEndAt" type="datetime-local" class="form-input" value="{{ $couponDefaults['end_at'] }}" data-coupon-end-at>
          </div>

          <div class="form-group">
            <label class="form-label" for="couponAppliesTo">Applies To</label>
            <select id="couponAppliesTo" class="form-select settings-coupon-dropdown" data-coupon-applies-to>
              <option value="all_products" {{ $couponDefaults['applies_to'] === 'all_products' ? 'selected' : '' }}>Apply to all product</option>
              <option value="specific_products" {{ $couponDefaults['applies_to'] === 'specific_products' ? 'selected' : '' }}>Apply to specific product</option>
            </select>
          </div>

          <div class="form-group {{ $couponDefaults['applies_to'] === 'specific_products' ? '' : 'hidden' }}" style="grid-column: 1 / -1;" data-coupon-specific-wrap>
            <label class="form-label" for="couponSpecificProductList">Select Specific Products</label>
            <select
              id="couponSpecificProductList"
              class="form-multi-select settings-coupon-multi"
              multiple
              data-coreui-search="true"
              data-coupon-specific-list
              {{ $couponDefaults['applies_to'] === 'specific_products' ? '' : 'disabled' }}
            >
              @foreach ($productGroups as $group)
                <optgroup label="{{ $group['label'] }}">
                  <option value="select_all_{{ $group['key'] }}" data-group-select-all="1" data-group-key="{{ $group['key'] }}">Select All</option>
                  @foreach ($group['products'] as $product)
                    <option
                      value="{{ $product['value'] }}"
                      data-group-key="{{ $group['key'] }}"
                      {{ in_array($product['value'], $couponDefaults['specific_product_ids'], true) ? 'selected' : '' }}
                    >
                      {{ $product['label'] }}
                    </option>
                  @endforeach
                </optgroup>
              @endforeach
            </select>
            <small class="form-help">Choose individual products or use Select All inside a category.</small>
          </div>

          <div class="form-group">
            <label class="form-label" for="couponStatus">Status</label>
            <select id="couponStatus" class="form-select settings-coupon-dropdown" data-coupon-status>
              <option {{ $couponDefaults['status'] === 'Active' ? 'selected' : '' }}>Active</option>
              <option {{ $couponDefaults['status'] === 'Scheduled' ? 'selected' : '' }}>Scheduled</option>
              <option {{ $couponDefaults['status'] === 'Draft' ? 'selected' : '' }}>Draft</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coupon-restore>Restore</button>
        <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
        <button type="button" class="btn btn-success" data-coupon-save-btn>Create Coupon</button>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="offersCouponDeleteModal" aria-hidden="true">
    <div class="modal settings-coupon-modal" role="dialog" aria-modal="true" aria-labelledby="offersCouponDeleteModalTitle">
      <div class="modal-header">
        <h3 class="modal-title" id="offersCouponDeleteModalTitle">Delete Coupon</h3>
        <button type="button" class="modal-close" data-modal-close aria-label="Close delete coupon modal">x</button>
      </div>
      <div class="modal-body">
        <div class="settings-content-card">
          <div class="settings-content-head">
            <strong>Permanent Delete</strong>
            <span class="badge badge-danger">Cannot Undo</span>
          </div>
          <p class="mb-0">
            <strong data-coupon-delete-code>COUPON_CODE</strong> will be removed from your database permanently.
            This action cannot be undone.
          </p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
        <button type="button" class="btn btn-danger" data-coupon-delete-confirm>Delete Coupon</button>
      </div>
    </div>
  </div>

  <script type="application/json" id="offersCouponDefaultsJson">@json($couponDefaults)</script>
  <script type="application/json" id="offersCouponListJson">@json($coupons)</script>
  <script type="application/json" id="offersProductGroupsJson">@json($productGroups)</script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const couponManager = document.querySelector('[data-coupon-manager]');
      if (!couponManager) {
        return;
      }

      const apiBaseUrl = String(couponManager.dataset.apiBaseUrl || '').trim();
      const refreshToken = String(couponManager.dataset.refreshToken || window.API?.getToken?.() || '').trim();

      const couponDefaultsScript = document.getElementById('offersCouponDefaultsJson');
      const couponListScript = document.getElementById('offersCouponListJson');
      const productGroupsScript = document.getElementById('offersProductGroupsJson');
      let couponDefaults = couponDefaultsScript ? JSON.parse(couponDefaultsScript.textContent || '{}') : {};
      let coupons = couponListScript ? JSON.parse(couponListScript.textContent || '[]') : [];
      let productGroups = productGroupsScript ? JSON.parse(productGroupsScript.textContent || '[]') : [];

      const stats = Array.from(document.querySelectorAll('[data-offers-stat]'));
      const createButtons = Array.from(document.querySelectorAll('[data-coupon-open-create]'));
      const countBadge = document.querySelector('[data-coupon-count-badge]');
      const tableBody = document.querySelector('[data-coupon-table-body]');
      const modal = document.getElementById('offersCouponModal');
      const deleteModal = document.getElementById('offersCouponDeleteModal');
      const modalTitle = document.querySelector('[data-coupon-modal-title]');
      const modalModeInput = document.querySelector('[data-coupon-modal-mode]');
      const modalIdInput = document.querySelector('[data-coupon-modal-id]');
      const codeInput = document.querySelector('[data-coupon-code-input]');
      const typeInputs = Array.from(document.querySelectorAll('[data-coupon-discount-type]'));
      const flatWrap = document.querySelector('[data-coupon-flat-wrap]');
      const percentageWrap = document.querySelector('[data-coupon-percentage-wrap]');
      const maxWrap = document.querySelector('[data-coupon-max-wrap]');
      const appliesToSelect = document.querySelector('[data-coupon-applies-to]');
      const specificWrap = document.querySelector('[data-coupon-specific-wrap]');
      const specificList = document.querySelector('[data-coupon-specific-list]');
      const flatValueInput = document.querySelector('[data-coupon-flat-value]');
      const percentageValueInput = document.querySelector('[data-coupon-percentage-value]');
      const minimumOrderInput = document.querySelector('[data-coupon-minimum-order]');
      const maxDiscountInput = document.querySelector('[data-coupon-max-discount]');
      const usageLimitInput = document.querySelector('[data-coupon-usage-limit]');
      const perUserLimitInput = document.querySelector('[data-coupon-per-user-limit]');
      const startAtInput = document.querySelector('[data-coupon-start-at]');
      const endAtInput = document.querySelector('[data-coupon-end-at]');
      const statusInput = document.querySelector('[data-coupon-status]');
      const typePreview = document.querySelector('[data-coupon-type-preview]');
      const previewLine = document.querySelector('[data-coupon-preview-line]');
      const restoreButton = document.querySelector('[data-coupon-restore]');
      const saveButton = document.querySelector('[data-coupon-save-btn]');
      const deleteCodeNode = document.querySelector('[data-coupon-delete-code]');
      const deleteConfirmButton = document.querySelector('[data-coupon-delete-confirm]');

      if (
        !(countBadge instanceof HTMLElement) ||
        !(tableBody instanceof HTMLElement) ||
        !(modal instanceof HTMLElement) ||
        !(deleteModal instanceof HTMLElement) ||
        !(modalTitle instanceof HTMLElement) ||
        !(modalModeInput instanceof HTMLInputElement) ||
        !(modalIdInput instanceof HTMLInputElement) ||
        !(codeInput instanceof HTMLInputElement) ||
        typeInputs.length === 0 ||
        !(flatWrap instanceof HTMLElement) ||
        !(percentageWrap instanceof HTMLElement) ||
        !(maxWrap instanceof HTMLElement) ||
        !(appliesToSelect instanceof HTMLSelectElement) ||
        !(specificWrap instanceof HTMLElement) ||
        !(specificList instanceof HTMLSelectElement) ||
        !(flatValueInput instanceof HTMLInputElement) ||
        !(percentageValueInput instanceof HTMLInputElement) ||
        !(minimumOrderInput instanceof HTMLInputElement) ||
        !(maxDiscountInput instanceof HTMLInputElement) ||
        !(usageLimitInput instanceof HTMLInputElement) ||
        !(perUserLimitInput instanceof HTMLInputElement) ||
        !(startAtInput instanceof HTMLInputElement) ||
        !(endAtInput instanceof HTMLInputElement) ||
        !(statusInput instanceof HTMLSelectElement) ||
        !(typePreview instanceof HTMLElement) ||
        !(previewLine instanceof HTMLElement) ||
        !(restoreButton instanceof HTMLButtonElement) ||
        !(saveButton instanceof HTMLButtonElement) ||
        !(deleteCodeNode instanceof HTMLElement) ||
        !(deleteConfirmButton instanceof HTMLButtonElement)
      ) {
        return;
      }

      const couponMap = new Map();
      let loadedCouponState = null;
      let pendingDeleteCoupon = null;
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

      function syncCouponMap() {
        couponMap.clear();
        coupons.forEach((coupon) => {
          if (coupon && typeof coupon === 'object' && coupon.id) {
            couponMap.set(String(coupon.id), coupon);
          }
        });
      }

      function sanitizeCode(rawValue) {
        return String(rawValue || '')
          .toUpperCase()
          .replace(/[^A-Z0-9_-]/g, '')
          .slice(0, 24);
      }

      function normalizeNumber(value, fallback = 0) {
        const parsed = Number.parseFloat(String(value ?? '').trim());
        return Number.isFinite(parsed) ? parsed : fallback;
      }

      function normalizeInt(value, fallback = 0) {
        const parsed = Number.parseInt(String(value ?? '').trim(), 10);
        return Number.isFinite(parsed) ? parsed : fallback;
      }

      function normalizeSpecificProducts(value) {
        if (!Array.isArray(value)) {
          return [];
        }

        return value
          .map((item) => String(item || '').trim())
          .filter(Boolean);
      }

      function normalizeCouponState(raw = {}) {
        const source = raw && typeof raw === 'object' ? raw : {};
        const defaultState = couponDefaults && typeof couponDefaults === 'object' ? couponDefaults : {};
        const discountType = source.discount_type === 'flat' ? 'flat' : (source.discount_type === 'percentage' ? 'percentage' : (defaultState.discount_type === 'flat' ? 'flat' : 'percentage'));
        const appliesTo = source.applies_to === 'specific_products'
          ? 'specific_products'
          : (source.applies_to === 'all_products' ? 'all_products' : (defaultState.applies_to === 'specific_products' ? 'specific_products' : 'all_products'));
        const status = ['Active', 'Scheduled', 'Draft'].includes(String(source.status || ''))
          ? String(source.status)
          : (['Active', 'Scheduled', 'Draft'].includes(String(defaultState.status || '')) ? String(defaultState.status) : 'Draft');

        return {
          id: source.id ? String(source.id) : '',
          code: sanitizeCode(source.code || defaultState.code || ''),
          discount_type: discountType,
          flat_value: Math.max(0, normalizeNumber(source.flat_value, normalizeNumber(defaultState.flat_value, 0))),
          percentage_value: Math.max(0, normalizeNumber(source.percentage_value, normalizeNumber(defaultState.percentage_value, 0))),
          minimum_order: Math.max(0, normalizeNumber(source.minimum_order, normalizeNumber(defaultState.minimum_order, 0))),
          max_discount: Math.max(0, normalizeNumber(source.max_discount, normalizeNumber(defaultState.max_discount, 0))),
          usage_limit: Math.max(1, normalizeInt(source.usage_limit, normalizeInt(defaultState.usage_limit, 1))),
          per_user_limit: Math.max(1, normalizeInt(source.per_user_limit, normalizeInt(defaultState.per_user_limit, 1))),
          start_at: String(source.start_at || defaultState.start_at || ''),
          end_at: String(source.end_at || defaultState.end_at || ''),
          applies_to: appliesTo,
          specific_product_ids: normalizeSpecificProducts(source.specific_product_ids || defaultState.specific_product_ids || []),
          status,
        };
      }

      function renderStatValue(key, value) {
        const card = stats.find((item) => item instanceof HTMLElement && item.dataset.offersStat === key);
        const valueNode = card instanceof HTMLElement ? card.querySelector('[data-offers-stat-value]') : null;
        if (valueNode instanceof HTMLElement) {
          valueNode.textContent = String(value ?? '--');
        }
      }

      function renderStats(payload = {}) {
        renderStatValue('active_coupons', payload.active_coupons ?? '--');
        renderStatValue('total_redemptions', payload.total_redemptions ?? '--');
        renderStatValue('average_discount_label', payload.average_discount_label ?? '--');
        renderStatValue('expiring_soon', payload.expiring_soon ?? '--');
      }

      function renderCountBadge() {
        const total = Array.isArray(coupons) ? coupons.length : 0;
        countBadge.textContent = `${total} coupon${total === 1 ? '' : 's'}`;
      }

      function couponStatusClass(status) {
        switch (String(status || '').trim()) {
          case 'Active':
            return 'badge-success';
          case 'Scheduled':
            return 'badge-warning';
          default:
            return 'badge-info';
        }
      }

      function renderCouponMessage(message) {
        tableBody.innerHTML = '';

        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 8;
        cell.textContent = message;
        row.appendChild(cell);
        tableBody.appendChild(row);
      }

      function appendCouponCell(row, text, className = '') {
        const cell = document.createElement('td');
        if (className) {
          cell.className = className;
        }
        cell.textContent = String(text ?? '');
        row.appendChild(cell);
        return cell;
      }

      function buildCouponRow(coupon) {
        const row = document.createElement('tr');
        row.dataset.couponRow = String(coupon.id || '');

        appendCouponCell(row, coupon.code || '', 'settings-cell-strong');
        appendCouponCell(row, coupon.discount_type_label || '');
        appendCouponCell(row, coupon.discount_value_label || '');
        appendCouponCell(row, coupon.minimum_order_label || '');
        appendCouponCell(row, coupon.usage || '');

        const statusCell = document.createElement('td');
        const statusBadge = document.createElement('span');
        statusBadge.className = `badge ${couponStatusClass(coupon.status)}`;
        statusBadge.textContent = String(coupon.status || '');
        statusCell.appendChild(statusBadge);
        row.appendChild(statusCell);

        appendCouponCell(row, coupon.validity || '');

        const actionsCell = document.createElement('td');
        const actions = document.createElement('div');
        actions.className = 'settings-offer-actions';

        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.className = 'btn btn-primary btn-sm';
        editButton.dataset.couponEditBtn = '1';
        editButton.dataset.couponId = String(coupon.id || '');
        editButton.textContent = 'Edit';

        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.className = 'btn btn-danger btn-sm';
        deleteButton.dataset.couponDeleteBtn = '1';
        deleteButton.dataset.couponId = String(coupon.id || '');
        deleteButton.dataset.couponCode = String(coupon.code || '');
        deleteButton.textContent = 'Delete';

        actions.append(editButton, deleteButton);
        actionsCell.appendChild(actions);
        row.appendChild(actionsCell);

        return row;
      }

      function renderCouponTable() {
        if (!Array.isArray(coupons) || coupons.length === 0) {
          renderCouponMessage('No coupons found yet.');
          return;
        }

        tableBody.innerHTML = '';
        coupons.forEach((coupon) => {
          tableBody.appendChild(buildCouponRow(coupon));
        });
      }

      function renderProductGroups(selectedIds = []) {
        specificList.innerHTML = '';

        productGroups.forEach((group) => {
          const optGroup = document.createElement('optgroup');
          optGroup.label = String(group.label || '');

          const selectAll = document.createElement('option');
          selectAll.value = `select_all_${String(group.key || '')}`;
          selectAll.textContent = 'Select All';
          selectAll.dataset.groupSelectAll = '1';
          selectAll.dataset.groupKey = String(group.key || '');
          optGroup.appendChild(selectAll);

          const products = Array.isArray(group.products) ? group.products : [];
          products.forEach((product) => {
            const option = document.createElement('option');
            option.value = String(product.value ?? '');
            option.textContent = String(product.label ?? '');
            option.dataset.groupKey = String(group.key || '');
            optGroup.appendChild(option);
          });

          specificList.appendChild(optGroup);
        });

        markSelectedProducts(selectedIds);
      }

      function selectedType() {
        const selected = typeInputs.find((input) => input instanceof HTMLInputElement && input.checked);
        return selected instanceof HTMLInputElement && selected.value === 'flat' ? 'flat' : 'percentage';
      }

      function showCouponModal() {
        if (typeof window.openModal === 'function') {
          window.openModal('offersCouponModal');
        } else {
          modal.classList.add('active');
          document.body.style.overflow = 'hidden';
        }

        window.setTimeout(() => {
          codeInput.focus();
          codeInput.select();
        }, 0);
      }

      function hideCouponModal() {
        if (typeof window.closeAllModals === 'function') {
          window.closeAllModals();
        } else {
          modal.classList.remove('active');
          document.body.style.overflow = '';
        }
      }

      function showDeleteModal() {
        if (typeof window.openModal === 'function') {
          window.openModal('offersCouponDeleteModal');
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

      function setPendingDeleteCoupon(coupon) {
        const source = coupon && typeof coupon === 'object' ? coupon : {};
        pendingDeleteCoupon = {
          id: String(source.id || '').trim(),
          code: String(source.code || 'this coupon').trim() || 'this coupon',
        };

        deleteCodeNode.textContent = pendingDeleteCoupon.code;
        deleteConfirmButton.disabled = false;
        deleteConfirmButton.textContent = 'Delete Coupon';
      }

      function setModalMode(mode) {
        const editMode = mode === 'edit';

        modalModeInput.value = editMode ? 'edit' : 'create';
        modalTitle.textContent = editMode ? 'Edit Coupon Code' : 'Create Coupon Code';
        saveButton.textContent = editMode ? 'Update Coupon' : 'Create Coupon';
        saveButton.classList.toggle('btn-primary', editMode);
        saveButton.classList.toggle('btn-success', !editMode);
      }

      function refreshTypeUI() {
        const flatMode = selectedType() === 'flat';

        flatWrap.classList.toggle('hidden', !flatMode);
        percentageWrap.classList.toggle('hidden', flatMode);
        maxWrap.classList.toggle('hidden', flatMode);

        flatValueInput.disabled = !flatMode;
        percentageValueInput.disabled = flatMode;
        maxDiscountInput.disabled = flatMode;
        typePreview.textContent = flatMode ? 'Flat' : 'Percentage';
      }

      function refreshAppliesToUI() {
        const specificMode = appliesToSelect.value === 'specific_products';
        specificWrap.classList.toggle('hidden', !specificMode);
        specificList.disabled = !specificMode;
      }

      function applyGroupSelectAll() {
        const options = Array.from(specificList.options);
        const selectAllOptions = options.filter((option) => option.dataset.groupSelectAll === '1' && option.selected);

        selectAllOptions.forEach((selectAllOption) => {
          const groupKey = selectAllOption.dataset.groupKey || '';

          options.forEach((option) => {
            if (option.dataset.groupKey === groupKey && option.dataset.groupSelectAll !== '1') {
              option.selected = true;
            }
          });

          selectAllOption.selected = false;
        });
      }

      function refreshPreview() {
        const sanitizedCode = sanitizeCode(codeInput.value);
        if (codeInput.value !== sanitizedCode) {
          codeInput.value = sanitizedCode;
        }

        const code = sanitizedCode || 'COUPON_CODE';
        const flatMode = selectedType() === 'flat';
        const valueLabel = flatMode
          ? `BDT ${Math.max(0, normalizeNumber(flatValueInput.value, 0))}`
          : `${Math.max(0, normalizeNumber(percentageValueInput.value, 0))}%`;
        const maxDiscount = Math.max(0, normalizeNumber(maxDiscountInput.value, 0));
        const capLabel = !flatMode && maxDiscount > 0 ? ` up to BDT ${maxDiscount}` : '';

        previewLine.textContent = `${code} gives ${valueLabel} off${capLabel}.`;
      }

      function markSelectedProducts(productIds) {
        const selectedIds = new Set(productIds);

        Array.from(specificList.options).forEach((option) => {
          if (option.dataset.groupSelectAll === '1') {
            option.selected = false;
            return;
          }

          option.selected = selectedIds.has(option.value);
        });
      }

      function fillForm(state) {
        const normalized = normalizeCouponState(state);

        modalIdInput.value = normalized.id;
        codeInput.value = normalized.code;

        typeInputs.forEach((input) => {
          if (input instanceof HTMLInputElement) {
            input.checked = input.value === normalized.discount_type;
          }
        });

        flatValueInput.value = String(normalized.flat_value);
        percentageValueInput.value = String(normalized.percentage_value);
        minimumOrderInput.value = String(normalized.minimum_order);
        maxDiscountInput.value = String(normalized.max_discount);
        usageLimitInput.value = String(normalized.usage_limit);
        perUserLimitInput.value = String(normalized.per_user_limit);
        startAtInput.value = normalized.start_at;
        endAtInput.value = normalized.end_at;
        appliesToSelect.value = normalized.applies_to;
        statusInput.value = normalized.status;
        markSelectedProducts(normalized.specific_product_ids);

        refreshTypeUI();
        refreshAppliesToUI();
        refreshPreview();
        saveButton.disabled = false;
        restoreButton.disabled = false;
      }

      function openCreateModal() {
        loadedCouponState = normalizeCouponState(couponDefaults);
        setModalMode('create');
        fillForm(loadedCouponState);
        showCouponModal();
      }

      function openEditModal(button) {
        if (!(button instanceof HTMLButtonElement)) {
          return;
        }

        const couponId = String(button.dataset.couponId || '');
        const coupon = couponMap.get(couponId);

        if (!coupon) {
          if (typeof window.showError === 'function') {
            window.showError('Coupon details are unavailable for editing.');
          }
          return;
        }

        loadedCouponState = normalizeCouponState(coupon);
        setModalMode('edit');
        fillForm(loadedCouponState);
        showCouponModal();
      }

      function openDeleteCouponModal(button) {
        if (!(button instanceof HTMLButtonElement)) {
          return;
        }

        const couponId = String(button.dataset.couponId || '').trim();
        const fallbackCode = String(button.dataset.couponCode || 'this coupon').trim() || 'this coupon';
        if (!couponId) {
          showError('Coupon details are unavailable for delete action.');
          return;
        }

        setPendingDeleteCoupon(couponMap.get(couponId) || { id: couponId, code: fallbackCode });
        showDeleteModal();
      }

      function restoreLoadedState() {
        fillForm(loadedCouponState || couponDefaults);

        if (typeof window.showInfo === 'function') {
          window.showInfo('Coupon form restored.');
        }
      }

      function currentFormState() {
        const appliesTo = appliesToSelect.value === 'specific_products' ? 'specific_products' : 'all_products';

        return normalizeCouponState({
          id: modalIdInput.value,
          code: codeInput.value,
          discount_type: selectedType(),
          flat_value: flatValueInput.value,
          percentage_value: percentageValueInput.value,
          minimum_order: minimumOrderInput.value,
          max_discount: maxDiscountInput.value,
          usage_limit: usageLimitInput.value,
          per_user_limit: perUserLimitInput.value,
          start_at: startAtInput.value,
          end_at: endAtInput.value,
          applies_to: appliesTo,
          specific_product_ids: appliesTo === 'specific_products'
            ? Array.from(specificList.selectedOptions)
              .filter((option) => option.dataset.groupSelectAll !== '1')
              .map((option) => option.value)
            : [],
          status: statusInput.value,
        });
      }

      function toApiPayload(state) {
        return {
          ...state,
          specific_product_ids: Array.isArray(state.specific_product_ids)
            ? state.specific_product_ids
              .map((value) => Number.parseInt(String(value || '').trim(), 10))
              .filter((value) => Number.isFinite(value) && value > 0)
            : [],
        };
      }

      function applyOffersPayload(payload = {}, options = {}) {
        if (payload && typeof payload.defaults === 'object') {
          couponDefaults = payload.defaults;
        }
        coupons = Array.isArray(payload?.coupons) ? payload.coupons : [];
        productGroups = Array.isArray(payload?.product_groups) ? payload.product_groups : [];

        syncCouponMap();
        renderStats(payload?.stats && typeof payload.stats === 'object' ? payload.stats : {});
        renderCountBadge();
        renderCouponTable();

        if (options.resetForm !== false || !loadedCouponState) {
          loadedCouponState = normalizeCouponState(couponDefaults);
          setModalMode('create');
        } else if (loadedCouponState.id) {
          const refreshedCoupon = couponMap.get(String(loadedCouponState.id));
          if (refreshedCoupon) {
            loadedCouponState = normalizeCouponState(refreshedCoupon);
          }
        }

        renderProductGroups(loadedCouponState?.specific_product_ids || []);
        fillForm(loadedCouponState || couponDefaults);
      }

      async function fetchOffersData(options = {}) {
        if (!apiBaseUrl || !refreshToken || !window.API?.Admin?.Offers?.get) {
          renderCouponMessage('Offers API is not configured.');
          if (options.showErrors !== false) {
            showError('Offers API is not configured.');
          }
          return false;
        }

        if (options.showLoading !== false) {
          renderCouponMessage('Loading coupons...');
          countBadge.textContent = 'Loading...';
        }

        try {
          const response = await window.API.Admin.Offers.get({
            apiBaseUrl,
            refreshToken,
          });

          applyOffersPayload(response, options);
          return true;
        } catch (error) {
          if (!Array.isArray(coupons) || coupons.length === 0) {
            renderCouponMessage('Unable to load coupons right now.');
          }

          if (options.showErrors !== false) {
            showError(error?.message || 'Unable to load coupons right now.');
          }

          return false;
        }
      }

      typeInputs.forEach((input) => {
        input.addEventListener('change', () => {
          refreshTypeUI();
          refreshPreview();
        });
      });

      codeInput.addEventListener('input', refreshPreview);
      codeInput.addEventListener('blur', refreshPreview);
      flatValueInput.addEventListener('input', refreshPreview);
      percentageValueInput.addEventListener('input', refreshPreview);
      maxDiscountInput.addEventListener('input', refreshPreview);
      appliesToSelect.addEventListener('change', refreshAppliesToUI);
      specificList.addEventListener('change', applyGroupSelectAll);

      createButtons.forEach((button) => {
        button.addEventListener('click', openCreateModal);
      });

      tableBody.addEventListener('click', async (event) => {
        const target = event.target instanceof HTMLElement ? event.target : null;
        if (!(target instanceof HTMLElement)) {
          return;
        }

        const editButton = target.closest('[data-coupon-edit-btn]');
        if (editButton instanceof HTMLButtonElement) {
          openEditModal(editButton);
          return;
        }

        const deleteButton = target.closest('[data-coupon-delete-btn]');
        if (!(deleteButton instanceof HTMLButtonElement)) {
          return;
        }

        if (!apiBaseUrl || !refreshToken || !window.API?.Admin?.Offers?.remove) {
          showError('Offers API is not configured.');
          return;
        }

        openDeleteCouponModal(deleteButton);
      });

      deleteConfirmButton.addEventListener('click', async () => {
        const couponId = String(pendingDeleteCoupon?.id || '').trim();
        const couponCode = String(pendingDeleteCoupon?.code || 'this coupon').trim() || 'this coupon';

        if (!couponId) {
          showError('Coupon details are unavailable for delete action.');
          return;
        }

        if (!apiBaseUrl || !refreshToken || !window.API?.Admin?.Offers?.remove) {
          showError('Offers API is not configured.');
          return;
        }

        deleteConfirmButton.disabled = true;
        deleteConfirmButton.textContent = 'Deleting...';

        try {
          const response = await window.API.Admin.Offers.remove({
            apiBaseUrl,
            refreshToken,
            couponId,
          });

          pendingDeleteCoupon = null;
          hideDeleteModal();
          showSuccess(response?.message || `${couponCode} deleted successfully.`);
          await fetchOffersData({ resetForm: true, showErrors: false, showLoading: false });
        } catch (error) {
          showError(error?.message || 'Unable to delete the coupon.');
          deleteConfirmButton.disabled = false;
          deleteConfirmButton.textContent = 'Delete Coupon';
        }
      });

      restoreButton.addEventListener('click', restoreLoadedState);

      saveButton.addEventListener('click', async () => {
        const formState = currentFormState();
        fillForm(formState);

        if (!apiBaseUrl || !refreshToken || !window.API?.Admin?.Offers?.save) {
          showError('Offers API is not configured.');
          return;
        }

        const action = modalModeInput.value === 'edit' ? 'edit' : 'create';
        const couponId = action === 'edit' ? modalIdInput.value : '';
        saveButton.disabled = true;
        restoreButton.disabled = true;
        saveButton.textContent = action === 'edit' ? 'Updating...' : 'Creating...';

        try {
          const response = await window.API.Admin.Offers.save({
            apiBaseUrl,
            refreshToken,
            payload: toApiPayload(formState),
            couponId: couponId !== '' ? couponId : null,
          });

          showSuccess(response?.message || `Coupon ${formState.code || 'COUPON_CODE'} saved successfully.`);
          hideCouponModal();
          await fetchOffersData({ resetForm: true, showErrors: false, showLoading: false });
        } catch (error) {
          showError(error?.message || 'Unable to save the coupon.');
          saveButton.disabled = false;
          restoreButton.disabled = false;
          setModalMode(action);
        }
      });

      syncCouponMap();
      loadedCouponState = normalizeCouponState(couponDefaults);
      setModalMode('create');
      renderStats({});
      countBadge.textContent = 'Loading...';
      renderCouponMessage('Loading coupons...');
      renderProductGroups(loadedCouponState.specific_product_ids);
      fillForm(loadedCouponState);
      fetchOffersData({ resetForm: true, showErrors: false });
    });
  </script>
@endsection
