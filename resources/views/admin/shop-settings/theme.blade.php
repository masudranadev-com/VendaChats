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

  <div
    class="settings-layout settings-layout-single settings-theme-layout"
    data-theme-manager
    data-theme-ready="{{ $themePageUnlocked ? '1' : '0' }}"
    data-domain-value="{{ $activeDomain['value'] ?? '' }}"
    data-default-theme="{{ $activeDomain['default_theme_id'] ?? '' }}"
    data-allowed-themes="{{ implode(',', $allowedThemeIds) }}"
    data-api-base-url="{{ $themeApiBaseUrl }}"
    data-refresh-token="{{ $themeRefreshToken }}"
  >
    <section class="settings-main-column">
      @if (! $hasThemeDomain)
        <article class="card settings-panel" data-theme-locked-panel>
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
        <article class="card settings-panel mt-md" data-theme-locked-panel>
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
            <h3 class="card-title">Theme Experience Summary</h3>
            <p class="settings-panel-subtitle">A compact view of the currently active theme, brand surface, and live storefront features.</p>
          </div>
          <span class="badge badge-success" data-theme-summary-status>{{ $themePageUnlocked ? 'Ready to publish' : 'Locked' }}</span>
        </div>

        <div class="settings-theme-summary-grid">
          <div class="settings-theme-summary-card">
            <span>Active Theme</span>
            <strong data-theme-summary-active>{{ $themeCatalog[0]['name'] ?? 'Not selected' }}</strong>
            <small data-theme-summary-domain>{{ $activeDomain['value'] ?? 'No domain connected' }}</small>
          </div>

          <div class="settings-theme-summary-card">
            <span>Brand Surface</span>
            <strong data-theme-summary-brand>Ocean Blue / Modern Sans</strong>
            <small data-theme-summary-layout>Contained layout with sticky navigation</small>
          </div>

          <div class="settings-theme-summary-card">
            <span>Enabled Smart Features</span>
            <strong data-theme-summary-features>8 features</strong>
            <small data-theme-summary-motion>Subtle Reveal motion</small>
          </div>

          <div class="settings-theme-summary-card">
            <span>Last Synced</span>
            <strong data-theme-summary-updated>Not synced yet</strong>
            <small data-theme-summary-status-note>{{ $activeDomain['status'] ?? 'Waiting for domain' }}</small>
          </div>
        </div>
      </article>

      <article class="card settings-panel mt-md {{ $themePageUnlocked ? '' : 'hidden' }}" data-theme-domain-panel>
        <div class="card-header">
          <div>
            <h3 class="card-title">Auto-Generated Theme Library</h3>
            <p class="settings-panel-subtitle">Users cannot create themes manually. The system curates and unlocks theme presets based on the connected domain mode.</p>
          </div>
          <span class="badge badge-info">Generated Themes</span>
        </div>

        <div class="settings-theme-list">
          @foreach ($themeCatalog as $theme)
            <div class="settings-theme-item" data-theme-item data-theme-id="{{ $theme['id'] }}" data-theme-name="{{ $theme['name'] }}">
              <div
                class="settings-theme-preview"
                style="--theme-preview-from: {{ $theme['preview_from'] }}; --theme-preview-to: {{ $theme['preview_to'] }};"
              ></div>
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
                  <span>{{ $theme['mode'] }}</span>
                </div>
                <div class="settings-theme-highlights">
                  @foreach ($theme['highlights'] as $highlight)
                    <span>{{ $highlight }}</span>
                  @endforeach
                </div>
                <label class="settings-theme-pick">
                  <input
                    type="radio"
                    name="active_theme"
                    value="{{ $theme['id'] }}"
                    data-theme-radio
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
            <p class="settings-panel-subtitle">Configure the storefront surface professionally across branding, layout, navigation, and conversion behavior.</p>
          </div>
          <span class="badge badge-primary">Theme Only</span>
        </div>

        <div class="settings-theme-control-stack">
          @foreach ($themeControlGroups as $group)
            <section class="settings-theme-group">
              <div class="settings-theme-group-header">
                <h4>{{ $group['title'] }}</h4>
                <p>{{ $group['subtitle'] }}</p>
              </div>

              <div class="settings-field-grid">
                @foreach ($group['controls'] as $control)
                  <div class="form-group">
                    <label class="form-label" for="themeControl_{{ $control['name'] }}">{{ $control['label'] }}</label>
                    <select
                      id="themeControl_{{ $control['name'] }}"
                      name="{{ $control['name'] }}"
                      class="form-select"
                      data-theme-control-name="{{ $control['name'] }}"
                      {{ $themePageUnlocked ? '' : 'disabled' }}
                    >
                      @foreach ($control['options'] as $option)
                        <option value="{{ $option['value'] }}" {{ $control['value'] === $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                      @endforeach
                    </select>
                    <small class="form-help">{{ $control['help'] }}</small>
                  </div>
                @endforeach
              </div>
            </section>
          @endforeach
        </div>
      </article>

      <article class="card settings-panel mt-md {{ $themePageUnlocked ? '' : 'hidden' }}" data-theme-domain-panel>
        <div class="card-header">
          <div>
            <h3 class="card-title">Storefront Feature Switches</h3>
            <p class="settings-panel-subtitle">Enable or disable high-impact storefront helpers without changing the selected preset.</p>
          </div>
          <span class="badge badge-info">Experience Controls</span>
        </div>

        <div class="settings-theme-toggle-grid">
          @foreach ($themeFeatureToggles as $toggle)
            <div class="settings-theme-toggle-card">
              <div>
                <strong>{{ $toggle['label'] }}</strong>
                <p>{{ $toggle['help'] }}</p>
              </div>

              <label class="bot-switch">
                <input
                  type="checkbox"
                  class="bot-toggle-input"
                  data-theme-toggle-name="{{ $toggle['name'] }}"
                  {{ $toggle['enabled'] ? 'checked' : '' }}
                  {{ $themePageUnlocked ? '' : 'disabled' }}
                >
                <span class="bot-switch-ui"></span>
              </label>
            </div>
          @endforeach
        </div>
      </article>
    </section>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const manager = document.querySelector('[data-theme-manager]');
      if (!(manager instanceof HTMLElement)) {
        return;
      }

      const text = (value) => String(value ?? '').trim();
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
      const showWarning = (message) => {
        if (typeof window.showWarning === 'function') {
          window.showWarning(message);
          return;
        }

        window.alert(message);
      };

      const activateButton = document.querySelector('[data-theme-activate-btn]');
      const saveButton = document.querySelector('[data-theme-save-btn]');
      const domainPanels = Array.from(manager.querySelectorAll('[data-theme-domain-panel]'));
      const lockedPanels = Array.from(manager.querySelectorAll('[data-theme-locked-panel]'));
      const themeRows = Array.from(manager.querySelectorAll('[data-theme-item]'));
      const themeRadios = Array.from(manager.querySelectorAll('[data-theme-radio]'));
      const selectControls = Array.from(manager.querySelectorAll('[data-theme-control-name]'));
      const toggleControls = Array.from(manager.querySelectorAll('[data-theme-toggle-name]'));
      const summaryActive = manager.querySelector('[data-theme-summary-active]');
      const summaryDomain = manager.querySelector('[data-theme-summary-domain]');
      const summaryBrand = manager.querySelector('[data-theme-summary-brand]');
      const summaryLayout = manager.querySelector('[data-theme-summary-layout]');
      const summaryFeatures = manager.querySelector('[data-theme-summary-features]');
      const summaryMotion = manager.querySelector('[data-theme-summary-motion]');
      const summaryUpdated = manager.querySelector('[data-theme-summary-updated]');
      const summaryStatus = manager.querySelector('[data-theme-summary-status]');
      const summaryStatusNote = manager.querySelector('[data-theme-summary-status-note]');

      if (!(activateButton instanceof HTMLButtonElement) || !(saveButton instanceof HTMLButtonElement)) {
        return;
      }

      const buttonLabels = new Map([
        [activateButton, text(activateButton.textContent)],
        [saveButton, text(saveButton.textContent)],
      ]);

      const state = {
        ready: manager.dataset.themeReady === '1',
        domainValue: text(manager.dataset.domainValue),
        defaultThemeId: text(manager.dataset.defaultTheme),
        availableThemeIds: text(manager.dataset.allowedThemes)
          .split(',')
          .map((value) => value.trim())
          .filter((value) => value.length > 0),
        domainStatus: '',
      };

      const themeNameById = new Map(themeRows.map((row) => [
        text(row.getAttribute('data-theme-id')),
        text(row.getAttribute('data-theme-name')),
      ]));

      function selectedThemeId() {
        const selected = manager.querySelector('[data-theme-radio]:checked');
        return selected instanceof HTMLInputElement && !selected.disabled
          ? text(selected.value)
          : '';
      }

      function getSelect(name) {
        return manager.querySelector(`[data-theme-control-name="${name}"]`);
      }

      function getToggle(name) {
        return manager.querySelector(`[data-theme-toggle-name="${name}"]`);
      }

      function setSelectValue(name, value) {
        const field = getSelect(name);
        if (!(field instanceof HTMLSelectElement)) {
          return;
        }

        const nextValue = text(value);
        const option = Array.from(field.options).find((item) => item.value === nextValue);
        if (option) {
          field.value = nextValue;
        }
      }

      function setToggleValue(name, enabled) {
        const field = getToggle(name);
        if (field instanceof HTMLInputElement) {
          field.checked = Boolean(enabled);
        }
      }

      function currentSelectLabel(name) {
        const field = getSelect(name);
        if (!(field instanceof HTMLSelectElement)) {
          return '';
        }

        const option = field.options[field.selectedIndex];
        return option ? text(option.textContent) : '';
      }

      function enabledFeaturesCount() {
        return toggleControls.filter((control) => control instanceof HTMLInputElement && control.checked).length;
      }

      function formatTimestamp(value) {
        const raw = text(value);
        if (!raw) {
          return 'Not synced yet';
        }

        const parsed = new Date(raw);
        if (Number.isNaN(parsed.getTime())) {
          return raw;
        }

        return new Intl.DateTimeFormat(undefined, {
          dateStyle: 'medium',
          timeStyle: 'short',
        }).format(parsed);
      }

      function setActionBusy(button, busy, busyLabel) {
        if (!(button instanceof HTMLButtonElement)) {
          return;
        }

        button.disabled = busy || !state.ready;
        button.textContent = busy
          ? busyLabel
          : (buttonLabels.get(button) || button.textContent);
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

          const isActive = text(row.dataset.themeId) === themeId;
          row.classList.toggle('is-active', isActive);
          badge.textContent = isActive ? 'Active' : 'Available';
          badge.classList.toggle('badge-success', isActive);
          badge.classList.toggle('badge-info', !isActive);
        });
      }

      function applyThemeAvailability() {
        themeRows.forEach((row) => {
          if (!(row instanceof HTMLElement)) {
            return;
          }

          const themeId = text(row.dataset.themeId);
          const visible = state.availableThemeIds.length === 0 || state.availableThemeIds.includes(themeId);
          row.classList.toggle('hidden', !visible);

          const radio = row.querySelector('[data-theme-radio]');
          if (radio instanceof HTMLInputElement) {
            radio.disabled = !state.ready || !visible;
            if (!visible) {
              radio.checked = false;
            }
          }
        });

        const visibleRadios = themeRows
          .filter((row) => row instanceof HTMLElement && !row.classList.contains('hidden'))
          .map((row) => row.querySelector('[data-theme-radio]'))
          .filter((radio) => radio instanceof HTMLInputElement && !radio.disabled);

        if (!visibleRadios.length) {
          updateActiveThemeBadge('');
          return;
        }

        const preferred = visibleRadios.find((radio) => radio.value === state.defaultThemeId);
        const selected = themeRows
          .map((row) => row.querySelector('[data-theme-radio]:checked'))
          .find((radio) => radio instanceof HTMLInputElement && !radio.disabled);
        const target = selected || preferred || visibleRadios[0];

        visibleRadios.forEach((radio) => {
          radio.checked = radio === target;
        });

        updateActiveThemeBadge(text(target.value));
      }

      function setControlsEnabled(enabled) {
        selectControls.forEach((control) => {
          if (control instanceof HTMLSelectElement) {
            control.disabled = !enabled;
          }
        });

        toggleControls.forEach((control) => {
          if (control instanceof HTMLInputElement) {
            control.disabled = !enabled;
          }
        });

        themeRadios.forEach((radio) => {
          if (radio instanceof HTMLInputElement) {
            const parentRow = radio.closest('[data-theme-item]');
            const rowHidden = parentRow instanceof HTMLElement && parentRow.classList.contains('hidden');
            radio.disabled = !enabled || rowHidden;
          }
        });

        activateButton.disabled = !enabled;
        saveButton.disabled = !enabled;
      }

      function syncPanelVisibility() {
        domainPanels.forEach((panel) => {
          if (panel instanceof HTMLElement) {
            panel.classList.toggle('hidden', !state.ready);
          }
        });

        lockedPanels.forEach((panel) => {
          if (panel instanceof HTMLElement) {
            panel.classList.toggle('hidden', state.ready);
          }
        });

        activateButton.classList.toggle('hidden', !state.ready);
        saveButton.classList.toggle('hidden', !state.ready);
      }

      function syncSummary(updatedAt = '') {
        const themeId = selectedThemeId();
        const themeName = themeNameById.get(themeId) || 'Not selected';
        const brandSurface = `${currentSelectLabel('color_preset') || 'Preset'} / ${currentSelectLabel('typography_pack') || 'Typography'}`;
        const layoutSurface = `${currentSelectLabel('content_width') || 'Layout'} layout with ${currentSelectLabel('navigation_behavior') || 'navigation'}`.trim();
        const featureCount = enabledFeaturesCount();
        const motion = currentSelectLabel('animation_style') || 'Motion';

        if (summaryActive instanceof HTMLElement) {
          summaryActive.textContent = themeName;
        }
        if (summaryDomain instanceof HTMLElement) {
          summaryDomain.textContent = state.domainValue || 'No domain connected';
        }
        if (summaryBrand instanceof HTMLElement) {
          summaryBrand.textContent = brandSurface;
        }
        if (summaryLayout instanceof HTMLElement) {
          summaryLayout.textContent = layoutSurface;
        }
        if (summaryFeatures instanceof HTMLElement) {
          summaryFeatures.textContent = `${featureCount} features`;
        }
        if (summaryMotion instanceof HTMLElement) {
          summaryMotion.textContent = `${motion} motion`;
        }
        if (summaryUpdated instanceof HTMLElement) {
          summaryUpdated.textContent = formatTimestamp(updatedAt);
        }
        if (summaryStatus instanceof HTMLElement) {
          summaryStatus.textContent = state.ready ? 'Ready to publish' : 'Locked';
          summaryStatus.classList.toggle('badge-success', state.ready);
          summaryStatus.classList.toggle('badge-warning', !state.ready);
        }
        if (summaryStatusNote instanceof HTMLElement) {
          summaryStatusNote.textContent = state.domainStatus || (state.ready ? 'Domain connected' : 'Waiting for domain');
        }
      }

      function collectPayload() {
        const themeId = selectedThemeId();
        if (!themeId) {
          return null;
        }

        return {
          active_theme_id: themeId,
          store_currency: text(getSelect('store_currency')?.value),
          color_preset: text(getSelect('color_preset')?.value),
          typography_pack: text(getSelect('typography_pack')?.value),
          section_spacing: text(getSelect('section_spacing')?.value),
          corner_style: text(getSelect('corner_style')?.value),
          grid_density: text(getSelect('grid_density')?.value),
          image_ratio_mode: text(getSelect('image_ratio_mode')?.value),
          hero_layout: text(getSelect('hero_layout')?.value),
          header_style: text(getSelect('header_style')?.value),
          navigation_behavior: text(getSelect('navigation_behavior')?.value),
          product_card_style: text(getSelect('product_card_style')?.value),
          cta_style: text(getSelect('cta_style')?.value),
          checkout_style: text(getSelect('checkout_style')?.value),
          content_width: text(getSelect('content_width')?.value),
          animation_style: text(getSelect('animation_style')?.value),
          mobile_nav_style: text(getSelect('mobile_nav_style')?.value),
          announcement_bar_enabled: Boolean(getToggle('announcement_bar_enabled')?.checked),
          sticky_add_to_cart_enabled: Boolean(getToggle('sticky_add_to_cart_enabled')?.checked),
          quick_view_enabled: Boolean(getToggle('quick_view_enabled')?.checked),
          wishlist_enabled: Boolean(getToggle('wishlist_enabled')?.checked),
          show_stock_badges: Boolean(getToggle('show_stock_badges')?.checked),
          show_sale_badges: Boolean(getToggle('show_sale_badges')?.checked),
          show_trust_badges: Boolean(getToggle('show_trust_badges')?.checked),
          show_breadcrumbs: Boolean(getToggle('show_breadcrumbs')?.checked),
          enable_product_hover: Boolean(getToggle('enable_product_hover')?.checked),
          enable_scroll_reveal: Boolean(getToggle('enable_scroll_reveal')?.checked),
        };
      }

      function applySettings(settings) {
        if (!settings || typeof settings !== 'object') {
          return;
        }

        const activeThemeId = text(settings.active_theme_id);
        if (activeThemeId) {
          const targetRadio = themeRows
            .map((row) => row.querySelector('[data-theme-radio]'))
            .find((radio) => radio instanceof HTMLInputElement && radio.value === activeThemeId);
          if (targetRadio instanceof HTMLInputElement && !targetRadio.disabled) {
            targetRadio.checked = true;
          }
        }

        setSelectValue('store_currency', settings.store_currency);
        setSelectValue('color_preset', settings.color_preset);
        setSelectValue('typography_pack', settings.typography_pack);
        setSelectValue('section_spacing', settings.section_spacing);
        setSelectValue('corner_style', settings.corner_style);
        setSelectValue('grid_density', settings.grid_density);
        setSelectValue('image_ratio_mode', settings.image_ratio_mode);
        setSelectValue('hero_layout', settings.hero_layout);
        setSelectValue('header_style', settings.header_style);
        setSelectValue('navigation_behavior', settings.navigation_behavior);
        setSelectValue('product_card_style', settings.product_card_style);
        setSelectValue('cta_style', settings.cta_style);
        setSelectValue('checkout_style', settings.checkout_style);
        setSelectValue('content_width', settings.content_width);
        setSelectValue('animation_style', settings.animation_style);
        setSelectValue('mobile_nav_style', settings.mobile_nav_style);

        setToggleValue('announcement_bar_enabled', settings.announcement_bar_enabled);
        setToggleValue('sticky_add_to_cart_enabled', settings.sticky_add_to_cart_enabled);
        setToggleValue('quick_view_enabled', settings.quick_view_enabled);
        setToggleValue('wishlist_enabled', settings.wishlist_enabled);
        setToggleValue('show_stock_badges', settings.show_stock_badges);
        setToggleValue('show_sale_badges', settings.show_sale_badges);
        setToggleValue('show_trust_badges', settings.show_trust_badges);
        setToggleValue('show_breadcrumbs', settings.show_breadcrumbs);
        setToggleValue('enable_product_hover', settings.enable_product_hover);
        setToggleValue('enable_scroll_reveal', settings.enable_scroll_reveal);

        updateActiveThemeBadge(selectedThemeId());
        syncSummary(settings.updated_at);
      }

      function applyRemotePayload(payload) {
        if (!payload || typeof payload !== 'object') {
          return;
        }

        state.ready = Boolean(payload.theme_ready);
        state.domainValue = text(payload.current_domain) || state.domainValue;
        state.domainStatus = text(payload.current_domain_status) || state.domainStatus;
        state.defaultThemeId = text(payload.default_theme_id) || state.defaultThemeId;
        state.availableThemeIds = Array.isArray(payload.available_theme_ids)
          ? payload.available_theme_ids.map((item) => text(item)).filter(Boolean)
          : state.availableThemeIds;

        applyThemeAvailability();
        syncPanelVisibility();
        setControlsEnabled(state.ready);
        applySettings(payload.settings || {});
      }

      async function loadThemeSettings() {
        const apiBaseUrl = text(manager.dataset.apiBaseUrl);
        const refreshToken = text(manager.dataset.refreshToken || window.API?.getToken?.());

        if (!apiBaseUrl || !refreshToken || !window.API?.Admin?.ThemeSettings?.get) {
          if (state.ready) {
            showWarning('Theme settings API is not configured.');
          }
          return;
        }

        try {
          const payload = await window.API.Admin.ThemeSettings.get({
            apiBaseUrl,
            refreshToken,
          });
          applyRemotePayload(payload);
        } catch (error) {
          showError(error?.message || 'Unable to load theme settings.');
        }
      }

      async function persistThemeSettings(action) {
        if (!state.ready) {
          showWarning('Theme page is locked until the connected domain is verified.');
          return;
        }

        const payload = collectPayload();
        if (!payload) {
          showWarning('Select one generated theme to continue.');
          return;
        }

        const apiBaseUrl = text(manager.dataset.apiBaseUrl);
        const refreshToken = text(manager.dataset.refreshToken || window.API?.getToken?.());
        if (!apiBaseUrl || !refreshToken || !window.API?.Admin?.ThemeSettings?.update) {
          showError('Theme settings API is not configured.');
          return;
        }

        setActionBusy(action === 'activate' ? activateButton : saveButton, true, action === 'activate' ? 'Activating...' : 'Saving...');
        setActionBusy(action === 'activate' ? saveButton : activateButton, true, action === 'activate' ? 'Saving...' : 'Activating...');

        try {
          const response = await window.API.Admin.ThemeSettings.update({
            apiBaseUrl,
            refreshToken,
            payload,
          });
          const data = response?.data || response;
          applyRemotePayload(data);
          showSuccess(
            action === 'activate'
              ? `Theme activated for ${state.domainValue}.`
              : `Theme settings saved for ${state.domainValue}.`
          );
        } catch (error) {
          showError(error?.message || 'Unable to update theme settings.');
        } finally {
          setActionBusy(activateButton, false, 'Activating...');
          setActionBusy(saveButton, false, 'Saving...');
        }
      }

      applyThemeAvailability();
      syncPanelVisibility();
      setControlsEnabled(state.ready);
      syncSummary();

      themeRadios.forEach((radio) => {
        if (!(radio instanceof HTMLInputElement)) {
          return;
        }

        radio.addEventListener('change', () => {
          updateActiveThemeBadge(selectedThemeId());
          syncSummary();
        });
      });

      selectControls.forEach((control) => {
        if (control instanceof HTMLSelectElement) {
          control.addEventListener('change', () => syncSummary());
        }
      });

      toggleControls.forEach((control) => {
        if (control instanceof HTMLInputElement) {
          control.addEventListener('change', () => syncSummary());
        }
      });

      activateButton.addEventListener('click', () => {
        persistThemeSettings('activate');
      });

      saveButton.addEventListener('click', () => {
        persistThemeSettings('save');
      });

      loadThemeSettings();
    });
  </script>
@endsection
