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

    <div class="settings-header-actions">
      <button type="button" class="btn btn-secondary {{ $themePageUnlocked ? '' : 'hidden' }}" data-theme-save-btn {{ $themePageUnlocked ? '' : 'disabled' }}>Save Theme Settings</button>
      <button type="button" class="btn btn-primary {{ $themePageUnlocked ? '' : 'hidden' }}" data-theme-activate-btn {{ $themePageUnlocked ? '' : 'disabled' }}>Activate Selected Theme</button>
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

  <div
    class="settings-layout mt-xl"
    data-theme-manager
    data-theme-ready="{{ $themePageUnlocked ? '1' : '0' }}"
    data-domain-value="{{ $activeDomain['value'] ?? '' }}"
    data-default-theme="{{ $activeDomain['default_theme_id'] ?? '' }}"
    data-allowed-themes="{{ implode(',', $allowedThemeIds) }}"
  >
    <section class="settings-main-column">
      <article class="card settings-panel">
        <div class="card-header">
          <div>
            <h3 class="card-title">Domain Link</h3>
            <p class="settings-panel-subtitle">Theme setup runs only with one existing domain.</p>
          </div>
          @if (! $hasThemeDomain)
            <span class="badge badge-warning">No Domain</span>
          @elseif ($themePageUnlocked)
            <span class="badge badge-success">Linked</span>
          @else
            <span class="badge badge-warning">Pending</span>
          @endif
        </div>

        @if ($hasThemeDomain)
          <div class="settings-field-grid">
            <div class="form-group">
              <label class="form-label">Linked Domain</label>
              <input type="text" class="form-input" value="{{ $activeDomain['value'] }}" readonly>
              <small class="form-help">Theme settings are scoped to this domain.</small>
            </div>

            <div class="form-group">
              <label class="form-label">Domain Status</label>
              <input type="text" class="form-input" value="{{ $activeDomain['status'] }}" readonly>
              <small class="form-help">To use another domain, change it from Domain setup page.</small>
            </div>
          </div>

          <div class="settings-content-card mt-md">
            <div class="settings-content-head">
              <strong>Single Domain Mode</strong>
              <span class="badge {{ $themePageUnlocked ? 'badge-success' : 'badge-warning' }}">{{ $themePageUnlocked ? 'Ready' : 'Blocked' }}</span>
            </div>
            <p>
              @if ($themePageUnlocked)
                Theme configuration is already loaded for <strong>{{ $activeDomain['value'] }}</strong>. No domain selection is needed.
              @else
                <strong>{{ $activeDomain['value'] }}</strong> is not connected yet. Theme settings stay locked until domain verification is complete.
              @endif
            </p>
            <small>THEME SETTINGS ALWAYS FOLLOW YOUR CURRENT ACTIVE DOMAIN</small>
          </div>
        @else
          <div class="settings-content-card">
            <div class="settings-content-head">
              <strong>Theme Access Blocked</strong>
              <span class="badge badge-warning">Domain Required</span>
            </div>
            <p>No domain found. Theme page cannot be used until you create one domain first.</p>
            <div class="settings-inline-actions">
              <a href="{{ route('admin.shop-settings.domain') }}" class="btn btn-primary btn-sm">Go To Domain Setup</a>
            </div>
          </div>
        @endif
      </article>

      @if (! $hasThemeDomain)
        <article class="card settings-panel mt-xl">
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
        <article class="card settings-panel mt-xl">
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

      <article class="card settings-panel mt-xl {{ $themePageUnlocked ? '' : 'hidden' }}" data-theme-domain-panel>
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

      <article class="card settings-panel mt-xl {{ $themePageUnlocked ? '' : 'hidden' }}" data-theme-domain-panel>
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
                class="form-select"
                data-theme-control
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

    <section class="settings-side-column">
      <article class="card settings-panel {{ $themePageUnlocked ? '' : 'hidden' }}" data-theme-domain-panel>
        <div class="card-header">
          <div>
            <h3 class="card-title">Behavior Controls</h3>
            <p class="settings-panel-subtitle">Advanced switches for storefront interaction and conversion behavior.</p>
          </div>
          <span class="badge badge-info">Advanced</span>
        </div>

        <div class="bot-settings-list">
          @foreach ($behaviorSettings as $setting)
            <label class="bot-setting-row">
              <div class="bot-setting-info">
                <h4>{{ $setting['label'] }}</h4>
                <p>{{ $setting['description'] }}</p>
              </div>
              <span class="bot-setting-state {{ $setting['enabled'] ? 'on' : 'off' }}">{{ $setting['enabled'] ? 'On' : 'Off' }}</span>
              <span class="bot-switch">
                <input
                  type="checkbox"
                  class="bot-toggle-input"
                  data-theme-control
                  {{ $setting['enabled'] ? 'checked' : '' }}
                  {{ $themePageUnlocked ? '' : 'disabled' }}
                >
                <span class="bot-switch-ui"></span>
              </span>
            </label>
          @endforeach
        </div>
      </article>

      <article class="card settings-panel mt-xl {{ $themePageUnlocked ? '' : 'hidden' }}" data-theme-domain-panel>
        <div class="card-header">
          <div>
            <h3 class="card-title">Checkout + CTA Style</h3>
            <p class="settings-panel-subtitle">Fine tune cart, checkout flow, and call-to-action styling.</p>
          </div>
          <span class="badge badge-primary">Theme Settings</span>
        </div>

        <div class="settings-theme-control-grid">
          @foreach ($checkoutControls as $control)
            <div class="form-group">
              <label class="form-label" for="themeCheckout_{{ $control['name'] }}">{{ $control['label'] }}</label>
              <select
                id="themeCheckout_{{ $control['name'] }}"
                class="form-select"
                data-theme-control
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

        if (typeof window.showSuccess === 'function') {
          window.showSuccess(`Theme settings saved for ${domainValue}.`);
        }
      });
    });
  </script>
@endsection
