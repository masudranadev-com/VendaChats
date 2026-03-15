@extends('admin.master')

@section('title', $title)

@section('admin.content')
  @php
    $timezoneSummary = $localeOptions['timezones'][$selectedLocale['timezone']] ?? $selectedLocale['timezone'];
    $adminLanguageSummary = $localeOptions['admin_languages'][$selectedLocale['admin_language']] ?? $selectedLocale['admin_language'];
    $websiteLanguageSummary = $localeOptions['website_languages'][$selectedLocale['website_language']] ?? $selectedLocale['website_language'];
  @endphp

  <div
    class="shop-setup-shell"
    data-shop-settings-setup
    data-continue-url="{{ route('admin.onboardingContinue') }}"
    data-csrf-token="{{ csrf_token() }}"
    data-products-api-base-url="{{ $productsApiBaseUrl }}"
    data-products-refresh-token="{{ $productsRefreshToken }}"
    data-current-product-type="{{ $selectedProductType }}"
  >
    <div class="page-header settings-page-header">
      <div>
        <h1 class="page-title">{{ $title }}</h1>
        <p class="page-subtitle">{{ $subtitle }}</p>
      </div>
    </div>

    <section class="settings-section-intro shop-setup-intro">
      <span class="shop-setup-kicker">{{ $sectionHeading }}</span>
      <h3>Revisit your onboarding choices</h3>
      <p>{{ $sectionSubtitle }}</p>
      <strong class="shop-setup-storefront">Current storefront: {{ $storefrontUrl }}</strong>
    </section>

    <div class="settings-layout mt-md shop-setup-layout">
      <section class="settings-main-column">
        <article class="card settings-panel shop-setup-panel">
          <div class="card-header">
            <div>
              <span class="shop-setup-section-index">1. Choose your product type</span>
              <h3 class="card-title">Choose your product type</h3>
              <p class="settings-panel-subtitle">This is the same product-type choice used in first-time setup, and users can change it here any time.</p>
            </div>
            <span class="badge badge-primary" data-shop-current-product-badge>{{ $selectedProductTypeLabel }}</span>
          </div>

          <form data-shop-settings-form="product">
            <div class="admin-onboarding-choice-grid shop-setup-choice-grid">
              @foreach ($productTypeChoices as $choice)
                <label class="admin-onboarding-choice-card {{ $choice['value'] === $selectedProductType ? 'is-active' : '' }}" data-shop-product-card>
                  <input
                    type="radio"
                    name="shopSettingsProductType"
                    value="{{ $choice['value'] }}"
                    {{ $choice['value'] === $selectedProductType ? 'checked' : '' }}
                    data-shop-product-input
                  >
                  <span class="admin-onboarding-choice-badge">{{ $choice['badge'] }}</span>
                  <strong>{{ $choice['label'] }}</strong>
                  <p>{{ $choice['description'] }}</p>
                  <small>{{ $choice['note'] }}</small>
                </label>
              @endforeach
            </div>

            <div class="shop-setup-actions">
              <p class="shop-setup-note">If products already exist, switching the store type will require a backup and full product cleanup before the new mode can be activated.</p>
              <div class="shop-setup-action-group">
                <span class="shop-setup-save-state" data-shop-save-state="product">Current: {{ $selectedProductTypeLabel }}</span>
                <button type="submit" class="btn btn-primary" data-shop-save-button="product">Save product type</button>
              </div>
            </div>
          </form>
        </article>

        <article class="card settings-panel mt-md shop-setup-panel">
          <div class="card-header">
            <div>
              <span class="shop-setup-section-index">2. Locale</span>
              <h3 class="card-title">Locale</h3>
              <p class="settings-panel-subtitle">Update the timezone and default languages that operators and customers should see first.</p>
            </div>
            <span class="badge badge-info">Locale defaults</span>
          </div>

          <form data-shop-settings-form="locale">
            <div class="admin-onboarding-form-grid shop-setup-form-grid">
              <div class="form-group admin-onboarding-field-span-2">
                <label class="form-label" for="shopSettingsTimezone">Timezone</label>
                <select id="shopSettingsTimezone" class="form-select" data-shop-locale-timezone>
                  @foreach ($localeOptions['timezones'] as $value => $label)
                    <option value="{{ $value }}" {{ $value === $selectedLocale['timezone'] ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
                </select>
              </div>

              <div class="form-group">
                <label class="form-label" for="shopSettingsAdminLanguage">Admin panel language</label>
                <select id="shopSettingsAdminLanguage" class="form-select" data-shop-locale-admin-language>
                  @foreach ($localeOptions['admin_languages'] as $value => $label)
                    <option value="{{ $value }}" {{ $value === $selectedLocale['admin_language'] ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
                </select>
              </div>

              <div class="form-group">
                <label class="form-label" for="shopSettingsWebsiteLanguage">Website language</label>
                <select id="shopSettingsWebsiteLanguage" class="form-select" data-shop-locale-website-language>
                  @foreach ($localeOptions['website_languages'] as $value => $label)
                    <option value="{{ $value }}" {{ $value === $selectedLocale['website_language'] ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="shop-setup-actions">
              <p class="shop-setup-note">Locale changes update the saved admin and storefront defaults for future sessions.</p>
              <div class="shop-setup-action-group">
                <span class="shop-setup-save-state" data-shop-save-state="locale">{{ $timezoneSummary }} / {{ $adminLanguageSummary }} admin / {{ $websiteLanguageSummary }} site</span>
                <button type="submit" class="btn btn-primary" data-shop-save-button="locale">Save locale</button>
              </div>
            </div>
          </form>
        </article>
      </section>

      <aside class="settings-side-column">
        <article class="card settings-panel shop-setup-panel">
          <div class="card-header">
            <div>
              <span class="shop-setup-section-index">3. Shop Settings Sections</span>
              <h3 class="card-title">Shop Settings Sections</h3>
              <p class="settings-panel-subtitle">Move into the next shop settings pages after first-time setup choices are updated.</p>
            </div>
            <span class="badge badge-success">Shortcuts</span>
          </div>

          @foreach ($shopSections as $section)
            <div class="settings-content-card">
              <div class="settings-content-head">
                <strong>{{ $section['label'] }}</strong>
                <span class="badge {{ $section['badge'] }}">{{ $section['status'] }}</span>
              </div>
              <p>{{ $section['note'] }}</p>
              <a href="{{ route($section['route']) }}" class="btn btn-secondary btn-sm">Open {{ $section['label'] }}</a>
            </div>
          @endforeach
        </article>
      </aside>
    </div>
  </div>

  <div class="modal-overlay" id="shopProductTypeSwitchModal" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="shopProductTypeSwitchTitle">
      <div class="modal-header">
        <h3 class="modal-title" id="shopProductTypeSwitchTitle">Switch Product Type</h3>
        <button type="button" class="modal-close" data-shop-switch-close aria-label="Close">x</button>
      </div>
      <div class="modal-body">
        <p data-shop-switch-message>
          To switch the store product type, you need to delete the old product data first.
        </p>

        <div class="shop-setup-switch-summary">
          <article class="shop-setup-switch-summary-card">
            <span>Current type</span>
            <strong data-shop-switch-current-type>{{ $selectedProductTypeLabel }}</strong>
          </article>
          <article class="shop-setup-switch-summary-card">
            <span>New type</span>
            <strong data-shop-switch-next-type>{{ $selectedProductTypeLabel }}</strong>
          </article>
          <article class="shop-setup-switch-summary-card">
            <span>Products to remove</span>
            <strong data-shop-switch-total-products>0</strong>
          </article>
        </div>

        <section class="shop-setup-switch-backup">
          <strong>Backup and delete</strong>
          <p>Download a meaningful JSON backup for all products first. After the backup is complete, you can delete the old products and activate the new type.</p>
          <div class="shop-setup-switch-backup-actions">
            <button type="button" class="btn btn-secondary" data-shop-switch-backup-button>Backup Products</button>
            <span class="shop-setup-switch-backup-status" data-shop-switch-backup-status>Backup required before switching.</span>
          </div>
        </section>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-shop-switch-close>Cancel</button>
        <button type="button" class="btn btn-danger" data-shop-switch-confirm-button disabled>Delete Old Products &amp; Switch</button>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const shell = document.querySelector('[data-shop-settings-setup]');
      if (!(shell instanceof HTMLElement)) {
        return;
      }

      const continueUrl = String(shell.dataset.continueUrl || '').trim();
      const csrfToken = String(shell.dataset.csrfToken || '').trim();
      const productsApiBaseUrl = String(shell.dataset.productsApiBaseUrl || '').trim();
      const refreshToken = String(shell.dataset.productsRefreshToken || window.localStorage.getItem('refresh_token') || '').trim();
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

      const productForm = shell.querySelector('[data-shop-settings-form="product"]');
      const localeForm = shell.querySelector('[data-shop-settings-form="locale"]');
      const productInputs = Array.from(shell.querySelectorAll('[data-shop-product-input]'));
      const productCards = Array.from(shell.querySelectorAll('[data-shop-product-card]'));
      const productState = shell.querySelector('[data-shop-save-state="product"]');
      const localeState = shell.querySelector('[data-shop-save-state="locale"]');
      const productBadge = shell.querySelector('[data-shop-current-product-badge]');
      const timezoneSelect = shell.querySelector('[data-shop-locale-timezone]');
      const adminLanguageSelect = shell.querySelector('[data-shop-locale-admin-language]');
      const websiteLanguageSelect = shell.querySelector('[data-shop-locale-website-language]');
      const switchModal = document.getElementById('shopProductTypeSwitchModal');
      const switchMessage = switchModal?.querySelector('[data-shop-switch-message]');
      const switchCurrentType = switchModal?.querySelector('[data-shop-switch-current-type]');
      const switchNextType = switchModal?.querySelector('[data-shop-switch-next-type]');
      const switchTotalProducts = switchModal?.querySelector('[data-shop-switch-total-products]');
      const switchBackupButton = switchModal?.querySelector('[data-shop-switch-backup-button]');
      const switchBackupStatus = switchModal?.querySelector('[data-shop-switch-backup-status]');
      const switchConfirmButton = switchModal?.querySelector('[data-shop-switch-confirm-button]');

      if (
        !(productForm instanceof HTMLFormElement)
        || !(localeForm instanceof HTMLFormElement)
        || !(timezoneSelect instanceof HTMLSelectElement)
        || !(adminLanguageSelect instanceof HTMLSelectElement)
        || !(websiteLanguageSelect instanceof HTMLSelectElement)
      ) {
        return;
      }

      let currentProductType = text(shell.dataset.currentProductType || '').toLowerCase() || 'physical';
      let totalProducts = 0;
      let productsSummaryKnown = false;
      let backupCompletedForType = '';
      let pendingProductType = '';

      const buttonLabels = new Map();
      shell.querySelectorAll('[data-shop-save-button]').forEach((button) => {
        if (button instanceof HTMLButtonElement) {
          buttonLabels.set(button, text(button.textContent));
        }
      });

      const setButtonState = (button, label, disabled) => {
        if (!(button instanceof HTMLButtonElement)) {
          return;
        }

        button.disabled = disabled;
        button.textContent = label;
      };

      const selectedProductInput = () => productInputs.find((input) => input instanceof HTMLInputElement && input.checked) || null;

      const selectedProductLabel = () => {
        const input = selectedProductInput();
        if (!(input instanceof HTMLInputElement)) {
          return '';
        }

        const card = input.closest('[data-shop-product-card]');
        const labelNode = card instanceof HTMLElement ? card.querySelector('strong') : null;

        return text(labelNode?.textContent || input.value);
      };
      const productTypeLabel = (value) => {
        const labels = {
          physical: 'Physical',
          downloadable: 'Downloadable',
          subscription: 'Subscription',
        };
        return labels[text(value).toLowerCase()] || text(value);
      };

      const localeSummary = () => {
        const timezoneLabel = text(timezoneSelect.options[timezoneSelect.selectedIndex]?.textContent || timezoneSelect.value);
        const adminLabel = text(adminLanguageSelect.options[adminLanguageSelect.selectedIndex]?.textContent || adminLanguageSelect.value);
        const websiteLabel = text(websiteLanguageSelect.options[websiteLanguageSelect.selectedIndex]?.textContent || websiteLanguageSelect.value);

        return `${timezoneLabel} / ${adminLabel} admin / ${websiteLabel} site`;
      };

      const renderProductCards = () => {
        productCards.forEach((card) => {
          if (!(card instanceof HTMLElement)) {
            return;
          }

          const input = card.querySelector('[data-shop-product-input]');
          const isActive = input instanceof HTMLInputElement && input.checked;
          card.classList.toggle('is-active', isActive);
        });
      };

      const postUpdate = async (payload) => {
        if (!continueUrl) {
          throw new Error('Save endpoint is not configured.');
        }

        if (!csrfToken) {
          throw new Error('Missing CSRF token. Reload the page and try again.');
        }

        const response = await fetch(continueUrl, {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify(payload),
        });

        const contentType = response.headers.get('content-type') || '';
        const isJson = contentType.includes('application/json');
        const result = isJson ? await response.json().catch(() => ({})) : {};

        if (!response.ok || !isJson) {
          throw new Error(text(result.error || result.message) || 'Unable to save right now.');
        }

        return result;
      };
      const fetchProductsSummary = async () => {
        if (!window.API?.Admin?.Products?.list || !productsApiBaseUrl || !refreshToken) {
          totalProducts = 0;
          productsSummaryKnown = false;
          return false;
        }

        try {
          const response = await window.API.Admin.Products.list({
            apiBaseUrl: productsApiBaseUrl,
            refreshToken,
            page: 1,
            perPage: 1,
            timeoutMs: 12000,
          });

          totalProducts = Number(response?.products?.pagination?.total ?? response?.products?.pagination_info?.total ?? 0) || 0;
          productsSummaryKnown = true;
          return true;
        } catch (error) {
          totalProducts = 0;
          productsSummaryKnown = false;
          return false;
        }
      };
      const downloadBackupFile = (payload) => {
        const fileName = `products-backup-${new Date().toISOString().replace(/[:.]/g, '-')}.json`;
        const blob = new Blob([JSON.stringify(payload, null, 2)], { type: 'application/json' });
        const downloadUrl = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.setTimeout(() => window.URL.revokeObjectURL(downloadUrl), 500);
      };
      const setSwitchConfirmState = (enabled) => {
        if (!(switchConfirmButton instanceof HTMLButtonElement)) {
          return;
        }

        switchConfirmButton.disabled = !enabled;
      };
      const closeSwitchModal = () => {
        if (!(switchModal instanceof HTMLElement)) {
          return;
        }

        switchModal.classList.remove('active');
        switchModal.setAttribute('aria-hidden', 'true');
      };
      const openSwitchModal = (nextType) => {
        if (!(switchModal instanceof HTMLElement)) {
          return;
        }

        pendingProductType = nextType;
        const nextLabel = productTypeLabel(nextType);
        const currentLabel = productTypeLabel(currentProductType);
        const requiresBackup = totalProducts > 0;

        if (switchMessage instanceof HTMLElement) {
          switchMessage.textContent = requiresBackup
            ? 'To switch the product type, you must delete all old product data first. Backup the current products, then continue with delete and switch.'
            : 'No products are currently stored, so you can switch the product type immediately.';
        }
        if (switchCurrentType instanceof HTMLElement) {
          switchCurrentType.textContent = currentLabel;
        }
        if (switchNextType instanceof HTMLElement) {
          switchNextType.textContent = nextLabel;
        }
        if (switchTotalProducts instanceof HTMLElement) {
          switchTotalProducts.textContent = String(totalProducts);
        }
        if (switchBackupStatus instanceof HTMLElement) {
          switchBackupStatus.textContent = requiresBackup
            ? (backupCompletedForType === nextType ? 'Backup downloaded. You can switch now.' : 'Backup required before switching.')
            : 'No backup needed because no products were found.';
        }
        if (switchBackupButton instanceof HTMLButtonElement) {
          switchBackupButton.disabled = !requiresBackup;
        }

        setSwitchConfirmState(!requiresBackup || backupCompletedForType === nextType);
        switchModal.classList.add('active');
        switchModal.setAttribute('aria-hidden', 'false');
      };
      const saveProductType = async (nextType) => {
        await postUpdate({
          type: 'product',
          data: {
            product_type: text(nextType),
          },
        });

        currentProductType = text(nextType).toLowerCase();
        shell.dataset.currentProductType = currentProductType;

        const label = productTypeLabel(currentProductType);
        if (productState instanceof HTMLElement) {
          productState.textContent = `Current: ${label}`;
        }
        if (productBadge instanceof HTMLElement) {
          productBadge.textContent = label;
        }
      };

      productInputs.forEach((input) => {
        if (!(input instanceof HTMLInputElement)) {
          return;
        }

        input.addEventListener('change', () => {
          renderProductCards();
        });
      });

      productForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const submitButton = productForm.querySelector('[data-shop-save-button="product"]');
        const productInput = selectedProductInput();
        if (!(productInput instanceof HTMLInputElement)) {
          showError('Select a product type to continue.');
          return;
        }

        const nextType = text(productInput.value).toLowerCase();
        if (nextType !== currentProductType) {
          const loaded = await fetchProductsSummary();
          if (!loaded && !productsSummaryKnown) {
            showError('Could not verify the existing product count. Please try again.');
            return;
          }
          openSwitchModal(nextType);
          return;
        }

        setButtonState(submitButton, 'Saving...', true);

        try {
          await saveProductType(nextType);

          showSuccess('Product type updated.');
        } catch (error) {
          showError(error instanceof Error ? error.message : 'Failed to update product type.');
        } finally {
          setButtonState(submitButton, buttonLabels.get(submitButton) || 'Save product type', false);
        }
      });

      localeForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const submitButton = localeForm.querySelector('[data-shop-save-button="locale"]');
        const timezone = text(timezoneSelect.value);
        const adminLanguage = text(adminLanguageSelect.value);
        const websiteLanguage = text(websiteLanguageSelect.value);

        if (!timezone || !adminLanguage || !websiteLanguage) {
          showError('Complete timezone and language preferences before saving.');
          return;
        }

        setButtonState(submitButton, 'Saving...', true);

        try {
          await postUpdate({
            type: 'locale',
            data: {
              timezone,
              admin_language: adminLanguage.toLowerCase(),
              website_language: websiteLanguage.toLowerCase(),
            },
          });

          if (localeState instanceof HTMLElement) {
            localeState.textContent = localeSummary();
          }

          showSuccess('Locale updated.');
        } catch (error) {
          showError(error instanceof Error ? error.message : 'Failed to update locale.');
        } finally {
          setButtonState(submitButton, buttonLabels.get(submitButton) || 'Save locale', false);
        }
      });

      switchModal?.querySelectorAll('[data-shop-switch-close]').forEach((button) => {
        if (button instanceof HTMLButtonElement) {
          button.addEventListener('click', closeSwitchModal);
        }
      });

      switchBackupButton?.addEventListener('click', async () => {
        if (!pendingProductType) {
          return;
        }
        if (!window.API?.Admin?.Products?.backupAll || !productsApiBaseUrl || !refreshToken) {
          showError('Backup API is not configured.');
          return;
        }

        if (switchBackupButton instanceof HTMLButtonElement) {
          switchBackupButton.disabled = true;
          switchBackupButton.textContent = 'Backing up...';
        }

        try {
          const payload = await window.API.Admin.Products.backupAll({
            apiBaseUrl: productsApiBaseUrl,
            refreshToken,
            timeoutMs: 30000,
          });

          downloadBackupFile(payload || {});
          backupCompletedForType = pendingProductType;
          if (switchBackupStatus instanceof HTMLElement) {
            switchBackupStatus.textContent = 'Congratulations, your backup is done. Now you can switch.';
          }
          setSwitchConfirmState(true);
          showSuccess('Products backup downloaded.');
        } catch (error) {
          showError(error?.message || 'Failed to create backup.');
        } finally {
          if (switchBackupButton instanceof HTMLButtonElement) {
            switchBackupButton.disabled = false;
            switchBackupButton.textContent = 'Backup Products';
          }
        }
      });

      switchConfirmButton?.addEventListener('click', async () => {
        if (!pendingProductType) {
          return;
        }
        if (totalProducts > 0 && backupCompletedForType !== pendingProductType) {
          showError('Complete the backup first before switching the product type.');
          return;
        }
        if (!window.API?.Admin?.Products?.deleteAll || !productsApiBaseUrl || !refreshToken) {
          showError('Delete API is not configured.');
          return;
        }

        setSwitchConfirmState(false);

        try {
          if (totalProducts > 0) {
            await window.API.Admin.Products.deleteAll({
              apiBaseUrl: productsApiBaseUrl,
              refreshToken,
              timeoutMs: 30000,
            });
          }

          await saveProductType(pendingProductType);
          totalProducts = 0;
          backupCompletedForType = '';
          closeSwitchModal();
          showSuccess('Old products deleted and product type switched successfully.');
        } catch (error) {
          showError(error?.message || 'Failed to switch product type.');
          setSwitchConfirmState(true);
        }
      });

      renderProductCards();
      fetchProductsSummary();
    });
  </script>
@endsection
