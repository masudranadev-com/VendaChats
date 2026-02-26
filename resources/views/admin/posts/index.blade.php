@extends('admin.master')

@section('title', $title)

@section('admin.content')
  <style>
    /* ── Posts Multi-Select ── */
    .pms {
      position: relative;
      width: 100%;
    }

    .pms__trigger {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      padding: 8px 12px;
      min-height: 38px;
      background: var(--input-bg, #fff);
      border: 1px solid var(--border-color, #e2e8f0);
      border-radius: var(--radius, 6px);
      cursor: pointer;
      user-select: none;
      font-size: 14px;
      color: var(--text-primary, #1a202c);
      transition: border-color 0.15s, box-shadow 0.15s;
    }

    .pms__trigger:hover { border-color: var(--primary, #6366f1); }

    .pms__trigger:focus-visible {
      outline: none;
      border-color: var(--primary, #6366f1);
      box-shadow: 0 0 0 3px rgba(99,102,241,.15);
    }

    .pms__trigger[aria-expanded="true"] {
      border-color: var(--primary, #6366f1);
      box-shadow: 0 0 0 3px rgba(99,102,241,.15);
    }

    .pms__trigger[aria-expanded="true"] .pms__arrow {
      transform: rotate(180deg);
    }

    .pms__arrow {
      flex-shrink: 0;
      color: var(--text-muted, #718096);
      transition: transform 0.2s;
    }

    .pms__dropdown {
      position: absolute;
      top: calc(100% + 4px);
      left: 0;
      right: 0;
      background: var(--bg-card, #fff);
      border: 1px solid var(--border-color, #e2e8f0);
      border-radius: var(--radius, 6px);
      box-shadow: 0 8px 24px rgba(0,0,0,.12);
      z-index: 200;
      overflow: hidden;
    }

    .pms__search-wrap {
      padding: 8px;
      border-bottom: 1px solid var(--border-color, #e2e8f0);
    }

    .pms__search {
      width: 100%;
      padding: 6px 10px;
      border: 1px solid var(--border-color, #e2e8f0);
      border-radius: 4px;
      font-size: 13px;
      background: var(--input-bg, #f8fafc);
      color: var(--text-primary, #1a202c);
      outline: none;
      box-sizing: border-box;
    }

    .pms__search:focus { border-color: var(--primary, #6366f1); }

    .pms__list {
      list-style: none;
      margin: 0;
      padding: 4px;
      max-height: 240px;
      overflow-y: auto;
    }

    .pms__item[hidden] { display: none; }

    .pms__item-label {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 8px 10px;
      border-radius: 4px;
      cursor: pointer;
      transition: background 0.1s;
    }

    .pms__item-label:hover { background: var(--bg-hover, #f1f5f9); }

    .pms__checkbox {
      flex-shrink: 0;
      width: 16px;
      height: 16px;
      accent-color: var(--primary, #6366f1);
      cursor: pointer;
    }

    .pms__item-title {
      flex: 1;
      font-size: 13px;
      color: var(--text-primary, #1a202c);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .pms__badge {
      flex-shrink: 0;
      font-size: 11px;
      font-weight: 600;
      padding: 2px 8px;
      border-radius: 99px;
      background: #fee2e2;
      color: #dc2626;
    }

    .pms__empty {
      padding: 16px;
      text-align: center;
      font-size: 13px;
      color: var(--text-muted, #718096);
    }

    .pms__item--locked .pms__item-label {
      opacity: 0.4;
      cursor: not-allowed;
    }

    .pms__item--locked .pms__checkbox {
      cursor: not-allowed;
    }
  </style>

  <div class="page-header posts-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="posts-meta-grid">
      <div class="posts-meta-card">
        <span>Total Posts</span>
        <strong>100</strong>
      </div>
      <div class="posts-meta-card">
        <span>Total Comments</span>
        <strong>10</strong>
      </div>
    </div>
  </div>

  <section class="card posts-countdown-card">
    <div class="posts-countdown-copy">
      <h3>Next auto reply will execute:</h3>
      <p>(count down)</p>
    </div>

    <div
      class="posts-countdown-time"
      data-auto-reply-countdown
      data-seconds="200"
      aria-live="polite"
    >
      10m 10s later
    </div>
  </section>

  <section class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Filtering Options</h3>
      <span class="badge badge-info">12 shown</span>
    </div>

    <form method="GET" action="{{ route('admin.posts') }}" class="posts-filter-form">
      <div class="form-group">
        <label class="form-label">Filter by Posts</label>

        <div class="pms" id="postsMultiselect">
          {{-- Trigger --}}
          <div
            class="pms__trigger"
            id="pmsTrigger"
            tabindex="0"
            role="combobox"
            aria-haspopup="listbox"
            aria-expanded="false"
            aria-controls="pmsDropdown"
          >
            <span id="pmsLabel">Select posts...</span>
            <span class="pms__arrow" aria-hidden="true">
              <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
              </svg>
            </span>
          </div>

          {{-- Dropdown --}}
          <div class="pms__dropdown" id="pmsDropdown" role="listbox" aria-multiselectable="true" hidden>
            <div class="pms__search-wrap">
              <input class="pms__search" id="pmsSearch" type="text" placeholder="Search posts..." autocomplete="off">
            </div>

            <ul class="pms__list" id="pmsList">
              <li class="pms__item" data-title="Auto reply latest 5 comments" style="background: aliceblue;">
                <label class="pms__item-label">
                  <input class="pms__checkbox" type="checkbox" name="post_ids[]" value="auto">
                  <span class="pms__item-title">Auto reply latest 5 comments</span>
                </label>
              </li>

              <li class="pms__item" data-title="how to grow your store sales in 2024">
                <label class="pms__item-label">
                  <input class="pms__checkbox" type="checkbox" name="post_ids[]" value="1">
                  <span class="pms__item-title">How to grow your store sales in 2024</span>
                  <span class="pms__badge">12 unreplied</span>
                </label>
              </li>
              <li class="pms__item" data-title="top 5 marketing strategies for small businesses">
                <label class="pms__item-label">
                  <input class="pms__checkbox" type="checkbox" name="post_ids[]" value="2">
                  <span class="pms__item-title">Top 5 marketing strategies for small businesses</span>
                  <span class="pms__badge">7 unreplied</span>
                </label>
              </li>
              <li class="pms__item" data-title="customer retention tips that actually work">
                <label class="pms__item-label">
                  <input class="pms__checkbox" type="checkbox" name="post_ids[]" value="3">
                  <span class="pms__item-title">Customer retention tips that actually work</span>
                  <span class="pms__badge">3 unreplied</span>
                </label>
              </li>
              <li class="pms__item" data-title="hello world">
                <label class="pms__item-label">
                  <input class="pms__checkbox" type="checkbox" name="post_ids[]" value="4">
                  <span class="pms__item-title">Hello world</span>
                  <span class="pms__badge">121 unreplied</span>
                </label>
              </li>
            </ul>

            <p class="pms__empty" id="pmsEmpty" hidden>No posts found</p>
          </div>
        </div>
      </div>

      <div class="posts-filter-actions">
        <button type="submit" class="btn btn-primary">Apply Filters</button>
      </div>
    </form>
  </section>

  <section class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Posts Table</h3>
      <span class="badge badge-primary">Live Feed</span>
    </div>

    <div class="table-container posts-table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Post Title</th>
            <th>Total Comments</th>
            <th>Time</th>
          </tr>
        </thead>
        <tbody>
          <tr>
              <td class="posts-title-cell" title="{{ "Hello world" }}">
                {{ \Illuminate\Support\Str::limit("Hello world", 25, '...') }}
              </td>
              <td>
                <span class="posts-comment-count">121</span>
              </td>
              <td>
                <span class="posts-time">10m ago</span>
              </td>
            </tr>
        </tbody>
      </table>
    </div>
  </section>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // ── Countdown ──
      const countdown = document.querySelector('[data-auto-reply-countdown]');
      if (countdown) {
        let remaining = Number.parseInt(countdown.dataset.seconds || '610', 10);
        if (!Number.isFinite(remaining) || remaining < 0) remaining = 610;

        const render = () => {
          if (remaining <= 0) { countdown.textContent = 'Executing now'; return; }
          const minutes = Math.floor(remaining / 60);
          const seconds = remaining % 60;
          countdown.textContent = `${minutes}m ${String(seconds).padStart(2, '0')}s later`;
        };

        render();
        const timer = window.setInterval(() => {
          if (remaining <= 0) { window.clearInterval(timer); return; }
          remaining -= 1;
          render();
        }, 1000);
      }

      // ── Posts Multi-Select ──
      const pms            = document.getElementById('postsMultiselect');
      if (!pms) return;

      const trigger        = document.getElementById('pmsTrigger');
      const dropdown       = document.getElementById('pmsDropdown');
      const labelEl        = document.getElementById('pmsLabel');
      const searchEl       = document.getElementById('pmsSearch');
      const listEl         = document.getElementById('pmsList');
      const emptyEl        = document.getElementById('pmsEmpty');
      const autoCheckbox   = listEl.querySelector('.pms__checkbox[value="auto"]');
      const regularItems   = [...listEl.querySelectorAll('.pms__item')].filter(item => !item.querySelector('.pms__checkbox[value="auto"]'));

      function open() {
        dropdown.hidden = false;
        trigger.setAttribute('aria-expanded', 'true');
        searchEl.focus();
      }

      function close() {
        dropdown.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');
        searchEl.value = '';
        filterList('');
      }

      function updateLabel() {
        if (autoCheckbox.checked) {
          labelEl.textContent = 'Auto mode';
          return;
        }
        const checked = listEl.querySelectorAll('.pms__checkbox:checked');
        labelEl.textContent = checked.length === 0
          ? 'Select posts...'
          : checked.length === 1 ? '1 post selected' : `${checked.length} posts selected`;
      }

      function setAutoMode(active) {
        regularItems.forEach(item => {
          const cb = item.querySelector('.pms__checkbox');
          if (active) {
            cb.checked  = false;
            cb.disabled = true;
            item.classList.add('pms__item--locked');
          } else {
            cb.disabled = false;
            item.classList.remove('pms__item--locked');
          }
        });
        updateLabel();
      }

      function filterList(query) {
        const q = query.toLowerCase().trim();
        let visible = 0;
        listEl.querySelectorAll('.pms__item').forEach(item => {
          const match = !q || item.dataset.title.includes(q);
          item.hidden = !match;
          if (match) visible++;
        });
        emptyEl.hidden = visible > 0;
      }

      autoCheckbox.addEventListener('change', () => setAutoMode(autoCheckbox.checked));

      regularItems.forEach(item => {
        item.querySelector('.pms__checkbox').addEventListener('change', updateLabel);
      });

      trigger.addEventListener('click', () => dropdown.hidden ? open() : close());

      trigger.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); dropdown.hidden ? open() : close(); }
      });

      searchEl.addEventListener('input', () => filterList(searchEl.value));

      document.addEventListener('click', e => { if (!pms.contains(e.target)) close(); });
      document.addEventListener('keydown', e => { if (e.key === 'Escape' && !dropdown.hidden) close(); });
    });
  </script>
@endsection
