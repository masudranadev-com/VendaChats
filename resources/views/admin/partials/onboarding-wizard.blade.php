@php
  $orderCallLanguages = [
    'english' => 'English',
    'bangla' => 'Bangla',
    'hindi' => 'Hindi',
    'arabic' => 'Arabic',
    'spanish' => 'Spanish',
    'urdu' => 'Urdu',
  ];
  $panelLanguages = [
    'English' => 'English',
    'Bangla' => 'Bangla',
    'Hindi' => 'Hindi',
    'Arabic' => 'Arabic',
  ];
  $websiteLanguages = [
    'English' => 'English',
    'Bangla' => 'Bangla',
    'Hindi' => 'Hindi',
    'Arabic' => 'Arabic',
  ];
  $timezones = [
    'Asia/Dhaka' => 'Asia/Dhaka (GMT +6)',
    'Asia/Kolkata' => 'Asia/Kolkata (GMT +5:30)',
    'Asia/Dubai' => 'Asia/Dubai (GMT +4)',
    'UTC' => 'UTC (GMT +0)',
    'Europe/London' => 'Europe/London (GMT +0)',
    'America/New_York' => 'America/New_York (GMT -5)',
    'America/Chicago' => 'America/Chicago (GMT -6)',
    'America/Los_Angeles' => 'America/Los_Angeles (GMT -8)',
  ];
  $initialWebsiteName = trim((string) ($websiteName ?? 'A Metafy')) ?: 'A Metafy';
  $defaultSubdomain = \Illuminate\Support\Str::slug($initialWebsiteName, '-') ?: 'my-store';
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
      data_get($adminGlobalConfig ?? [], 'primary_language')
      ?? data_get($adminGlobalConfig ?? [], 'language')
      ?? 'english'
    ))),
    'callScope' => strtolower(trim((string) (
      data_get($adminGlobalConfig ?? [], 'call_buyer_scope')
      ?? 'cod'
    ))),
    'timezone' => trim((string) (
      data_get($adminGlobalConfig ?? [], 'timezone')
      ?? 'Asia/Dhaka'
    )),
    'adminLanguage' => trim((string) (
      data_get($adminGlobalConfig ?? [], 'admin_panel_language')
      ?? data_get($adminGlobalConfig ?? [], 'language')
      ?? 'English'
    )),
    'websiteLanguage' => trim((string) (
      data_get($adminGlobalConfig ?? [], 'website_language')
      ?? data_get($adminGlobalConfig ?? [], 'language')
      ?? 'English'
    )),
  ];
@endphp

<div
  class="admin-onboarding-shell"
  data-admin-onboarding
  data-storage-key="admin_onboarding_draft_v1"
  data-hidden-key="admin_onboarding_hidden_v1"
  data-domain-suffix="vendachats.com"
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
        <h2 class="admin-onboarding-title" id="adminOnboardingTitle">Set up your admin panel in four steps</h2>
        <p class="admin-onboarding-description" id="adminOnboardingDescription">
          Configure your selling model, storefront subdomain, order call confirmation, and regional preferences.
        </p>
      </div>

      <div class="admin-onboarding-topbar-actions">
        <div class="admin-onboarding-progress">
          <span class="admin-onboarding-progress-label" data-admin-onboarding-progress-label>Step 1 of 4</span>
          <div class="admin-onboarding-progress-track" aria-hidden="true">
            <span class="admin-onboarding-progress-fill" data-admin-onboarding-progress-fill></span>
          </div>
        </div>

        <button type="button" class="btn btn-ghost admin-onboarding-dismiss" data-admin-onboarding-dismiss>
          Set up later
        </button>
      </div>
    </div>

    <div class="admin-onboarding-overview">
      <div class="admin-onboarding-step-strip" aria-label="Admin panel setup progress">
        <article class="admin-onboarding-step-chip is-active" data-admin-onboarding-step-chip="0" aria-current="step">
          <span class="admin-onboarding-step-chip-index">01</span>
          <span class="admin-onboarding-step-chip-copy">
            <strong>Product type</strong>
          </span>
          <span class="admin-onboarding-step-chip-state">Current</span>
        </article>

        <article class="admin-onboarding-step-chip" data-admin-onboarding-step-chip="1">
          <span class="admin-onboarding-step-chip-index">02</span>
          <span class="admin-onboarding-step-chip-copy">
            <strong>Subdomain</strong>
          </span>
          <span class="admin-onboarding-step-chip-state">Locked</span>
        </article>

        <article class="admin-onboarding-step-chip" data-admin-onboarding-step-chip="2">
          <span class="admin-onboarding-step-chip-index">03</span>
          <span class="admin-onboarding-step-chip-copy">
            <strong>Order call</strong>
          </span>
          <span class="admin-onboarding-step-chip-state">Locked</span>
        </article>

        <article class="admin-onboarding-step-chip" data-admin-onboarding-step-chip="3">
          <span class="admin-onboarding-step-chip-index">04</span>
          <span class="admin-onboarding-step-chip-copy">
            <strong>Locale</strong>
          </span>
          <span class="admin-onboarding-step-chip-state">Locked</span>
        </article>
      </div>
    </div>

    <div class="admin-onboarding-main">
        <section class="admin-onboarding-step-panel is-active" data-admin-onboarding-step="0" role="tabpanel">
          <div class="admin-onboarding-panel-head">
            <span class="admin-onboarding-panel-step">Step 1</span>
            <h3>Choose your product type</h3>
            <p>Start with the selling model that best matches your business. You can fine-tune product rules later from the Products section.</p>
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
              <input type="radio" name="adminOnboardingProductType" value="digital" data-admin-onboarding-product-type>
              <span class="admin-onboarding-choice-badge">Instant</span>
              <strong>Digital</strong>
              <p>For memberships, services, licenses, or access-based offers.</p>
              <small>No shipping flow. Great for coaching, subscriptions, and tools.</small>
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
            <p>Pick a short, memorable subdomain for the page customers will open. Lowercase letters, numbers, and hyphens work best.</p>
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
            <p>Choose the page name your team mentions on calls, the primary language, and which buyers should receive verification calls.</p>
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
            <p>Finalize the control panel by aligning your timezone, admin language, and storefront language for a clean first launch.</p>
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

          <div class="admin-onboarding-finish-card">
            <span class="admin-onboarding-finish-kicker">Frontend only for now</span>
            <strong>Finish the UI flow today, connect save actions later.</strong>
            <p>This wizard currently keeps values in the browser so the full interaction can be reviewed before API wiring starts.</p>
          </div>
        </section>
    </div>

    <div class="admin-onboarding-footer">
      <p class="admin-onboarding-footer-note">
        Complete each step to unlock the next one. This flow is UI-only for now and keeps your draft in this browser until backend save actions are connected.
      </p>

      <div class="admin-onboarding-footer-actions">
        <button type="button" class="btn btn-secondary" data-admin-onboarding-back>
          Back
        </button>
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
