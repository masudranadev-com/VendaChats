@extends('admin.master')

@section('title', $title)

@section('admin.content')
  @php
    $hasThemeDomain = $hasDomain ?? false;
    $activeDomain = $domainContext ?? null;
    $themePageUnlocked = $hasThemeDomain && ($activeDomain['is_connected'] ?? false);
    $allowedThemeIds = $hasThemeDomain ? ($activeDomain['available_theme_ids'] ?? []) : [];
  @endphp

  <div class="page-header settings-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="settings-header-actions settings-theme-actions">
      <button type="button" class="btn btn-secondary {{ $themePageUnlocked ? '' : 'hidden' }}" data-theme-save-btn {{ $themePageUnlocked ? '' : 'disabled' }}>Save Theme Settings</button>
      <button type="button" class="btn btn-primary {{ $themePageUnlocked ? '' : 'hidden' }}" data-theme-activate-btn {{ $themePageUnlocked ? '' : 'disabled' }}>Activate Selected Theme</button>
    </div>
  </div>

  @include('admin.shop-settings.partials.tab-row')

  <div
    class="settings-layout settings-layout-single settings-theme-layout"
    data-theme-manager
    data-theme-ready="{{ $themePageUnlocked ? '1' : '0' }}"
    data-domain-value="{{ $activeDomain['value'] ?? '' }}"
    data-default-theme="{{ $activeDomain['default_theme_id'] ?? '' }}"
    data-allowed-themes="{{ implode(',', $allowedThemeIds) }}"
  >
    <section class="settings-main-column">
      @if (! $hasThemeDomain)
        <article class="card settings-panel">
          <div class="card-header">
            <div>
              <h3 class="card-title">Theme Setup Unavailable</h3>
              <p class="settings-panel-subtitle">Create one domain first, then this page will open automatically.</p>
            </div>
            <span class="badge badge-warning">Blocked</span>
          </div>

          <ul class="settings-focus-list">
            <li>Go to Domain setup page.</li>
            <li>Create exactly one domain.</li>
            <li>Return here to manage theme settings.</li>
          </ul>
        </article>
      @elseif (! $themePageUnlocked)
        <article class="card settings-panel mt-md">
          <div class="card-header">
            <div>
              <h3 class="card-title">Domain Verification Pending</h3>
              <p class="settings-panel-subtitle">Theme controls are locked until domain status becomes connected.</p>
            </div>
            <span class="badge badge-warning">Pending</span>
          </div>

          <p class="settings-panel-subtitle">Current domain status: <strong>{{ $activeDomain['status'] }}</strong></p>
        </article>
      @endif

      <article class="card settings-panel mt-md {{ $themePageUnlocked ? '' : 'hidden' }}" data-theme-domain-panel>
        <div class="card-header">
          <div>
            <h3 class="card-title">Auto-Generated Theme Library</h3>
            <p class="settings-panel-subtitle">Users cannot create themes manually. System generates the available options automatically.</p>
          </div>
          <span class="badge badge-info">Generated Themes</span>
        </div>

        <div class="settings-theme-list">
          @foreach ($themeCatalog as $theme)
            <div class="settings-theme-item" data-theme-item data-theme-id="{{ $theme['id'] }}">
              <div class="settings-theme-preview"></div>
              <div class="settings-theme-content">
                <div class="flex-between">
                  <strong>{{ $theme['name'] }}</strong>
                  <span class="badge {{ $theme['is_active'] ? 'badge-success' : 'badge-info' }}" data-theme-status>
                    {{ $theme['is_active'] ? 'Active' : 'Available' }}
                  </span>
                </div>
                <p>{{ $theme['note'] }}</p>
                <div class="settings-theme-meta">
                  <span>Speed: {{ $theme['speed'] }}</span>
                  <span>Conversion: {{ $theme['conversion'] }}</span>
                  <span>Best For: {{ $theme['best_for'] }}</span>
                  <span>Generated: {{ $theme['generated_at'] }}</span>
                </div>
                <label class="settings-theme-pick">
                  <input
                    type="radio"
                    name="active_theme"
                    value="{{ $theme['id'] }}"
                    data-theme-radio
                    data-theme-control
                    {{ $theme['is_active'] ? 'checked' : '' }}
                    {{ $themePageUnlocked ? '' : 'disabled' }}
                  >
                  Select this theme
                </label>
              </div>
            </div>
          @endforeach
        </div>
      </article>

      <article class="card settings-panel mt-md {{ $themePageUnlocked ? '' : 'hidden' }}" data-theme-domain-panel>
        <div class="card-header">
          <div>
            <h3 class="card-title">Advanced Visual Settings</h3>
            <p class="settings-panel-subtitle">Theme-level controls for colors, spacing, typography, and product presentation.</p>
          </div>
          <span class="badge badge-primary">Theme Only</span>
        </div>

        <div class="settings-field-grid">
          @foreach ($advancedControls as $control)
            <div class="form-group">
              <label class="form-label" for="themeControl_{{ $control['name'] }}">{{ $control['label'] }}</label>
              <select
                id="themeControl_{{ $control['name'] }}"
                name="{{ $control['name'] }}"
                class="form-select"
                data-theme-control
                data-theme-control-name="{{ $control['name'] }}"
                {{ $themePageUnlocked ? '' : 'disabled' }}
              >
                @foreach ($control['options'] as $option)
                  <option value="{{ $option }}" {{ $control['value'] === $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
              </select>
              <small class="form-help">{{ $control['help'] }}</small>
            </div>
          @endforeach
        </div>
      </article>
    </section>

  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const manager = document.querySelector('[data-theme-manager]');
      if (!manager) {
        return;
      }

      const themeReady = manager.dataset.themeReady === '1';
      const domainValue = String(manager.dataset.domainValue || '').trim();
      const defaultThemeId = String(manager.dataset.defaultTheme || '').trim();
      const allowedThemeIds = String(manager.dataset.allowedThemes || '')
        .split(',')
        .map((value) => value.trim())
        .filter((value) => value.length > 0);

      const activateButton = document.querySelector('[data-theme-activate-btn]');
      const saveButton = document.querySelector('[data-theme-save-btn]');
      const controls = Array.from(manager.querySelectorAll('[data-theme-control]'));
      const themeRows = Array.from(manager.querySelectorAll('[data-theme-item]'));
      const themeRadios = Array.from(manager.querySelectorAll('[data-theme-radio]'));

      if (!activateButton || !saveButton) {
        return;
      }

      function updateActiveThemeBadge(themeId) {
        themeRows.forEach((row) => {
          if (!(row instanceof HTMLElement)) {
            return;
          }

          const badge = row.querySelector('[data-theme-status]');
          if (!(badge instanceof HTMLElement)) {
            return;
          }

          const isActive = row.dataset.themeId === themeId;
          badge.textContent = isActive ? 'Active' : 'Available';
          badge.classList.toggle('badge-success', isActive);
          badge.classList.toggle('badge-info', !isActive);
        });
      }

      function selectedThemeId() {
        const selected = manager.querySelector('[data-theme-radio]:checked');
        if (!(selected instanceof HTMLInputElement) || selected.disabled) {
          return '';
        }

        return selected.value;
      }

      function setControlsEnabled(enabled) {
        controls.forEach((control) => {
          if (control instanceof HTMLInputElement || control instanceof HTMLSelectElement || control instanceof HTMLButtonElement) {
            control.disabled = !enabled;
          }
        });

        activateButton.disabled = !enabled;
        saveButton.disabled = !enabled;
      }

      function applyThemeAvailability() {
        themeRows.forEach((row) => {
          if (!(row instanceof HTMLElement)) {
            return;
          }

          const themeId = String(row.dataset.themeId || '');
          const visible = allowedThemeIds.length === 0 || allowedThemeIds.includes(themeId);
          row.classList.toggle('hidden', !visible);

          const radio = row.querySelector('[data-theme-radio]');
          if (radio instanceof HTMLInputElement) {
            radio.disabled = !themeReady || !visible;
            if (!visible) {
              radio.checked = false;
            }
          }
        });
      }

      function applyDefaultThemeSelection() {
        const visibleRadios = themeRows
          .filter((row) => row instanceof HTMLElement && !row.classList.contains('hidden'))
          .map((row) => row.querySelector('[data-theme-radio]'))
          .filter((radio) => radio instanceof HTMLInputElement && !radio.disabled);

        if (visibleRadios.length === 0) {
          updateActiveThemeBadge('');
          return;
        }

        themeRadios.forEach((radio) => {
          if (radio instanceof HTMLInputElement) {
            radio.checked = false;
          }
        });

        const preferred = defaultThemeId
          ? visibleRadios.find((radio) => radio.value === defaultThemeId)
          : null;

        const selected = preferred || visibleRadios[0];
        selected.checked = true;
        updateActiveThemeBadge(selected.value);
      }

      function selectedCurrencyLabel() {
        const currencyControl = manager.querySelector('[data-theme-control-name="store_currency"]');
        if (!(currencyControl instanceof HTMLSelectElement)) {
          return '';
        }

        const selected = currencyControl.options[currencyControl.selectedIndex];
        return selected ? String(selected.textContent || '').trim() : '';
      }

      setControlsEnabled(themeReady);

      if (themeReady) {
        applyThemeAvailability();
        applyDefaultThemeSelection();
      }

      themeRadios.forEach((radio) => {
        if (!(radio instanceof HTMLInputElement)) {
          return;
        }

        radio.addEventListener('change', () => {
          updateActiveThemeBadge(selectedThemeId());
        });
      });

      activateButton.addEventListener('click', () => {
        if (!themeReady) {
          if (typeof window.showWarning === 'function') {
            window.showWarning('Theme page is locked until domain is available and connected.');
          }
          return;
        }

        const themeId = selectedThemeId();
        if (!themeId) {
          if (typeof window.showWarning === 'function') {
            window.showWarning('Select one generated theme to activate.');
          }
          return;
        }

        updateActiveThemeBadge(themeId);

        if (typeof window.showSuccess === 'function') {
          window.showSuccess(`Theme activated for ${domainValue}.`);
        }
      });

      saveButton.addEventListener('click', () => {
        if (!themeReady) {
          if (typeof window.showWarning === 'function') {
            window.showWarning('Theme page is locked until domain is available and connected.');
          }
          return;
        }

        const currencyLabel = selectedCurrencyLabel();

        if (typeof window.showSuccess === 'function') {
          window.showSuccess(
            currencyLabel
              ? `Theme settings saved for ${domainValue}. Currency: ${currencyLabel}.`
              : `Theme settings saved for ${domainValue}.`
          );
        }
      });
    });
  </script>
@endsection
