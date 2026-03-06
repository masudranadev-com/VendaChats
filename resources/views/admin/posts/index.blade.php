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

    .posts-row-actions {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
    }

    .posts-product-title {
      font-weight: 600;
      color: var(--text-primary, #1a202c);
    }

    .posts-product-price {
      font-weight: 700;
      color: var(--text-primary, #1a202c);
      white-space: nowrap;
    }
  </style>

  <div class="toast-container" id="toastContainer" aria-live="polite"></div>

  <div class="page-header posts-page-header">
    <div>
      <h1 class="page-title">{{ $title }}</h1>
      <p class="page-subtitle">{{ $subtitle }}</p>
    </div>

    <div class="posts-meta-grid">
      <div class="posts-meta-card">
        <span>Total Posts</span>
        <strong data-posts-total-count>0</strong>
      </div>
      <div class="posts-meta-card">
        <span>Total Comments</span>
        <strong data-comments-total-count>0</strong>
      </div>
    </div>
  </div>

  <section class="card mt-xl">
    <div class="card-header">
      <h3 class="card-title">Filtering Options</h3>
      <span class="badge badge-info" data-posts-filter-count>Loading...</span>
    </div>

    <form method="GET" action="{{ route('admin.posts') }}" class="posts-filter-form">
      <div class="form-group">
        <label class="form-label">Filter by Posts</label>

        <div
          class="pms"
          id="postsMultiselect"
          data-api-base-url="{{ $postsApiBaseUrl }}"
          data-refresh-token="{{ $postsRefreshToken }}"
        >
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

            <ul class="pms__list" id="pmsList"></ul>
            <p class="pms__empty" id="pmsEmpty">Loading posts...</p>
          </div>
        </div>

        {{-- Hidden inputs populated when a post is selected --}}
        <input type="hidden" name="post_id"         id="hiddenPostId">
        <input type="hidden" name="message"         id="hiddenPostMessage">
        <input type="hidden" name="post_date"       id="hiddenPostDate">
        <input type="hidden" name="total_comments"  id="hiddenPostComments">
      </div>

      <div class="form-group">
        <label class="form-label">Filter by Product</label>

        <div class="pms" id="productSelect">
          {{-- Trigger --}}
          <div
            class="pms__trigger"
            id="productTrigger"
            tabindex="0"
            role="combobox"
            aria-haspopup="listbox"
            aria-expanded="false"
            aria-controls="productDropdown"
          >
            <span id="productLabel">Select a product...</span>
            <span class="pms__arrow" aria-hidden="true">
              <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
              </svg>
            </span>
          </div>

          {{-- Dropdown --}}
          <div class="pms__dropdown" id="productDropdown" role="listbox" hidden>
            <div class="pms__search-wrap">
              <input class="pms__search" id="productSearch" type="text" placeholder="Search products..." autocomplete="off">
            </div>
            <ul class="pms__list" id="productList"></ul>
            <p class="pms__empty" id="productEmpty" hidden>No products found</p>
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
      <span class="badge badge-primary" data-posts-table-count>Loading...</span>
    </div>

    <div class="table-container posts-table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Post Title</th>
            <th>Product</th>
            <th>Price</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody data-posts-queue-body>
          <tr>
            <td colspan="4" class="posts-time" style="text-align: center;">Loading queued posts...</td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const pms = document.getElementById('postsMultiselect');
      const trigger = document.getElementById('pmsTrigger');
      const dropdown = document.getElementById('pmsDropdown');
      const labelEl = document.getElementById('pmsLabel');
      const searchEl = document.getElementById('pmsSearch');
      const listEl = document.getElementById('pmsList');
      const emptyEl = document.getElementById('pmsEmpty');
      const queueBody = document.querySelector('[data-posts-queue-body]');
      const postsCountNode = document.querySelector('[data-posts-total-count]');
      const commentsCountNode = document.querySelector('[data-comments-total-count]');
      const filterCountBadge = document.querySelector('[data-posts-filter-count]');
      const tableCountBadge = document.querySelector('[data-posts-table-count]');

      if (
        !pms ||
        !trigger ||
        !dropdown ||
        !labelEl ||
        !searchEl ||
        !listEl ||
        !emptyEl ||
        !queueBody
      ) {
        return;
      }

      const apiBaseUrl = String(pms.dataset.apiBaseUrl || '').replace(/\/+$/, '');
      const refreshToken = String(pms.dataset.refreshToken || '').trim();

      const toInt = (value) => {
        const parsed = Number.parseInt(String(value ?? '0'), 10);
        return Number.isFinite(parsed) ? parsed : 0;
      };

      const toBool = (value) => value === true || value === 1 || value === '1' || value === 'true';

      const truncate = (value, maxLength = 25) => {
        const text = String(value || '').trim();
        if (text.length <= maxLength) {
          return text;
        }
        return `${text.slice(0, Math.max(0, maxLength - 3))}...`;
      };

      const formatMoney = (value) => {
        const parsed = Number(value);
        if (!Number.isFinite(parsed)) {
          return '-';
        }
        return `৳${parsed.toLocaleString('en-US', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2,
        })}`;
      };

      const clearElement = (node) => {
        while (node.firstChild) {
          node.removeChild(node.firstChild);
        }
      };

      const coerceArrayPayload = (payload) => {
        if (Array.isArray(payload)) {
          return payload;
        }
        if (payload && typeof payload === 'object' && Array.isArray(payload.data)) {
          return payload.data;
        }
        return null;
      };

      const readErrorMessage = (payload, fallback) => {
        if (typeof payload === 'string' && payload.trim()) {
          return payload.trim();
        }
        if (payload && typeof payload === 'object') {
          return String(payload.message || payload.error || fallback);
        }
        return fallback;
      };

      const setPmsMessage = (message) => {
        clearElement(listEl);
        emptyEl.textContent = message;
        emptyEl.hidden = false;
      };

      const setQueueMessage = (message) => {
        clearElement(queueBody);
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 4;
        cell.className = 'posts-time';
        cell.style.textAlign = 'center';
        cell.textContent = message;
        row.appendChild(cell);
        queueBody.appendChild(row);
      };

      const getAutoCheckbox = () => listEl.querySelector('.pms__checkbox[value="auto"]');
      const getRegularItems = () => Array.from(listEl.querySelectorAll('.pms__item')).filter((item) => {
        const checkbox = item.querySelector('.pms__checkbox');
        return checkbox && checkbox.value !== 'auto';
      });

      const getRegularCheckboxes = () => getRegularItems()
        .map((item) => item.querySelector('.pms__checkbox'))
        .filter((checkbox) => checkbox instanceof HTMLInputElement);

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
        const autoCheckbox = getAutoCheckbox();
        if (autoCheckbox instanceof HTMLInputElement && autoCheckbox.checked) {
          labelEl.textContent = 'Auto mode';
          return;
        }

        const checkedCount = getRegularCheckboxes().filter((checkbox) => checkbox.checked).length;
        labelEl.textContent = checkedCount === 0
          ? 'Select posts...'
          : checkedCount === 1 ? '1 post selected' : `${checkedCount} posts selected`;
      }

      function setAutoMode(active) {
        getRegularItems().forEach((item) => {
          const checkbox = item.querySelector('.pms__checkbox');
          if (!(checkbox instanceof HTMLInputElement)) {
            return;
          }
          const isQueueLocked = item.dataset.queueLocked === '1';

          if (active) {
            checkbox.checked = false;
            checkbox.disabled = true;
            item.classList.add('pms__item--locked');
          } else {
            checkbox.disabled = isQueueLocked;
            item.classList.toggle('pms__item--locked', isQueueLocked);
          }
        });

        updateLabel();
      }

      function filterList(query) {
        const q = String(query || '').trim().toLowerCase();
        let visibleCount = 0;
        const hasListItems = listEl.querySelector('.pms__item') !== null;

        Array.from(listEl.querySelectorAll('.pms__item')).forEach((item) => {
          const title = String(item.dataset.title || '').toLowerCase();
          const match = !q || title.includes(q);
          item.hidden = !match;
          if (match) {
            visibleCount += 1;
          }
        });

        if (visibleCount === 0 && hasListItems) {
          emptyEl.textContent = 'No posts found';
        }
        emptyEl.hidden = visibleCount > 0;
      }

      function bindListCheckboxEvents() {
        const autoCheckbox = getAutoCheckbox();
        if (autoCheckbox instanceof HTMLInputElement) {
          autoCheckbox.addEventListener('change', () => setAutoMode(autoCheckbox.checked));
        }

        const hiddenPostId       = document.getElementById('hiddenPostId');
        const hiddenPostMsg      = document.getElementById('hiddenPostMessage');
        const hiddenPostDate     = document.getElementById('hiddenPostDate');
        const hiddenPostComments = document.getElementById('hiddenPostComments');

        getRegularCheckboxes().forEach((checkbox) => {
          checkbox.addEventListener('change', () => {
            if (checkbox.checked) {
              getRegularCheckboxes().forEach((other) => {
                if (other !== checkbox) other.checked = false;
              });

              const item = checkbox.closest('.pms__item');
              if (hiddenPostId)       hiddenPostId.value       = item?.dataset.postId       ?? '';
              if (hiddenPostMsg)      hiddenPostMsg.value      = item?.dataset.postMsg      ?? '';
              if (hiddenPostDate)     hiddenPostDate.value     = item?.dataset.postDate     ?? '';
              if (hiddenPostComments) hiddenPostComments.value = item?.dataset.postComments ?? '0';
            } else {
              if (hiddenPostId)       hiddenPostId.value       = '';
              if (hiddenPostMsg)      hiddenPostMsg.value      = '';
              if (hiddenPostDate)     hiddenPostDate.value     = '';
              if (hiddenPostComments) hiddenPostComments.value = '';
            }

            updateLabel();
          });
        });
      }

      const buildListItem = ({value, title, badgeText = '', auto = false, postMessage = '', postDate = '', postComments = 0, isQueue = false}) => {
        const item = document.createElement('li');
        item.className = 'pms__item';
        item.dataset.title        = String(title || '').toLowerCase();
        item.dataset.postId       = String(value);
        item.dataset.postMsg      = String(postMessage || title || '');
        item.dataset.postDate     = String(postDate || '');
        item.dataset.postComments = String(postComments);
        item.dataset.queueLocked  = isQueue ? '1' : '0';

        if (auto) {
          item.style.background = 'aliceblue';
        }
        if (isQueue) {
          item.classList.add('pms__item--locked');
        }

        const label = document.createElement('label');
        label.className = 'pms__item-label';

        const checkbox = document.createElement('input');
        checkbox.className = 'pms__checkbox';
        checkbox.type = 'checkbox';
        checkbox.value = String(value);
        checkbox.disabled = isQueue;

        const titleNode = document.createElement('span');
        titleNode.className = 'pms__item-title';
        titleNode.textContent = String(title || '');

        label.appendChild(checkbox);
        label.appendChild(titleNode);

        if (badgeText) {
          const badge = document.createElement('span');
          badge.className = 'pms__badge';
          badge.textContent = badgeText;
          label.appendChild(badge);
        }

        item.appendChild(label);
        return item;
      };

      const renderPmsPosts = (posts) => {
        clearElement(listEl);

        // listEl.appendChild(buildListItem({
        //   value: 'auto',
        //   title: 'Auto reply latest 5 comments',
        //   auto: true,
        // }));

        posts.forEach((post, index) => {
          const postId   = String(post.post_id || `post_${index + 1}`);
          const title    = String(post.message || post.post_title || postId).trim() || postId;
          const comments = toInt(post.total_comments);
          const postDate = String(post.post_date || post.created_time || '');
          const isQueue  = toBool(post.is_queue);

          listEl.appendChild(buildListItem({
            value: postId,
            title,
            badgeText: isQueue ? `Queued - ${comments} comments` : `${comments} comments`,
            postMessage: title,
            postDate,
            postComments: comments,
            isQueue,
          }));
        });

        emptyEl.hidden = true;
        bindListCheckboxEvents();
        setAutoMode(false);
        filterList(searchEl.value);
        updateLabel();

        const totalComments = posts.reduce((sum, post) => sum + toInt(post.total_comments), 0);
        if (postsCountNode) {
          postsCountNode.textContent = String(posts.length);
        }
        if (commentsCountNode) {
          commentsCountNode.textContent = String(totalComments);
        }
        if (filterCountBadge) {
          filterCountBadge.textContent = `${posts.length} shown`;
        }
      };

      const renderQueuePosts = (posts) => {
        clearElement(queueBody);

        if (!posts.length) {
          setQueueMessage('No queued posts found.');
          if (tableCountBadge) {
            tableCountBadge.textContent = '0 queued';
          }
          return;
        }

        const fragment = document.createDocumentFragment();

        posts.forEach((post, index) => {
          const queueId = toInt(post.id);
          const row = document.createElement('tr');
          const title = String(post.post_title || post.message || post.post_id || `Post ${index + 1}`).trim();
          const productTitle = String(post.product_title || 'N/A').trim() || 'N/A';
          const productPrice = formatMoney(post.product_price);

          const titleCell = document.createElement('td');
          titleCell.className = 'posts-title-cell';
          titleCell.title = title;
          titleCell.textContent = truncate(title, 25);

          const productCell = document.createElement('td');
          const productText = document.createElement('span');
          productText.className = 'posts-product-title';
          productText.textContent = truncate(productTitle, 35);
          productText.title = productTitle;
          productCell.appendChild(productText);

          const priceCell = document.createElement('td');
          const priceText = document.createElement('span');
          priceText.className = 'posts-product-price';
          priceText.textContent = productPrice;
          priceCell.appendChild(priceText);

          const actionCell = document.createElement('td');
          const actionWrap = document.createElement('div');
          actionWrap.className = 'posts-row-actions';

          const viewButton = document.createElement('button');
          viewButton.type = 'button';
          viewButton.className = 'btn btn-secondary btn-sm';
          viewButton.textContent = 'View Product';
          viewButton.addEventListener('click', () => {
            showToast(`Product: ${productTitle} | Price: ${productPrice}`, 'info');
          });

          const deleteButton = document.createElement('button');
          deleteButton.type = 'button';
          deleteButton.className = 'btn btn-danger btn-sm';
          deleteButton.textContent = 'Delete';
          deleteButton.addEventListener('click', async () => {
            if (!queueId) {
              showToast('Invalid queue post id.', 'error');
              return;
            }

            const shouldDelete = window.confirm('Delete this queue post?');
            if (!shouldDelete) {
              return;
            }

            const originalLabel = deleteButton.textContent;
            deleteButton.disabled = true;
            deleteButton.textContent = 'Deleting...';

            try {
              const payload = await deleteQueuePost(queueId);
              showToast(payload.message || 'Queue post deleted', 'success');
              await loadPostsData();
            } catch (error) {
              showToast(error?.message || 'Failed to delete queue post.', 'error');
            } finally {
              deleteButton.disabled = false;
              deleteButton.textContent = originalLabel;
            }
          });

          actionWrap.appendChild(viewButton);
          actionWrap.appendChild(deleteButton);
          actionCell.appendChild(actionWrap);

          row.appendChild(titleCell);
          row.appendChild(productCell);
          row.appendChild(priceCell);
          row.appendChild(actionCell);
          fragment.appendChild(row);
        });

        queueBody.appendChild(fragment);
        if (tableCountBadge) {
          tableCountBadge.textContent = `${posts.length} queued`;
        }
      };

      const deleteQueuePost = async (queueId) => {
        if (!apiBaseUrl) {
          throw new Error('Missing backend API URL.');
        }

        const endpoint = `${apiBaseUrl}/api/admin/posts/queue/${encodeURIComponent(String(queueId))}`;
        const abortController = new AbortController();
        const timeoutId = window.setTimeout(() => abortController.abort(), 12000);

        try {
          const response = await fetch(endpoint, {
            method: 'DELETE',
            headers: {
              'Accept': 'application/json',
              'x-refresh-token': refreshToken,
            },
            signal: abortController.signal,
          });

          const contentType = response.headers.get('content-type') || '';
          const payload = contentType.includes('application/json')
            ? await response.json()
            : await response.text();

          if (!response.ok) {
            if (response.status === 401) {
              throw new Error('Unauthorized (401). Session token is invalid or expired.');
            }
            throw new Error(readErrorMessage(payload, `Delete failed (${response.status}).`));
          }

          return payload && typeof payload === 'object'
            ? payload
            : { message: 'queue post deleted' };
        } catch (error) {
          if (error?.name === 'AbortError') {
            throw new Error('Request timed out. Please try again.');
          }
          throw error;
        } finally {
          window.clearTimeout(timeoutId);
        }
      };

      const fetchApiArray = async (path, refreshToken) => {
        if (!apiBaseUrl) {
          throw new Error('Missing backend API URL.');
        }

        const endpoint = `${apiBaseUrl}${path}`;
        const abortController = new AbortController();
        const timeoutId = window.setTimeout(() => abortController.abort(), 12000);

        try {
          const response = await fetch(endpoint, {
            method: 'GET',
            headers: {
              'Accept': 'application/json',
              'x-refresh-token': refreshToken,
            },
            signal: abortController.signal,
          });

          const contentType = response.headers.get('content-type') || '';
          const payload = contentType.includes('application/json')
            ? await response.json()
            : await response.text();

          if (!response.ok) {
            if (response.status === 401) {
              throw new Error('Unauthorized (401). Session token is invalid or expired.');
            }
            throw new Error(readErrorMessage(payload, `Request failed (${response.status}).`));
          }

          const data = coerceArrayPayload(payload);
          if (!data) {
            throw new Error('Unexpected response format from backend API.');
          }

          return data;
        } catch (error) {
          if (error?.name === 'AbortError') {
            throw new Error('Request timed out. Please try again.');
          }
          throw error;
        } finally {
          window.clearTimeout(timeoutId);
        }
      };

      const loadPostsData = async () => {
        if (!refreshToken) {
          const message = 'Missing refresh token. Please login again.';
          setPmsMessage(message);
          setQueueMessage(message);
          if (typeof window.showError === 'function') {
            window.showError(message);
          }
          return;
        }

        setPmsMessage('Loading posts...');
        setQueueMessage('Loading queued posts...');

        const [facebookPostsResult, queuePostsResult] = await Promise.allSettled([
          fetchApiArray('/api/admin/posts', refreshToken),
          fetchApiArray('/api/admin/posts/queue', refreshToken),
        ]);

        if (facebookPostsResult.status === 'fulfilled') {
          renderPmsPosts(facebookPostsResult.value);
        } else {
          const message = facebookPostsResult.reason?.message || 'Failed to load Facebook posts.';
          setPmsMessage(message);
          if (filterCountBadge) {
            filterCountBadge.textContent = 'Unavailable';
          }
          if (typeof window.showError === 'function') {
            window.showError(message);
          }
        }

        if (queuePostsResult.status === 'fulfilled') {
          renderQueuePosts(queuePostsResult.value);
        } else {
          const message = queuePostsResult.reason?.message || 'Failed to load queued posts.';
          setQueueMessage(message);
          if (tableCountBadge) {
            tableCountBadge.textContent = 'Unavailable';
          }
          if (typeof window.showError === 'function') {
            window.showError(message);
          }
        }
      };

      trigger.addEventListener('click', () => dropdown.hidden ? open() : close());

      trigger.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          dropdown.hidden ? open() : close();
        }
      });

      searchEl.addEventListener('input', () => filterList(searchEl.value));

      document.addEventListener('click', (event) => {
        if (!pms.contains(event.target)) {
          close();
        }
      });

      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !dropdown.hidden) {
          close();
        }
      });

      loadPostsData();

      // ── Toaster ──
      const toastContainer = document.getElementById('toastContainer');

      const showToast = (message, type = 'success') => {
        if (!toastContainer) return;
        const iconByType = {
          success: '✓',
          error: '✕',
          info: 'i',
        };

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
          <span class="toast__icon">${iconByType[type] || '✓'}</span>
          <span class="toast__msg">${String(message)}</span>
          <button class="toast__close" aria-label="Dismiss">×</button>
        `;

        toast.querySelector('.toast__close').addEventListener('click', () => toast.remove());
        toastContainer.appendChild(toast);
        window.setTimeout(() => toast.remove(), 4000);
      };

      // ── Form Submit → POST /api/admin/posts/queue ──
      const filterForm = document.querySelector('.posts-filter-form');
      if (filterForm) {
        filterForm.addEventListener('submit', async (event) => {
          event.preventDefault();

          const postId       = document.getElementById('hiddenPostId')?.value.trim()       ?? '';
          const postTitle    = document.getElementById('hiddenPostMessage')?.value.trim()  ?? '';
          const postDate     = document.getElementById('hiddenPostDate')?.value.trim()     ?? '';
          const postComments = Number(document.getElementById('hiddenPostComments')?.value ?? '0');
          const productEl = filterForm.querySelector('input[name="product_id"]:checked');
          const productId = productEl ? Number(productEl.value) : null;

          if (!postId || !postTitle || !productId) {
            showToast('Please select a post and a product before applying filters.', 'error');
            return;
          }

          const submitBtn = filterForm.querySelector('button[type="submit"]');
          if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Applying...'; }

          try {
            const response = await fetch(`${apiBaseUrl}/api/admin/posts/queue`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'x-refresh-token': refreshToken,
              },
              body: JSON.stringify({
                post_id:    postId,
                post_title: postTitle,
                product_id:     productId,
                total_comments: postComments,
                post_date:      postDate || null,
              }),
            });

            const payload = await response.json().catch(() => ({}));

            if (response.status === 201) {
              showToast(payload.message || 'Post assigned to queue.', 'success');
              loadPostsData();
            } else if (response.status === 400) {
              showToast(payload.error || 'Validation error. Please check your input.', 'error');
            } else {
              showToast(payload.error || `Server error (${response.status}). Please try again.`, 'error');
            }
          } catch {
            showToast('Network error. Could not reach the server.', 'error');
          } finally {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Apply Filters'; }
          }
        });
      }

      // ── Product Select ──
      (() => {
        const ps         = document.getElementById('productSelect');
        const pTrigger   = document.getElementById('productTrigger');
        const pDropdown  = document.getElementById('productDropdown');
        const pLabelEl   = document.getElementById('productLabel');
        const pSearchEl  = document.getElementById('productSearch');
        const pListEl    = document.getElementById('productList');
        const pEmptyEl   = document.getElementById('productEmpty');

        if (!ps || !pTrigger || !pDropdown || !pLabelEl || !pSearchEl || !pListEl || !pEmptyEl) return;

        const openP  = () => { pDropdown.hidden = false; pTrigger.setAttribute('aria-expanded', 'true'); pSearchEl.focus(); };
        const closeP = () => { pDropdown.hidden = true;  pTrigger.setAttribute('aria-expanded', 'false'); pSearchEl.value = ''; filterP(''); };

        const filterP = (query) => {
          const q = query.toLowerCase().trim();
          let visible = 0;
          pListEl.querySelectorAll('.pms__item').forEach(item => {
            const match = !q || item.dataset.title.includes(q);
            item.hidden = !match;
            if (match) visible++;
          });
          if (visible === 0) {
            pEmptyEl.textContent = 'No products found';
          }
          pEmptyEl.hidden = visible > 0;
        };

        const setProductMessage = (message) => {
          clearElement(pListEl);
          pEmptyEl.textContent = message;
          pEmptyEl.hidden = false;
        };

        const formatProductPrice = (value) => {
          const parsed = Number(value);
          if (!Number.isFinite(parsed)) {
            return '';
          }
          return `$${parsed.toLocaleString('en-US')}`;
        };

        const renderProducts = (products) => {
          clearElement(pListEl);

          if (!products.length) {
            setProductMessage('No products found');
            return;
          }

          products.forEach(product => {
            const productId = Number.parseInt(String(product?.id ?? ''), 10);
            const productName = String(product?.name || '').trim();
            if (!Number.isFinite(productId) || !productName) {
              return;
            }

            const badgeText = formatProductPrice(product?.price);

            const item = document.createElement('li');
            item.className = 'pms__item';
            item.dataset.title = productName.toLowerCase();

            const label = document.createElement('label');
            label.className = 'pms__item-label';

            const radio = document.createElement('input');
            radio.type  = 'radio';
            radio.name  = 'product_id';
            radio.value = String(productId);
            radio.className = 'pms__checkbox';

            radio.addEventListener('change', () => {
              pLabelEl.textContent = productName;
              closeP();
            });

            const titleSpan = document.createElement('span');
            titleSpan.className   = 'pms__item-title';
            titleSpan.textContent = productName;

            label.appendChild(radio);
            label.appendChild(titleSpan);

            if (badgeText) {
              const badge = document.createElement('span');
              badge.className   = 'pms__badge';
              badge.style.background = '#dbeafe';
              badge.style.color      = '#1d4ed8';
              badge.textContent = badgeText;
              label.appendChild(badge);
            }

            item.appendChild(label);
            pListEl.appendChild(item);
          });

          filterP(pSearchEl.value);
        };

        const getProductsFromPayload = (payload) => {
          if (Array.isArray(payload?.products?.data)) {
            return payload.products.data;
          }
          if (Array.isArray(payload?.data)) {
            return payload.data;
          }
          if (Array.isArray(payload)) {
            return payload;
          }
          return [];
        };

        const getProductsLastPage = (payload) => {
          const value = payload?.products?.pagination?.last_page ?? payload?.pagination?.last_page ?? 1;
          const parsed = Number.parseInt(String(value), 10);
          return Number.isFinite(parsed) && parsed > 0 ? parsed : 1;
        };

        const fetchProductsPage = async (page) => {
          if (!apiBaseUrl) {
            throw new Error('Missing backend API URL.');
          }

          const endpoint = new URL(`${apiBaseUrl}/api/admin/products`);
          endpoint.searchParams.set('page', String(page));
          endpoint.searchParams.set('per_page', '50');
          endpoint.searchParams.set('sort_by', 'latest');

          const abortController = new AbortController();
          const timeoutId = window.setTimeout(() => abortController.abort(), 12000);

          try {
            const response = await fetch(endpoint.toString(), {
              method: 'GET',
              headers: {
                'Accept': 'application/json',
                'x-refresh-token': refreshToken,
              },
              signal: abortController.signal,
            });

            const contentType = response.headers.get('content-type') || '';
            const payload = contentType.includes('application/json')
              ? await response.json()
              : await response.text();

            if (!response.ok) {
              if (response.status === 401) {
                throw new Error('Unauthorized (401). Session token is invalid or expired.');
              }
              throw new Error(readErrorMessage(payload, `Request failed (${response.status}).`));
            }

            if (!payload || typeof payload !== 'object') {
              throw new Error('Unexpected response format from products API.');
            }

            return payload;
          } catch (error) {
            if (error?.name === 'AbortError') {
              throw new Error('Request timed out. Please try again.');
            }
            throw error;
          } finally {
            window.clearTimeout(timeoutId);
          }
        };

        const loadProducts = async () => {
          if (!refreshToken) {
            setProductMessage('Missing refresh token. Please login again.');
            return;
          }

          setProductMessage('Loading products...');

          const allProducts = [];
          let page = 1;
          let lastPage = 1;

          do {
            const payload = await fetchProductsPage(page);
            allProducts.push(...getProductsFromPayload(payload));
            lastPage = getProductsLastPage(payload);
            page += 1;
          } while (page <= lastPage);

          const uniqueProducts = Array.from(
            new Map(
              allProducts
                .filter(product => product && typeof product === 'object')
                .map(product => [String(product.id), product])
            ).values()
          );

          renderProducts(uniqueProducts);
        };

        pTrigger.addEventListener('click', () => pDropdown.hidden ? openP() : closeP());
        pTrigger.addEventListener('keydown', e => {
          if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); pDropdown.hidden ? openP() : closeP(); }
        });
        pSearchEl.addEventListener('input', () => filterP(pSearchEl.value));
        document.addEventListener('click', e => { if (!ps.contains(e.target)) closeP(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && !pDropdown.hidden) closeP(); });

        loadProducts().catch((error) => {
          const message = error?.message || 'Failed to load products.';
          setProductMessage(message);
          if (typeof window.showError === 'function') {
            window.showError(message);
          }
        });
      })();
    });
  </script>
@endsection
