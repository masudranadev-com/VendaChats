@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <div class="page-header courier-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>
  </div>

  <div class="mt-xl">
    <section class="card">
      <div class="card-header">
        <h3 class="card-title">Courier API Connection</h3>
        <span class="badge badge-info">Design only</span>
      </div>

      <div class="courier-provider-list">
        @foreach ($providers as $provider)
          <article class="courier-provider-card">
            <div class="courier-provider-head">
              <div>
                <h4>{{ $provider['name'] }}</h4>
                <p>Primary endpoint: {{ $provider['base_url'] }}</p>
              </div>
              <span class="badge {{ $provider['status_class'] }}">{{ $provider['status'] }}</span>
            </div>

            <form class="courier-provider-form">
              <div class="courier-provider-grid">
                <div class="form-group">
                  <label class="form-label">Base URL</label>
                  <input type="text" class="form-input" value="{{ $provider['base_url'] }}">
                </div>
                <div class="form-group">
                  <label class="form-label">API Key</label>
                  <input type="password" class="form-input" value="********************">
                </div>
                <div class="form-group">
                  <label class="form-label">API Secret</label>
                  <input type="password" class="form-input" value="********************">
                </div>
                <div class="form-group">
                  <label class="form-label">{{ $provider['merchant_field'] }}</label>
                  <input type="text" class="form-input" value="{{ $provider['merchant_value'] }}">
                </div>
                <div class="form-group">
                  <label class="form-label">Mode</label>
                  <select class="form-select">
                    <option {{ $provider['mode'] === 'Live' ? 'selected' : '' }}>Live</option>
                    <option {{ $provider['mode'] === 'Sandbox' ? 'selected' : '' }}>Sandbox</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Webhook URL</label>
                  <input type="text" class="form-input" value="{{ url('/webhook') }}">
                </div>
              </div>

              <div class="courier-provider-actions">
                <button type="button" class="btn btn-primary btn-sm">Test Connection</button>
                <button type="button" class="btn btn-secondary btn-sm">Sync Zones</button>
                <button type="button" class="btn btn-ghost btn-sm">Save Provider</button>
              </div>
            </form>
          </article>
        @endforeach
      </div>
    </section>
  </div>

  <section class="card mt-xl" data-courier-zone-editor>
    <div class="card-header">
      <h3 class="card-title">Zone Rate Matrix</h3>
      <span class="badge badge-info">Draft UI only</span>
    </div>

    <div class="courier-matrix-toolbar">
      <div class="courier-provider-actions">
        <button type="button" class="btn btn-success btn-sm" data-zone-open-add>Add Zone</button>
        <button type="button" class="btn btn-secondary btn-sm" data-zone-save-draft>Save Local Draft</button>
        <button type="button" class="btn btn-ghost btn-sm" data-zone-reset>Reset Seed Data</button>
      </div>

      <p class="courier-note">
        Add and edit now use modal forms. This remains frontend-only for now and stores draft data in browser local storage.
      </p>
    </div>

    <div class="table-container">
      <table class="table courier-matrix-table">
        <thead>
          <tr>
            <th>Zone</th>
            <th>SteadFast</th>
            <th>RedX</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody data-zone-table-body>
          @forelse ($zones as $zone)
            @php
              $steadfastRate = (int) preg_replace('/[^\d]/', '', $zone['steadfast']);
              $redxRate = (int) preg_replace('/[^\d]/', '', $zone['redx']);
            @endphp
            <tr data-zone-row data-zone-id="seed-{{ $loop->index }}">
              <td class="courier-cell-strong">{{ $zone['zone'] }}</td>
              <td>৳{{ $steadfastRate }}</td>
              <td>৳{{ $redxRate }}</td>
              <td>
                <div class="courier-row-actions">
                  <button type="button" class="btn btn-primary btn-sm" data-zone-edit data-zone-id="seed-{{ $loop->index }}">Edit</button>
                  <button type="button" class="btn btn-danger btn-sm" data-zone-remove data-zone-id="seed-{{ $loop->index }}">Remove</button>
                </div>
              </td>
            </tr>
          @empty
            <tr data-zone-empty-row>
              <td colspan="4" class="courier-empty-row">No zone rows available.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="modal-overlay" id="courierZoneModal" data-zone-modal aria-hidden="true">
      <div class="modal" role="dialog" aria-modal="true" aria-labelledby="courierZoneModalTitle">
        <div class="modal-header">
          <h3 class="modal-title" id="courierZoneModalTitle" data-zone-modal-title>Add Zone</h3>
          <button type="button" class="modal-close" data-zone-modal-close aria-label="Close">&times;</button>
        </div>

        <form data-zone-form>
          <div class="modal-body">
            <div class="courier-zone-form-grid">
              <div class="form-group courier-zone-form-span-2">
                <label class="form-label" for="courierZoneName">Zone Name</label>
                <input id="courierZoneName" class="form-input" type="text" placeholder="Example: Chittagong Metro" data-zone-input-name required>
              </div>

              <div class="form-group">
                <label class="form-label" for="courierZoneSteadfast">SteadFast Rate</label>
                <input id="courierZoneSteadfast" class="form-input" type="number" min="0" step="1" placeholder="0" data-zone-input-steadfast required>
              </div>

              <div class="form-group">
                <label class="form-label" for="courierZoneRedx">RedX Rate</label>
                <input id="courierZoneRedx" class="form-input" type="number" min="0" step="1" placeholder="0" data-zone-input-redx required>
              </div>
            </div>

            <p class="courier-zone-form-note">
              This modal only updates draft UI state. Backend persistence can be connected later.
            </p>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" data-zone-modal-close>Cancel</button>
            <button type="submit" class="btn btn-success" data-zone-submit>Add Zone</button>
          </div>
        </form>
      </div>
    </div>
  </section>

  <section class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">API Activity Log</h3>
      <span class="badge badge-warning">{{ $logs->total() }} total requests</span>
    </div>

    <div class="table-container">
      <table class="table">
        <thead>
          <tr>
            <th>Time</th>
            <th>Provider</th>
            <th>Event</th>
            <th>Status</th>
            <th>Request ID</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($logs as $log)
            @php
              $statusClass = $log['status'] === 'Success' ? 'badge-success' : 'badge-danger';
            @endphp
            <tr>
              <td>{{ $log['time'] }}</td>
              <td class="courier-cell-strong">{{ $log['provider'] }}</td>
              <td>{{ $log['event'] }}</td>
              <td><span class="badge {{ $statusClass }}">{{ $log['status'] }}</span></td>
              <td>{{ $log['request_id'] }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="courier-empty-row">No API activity found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($logs->hasPages())
      <div class="courier-table-footer">
        <p class="courier-pagination-summary">
          @if ($logs->count() > 0)
            Showing {{ $logs->firstItem() }}-{{ $logs->lastItem() }} of {{ $logs->total() }} requests
          @else
            Showing 0 requests
          @endif
        </p>

        <nav class="courier-pagination-controls" aria-label="Courier logs pagination">
          @if ($logs->onFirstPage())
            <span class="courier-page-btn is-disabled" aria-disabled="true">Prev</span>
          @else
            <a href="{{ $logs->previousPageUrl() }}" class="courier-page-btn">Prev</a>
          @endif

          @for ($page = 1; $page <= $logs->lastPage(); $page++)
            @if ($page === $logs->currentPage())
              <span class="courier-page-btn is-active" aria-current="page">{{ $page }}</span>
            @else
              <a href="{{ $logs->url($page) }}" class="courier-page-btn">{{ $page }}</a>
            @endif
          @endfor

          @if ($logs->hasMorePages())
            <a href="{{ $logs->nextPageUrl() }}" class="courier-page-btn">Next</a>
          @else
            <span class="courier-page-btn is-disabled" aria-disabled="true">Next</span>
          @endif
        </nav>
      </div>
    @endif
  </section>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const editor = document.querySelector('[data-courier-zone-editor]');
      if (!editor) {
        return;
      }

      const storageKey = 'ametafy.courier.zone_matrix_draft';
      const seedRows = @json($zones);

      const tableBody = editor.querySelector('[data-zone-table-body]');
      const openAddButton = editor.querySelector('[data-zone-open-add]');
      const saveDraftButton = editor.querySelector('[data-zone-save-draft]');
      const resetButton = editor.querySelector('[data-zone-reset]');

      const modal = editor.querySelector('[data-zone-modal]');
      const modalTitle = editor.querySelector('[data-zone-modal-title]');
      const modalSubmit = editor.querySelector('[data-zone-submit]');
      const zoneForm = editor.querySelector('[data-zone-form]');
      const zoneNameInput = editor.querySelector('[data-zone-input-name]');
      const zoneSteadfastInput = editor.querySelector('[data-zone-input-steadfast]');
      const zoneRedxInput = editor.querySelector('[data-zone-input-redx]');
      const closeButtons = editor.querySelectorAll('[data-zone-modal-close]');

      if (
        !tableBody ||
        !openAddButton ||
        !saveDraftButton ||
        !resetButton ||
        !modal ||
        !modalTitle ||
        !modalSubmit ||
        !zoneForm ||
        !zoneNameInput ||
        !zoneSteadfastInput ||
        !zoneRedxInput
      ) {
        return;
      }

      let activeEditRowId = null;
      let rows = normalizeRows(seedRows, 'seed');

      loadDraftFromLocalStorage();
      renderRows();

      openAddButton.addEventListener('click', () => {
        activeEditRowId = null;
        zoneForm.reset();
        setModalMode('add');
        openModal();
        zoneNameInput.focus();
      });

      saveDraftButton.addEventListener('click', () => {
        window.localStorage.setItem(storageKey, JSON.stringify(rows));

        if (typeof window.showSuccess === 'function') {
          window.showSuccess('Zone matrix draft saved locally.');
        }
      });

      resetButton.addEventListener('click', () => {
        window.localStorage.removeItem(storageKey);
        rows = normalizeRows(seedRows, 'seed');
        renderRows();

        if (typeof window.showInfo === 'function') {
          window.showInfo('Zone matrix reset to seed data.');
        }
      });

      zoneForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const zoneName = zoneNameInput.value.trim();
        if (!zoneName) {
          if (typeof window.showWarning === 'function') {
            window.showWarning('Zone name is required.');
          }
          zoneNameInput.focus();
          return;
        }

        const payload = {
          zone: zoneName,
          steadfast: parseRate(zoneSteadfastInput.value),
          redx: parseRate(zoneRedxInput.value),
        };

        if (activeEditRowId) {
          rows = rows.map((row) => {
            if (row.id !== activeEditRowId) {
              return row;
            }

            return {
              ...row,
              ...payload,
            };
          });

          if (typeof window.showSuccess === 'function') {
            window.showSuccess('Zone row updated in draft.');
          }
        } else {
          rows.push({
            id: `draft-${Date.now()}-${Math.random().toString(16).slice(2, 8)}`,
            ...payload,
          });

          if (typeof window.showSuccess === 'function') {
            window.showSuccess('Zone row added to draft.');
          }
        }

        closeModal();
        renderRows();
      });

      tableBody.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
          return;
        }

        const editButton = target.closest('[data-zone-edit]');
        if (editButton instanceof HTMLElement) {
          const rowId = editButton.dataset.zoneId || '';
          const row = rows.find((item) => item.id === rowId);
          if (!row) {
            return;
          }

          activeEditRowId = row.id;
          zoneNameInput.value = row.zone;
          zoneSteadfastInput.value = String(row.steadfast);
          zoneRedxInput.value = String(row.redx);
          setModalMode('edit');
          openModal();
          zoneNameInput.focus();
          return;
        }

        const removeButton = target.closest('[data-zone-remove]');
        if (removeButton instanceof HTMLElement) {
          const rowId = removeButton.dataset.zoneId || '';
          rows = rows.filter((row) => row.id !== rowId);
          renderRows();

          if (typeof window.showWarning === 'function') {
            window.showWarning('Zone row removed from draft.');
          }
        }
      });

      closeButtons.forEach((button) => {
        button.addEventListener('click', closeModal);
      });

      modal.addEventListener('click', (event) => {
        if (event.target === modal) {
          closeModal();
        }
      });

      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('active')) {
          closeModal();
        }
      });

      function loadDraftFromLocalStorage() {
        try {
          const raw = window.localStorage.getItem(storageKey);
          if (!raw) {
            return;
          }

          const parsed = JSON.parse(raw);
          if (!Array.isArray(parsed)) {
            return;
          }

          rows = normalizeRows(parsed, 'draft');

          if (rows.length === 0) {
            rows = normalizeRows(seedRows, 'seed');
            return;
          }

          if (typeof window.showInfo === 'function') {
            window.showInfo('Loaded zone matrix draft from browser storage.');
          }
        } catch (error) {
          rows = normalizeRows(seedRows, 'seed');
          window.localStorage.removeItem(storageKey);
        }
      }

      function normalizeRows(rawRows, prefix) {
        if (!Array.isArray(rawRows)) {
          return [];
        }

        return rawRows
          .map((row, index) => normalizeRow(row, `${prefix}-${index}`))
          .filter((row) => row.zone !== '');
      }

      function normalizeRow(rawRow, fallbackId) {
        const source = rawRow && typeof rawRow === 'object' ? rawRow : {};

        return {
          id: String(source.id ?? fallbackId),
          zone: String(source.zone ?? '').trim(),
          steadfast: parseRate(source.steadfast),
          redx: parseRate(source.redx),
        };
      }

      function parseRate(value) {
        const numeric = Number.parseFloat(String(value ?? '').replace(/[^\d.]/g, ''));
        if (!Number.isFinite(numeric)) {
          return 0;
        }

        return Math.max(0, Math.round(numeric));
      }

      function renderRows() {
        if (rows.length === 0) {
          tableBody.innerHTML = `
            <tr data-zone-empty-row>
              <td colspan="4" class="courier-empty-row">No zone rows available.</td>
            </tr>
          `;
          return;
        }

        tableBody.innerHTML = rows.map((row) => {
          return `
            <tr data-zone-row data-zone-id="${escapeHtml(row.id)}">
              <td class="courier-cell-strong">${escapeHtml(row.zone)}</td>
              <td>৳${escapeHtml(String(row.steadfast))}</td>
              <td>৳${escapeHtml(String(row.redx))}</td>
              <td>
                <div class="courier-row-actions">
                  <button type="button" class="btn btn-primary btn-sm" data-zone-edit data-zone-id="${escapeHtml(row.id)}">Edit</button>
                  <button type="button" class="btn btn-danger btn-sm" data-zone-remove data-zone-id="${escapeHtml(row.id)}">Remove</button>
                </div>
              </td>
            </tr>
          `;
        }).join('');
      }

      function setModalMode(mode) {
        if (mode === 'edit') {
          modalTitle.textContent = 'Edit Zone';
          modalSubmit.textContent = 'Update Zone';
          modalSubmit.classList.remove('btn-success');
          modalSubmit.classList.add('btn-primary');
          return;
        }

        modalTitle.textContent = 'Add Zone';
        modalSubmit.textContent = 'Add Zone';
        modalSubmit.classList.remove('btn-primary');
        modalSubmit.classList.add('btn-success');
      }

      function openModal() {
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
      }

      function closeModal() {
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
      }

      function escapeHtml(value) {
        return String(value)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
      }
    });
  </script>
@endsection
