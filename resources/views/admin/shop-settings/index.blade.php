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
              <p class="shop-setup-note">Changing product type updates the saved onboarding profile for this store.</p>
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

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const shell = document.querySelector('[data-shop-settings-setup]');
      if (!(shell instanceof HTMLElement)) {
        return;
      }

      const continueUrl = String(shell.dataset.continueUrl || '').trim();
      const csrfToken = String(shell.dataset.csrfToken || '').trim();
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

      if (
        !(productForm instanceof HTMLFormElement)
        || !(localeForm instanceof HTMLFormElement)
        || !(timezoneSelect instanceof HTMLSelectElement)
        || !(adminLanguageSelect instanceof HTMLSelectElement)
        || !(websiteLanguageSelect instanceof HTMLSelectElement)
      ) {
        return;
      }

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

        setButtonState(submitButton, 'Saving...', true);

        try {
          await postUpdate({
            type: 'product',
            data: {
              product_type: text(productInput.value),
            },
          });

          const label = selectedProductLabel();
          if (productState instanceof HTMLElement) {
            productState.textContent = `Current: ${label}`;
          }
          if (productBadge instanceof HTMLElement) {
            productBadge.textContent = label;
          }

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

      renderProductCards();
    });
  </script>
@endsection
