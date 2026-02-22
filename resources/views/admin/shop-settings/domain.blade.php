@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header settings-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="settings-header-actions">
      <button type="button" class="btn btn-secondary">Run Verification Check</button>
      <button type="button" class="btn btn-primary">Save Domain Changes</button>
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

  <div class="settings-layout mt-xl">
    <section class="settings-main-column">
      <article class="card settings-panel">
        <div class="card-header">
          <div>
            <h3 class="card-title">Package Access Rules</h3>
            <p class="settings-panel-subtitle">Clear rule: everyone can use subdomain, only Pro+ can use custom domain.</p>
          </div>
          <span class="badge badge-primary">{{ $plan['name'] }} Plan</span>
        </div>

        <div class="settings-content-grid">
          @foreach ($accessRules as $rule)
            @php
              $badgeClass = str_contains($rule['status'], 'Locked') ? 'badge-warning' : 'badge-success';
            @endphp
            <article class="settings-content-card">
              <div class="settings-content-head">
                <strong>{{ $rule['name'] }}</strong>
                <span class="badge {{ $badgeClass }}">{{ $rule['status'] }}</span>
              </div>
              <p>{{ $rule['availability'] }}</p>
              <small>{{ $rule['dns_rule'] }}</small>
            </article>
          @endforeach
        </div>

        @if (! $plan['custom_domain_allowed'])
          <div class="settings-content-card">
            <div class="settings-content-head">
              <strong>Upgrade Needed</strong>
              <span class="badge badge-warning">Custom Domain Locked</span>
            </div>
            <p>Custom domain is locked on <strong>{{ $plan['name'] }}</strong>. Upgrade to <strong>{{ $plan['upgrade_plan'] }}</strong> or higher to enable it.</p>
          </div>
        @endif
      </article>

      @php
        $singleDomainLocked = ! ($canAddDomain ?? true);
      @endphp

      <article class="card settings-panel mt-xl" data-domain-setup>
        <div class="card-header">
          <div>
            <h3 class="card-title">Add Domain</h3>
            <p class="settings-panel-subtitle">Single-domain mode: user can create only one domain at a time.</p>
          </div>
          <span class="badge {{ $singleDomainLocked ? 'badge-warning' : 'badge-info' }}">{{ $singleDomainLocked ? 'Domain Limit Reached' : 'Guided setup' }}</span>
        </div>

        @if ($singleDomainLocked)
          <div class="settings-content-card">
            <div class="settings-content-head">
              <strong>Single Domain Restriction</strong>
              <span class="badge badge-warning">One Domain Only</span>
            </div>
            <p>You already have one configured domain. Remove current domain first if you want to add another one.</p>
            <small>NEW DOMAIN CREATION IS LOCKED UNTIL CURRENT DOMAIN IS REMOVED</small>
          </div>
        @endif

        <div class="settings-inline-actions">
          <button type="button" class="btn btn-primary btn-sm" data-domain-mode-btn data-mode="subdomain" aria-pressed="true" {{ $singleDomainLocked ? 'disabled' : '' }}>Subdomain</button>
          <button type="button" class="btn btn-ghost btn-sm" data-domain-mode-btn data-mode="custom" aria-pressed="false" {{ $singleDomainLocked ? 'disabled' : '' }}>Custom Domain</button>
        </div>

        <div class="settings-field-grid mt-md">
          <div class="form-group" data-domain-mode-panel="subdomain" style="grid-column: 1 / -1;">
            <label class="form-label">A Metafy Subdomain (No DNS Needed)</label>
            <input type="text" class="form-input" placeholder="yourbrand" {{ $singleDomainLocked ? 'disabled' : '' }}>
            <small class="form-help">Final URL: <strong>yourbrand.{{ $plan['subdomain_base'] }}</strong></small>
          </div>

          <div class="form-group hidden" data-domain-mode-panel="custom" style="grid-column: 1 / -1;">
            <label class="form-label">Custom Domain (DNS Required)</label>
            <input
              type="text"
              class="form-input"
              placeholder="store.yourbrand.com"
              {{ $plan['custom_domain_allowed'] && ! $singleDomainLocked ? '' : 'disabled' }}
            >
            <small class="form-help">
              {{ $singleDomainLocked ? 'Domain slot full. Remove current domain to add a new one.' : ($plan['custom_domain_allowed'] ? 'After adding domain, configure DNS records below.' : 'Upgrade required for custom domain.') }}
            </small>
          </div>
        </div>

        <div class="settings-inline-actions">
          <button type="button" class="btn btn-success btn-sm" data-domain-submit-btn data-domain-submit-mode="subdomain" {{ $singleDomainLocked ? 'disabled' : '' }}>Add Subdomain</button>
          <button
            type="button"
            class="btn btn-primary btn-sm hidden"
            data-domain-submit-btn
            data-domain-submit-mode="custom"
            {{ $plan['custom_domain_allowed'] && ! $singleDomainLocked ? '' : 'disabled' }}
          >
            Add Custom Domain
          </button>
        </div>
      </article>

      <article class="card settings-panel mt-xl">
        <div class="card-header">
          <div>
            <h3 class="card-title">Connected Domains</h3>
            <p class="settings-panel-subtitle">Single-domain mode is active. Only one domain entry can exist at a time.</p>
          </div>
          <button type="button" class="btn btn-ghost btn-sm">Refresh Status</button>
        </div>

        <div class="table-container">
          <table class="table">
            <thead>
              <tr>
                <th>Domain</th>
                <th>Type</th>
                <th>DNS Required</th>
                <th>Status</th>
                <th>SSL</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($connectedDomains as $domain)
                @php
                  $statusClass = str_contains($domain['status'], 'Pending') ? 'badge-warning' : 'badge-success';
                @endphp
                <tr>
                  <td class="settings-cell-strong">{{ $domain['domain'] }}</td>
                  <td>{{ $domain['type'] }}</td>
                  <td><span class="badge {{ $domain['dns_required'] === 'Yes' ? 'badge-warning' : 'badge-success' }}">{{ $domain['dns_required'] }}</span></td>
                  <td><span class="badge {{ $statusClass }}">{{ $domain['status'] }}</span></td>
                  <td>{{ $domain['ssl'] }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </article>

      <article class="card settings-panel mt-xl">
        <div class="card-header">
          <div>
            <h3 class="card-title">DNS Records (Only for Custom Domain)</h3>
            <p class="settings-panel-subtitle">If you use your own domain, copy these DNS records into your domain provider panel.</p>
          </div>
          <button type="button" class="btn btn-ghost btn-sm">Copy All</button>
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
            <tbody>
              @foreach ($dnsRecords as $record)
                <tr>
                  <td class="settings-cell-strong">{{ $record['host'] }}</td>
                  <td>{{ $record['type'] }}</td>
                  <td><code>{{ $record['value'] }}</code></td>
                  <td>{{ $record['ttl'] }}</td>
                  <td>{{ $record['notes'] }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </article>
    </section>

    <section class="settings-side-column">
      <article class="card settings-panel">
        <div class="card-header">
          <h3 class="card-title">Simple 4-Step Flow</h3>
          <span class="badge badge-info">Easy Guide</span>
        </div>

        <ul class="settings-focus-list">
          @foreach ($checklist as $item)
            <li>{{ $item }}</li>
          @endforeach
        </ul>
      </article>

      @include('admin.shop-settings.partials.recent-activity')
    </section>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const setupPanel = document.querySelector('[data-domain-setup]');
      if (!setupPanel) {
        return;
      }

      const modeButtons = Array.from(setupPanel.querySelectorAll('[data-domain-mode-btn]'));
      const modePanels = Array.from(setupPanel.querySelectorAll('[data-domain-mode-panel]'));
      const submitButtons = Array.from(setupPanel.querySelectorAll('[data-domain-submit-btn]'));

      if (modeButtons.length === 0 || modePanels.length === 0) {
        return;
      }

      function setMode(mode) {
        const nextMode = mode === 'custom' ? 'custom' : 'subdomain';

        modeButtons.forEach((button) => {
          const isActive = button.dataset.mode === nextMode;
          button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
          button.classList.toggle('btn-primary', isActive);
          button.classList.toggle('btn-ghost', !isActive);
        });

        modePanels.forEach((panel) => {
          panel.classList.toggle('hidden', panel.dataset.domainModePanel !== nextMode);
        });

        submitButtons.forEach((button) => {
          button.classList.toggle('hidden', button.dataset.domainSubmitMode !== nextMode);
        });
      }

      modeButtons.forEach((button) => {
        button.addEventListener('click', () => {
          if (button.disabled) {
            return;
          }

          setMode(button.dataset.mode || 'subdomain');
        });
      });

      setMode('subdomain');
    });
  </script>
@endsection
