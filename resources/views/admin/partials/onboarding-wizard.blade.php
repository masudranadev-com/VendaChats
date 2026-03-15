@php
  $localeOptions = $localeOptions ?? [];
  $selectedLocale = $selectedLocale ?? [];
  $orderCallLanguages = $localeOptions['languages'] ?? [];
  $panelLanguages = $localeOptions['admin_languages'] ?? $orderCallLanguages;
  $websiteLanguages = $localeOptions['website_languages'] ?? $orderCallLanguages;
  $timezones = $localeOptions['timezones'] ?? [];
  $initialWebsiteName = trim((string) ($websiteName ?? 'A Metafy')) ?: 'A Metafy';
  $defaultSubdomain = \Illuminate\Support\Str::slug($initialWebsiteName, '-') ?: 'my-store';
  $stepIndexes = [
    'product' => 0,
    'sub_domain' => 1,
    'call_order' => 2,
    'locale' => 3,
  ];
  $onboardingState = trim((string) data_get($adminGlobalConfig ?? [], 'onboarding', ''));
  $nextOnboardingStep = trim((string) data_get($adminGlobalConfig ?? [], 'onboarding_next_step', ''));
  $initialHighestCompletedStep = match ($onboardingState) {
    'product' => 0,
    'sub_domain' => 1,
    'call_order' => 2,
    'locale', 'completed' => 3,
    default => -1,
  };
  $initialCurrentStep = array_key_exists($nextOnboardingStep, $stepIndexes)
    ? $stepIndexes[$nextOnboardingStep]
    : match ($onboardingState) {
      'product' => 1,
      'sub_domain' => 2,
      'call_order' => 3,
      default => 0,
    };
  $initialState = [
    'productType' => trim((string) data_get($adminGlobalConfig ?? [], 'product_type', '')),
    'subdomain' => trim((string) (
      data_get($adminGlobalConfig ?? [], 'subdomain')
      ?? data_get($adminGlobalConfig ?? [], 'username')
      ?? $defaultSubdomain
    )),
    'pageName' => trim((string) (
      data_get($adminGlobalConfig ?? [], 'page_name')
      ?? data_get($adminGlobalConfig ?? [], 'website_name')
      ?? $initialWebsiteName
    )),
    'primaryLanguage' => strtolower(trim((string) (
      data_get($selectedLocale, 'primary_language')
      ?? data_get($adminGlobalConfig ?? [], 'primary_language')
      ?? data_get($adminGlobalConfig ?? [], 'language')
      ?? 'en'
    ))),
    'isCalling' => filter_var(
      data_get($adminGlobalConfig ?? [], 'is_calling', false),
      FILTER_VALIDATE_BOOLEAN
    ),
    'callScope' => strtolower(trim((string) (
      data_get($adminGlobalConfig ?? [], 'call_buyer_scope')
      ?? 'cod'
    ))),
    'timezone' => trim((string) (
      data_get($selectedLocale, 'timezone')
      ?? data_get($adminGlobalConfig ?? [], 'timezone')
      ?? 'Asia/Dhaka'
    )),
    'adminLanguage' => trim((string) (
      data_get($selectedLocale, 'admin_language')
      ?? data_get($adminGlobalConfig ?? [], 'admin_panel_language')
      ?? data_get($adminGlobalConfig ?? [], 'admin_language')
      ?? data_get($adminGlobalConfig ?? [], 'language')
      ?? 'en'
    )),
    'websiteLanguage' => trim((string) (
      data_get($selectedLocale, 'website_language')
      ?? data_get($adminGlobalConfig ?? [], 'website_language')
      ?? data_get($adminGlobalConfig ?? [], 'language')
      ?? 'en'
    )),
  ];
@endphp

<div
  class="admin-onboarding-shell"
  data-admin-onboarding
  data-storage-key="admin_onboarding_draft_v1"
  data-hidden-key="admin_onboarding_completed_v1"
  data-domain-suffix="vendachats.com"
  data-continue-url="{{ route('admin.onboardingContinue') }}"
  data-dashboard-url="{{ route('admin.dashboard') }}"
  data-initial-current-step="{{ $initialCurrentStep }}"
  data-initial-highest-completed-step="{{ $initialHighestCompletedStep }}"
  data-persist-hidden="{{ ($adminOnboardingStandalone ?? false) ? '0' : '1' }}"
  data-initial-state='@json($initialState)'
