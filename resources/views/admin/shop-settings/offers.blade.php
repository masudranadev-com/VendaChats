@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header settings-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="settings-header-actions">
      <button type="button" class="btn btn-secondary" data-coupon-reset>Reset Form</button>
      <button type="button" class="btn btn-success" data-coupon-create>Create Coupon</button>
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

  <div class="settings-layout mt-xl" data-coupon-manager style="grid-template-columns: minmax(0, 1fr);">
    <section class="settings-main-column">
      <article class="card settings-panel">
        <div class="card-header">
          <div>
            <h3 class="card-title">Create Coupon Code</h3>
            <p class="settings-panel-subtitle">Simple flow: set code, choose discount type, set limits, and save.</p>
          </div>
          <span class="badge badge-primary">Easy Setup</span>
        </div>

        <div class="settings-content-card">
          <div class="settings-content-head">
            <strong>Coupon Preview</strong>
            <span class="badge badge-info" data-coupon-type-preview>Percentage</span>
          </div>
          <p data-coupon-preview-line>WELCOME10 gives 10% off.</p>
          <small>Preview updates automatically while typing.</small>
        </div>

        <div class="settings-field-grid mt-md">
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
            <input id="couponFlatValue" type="number" min="1" class="form-input" value="120" data-coupon-flat-value>
            <small class="form-help">Example: 120 means BDT 120 off.</small>
          </div>

          <div class="form-group {{ $couponDefaults['discount_type'] === 'percentage' ? '' : 'hidden' }}" data-coupon-percentage-wrap>
            <label class="form-label" for="couponPercentageValue">Percentage Discount (%)</label>
            <input id="couponPercentageValue" type="number" min="1" max="90" class="form-input" value="{{ $couponDefaults['discount_value'] }}" data-coupon-percentage-value>
            <small class="form-help">Keep percentage between 1 and 90.</small>
          </div>

          <div class="form-group {{ $couponDefaults['discount_type'] === 'percentage' ? '' : 'hidden' }}" data-coupon-max-wrap>
            <label class="form-label" for="couponMaxDiscount">Max Discount Cap (BDT)</label>
            <input id="couponMaxDiscount" type="number" min="0" class="form-input" value="{{ $couponDefaults['max_discount'] }}" data-coupon-max-discount>
            <small class="form-help">Required for percentage type to control high discount values.</small>
          </div>

          <div class="form-group">
            <label class="form-label" for="couponMinOrder">Minimum Order (BDT)</label>
            <input id="couponMinOrder" type="number" min="0" class="form-input" value="{{ $couponDefaults['minimum_order'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="couponUsageLimit">Total Usage Limit</label>
            <input id="couponUsageLimit" type="number" min="1" class="form-input" value="{{ $couponDefaults['usage_limit'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="couponPerUserLimit">Per User Limit</label>
            <input id="couponPerUserLimit" type="number" min="1" class="form-input" value="{{ $couponDefaults['per_user_limit'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="couponStartAt">Start Date & Time</label>
            <input id="couponStartAt" type="datetime-local" class="form-input" value="{{ $couponDefaults['start_at'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="couponEndAt">End Date & Time</label>
            <input id="couponEndAt" type="datetime-local" class="form-input" value="{{ $couponDefaults['end_at'] }}">
          </div>

          <div class="form-group">
            <label class="form-label" for="couponAppliesTo">Applies To</label>
            <select id="couponAppliesTo" class="form-select settings-coupon-dropdown" data-coupon-applies-to>
              <option value="all_products" {{ $couponDefaults['applies_to'] === 'All Products' ? 'selected' : '' }}>Apply to all product</option>
              <option value="specific_products" {{ $couponDefaults['applies_to'] === 'Selected Products' ? 'selected' : '' }}>Apply to specific product</option>
            </select>
          </div>

          <div class="form-group hidden" style="grid-column: 1 / -1;" data-coupon-specific-wrap>
            <label class="form-label" for="couponSpecificProductList">Select Specific Products</label>
            <select
              id="couponSpecificProductList"
              class="form-multi-select settings-coupon-multi"
              multiple
              data-coreui-search="true"
              data-coupon-specific-list
              disabled
            >
              @foreach ($productGroups as $group)
                <optgroup label="{{ $group['label'] }}">
                  <option value="select_all_{{ $group['key'] }}" data-group-select-all="1" data-group-key="{{ $group['key'] }}">Select All</option>
                  @foreach ($group['products'] as $product)
                    <option value="{{ $product['value'] }}" data-group-key="{{ $group['key'] }}">{{ $product['label'] }}</option>
                  @endforeach
                </optgroup>
              @endforeach
            </select>
            <small class="form-help">CoreUI style multi-select with grouped products and per-group Select All.</small>
          </div>

          <div class="form-group">
            <label class="form-label" for="couponStatus">Status</label>
            <select id="couponStatus" class="form-select settings-coupon-dropdown">
              <option {{ $couponDefaults['status'] === 'Active' ? 'selected' : '' }}>Active</option>
              <option {{ $couponDefaults['status'] === 'Scheduled' ? 'selected' : '' }}>Scheduled</option>
              <option {{ $couponDefaults['status'] === 'Draft' ? 'selected' : '' }}>Draft</option>
            </select>
          </div>
        </div>

        <div class="settings-inline-actions">
          <button type="button" class="btn btn-success btn-sm" data-coupon-create>Create Coupon</button>
          <button type="button" class="btn btn-secondary btn-sm" data-coupon-reset>Clear Form</button>
        </div>
      </article>

      <article class="card settings-panel mt-xl">
        <div class="card-header">
          <div>
            <h3 class="card-title">Coupon Code List</h3>
            <p class="settings-panel-subtitle">Manage all coupons from one simple table.</p>
          </div>
          <span class="badge badge-info">{{ count($coupons) }} coupons</span>
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
            <tbody>
              @foreach ($coupons as $coupon)
                @php
                  $couponStatusClass = $coupon['status'] === 'Active' ? 'badge-success' : 'badge-warning';
                @endphp
                <tr>
                  <td class="settings-cell-strong">{{ $coupon['code'] }}</td>
                  <td>{{ $coupon['discount_type'] }}</td>
                  <td>{{ $coupon['discount_value'] }}</td>
                  <td>{{ $coupon['minimum_order'] }}</td>
                  <td>{{ $coupon['usage'] }}</td>
                  <td><span class="badge {{ $couponStatusClass }}">{{ $coupon['status'] }}</span></td>
                  <td>{{ $coupon['validity'] }}</td>
                  <td>
                    <div class="settings-offer-actions">
                      <button type="button" class="btn btn-primary btn-sm">Edit</button>
                      <button type="button" class="btn btn-danger btn-sm">Disable</button>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </article>
    </section>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const couponManager = document.querySelector('[data-coupon-manager]');
      if (!couponManager) {
        return;
      }

      const codeInput = couponManager.querySelector('[data-coupon-code-input]');
      const typeInputs = Array.from(couponManager.querySelectorAll('[data-coupon-discount-type]'));
      const flatWrap = couponManager.querySelector('[data-coupon-flat-wrap]');
      const percentageWrap = couponManager.querySelector('[data-coupon-percentage-wrap]');
      const maxWrap = couponManager.querySelector('[data-coupon-max-wrap]');
      const appliesToSelect = couponManager.querySelector('[data-coupon-applies-to]');
      const specificWrap = couponManager.querySelector('[data-coupon-specific-wrap]');
      const specificList = couponManager.querySelector('[data-coupon-specific-list]');
      const flatValueInput = couponManager.querySelector('[data-coupon-flat-value]');
      const percentageValueInput = couponManager.querySelector('[data-coupon-percentage-value]');
      const typePreview = couponManager.querySelector('[data-coupon-type-preview]');
      const previewLine = couponManager.querySelector('[data-coupon-preview-line]');
      const createButtons = Array.from(document.querySelectorAll('[data-coupon-create]'));
      const resetButtons = Array.from(document.querySelectorAll('[data-coupon-reset]'));

      if (
        !codeInput ||
        typeInputs.length === 0 ||
        !appliesToSelect ||
        !specificWrap ||
        !specificList ||
        !flatWrap ||
        !percentageWrap ||
        !maxWrap ||
        !flatValueInput ||
        !percentageValueInput ||
        !typePreview ||
        !previewLine
      ) {
        return;
      }

      function selectedType() {
        const selected = typeInputs.find((input) => input instanceof HTMLInputElement && input.checked);
        if (!(selected instanceof HTMLInputElement)) {
          return 'percentage';
        }

        return selected.value === 'flat' ? 'flat' : 'percentage';
      }

      function sanitizeCode(rawValue) {
        return String(rawValue || '')
          .toUpperCase()
          .replace(/[^A-Z0-9_\-]/g, '')
          .slice(0, 24);
      }

      function refreshTypeUI() {
        const discountType = selectedType();
        const flatMode = discountType === 'flat';

        flatWrap.classList.toggle('hidden', !flatMode);
        percentageWrap.classList.toggle('hidden', flatMode);
        maxWrap.classList.toggle('hidden', flatMode);

        flatValueInput.disabled = !flatMode;
        percentageValueInput.disabled = flatMode;

        if (typePreview instanceof HTMLElement) {
          typePreview.textContent = flatMode ? 'Flat' : 'Percentage';
        }
      }

      function refreshAppliesToUI() {
        const appliesTo = appliesToSelect.value;
        const specificMode = appliesTo === 'specific_products';

        specificWrap.classList.toggle('hidden', !specificMode);
        specificList.disabled = !specificMode;
      }

      function applyGroupSelectAll() {
        const options = Array.from(specificList.options);
        const selectAllOptions = options.filter((option) => option.dataset.groupSelectAll === '1');

        selectAllOptions.forEach((selectAllOption) => {
          if (!selectAllOption.selected) {
            return;
          }

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
        const code = sanitizeCode(codeInput.value) || 'COUPON_CODE';
        codeInput.value = code;

        const discountType = selectedType();
        const value = discountType === 'flat'
          ? `BDT ${Math.max(0, Number.parseFloat(flatValueInput.value || '0') || 0)}`
          : `${Math.max(0, Number.parseFloat(percentageValueInput.value || '0') || 0)}%`;

        previewLine.textContent = `${code} gives ${value} off.`;
      }

      function resetForm() {
        codeInput.value = 'WELCOME10';

        typeInputs.forEach((input) => {
          if (!(input instanceof HTMLInputElement)) {
            return;
          }

          input.checked = input.value === 'percentage';
        });

        flatValueInput.value = '120';
        percentageValueInput.value = '10';
        appliesToSelect.value = 'all_products';
        Array.from(specificList.options).forEach((option) => {
          option.selected = false;
        });

        refreshTypeUI();
        refreshAppliesToUI();
        refreshPreview();

        if (typeof window.showInfo === 'function') {
          window.showInfo('Coupon form reset to default values.');
        }
      }

      typeInputs.forEach((input) => {
        input.addEventListener('change', () => {
          refreshTypeUI();
          refreshPreview();
        });
      });

      codeInput.addEventListener('input', refreshPreview);
      flatValueInput.addEventListener('input', refreshPreview);
      percentageValueInput.addEventListener('input', refreshPreview);
      appliesToSelect.addEventListener('change', refreshAppliesToUI);
      specificList.addEventListener('change', applyGroupSelectAll);

      createButtons.forEach((button) => {
        button.addEventListener('click', () => {
          refreshPreview();

          if (typeof window.showSuccess === 'function') {
            window.showSuccess(`Coupon ${codeInput.value} created (UI demo).`);
          }
        });
      });

      resetButtons.forEach((button) => {
        button.addEventListener('click', resetForm);
      });

      refreshTypeUI();
      refreshAppliesToUI();
      refreshPreview();
    });
  </script>
@endsection
