@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header settings-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>
  </div>

  <div
    class="settings-layout settings-layout-single mt-md"
    data-domain-settings
    data-api-base-url="{{ $domainApiBaseUrl }}"
    data-refresh-token="{{ $domainRefreshToken }}"
    data-initial-subdomain-base="{{ $initialSubdomainBase }}"
  >
    <section class="settings-main-column">
      <article class="card settings-panel" data-domain-setup>
        <div class="card-header">
          <div>
            <h3 class="card-title">Add Domain</h3>
            <p class="settings-panel-subtitle">Use one live domain configuration per store. Saving again replaces the current subdomain or custom domain.</p>
          </div>
          <span class="badge badge-primary" data-domain-package-badge>Loading package...</span>
        </div>

        <div class="settings-inline-actions">
          <button type="button" class="btn btn-primary btn-sm" data-domain-mode-btn data-mode="subdomain" aria-pressed="true">Subdomain</button>
          <button type="button" class="btn btn-ghost btn-sm" data-domain-mode-btn data-mode="custom" aria-pressed="false">Custom Domain</button>
        </div>

        <div class="settings-field-grid mt-md">
          <div class="form-group" data-domain-mode-panel="subdomain" style="grid-column: 1 / -1;">
            <label class="form-label" for="domainSubdomainInput">A Metafy Subdomain (No DNS Needed)</label>
            <input
              id="domainSubdomainInput"
              type="text"
              class="form-input"
              placeholder="yourbrand"
              autocomplete="off"
              spellcheck="false"
              data-domain-subdomain-input
            >
            <small class="form-help" data-domain-subdomain-help>
              Final URL: <strong data-domain-preview>yourbrand.{{ $initialSubdomainBase }}</strong>
            </small>
          </div>

          <div class="form-group hidden" data-domain-mode-panel="custom" style="grid-column: 1 / -1;">
            <label class="form-label" for="domainCustomInput">Custom Domain (DNS Required)</label>
            <input
              id="domainCustomInput"
              type="text"
              class="form-input"
              placeholder="store.yourbrand.com"
              autocomplete="off"
              spellcheck="false"
              data-domain-custom-input
            >
            <small class="form-help" data-domain-custom-help>Loading package access...</small>
          </div>
        </div>

        <div class="settings-inline-actions">
          <button type="button" class="btn btn-success btn-sm" data-domain-submit-btn data-domain-submit-mode="subdomain">Save Subdomain</button>
          <button type="button" class="btn btn-primary btn-sm hidden" data-domain-submit-btn data-domain-submit-mode="custom">Save Custom Domain</button>
        </div>
      </article>

      <article class="card settings-panel mt-md">
        <div class="card-header">
          <div>
            <h3 class="card-title">Connected Domains</h3>
            <p class="settings-panel-subtitle">This table reflects the single active domain record stored for the current user.</p>
          </div>
          <button type="button" class="btn btn-ghost btn-sm" data-domain-refresh-btn>Refresh Status</button>
        </div>

        <div class="table-container">
          <table class="table">
            <thead>
              <tr>
                <th>Domain</th>
                <th>Type</th>
                <th>Status</th>
                <th>SSL</th>
              </tr>
            </thead>
            <tbody data-domain-connected-body>
              <tr>
                <td colspan="4">Loading domains...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>

      <article class="card settings-panel mt-md hidden" data-domain-dns-panel>
        <div class="card-header">
          <div>
            <h3 class="card-title">DNS Records (Only for Custom Domain)</h3>
            <p class="settings-panel-subtitle">Copy these demo DNS records into your domain provider when using a custom domain.</p>
          </div>
          <button type="button" class="btn btn-ghost btn-sm" data-domain-copy-dns-btn>Copy All</button>
        </div>

        <div class="table-container">
          <table class="table">
            <thead>
              <tr>
                <th>Host</th>
                <th>Type</th>
                <th>Value</th>
                <th>TTL</th>
                <th>Notes</th>
              </tr>
            </thead>
            <tbody data-domain-dns-body>
              <tr>
                <td colspan="5">Loading DNS records...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </article>
    </section>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const shell = document.querySelector('[data-domain-settings]');
      if (!(shell instanceof HTMLElement)) {
        return;
      }

      const apiBaseUrl = String(shell.dataset.apiBaseUrl || '').trim();
      const refreshToken = String(shell.dataset.refreshToken || window.API?.getToken?.() || '').trim();
      const text = (value) => String(value ?? '').trim();
      const slugify = (value) => text(value)
        .toLowerCase()
        .replace(/[^a-z0-9-]+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
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

      if (
        !apiBaseUrl
        || !refreshToken
        || !window.API?.Admin?.Packages?.getInfo
        || !window.API?.Admin?.Domains?.get
        || !window.API?.Admin?.Domains?.create
        || !window.API?.Admin?.GlobalConfig?.getInfo
      ) {
        showError('Domain settings API is not configured.');
        return;
      }

      const modeButtons = Array.from(shell.querySelectorAll('[data-domain-mode-btn]'));
      const modePanels = Array.from(shell.querySelectorAll('[data-domain-mode-panel]'));
      const submitButtons = Array.from(shell.querySelectorAll('[data-domain-submit-btn]'));
      const subdomainInput = shell.querySelector('[data-domain-subdomain-input]');
      const customInput = shell.querySelector('[data-domain-custom-input]');
      const previewNode = shell.querySelector('[data-domain-preview]');
      const subdomainHelp = shell.querySelector('[data-domain-subdomain-help]');
      const customHelp = shell.querySelector('[data-domain-custom-help]');
      const packageBadge = shell.querySelector('[data-domain-package-badge]');
      const connectedBody = shell.querySelector('[data-domain-connected-body]');
      const dnsPanel = shell.querySelector('[data-domain-dns-panel]');
      const dnsBody = shell.querySelector('[data-domain-dns-body]');
      const refreshButton = shell.querySelector('[data-domain-refresh-btn]');
      const copyDnsButton = shell.querySelector('[data-domain-copy-dns-btn]');

      if (
        !(subdomainInput instanceof HTMLInputElement)
        || !(customInput instanceof HTMLInputElement)
        || !(previewNode instanceof HTMLElement)
        || !(subdomainHelp instanceof HTMLElement)
        || !(customHelp instanceof HTMLElement)
        || !(packageBadge instanceof HTMLElement)
        || !(connectedBody instanceof HTMLElement)
        || !(dnsPanel instanceof HTMLElement)
        || !(dnsBody instanceof HTMLElement)
        || !(refreshButton instanceof HTMLButtonElement)
        || !(copyDnsButton instanceof HTMLButtonElement)
      ) {
        return;
      }

      const submitButtonLabels = new Map();
      submitButtons.forEach((button) => {
        if (button instanceof HTMLButtonElement) {
          submitButtonLabels.set(button, text(button.textContent));
        }
      });

      const state = {
        mode: 'subdomain',
        packageInfo: null,
        domains: [],
        dnsRecords: [],
        subdomainBase: text(shell.dataset.initialSubdomainBase || '') || 'ametafy.shop',
        loading: false,
      };

      const currentDomain = () => Array.isArray(state.domains) ? (state.domains[0] || null) : null;
      const customModeVisible = () => Boolean(state.packageInfo?.is_domain || currentDomain()?.type_key === 'domain');
      const statusBadgeClass = (status) => text(status).toLowerCase().includes('waiting') ? 'badge-warning' : 'badge-success';
      const setBusy = (button, busyText, busy) => {
        if (!(button instanceof HTMLButtonElement)) {
          return;
        }

        button.disabled = busy;
        button.textContent = busy ? busyText : (submitButtonLabels.get(button) || button.textContent);
      };
      const dnsClipboardText = () => state.dnsRecords.map((record) => {
        const host = text(record?.host);
        const type = text(record?.type);
        const value = text(record?.value);
        const ttl = text(record?.ttl);
        const notes = text(record?.notes);
        return `${host}\t${type}\t${value}\t${ttl}\t${notes}`;
      }).join('\n');
      const renderPreview = () => {
        const username = slugify(subdomainInput.value) || text(currentDomain()?.username) || 'yourbrand';
        previewNode.textContent = `${username}.${state.subdomainBase}`;
        subdomainHelp.innerHTML = `Final URL: <strong>${previewNode.textContent}</strong>`;
      };
      const renderConnectedDomains = () => {
        if (!Array.isArray(state.domains) || state.domains.length === 0) {
          connectedBody.innerHTML = '<tr><td colspan="4">No connected domain found yet. Save a subdomain or custom domain to create one.</td></tr>';
          return;
        }

        connectedBody.innerHTML = state.domains.map((domain) => {
          const domainValue = text(domain?.domain);
          const type = text(domain?.type);
          const status = text(domain?.status);
          const ssl = text(domain?.ssl);
          const badgeClass = statusBadgeClass(status);

          return `
            <tr>
              <td class="settings-cell-strong">${domainValue}</td>
              <td>${type}</td>
              <td><span class="badge ${badgeClass}">${status}</span></td>
              <td>${ssl}</td>
            </tr>
          `;
        }).join('');
      };
      const renderDNSRecords = () => {
        const shouldShow = state.mode === 'custom' || text(currentDomain()?.type_key) === 'domain';
        dnsPanel.classList.toggle('hidden', !shouldShow);
        if (!shouldShow) {
          return;
        }

        if (!Array.isArray(state.dnsRecords) || state.dnsRecords.length === 0) {
          dnsBody.innerHTML = '<tr><td colspan="5">No DNS records are configured yet.</td></tr>';
          return;
        }

        dnsBody.innerHTML = state.dnsRecords.map((record) => `
          <tr>
            <td class="settings-cell-strong">${text(record?.host)}</td>
            <td>${text(record?.type)}</td>
            <td><code>${text(record?.value)}</code></td>
            <td>${text(record?.ttl)}</td>
            <td>${text(record?.notes)}</td>
          </tr>
        `).join('');
      };
      const renderPackageAccess = () => {
        const packageName = text(state.packageInfo?.package_name) || 'No Package';
        const customEnabled = Boolean(state.packageInfo?.is_domain);
        packageBadge.textContent = customEnabled
          ? `${packageName} / Custom domain enabled`
          : `${packageName} / Subdomain only`;

        const customModeAllowed = customModeVisible();
        modeButtons.forEach((button) => {
          if (!(button instanceof HTMLButtonElement)) {
            return;
          }

          const isCustomButton = text(button.dataset.mode) === 'custom';
          button.disabled = isCustomButton && !customModeAllowed;
          const isActive = text(button.dataset.mode) === state.mode;
          button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
          button.classList.toggle('btn-primary', isActive);
          button.classList.toggle('btn-ghost', !isActive);
        });

        customHelp.textContent = customEnabled
          ? 'After saving a custom domain, use the DNS records below at your provider.'
          : `Current package "${packageName}" does not allow custom domain.`;
      };
      const renderModePanels = () => {
        if (state.mode === 'custom' && !customModeVisible()) {
          state.mode = 'subdomain';
        }

        modePanels.forEach((panel) => {
          if (panel instanceof HTMLElement) {
            panel.classList.toggle('hidden', text(panel.dataset.domainModePanel) !== state.mode);
          }
        });

        submitButtons.forEach((button) => {
          if (button instanceof HTMLElement) {
            button.classList.toggle('hidden', text(button.dataset.domainSubmitMode) !== state.mode);
          }
        });
      };
      const render = () => {
        renderPackageAccess();
        renderModePanels();
        renderPreview();
        renderConnectedDomains();
        renderDNSRecords();
      };
      const setMode = (mode, options = {}) => {
        const nextMode = mode === 'custom' ? 'custom' : 'subdomain';
        if (nextMode === 'custom' && !customModeVisible()) {
          if (!options.quiet) {
            showError('Current package does not allow custom domain.');
          }
          return;
        }

        state.mode = nextMode;
        render();
      };
      const fillInputsFromState = () => {
        const domain = currentDomain();
        if (!domain) {
          return;
        }

        if (text(domain.type_key) === 'sub_domain' && text(domain.username)) {
          subdomainInput.value = text(domain.username);
        }
        if (text(domain.type_key) === 'domain') {
          customInput.value = text(domain.domain);
        }
      };
      const loadPage = async () => {
        state.loading = true;
        refreshButton.disabled = true;
        refreshButton.textContent = 'Refreshing...';

        const [packageResult, domainResult, globalResult] = await Promise.allSettled([
          window.API.Admin.Packages.getInfo({
            apiBaseUrl,
            refreshToken,
          }),
          window.API.Admin.Domains.get({
            apiBaseUrl,
            refreshToken,
          }),
          window.API.Admin.GlobalConfig.getInfo({
            apiBaseUrl,
            refreshToken,
          }),
        ]);

        const errors = [];
        if (packageResult.status === 'fulfilled') {
          state.packageInfo = packageResult.value || null;
        } else {
          errors.push(packageResult.reason?.message || 'Failed to load package info.');
        }

        if (domainResult.status === 'fulfilled') {
          const payload = domainResult.value;
          state.domains = Array.isArray(payload?.domains) ? payload.domains : [];
        } else {
          state.domains = [];
          errors.push(domainResult.reason?.message || 'Failed to load connected domains.');
        }

        if (globalResult.status === 'fulfilled') {
          const payload = globalResult.value || {};
          const base = text(payload?.subdomain_base);
          state.subdomainBase = base || state.subdomainBase || 'ametafy.shop';
          state.dnsRecords = Array.isArray(payload?.dns_records) ? payload.dns_records : [];
        } else {
          state.dnsRecords = [];
          errors.push(globalResult.reason?.message || 'Failed to load global domain config.');
        }

        fillInputsFromState();
        const activeDomain = currentDomain();
        if (text(activeDomain?.type_key) === 'domain') {
          state.mode = 'custom';
        } else if (state.mode !== 'custom') {
          state.mode = 'subdomain';
        }

        render();

        refreshButton.disabled = false;
        refreshButton.textContent = 'Refresh Status';
        state.loading = false;

        if (errors.length > 0) {
          showError(errors[0]);
        }
      };
      const saveDomain = async (mode) => {
        const normalizedMode = mode === 'custom' ? 'custom' : 'subdomain';
        const button = submitButtons.find((item) => item instanceof HTMLButtonElement && text(item.dataset.domainSubmitMode) === normalizedMode);
        const payload = normalizedMode === 'custom'
          ? { type: 'domain', value: text(customInput.value) }
          : { type: 'sub_domain', value: slugify(subdomainInput.value) };

        if (normalizedMode === 'subdomain' && payload.value.length < 3) {
          showError('Subdomain username must be at least 3 characters.');
          subdomainInput.focus();
          return;
        }
        if (normalizedMode === 'custom' && text(payload.value) === '') {
          showError('Custom domain is required.');
          customInput.focus();
          return;
        }

        setBusy(button, normalizedMode === 'custom' ? 'Saving...' : 'Saving...', true);

        try {
          const response = await window.API.Admin.Domains.create({
            apiBaseUrl,
            refreshToken,
            payload,
          });

          if (normalizedMode === 'subdomain') {
            subdomainInput.value = text(response?.domain?.username || payload.value);
          }
          if (normalizedMode === 'custom') {
            customInput.value = text(response?.domain?.domain || payload.value);
          }

          await loadPage();
          showSuccess(text(response?.message) || 'Domain saved.');
        } catch (error) {
          showError(error?.message || 'Failed to save domain.');
        } finally {
          setBusy(button, 'Saving...', false);
          renderPreview();
        }
      };

      modeButtons.forEach((button) => {
        if (button instanceof HTMLButtonElement) {
          button.addEventListener('click', () => {
            setMode(text(button.dataset.mode), { quiet: false });
          });
        }
      });

      subdomainInput.addEventListener('input', () => {
        renderPreview();
      });

      submitButtons.forEach((button) => {
        if (button instanceof HTMLButtonElement) {
          button.addEventListener('click', () => {
            saveDomain(text(button.dataset.domainSubmitMode));
          });
        }
      });

      refreshButton.addEventListener('click', () => {
        loadPage();
      });

      copyDnsButton.addEventListener('click', async () => {
        if (!Array.isArray(state.dnsRecords) || state.dnsRecords.length === 0) {
          showError('No DNS records are available to copy.');
          return;
        }

        try {
          await navigator.clipboard.writeText(dnsClipboardText());
          showSuccess('DNS records copied.');
        } catch (error) {
          showError('Could not copy DNS records.');
        }
      });

      renderPreview();
      loadPage();
    });
  </script>
@endsection