>
  <div class="admin-onboarding-backdrop" aria-hidden="true"></div>

  <section
    class="admin-onboarding-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="adminOnboardingTitle"
    aria-describedby="adminOnboardingDescription"
  >
    <div class="admin-onboarding-topbar">
      <div class="admin-onboarding-topbar-copy">
        <span class="admin-onboarding-kicker">First-time setup</span>
        <h2 class="admin-onboarding-title" id="adminOnboardingTitle">Finish your admin setup</h2>
        <p class="admin-onboarding-description" id="adminOnboardingDescription">
          Complete product type, storefront domain, order-call preferences, and locale before entering the dashboard.
        </p>
      </div>

      <div class="admin-onboarding-topbar-actions">
        <div class="admin-onboarding-progress">
          <span class="admin-onboarding-progress-label" data-admin-onboarding-progress-label>Step 1 of 4</span>
          <div class="admin-onboarding-progress-track" aria-hidden="true">
            <span class="admin-onboarding-progress-fill" data-admin-onboarding-progress-fill></span>
          </div>
        </div>

      </div>
    </div>

    <div class="admin-onboarding-overview">
      <div class="admin-onboarding-step-strip" aria-label="Admin panel setup progress">
        <article class="admin-onboarding-step-chip is-active" data-admin-onboarding-step-chip="0" aria-current="step">
          <span class="admin-onboarding-step-chip-index">01</span>
          <span class="admin-onboarding-step-chip-copy">
            <strong>Product type</strong>
          </span>
          <span class="admin-onboarding-step-chip-state" data-state="current" title="Current step">
            <span class="admin-onboarding-step-chip-state-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" focusable="false">
                <circle cx="12" cy="12" r="4" fill="currentColor"></circle>
                <circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="2"></circle>
              </svg>
            </span>
            <span class="admin-onboarding-step-chip-state-label">Current step</span>
          </span>
        </article>

        <article class="admin-onboarding-step-chip" data-admin-onboarding-step-chip="1">
          <span class="admin-onboarding-step-chip-index">02</span>
          <span class="admin-onboarding-step-chip-copy">
            <strong>Subdomain</strong>
          </span>
          <span class="admin-onboarding-step-chip-state" data-state="next" title="Next step">
            <span class="admin-onboarding-step-chip-state-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" focusable="false">
                <path d="M8 12h8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                <path d="m12 8 4 4-4 4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
            </span>
            <span class="admin-onboarding-step-chip-state-label">Next step</span>
          </span>
        </article>

        <article class="admin-onboarding-step-chip" data-admin-onboarding-step-chip="2">
          <span class="admin-onboarding-step-chip-index">03</span>
          <span class="admin-onboarding-step-chip-copy">
            <strong>Order call</strong>
          </span>
          <span class="admin-onboarding-step-chip-state" data-state="locked" title="Locked step">
            <span class="admin-onboarding-step-chip-state-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" focusable="false">
                <rect x="6" y="11" width="12" height="9" rx="2" fill="none" stroke="currentColor" stroke-width="2"></rect>
                <path d="M9 11V8a3 3 0 0 1 6 0v3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
              </svg>
            </span>
            <span class="admin-onboarding-step-chip-state-label">Locked step</span>
          </span>
        </article>

        <article class="admin-onboarding-step-chip" data-admin-onboarding-step-chip="3">
          <span class="admin-onboarding-step-chip-index">04</span>
          <span class="admin-onboarding-step-chip-copy">
            <strong>Locale</strong>
          </span>
          <span class="admin-onboarding-step-chip-state" data-state="locked" title="Locked step">
            <span class="admin-onboarding-step-chip-state-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" focusable="false">
                <rect x="6" y="11" width="12" height="9" rx="2" fill="none" stroke="currentColor" stroke-width="2"></rect>
                <path d="M9 11V8a3 3 0 0 1 6 0v3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
              </svg>
            </span>
            <span class="admin-onboarding-step-chip-state-label">Locked step</span>
          </span>
        </article>
      </div>
    </div>

    <div class="admin-onboarding-main">
        <section class="admin-onboarding-step-panel is-active" data-admin-onboarding-step="0" role="tabpanel">
          <div class="admin-onboarding-panel-head">
            <span class="admin-onboarding-panel-step">Step 1</span>
            <h3>Choose your product type</h3>
            <p>Select the selling flow your store uses today. You can refine advanced product rules later.</p>
          </div>

          <div class="admin-onboarding-choice-grid">
            <label class="admin-onboarding-choice-card" data-admin-onboarding-product-card>
              <input type="radio" name="adminOnboardingProductType" value="physical" checked data-admin-onboarding-product-type>
              <span class="admin-onboarding-choice-badge">Recommended</span>
              <strong>Physical</strong>
              <p>For shipped products with inventory, courier, and confirmation flow.</p>
              <small>Best for retail, fashion, gadgets, and cash-on-delivery stores.</small>
            </label>

            <label class="admin-onboarding-choice-card" data-admin-onboarding-product-card>
              <input type="radio" name="adminOnboardingProductType" value="subscription" data-admin-onboarding-product-type>
              <span class="admin-onboarding-choice-badge">Access based</span>
              <strong>Subscription</strong>
              <p>For memberships, services, licenses, or recurring access-based offers.</p>
              <small>No shipping flow. Great for shared accounts, coaching, subscriptions, and tools.</small>
            </label>

            <label class="admin-onboarding-choice-card" data-admin-onboarding-product-card>
              <input type="radio" name="adminOnboardingProductType" value="downloadable" data-admin-onboarding-product-type>
              <span class="admin-onboarding-choice-badge">File delivery</span>
              <strong>Downloadable</strong>
              <p>For assets, templates, ebooks, and files delivered after purchase.</p>
              <small>Optimized for digital goods with quick post-purchase fulfillment.</small>
            </label>
          </div>
        </section>

        <section class="admin-onboarding-step-panel" data-admin-onboarding-step="1" role="tabpanel" aria-hidden="true">
          <div class="admin-onboarding-panel-head">
            <span class="admin-onboarding-panel-step">Step 2</span>
            <h3>Claim your storefront username</h3>
            <p>Reserve a short storefront username that customers can remember and share easily.</p>
          </div>

          <div class="admin-onboarding-form-grid is-domain">
            <div class="form-group">
              <label class="form-label" for="adminOnboardingSubdomain">Storefront username</label>
              <input
                id="adminOnboardingSubdomain"
                type="text"
                class="form-input admin-onboarding-input"
                value="{{ $defaultSubdomain }}"
                maxlength="30"
                autocomplete="off"
                spellcheck="false"
                placeholder="yourbrand"
                data-admin-onboarding-subdomain
              >
              <small class="form-help">Example: use your brand, shop name, or campaign identity.</small>
            </div>

            <div class="admin-onboarding-domain-preview">
              <span class="admin-onboarding-domain-label">Preview URL</span>
              <strong data-admin-onboarding-domain-preview>{{ $defaultSubdomain }}.vendachats.com</strong>
              <p>Customers will use this link to browse your storefront and place orders.</p>
              <ul class="admin-onboarding-domain-tips">
                <li>Keep it short and brand-friendly.</li>
                <li>Avoid spaces and special characters.</li>
                <li>You can map a custom domain later from Shop Settings.</li>
              </ul>
            </div>
          </div>
        </section>

        <section class="admin-onboarding-step-panel" data-admin-onboarding-step="2" role="tabpanel" aria-hidden="true">
          <div class="admin-onboarding-panel-head">
            <span class="admin-onboarding-panel-step">Step 3</span>
            <h3>Set up order call confirmation</h3>
            <p>Set the page name callers mention, the main language, and which buyers should receive confirmation calls.</p>
          </div>

          <div class="order-call-setting-switch-row">
            <div>
              <label class="form-label" for="adminOnboardingIsCalling">Order Call</label>
              <p class="order-call-setting-copy">Turn automated confirmation calls on or off for new incoming orders.</p>
            </div>

            <div class="order-call-setting-switch-box">
              <span class="order-call-setting-state" data-admin-onboarding-calling-state>Off</span>
              <label class="bot-switch">
                <input
                  id="adminOnboardingIsCalling"
                  class="bot-toggle-input"
                  type="checkbox"
                  data-admin-onboarding-calling-toggle
                >
                <span class="bot-switch-ui"></span>
              </label>
            </div>
          </div>

          <div class="admin-onboarding-form-grid">
            <div class="form-group">
              <label class="form-label" for="adminOnboardingPageName">Page name</label>
              <input
                id="adminOnboardingPageName"
                type="text"
                class="form-input admin-onboarding-input"
                value="{{ $initialWebsiteName }}"
                maxlength="80"
                placeholder="Your store or page name"
                data-admin-onboarding-page-name
              >
            </div>

            <div class="form-group">
              <label class="form-label" for="adminOnboardingPrimaryLanguage">Primary language</label>
              <select id="adminOnboardingPrimaryLanguage" class="form-select" data-admin-onboarding-primary-language>
                @foreach ($orderCallLanguages as $value => $label)
                  <option value="{{ $value }}" {{ $value === ($initialState['primaryLanguage'] ?: 'english') ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="admin-onboarding-scope-grid">
            <label class="admin-onboarding-scope-card" data-admin-onboarding-scope-card>
              <input type="radio" name="adminOnboardingCallScope" value="all" data-admin-onboarding-call-scope>
              <strong>Call all buyers</strong>
              <p>Use confirmation calls for every eligible order that enters the system.</p>
            </label>

            <label class="admin-onboarding-scope-card" data-admin-onboarding-scope-card>
              <input type="radio" name="adminOnboardingCallScope" value="cod" checked data-admin-onboarding-call-scope>
              <strong>Cash on delivery only</strong>
              <p>Keep the flow focused on COD orders that need the highest verification.</p>
            </label>
          </div>
        </section>

        <section class="admin-onboarding-step-panel" data-admin-onboarding-step="3" role="tabpanel" aria-hidden="true">
          <div class="admin-onboarding-panel-head">
            <span class="admin-onboarding-panel-step">Step 4</span>
            <h3>Finish timezone and language defaults</h3>
            <p>Apply timezone and language defaults for the admin team and storefront before launch.</p>
          </div>

          <div class="admin-onboarding-form-grid">
            <div class="form-group admin-onboarding-field-span-2">
              <label class="form-label" for="adminOnboardingTimezone">Timezone</label>
              <select id="adminOnboardingTimezone" class="form-select" data-admin-onboarding-timezone>
                @foreach ($timezones as $value => $label)
                  <option value="{{ $value }}" {{ $value === $initialState['timezone'] ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" for="adminOnboardingAdminLanguage">Admin panel language</label>
              <select id="adminOnboardingAdminLanguage" class="form-select" data-admin-onboarding-admin-language>
                @foreach ($panelLanguages as $value => $label)
                  <option value="{{ $value }}" {{ $value === $initialState['adminLanguage'] ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" for="adminOnboardingWebsiteLanguage">Website language</label>
              <select id="adminOnboardingWebsiteLanguage" class="form-select" data-admin-onboarding-website-language>
                @foreach ($websiteLanguages as $value => $label)
                  <option value="{{ $value }}" {{ $value === $initialState['websiteLanguage'] ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </section>
    </div>

    <div class="admin-onboarding-footer">
      <p class="admin-onboarding-footer-note">Complete each step to unlock the next one.</p>

      <div class="admin-onboarding-footer-actions">
        <button type="button" class="btn btn-primary" data-admin-onboarding-next>
          Continue
        </button>
        <button type="button" class="btn btn-success hidden" data-admin-onboarding-finish>
          Finish setup
        </button>
      </div>
    </div>
  </section>
</div>
