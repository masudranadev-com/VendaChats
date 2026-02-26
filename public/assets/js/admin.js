/* ═══════════════════════════════════════════════════════════
   A Metafy ADMIN PANEL — MAIN JAVASCRIPT
   All interactive functionality
═══════════════════════════════════════════════════════════ */

const AdminCkeditor = (() => {
  const selector = 'textarea[data-ckeditor], textarea.js-ckeditor';
  const instances = new Map();
  let hasWarnedMissingLibrary = false;

  function resolveTextarea(field) {
    if (field instanceof HTMLTextAreaElement) {
      return field;
    }

    if (typeof field === 'string') {
      const match = document.querySelector(field);
      return match instanceof HTMLTextAreaElement ? match : null;
    }

    return null;
  }

  function toolbarFromDataset(textarea) {
    const toolbarTokens = textarea.dataset.ckeditorToolbar || '';
    if (!toolbarTokens.trim()) {
      return ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'blockQuote', 'insertTable', 'codeBlock', '|', 'undo', 'redo'];
    }

    return toolbarTokens
      .split(',')
      .map((token) => token.trim())
      .filter(Boolean);
  }

  function createCk5Adapter(textarea, editor) {
    return {
      kind: 'ckeditor5',
      editor,
      setData(value = '') {
        const nextValue = String(value ?? '');
        editor.setData(nextValue);
        textarea.value = nextValue;
      },
      getData() {
        const value = editor.getData();
        textarea.value = value;
        return value;
      },
      sync() {
        textarea.value = editor.getData();
      },
    };
  }

  function createCk4Adapter(textarea, editor) {
    let isReady = editor.status === 'ready';
    let queuedData = null;

    if (typeof editor.on === 'function') {
      editor.on('instanceReady', () => {
        isReady = true;

        if (queuedData !== null) {
          editor.setData(queuedData);
          queuedData = null;
        }
      });
    }

    return {
      kind: 'ckeditor4',
      editor,
      setData(value = '') {
        const nextValue = String(value ?? '');
        textarea.value = nextValue;

        if (isReady && typeof editor.setData === 'function') {
          editor.setData(nextValue);
          return;
        }

        queuedData = nextValue;
      },
      getData() {
        if (isReady && typeof editor.getData === 'function') {
          const value = editor.getData();
          textarea.value = value;
          return value;
        }

        if (queuedData !== null) {
          textarea.value = queuedData;
          return queuedData;
        }

        return textarea.value;
      },
      sync() {
        if (isReady && typeof editor.updateElement === 'function') {
          editor.updateElement();
          return;
        }

        if (queuedData !== null) {
          textarea.value = queuedData;
        }
      },
    };
  }

  async function create(textarea) {
    if (!(textarea instanceof HTMLTextAreaElement)) {
      return null;
    }

    if (instances.has(textarea)) {
      return instances.get(textarea);
    }

    const editorConstructor = window.ClassicEditor || window.CKEDITOR?.ClassicEditor;
    const hasCk4 = typeof window.CKEDITOR !== 'undefined' && typeof window.CKEDITOR.replace === 'function';

    if (typeof editorConstructor === 'undefined' && !hasCk4) {
      if (!hasWarnedMissingLibrary) {
        console.warn('CKEditor script not found. Rich-text fields will stay as textarea.');
        hasWarnedMissingLibrary = true;
      }
      return null;
    }

    if (typeof editorConstructor !== 'undefined') {
      try {
        const editor = await editorConstructor.create(textarea, {
          toolbar: {
            items: toolbarFromDataset(textarea),
            shouldNotGroupWhenFull: true,
          }
        });
        const adapter = createCk5Adapter(textarea, editor);
        instances.set(textarea, adapter);
        textarea.dataset.ckeditorReady = 'true';

        const parentForm = textarea.closest('form');
        if (parentForm && parentForm.dataset.ckeditorSyncBound !== 'true') {
          parentForm.addEventListener('submit', () => syncAll());
          parentForm.dataset.ckeditorSyncBound = 'true';
        }

        return adapter;
      } catch (error) {
        console.error('Failed to initialize CKEditor 5 for', textarea.id || textarea.name || textarea, error);
      }
    }

    if (!hasCk4) {
      return null;
    }

    try {
      const ck4Editor = window.CKEDITOR.replace(textarea, {
        allowedContent: true,
        extraAllowedContent: '*(*);*{*}',
        removePlugins: 'elementspath',
        toolbar: [
          { name: 'document', items: ['Source'] },
          { name: 'clipboard', items: ['Undo', 'Redo'] },
          { name: 'styles', items: ['Format'] },
          { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat'] },
          { name: 'paragraph', items: ['NumberedList', 'BulletedList', 'Outdent', 'Indent', 'Blockquote'] },
          { name: 'links', items: ['Link', 'Unlink'] },
          { name: 'insert', items: ['Table'] },
        ],
      });

      const adapter = createCk4Adapter(textarea, ck4Editor);
      instances.set(textarea, adapter);

      if (typeof ck4Editor.on === 'function') {
        ck4Editor.on('instanceReady', () => {
          textarea.dataset.ckeditorReady = 'true';
        });
      }

      const parentForm = textarea.closest('form');
      if (parentForm && parentForm.dataset.ckeditorSyncBound !== 'true') {
        parentForm.addEventListener('submit', () => syncAll());
        parentForm.dataset.ckeditorSyncBound = 'true';
      }

      return adapter;
    } catch (error) {
      console.error('Failed to initialize CKEditor 4 for', textarea.id || textarea.name || textarea, error);
      return null;
    }
  }

  async function init(scope = document) {
    const root = scope && typeof scope.querySelectorAll === 'function' ? scope : document;
    const textareas = Array.from(root.querySelectorAll(selector)).filter((textarea) => !instances.has(textarea));
    if (!textareas.length) {
      return [];
    }

    return Promise.all(textareas.map((textarea) => create(textarea)));
  }

  function setData(field, value = '') {
    const textarea = resolveTextarea(field);
    if (!textarea) {
      return;
    }

    const nextValue = String(value ?? '');
    const instance = instances.get(textarea);
    if (instance && typeof instance.setData === 'function') {
      instance.setData(nextValue);
      return;
    }

    textarea.value = nextValue;
  }

  function getInstance(field) {
    const textarea = resolveTextarea(field);
    if (!textarea) {
      return null;
    }

    return instances.get(textarea) || null;
  }

  function getData(field) {
    const textarea = resolveTextarea(field);
    if (!textarea) {
      return '';
    }

    const instance = instances.get(textarea);
    if (!instance || typeof instance.getData !== 'function') {
      return textarea.value;
    }

    return instance.getData();
  }

  function sync(field) {
    const textarea = resolveTextarea(field);
    if (!textarea) {
      return;
    }

    const instance = instances.get(textarea);
    if (!instance || typeof instance.sync !== 'function') {
      return;
    }

    instance.sync();
  }

  function syncAll() {
    instances.forEach((instance) => {
      if (instance && typeof instance.sync === 'function') {
        instance.sync();
      }
    });
  }

  return {
    selector,
    instances,
    create,
    init,
    getInstance,
    setData,
    getData,
    sync,
    syncAll,
  };
})();

window.AdminCkeditor = AdminCkeditor;

// ── Initialize on DOM load ──
document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  initCkEditors();
  initSidebar();
  initDropdowns();
  initModals();
  initToasts();
  initTables();
  initCharts();
  initSearch();
  initCampaignBuilder();
  initOrdersManualOrder();
  initBotSettings();
  initProductsAttentionPanel();
  initProductCreateSliderControl();
  initProductCreateDiscountOfferControl();
  initProductCreateAiWriter();
  setActivePage();
});

function initCkEditors() {
  if (!window.AdminCkeditor || typeof window.AdminCkeditor.init !== 'function') {
    return;
  }

  window.AdminCkeditor.init(document);
}

// ══════════════════════════════════════════
// THEME MANAGEMENT
// ══════════════════════════════════════════
function initTheme() {
  applyTheme('light');

  // Theme toggle button
  document.getElementById('themeToggle')?.addEventListener('click', () => applyTheme('light'));
}

function applyTheme(theme) {
  document.body.setAttribute('data-theme', 'light');
  localStorage.setItem('sellbuzz-theme', 'light');

  const icon = document.getElementById('themeToggle');
  if (icon) {
    icon.textContent = '☀️';
  }
}

function toggleTheme() {
  applyTheme('light');
}

// ══════════════════════════════════════════
// SIDEBAR MANAGEMENT
// ══════════════════════════════════════════
function initSidebar() {
  const sidebar = document.querySelector('.sidebar');
  const menuToggle = document.getElementById('menuToggle');
  const mobileOverlay = document.getElementById('mobileOverlay');

  // Toggle sidebar collapse (desktop)
  menuToggle?.addEventListener('click', () => {
    if (window.innerWidth > 1024) {
      sidebar?.classList.toggle('collapsed');
      localStorage.setItem('sidebar-collapsed', sidebar?.classList.contains('collapsed'));
    } else {
      // Mobile: toggle open
      sidebar?.classList.toggle('mobile-open');
    }
  });

  // Close mobile sidebar when clicking overlay
  mobileOverlay?.addEventListener('click', () => {
    sidebar?.classList.remove('mobile-open');
  });

  // Restore collapsed state
  if (localStorage.getItem('sidebar-collapsed') === 'true') {
    sidebar?.classList.add('collapsed');
  }
}

function setActivePage() {
  const page = resolveCurrentPage();
  document.querySelectorAll('.nav-item').forEach(item => {
    const href = item.getAttribute('href') || '';
    const navPage = href.split('/').pop() || '';
    item.classList.toggle('active', navPage === page);
  });
}

function resolveCurrentPage() {
  if (typeof window !== 'undefined' && window.__ADMIN_PAGE) {
    return window.__ADMIN_PAGE;
  }

  const lastSegment = (window.location.pathname.split('/').pop() || 'dashboard').replace('.html', '');
  return lastSegment || 'dashboard';
}

// ══════════════════════════════════════════
// DROPDOWN MENUS
// ══════════════════════════════════════════
function initDropdowns() {
  document.querySelectorAll('.dropdown').forEach(dropdown => {
    const trigger = dropdown.querySelector('.dropdown-trigger') || dropdown.querySelector('.header-btn') || dropdown.querySelector('.user-menu');

    trigger?.addEventListener('click', (e) => {
      e.stopPropagation();

      // Close other dropdowns
      document.querySelectorAll('.dropdown.active').forEach(d => {
        if (d !== dropdown) d.classList.remove('active');
      });

      dropdown.classList.toggle('active');
    });
  });

  // Close dropdowns when clicking outside
  document.addEventListener('click', () => {
    document.querySelectorAll('.dropdown.active').forEach(d => d.classList.remove('active'));
  });
}

// ══════════════════════════════════════════
// MODALS
// ══════════════════════════════════════════
function initModals() {
  // Open modal
  document.querySelectorAll('[data-modal]').forEach(trigger => {
    trigger.addEventListener('click', () => {
      const modalId = trigger.getAttribute('data-modal');
      openModal(modalId);
    });
  });

  // Close modal buttons
  document.querySelectorAll('.modal-close, [data-modal-close]').forEach(btn => {
    btn.addEventListener('click', () => {
      closeAllModals();
    });
  });

  // Close on overlay click
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) closeAllModals();
    });
  });

  // Close on Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeAllModals();
  });
}

function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
}

function closeAllModals() {
  document.querySelectorAll('.modal-overlay.active').forEach(modal => {
    modal.classList.remove('active');
  });
  document.body.style.overflow = '';
}

// ══════════════════════════════════════════
// TOAST NOTIFICATIONS
// ══════════════════════════════════════════
function initToasts() {
  if (!document.querySelector('.toast-container') && document.body) {
    const container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
}

function resolveToastContainer() {
  let container = document.querySelector('.toast-container');
  if (container) {
    return container;
  }

  if (!document.body) {
    return null;
  }

  container = document.createElement('div');
  container.className = 'toast-container';
  document.body.appendChild(container);
  return container;
}

function showToast(message, type = 'info', duration = 4000) {
  const container = resolveToastContainer();
  if (!container) {
    return;
  }

  const icons = {
    success: '✓',
    error: '✗',
    warning: '⚠',
    info: 'ℹ'
  };

  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `
    <div class="toast-icon">${icons[type]}</div>
    <div class="toast-content">
      <div class="toast-message">${message}</div>
    </div>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.animation = 'slideOut 0.3s forwards';
    setTimeout(() => toast.remove(), 300);
  }, duration);
}

// Global toast functions
window.showToast = showToast;
window.showSuccess = (msg) => showToast(msg, 'success');
window.showError = (msg) => showToast(msg, 'error');
window.showWarning = (msg) => showToast(msg, 'warning');
window.showInfo = (msg) => showToast(msg, 'info');

// ══════════════════════════════════════════
// TABLE INTERACTIONS
// ══════════════════════════════════════════
function initTables() {
  // Sortable columns
  document.querySelectorAll('.table th[data-sort]').forEach(th => {
    th.style.cursor = 'pointer';
    th.addEventListener('click', () => {
      const table = th.closest('table');
      const column = th.cellIndex;
      const order = th.dataset.order === 'asc' ? 'desc' : 'asc';
      sortTable(table, column, order);
      th.dataset.order = order;
    });
  });

  // Selectable rows
  document.querySelectorAll('.table-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedCount);
  });

  // Select all checkbox
  document.querySelectorAll('.select-all-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', (e) => {
      const table = checkbox.closest('table');
      table.querySelectorAll('.table-checkbox').forEach(cb => {
        cb.checked = e.target.checked;
      });
      updateSelectedCount();
    });
  });
}

function sortTable(table, column, order) {
  const tbody = table.querySelector('tbody');
  const rows = Array.from(tbody.querySelectorAll('tr'));

  rows.sort((a, b) => {
    const aVal = a.cells[column].textContent.trim();
    const bVal = b.cells[column].textContent.trim();

    if (order === 'asc') {
      return aVal.localeCompare(bVal, undefined, {numeric: true});
    } else {
      return bVal.localeCompare(aVal, undefined, {numeric: true});
    }
  });

  rows.forEach(row => tbody.appendChild(row));
}

function updateSelectedCount() {
  const selected = document.querySelectorAll('.table-checkbox:checked').length;
  const counter = document.getElementById('selectedCount');
  if (counter) {
    counter.textContent = `${selected} selected`;
    counter.parentElement.style.display = selected > 0 ? 'flex' : 'none';
  }
}

// ══════════════════════════════════════════
// CHARTS (Basic setup - integrate with Chart.js)
// ══════════════════════════════════════════
function initCharts() {
  // Revenue chart
  const revenueChart = document.getElementById('revenueChart');
  if (revenueChart && typeof Chart !== 'undefined') {
    new Chart(revenueChart, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
          label: 'Revenue',
          data: [12000, 19000, 15000, 25000, 22000, 30000],
          borderColor: '#1352DC',
          backgroundColor: 'rgba(19,82,220,0.1)',
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false
      }
    });
  }
}

// ══════════════════════════════════════════
// FORM VALIDATION
// ══════════════════════════════════════════
function validateForm(formId) {
  const form = document.getElementById(formId);
  if (!form) return false;

  let isValid = true;

  form.querySelectorAll('[required]').forEach(input => {
    if (!input.value.trim()) {
      input.classList.add('error');
      isValid = false;
    } else {
      input.classList.remove('error');
    }
  });

  return isValid;
}

// ══════════════════════════════════════════
// TABS
// ══════════════════════════════════════════
function initTabs() {
  document.querySelectorAll('.tabs').forEach(tabContainer => {
    const tabs = tabContainer.querySelectorAll('.tab');

    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        // Remove active from all tabs
        tabs.forEach(t => t.classList.remove('active'));

        // Add active to clicked tab
        tab.classList.add('active');

        // Show corresponding content
        const target = tab.dataset.tab;
        if (target) {
          document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.toggle('active', content.id === target);
          });
        }
      });
    });
  });
}

// ══════════════════════════════════════════
// SEARCH FUNCTIONALITY
// ══════════════════════════════════════════
function initSearch() {
  const searchInput = document.getElementById('globalSearch');
  searchInput?.addEventListener('input', (e) => {
    const query = e.target.value.toLowerCase();
    // Implement search logic here
    console.log('Searching for:', query);
  });
}

function initCampaignBuilder() {
  const form = document.querySelector('[data-campaign-builder-form]');
  if (!form) return;

  const modeInputs = Array.from(form.querySelectorAll('[data-campaign-mode]'));
  const scheduleFields = form.querySelector('[data-campaign-schedule-fields]');
  const startDateInput = form.querySelector('[data-campaign-start-date]');
  const startTimeInput = form.querySelector('[data-campaign-start-time]');
  const statusNode = form.querySelector('[data-campaign-builder-status]');
  const submitButton = form.querySelector('[data-campaign-submit-action]');
  const draftButton = form.querySelector('[data-campaign-save-draft]');
  const previewButton = form.querySelector('[data-campaign-preview]');
  const scheduleQueueList = document.querySelector('[data-campaign-schedule-list]');

  if (!modeInputs.length || !submitButton) return;

  const requiredFields = [
    form.querySelector('#campaignName'),
    form.querySelector('#campaignProduct'),
    form.querySelector('#campaignAudience'),
    form.querySelector('#campaignTemplate'),
  ].filter(Boolean);

  const pad = (value) => String(value).padStart(2, '0');
  const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
  const getMode = () => modeInputs.find((input) => input.checked)?.value || 'instant';
  const isScheduledMode = () => getMode() === 'scheduled';

  const setStatus = (message, tone = '') => {
    if (!statusNode) return;
    statusNode.textContent = message;
    statusNode.className = `campaign-builder-status${tone ? ` is-${tone}` : ''}`;
  };

  const formatDateTimeForMessage = (dateValue, timeValue) => {
    const composedDate = new Date(`${dateValue}T${timeValue}`);
    if (Number.isNaN(composedDate.getTime())) {
      return `${dateValue} ${timeValue}`;
    }

    return composedDate.toLocaleString('en-BD', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
    });
  };

  const formatTimeLabel = (timeValue) => {
    if (!timeValue) return '--:--';
    const sampleDate = new Date(`2000-01-01T${timeValue}`);
    if (Number.isNaN(sampleDate.getTime())) return timeValue;
    return sampleDate.toLocaleTimeString('en-BD', {
      hour: 'numeric',
      minute: '2-digit',
    });
  };

  const prependScheduleQueueItem = () => {
    if (!scheduleQueueList) return;

    const campaignName = String(form.querySelector('#campaignName')?.value || 'New Scheduled Campaign').trim();
    const productName = String(form.querySelector('#campaignProduct')?.value || 'Selected Product').trim();
    const timeLabel = formatTimeLabel(startTimeInput?.value || '');

    const entry = `
      <li>
        <div class="campaign-schedule-time">${escapeHtml(timeLabel)}</div>
        <div class="campaign-schedule-copy">
          <strong>${escapeHtml(campaignName)}</strong>
          <span>${escapeHtml(productName)}</span>
        </div>
      </li>
    `;

    scheduleQueueList.insertAdjacentHTML('afterbegin', entry);
  };

  const getTodayDateValue = () => {
    const now = new Date();
    return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
  };

  const seedScheduleStart = () => {
    if (!startDateInput || !startTimeInput) return;
    if (startDateInput.value && startTimeInput.value) return;

    const seed = new Date();
    seed.setMinutes(seed.getMinutes() + 30);

    startDateInput.value = `${seed.getFullYear()}-${pad(seed.getMonth() + 1)}-${pad(seed.getDate())}`;
    startTimeInput.value = `${pad(seed.getHours())}:${pad(seed.getMinutes())}`;
  };

  const updateDateConstraints = () => {
    const today = getTodayDateValue();

    if (startDateInput) {
      startDateInput.min = today;
    }
  };

  const updateModeUi = () => {
    const scheduled = isScheduledMode();

    scheduleFields?.classList.toggle('hidden', !scheduled);
    [startDateInput, startTimeInput].forEach((input) => {
      if (!input) return;
      input.disabled = !scheduled;
    });

    if (scheduled) {
      seedScheduleStart();
      submitButton.textContent = 'Create Schedule';
      setStatus('Scheduled mode enabled. Set start date and time.', 'warning');
    } else {
      submitButton.textContent = 'Launch Instant';
      setStatus('Instant mode enabled. Campaign will launch immediately.', '');
    }

    updateDateConstraints();
  };

  const validateCampaignForm = () => {
    for (const field of requiredFields) {
      const value = String(field.value || '').trim();
      if (!value) {
        field.focus();
        return {
          valid: false,
          message: `Please complete "${field.closest('.form-group')?.querySelector('.form-label')?.textContent || 'required field'}".`,
        };
      }
    }

    if (!isScheduledMode()) {
      return {valid: true};
    }

    const startDateValue = String(startDateInput?.value || '').trim();
    const startTimeValue = String(startTimeInput?.value || '').trim();

    if (!startDateValue || !startTimeValue) {
      startDateInput?.focus();
      return {valid: false, message: 'Start date and start time are required for scheduled campaigns.'};
    }

    const startDateTime = new Date(`${startDateValue}T${startTimeValue}`);
    if (Number.isNaN(startDateTime.getTime())) {
      startDateInput?.focus();
      return {valid: false, message: 'Invalid schedule start date/time. Please recheck your input.'};
    }

    if (startDateTime.getTime() <= Date.now()) {
      startDateInput?.focus();
      return {valid: false, message: 'Scheduled start date/time must be in the future.'};
    }

    return {valid: true};
  };

  const runPreview = () => {
    const validation = validateCampaignForm();
    if (!validation.valid) {
      setStatus(validation.message, 'error');
      showWarning(validation.message);
      return;
    }

    if (!isScheduledMode()) {
      setStatus('Preview ready: campaign will launch instantly.', 'success');
      showInfo('Preview ready for instant campaign launch.');
      return;
    }

    const launchAt = formatDateTimeForMessage(startDateInput?.value || '', startTimeInput?.value || '');
    setStatus(`Preview ready: campaign will run on ${launchAt}.`, 'success');
    showInfo('Scheduled campaign preview is ready.');
  };

  draftButton?.addEventListener('click', () => {
    setStatus('Campaign draft saved (demo).', 'success');
    showSuccess('Campaign draft saved (demo).');
  });

  previewButton?.addEventListener('click', runPreview);

  modeInputs.forEach((input) => {
    input.addEventListener('change', updateModeUi);
  });

  [startDateInput, startTimeInput].forEach((input) => {
    input?.addEventListener('change', () => {
      updateDateConstraints();
    });
  });

  form.addEventListener('submit', (event) => {
    event.preventDefault();

    const validation = validateCampaignForm();
    if (!validation.valid) {
      setStatus(validation.message, 'error');
      showError(validation.message);
      return;
    }

    if (isScheduledMode()) {
      const launchAt = formatDateTimeForMessage(startDateInput?.value || '', startTimeInput?.value || '');

      setStatus(`Campaign scheduled for ${launchAt}.`, 'success');
      prependScheduleQueueItem();
      showSuccess('Campaign schedule created (demo).');
      return;
    }

    setStatus('Campaign launched instantly (demo).', 'success');
    showSuccess('Campaign launched instantly (demo).');
  });

  updateModeUi();
  updateDateConstraints();
}

function initOrdersManualOrder() {
  const form = document.querySelector('[data-manual-order-form]');
  const tableBody = document.querySelector('[data-orders-table-body]');
  if (!form || !tableBody) return;

  const countNode = document.querySelector('[data-orders-count]');
  const filterNode = document.querySelector('[data-orders-filter-result]');
  const resetButton = form.querySelector('[data-manual-order-reset]');
  const modeInputs = Array.from(form.querySelectorAll('input[name="customer_mode"]'));
  const existingUserWrap = form.querySelector('[data-manual-existing-user-wrap]');
  const existingUserSearchInput = form.querySelector('[data-manual-existing-user-search]');
  const existingUserResults = form.querySelector('[data-manual-existing-user-results]');
  const selectedUserLabel = form.querySelector('[data-manual-selected-user]');
  const customerNameInput = form.querySelector('[data-manual-user-name]');
  const customerEmailInput = form.querySelector('[data-manual-user-email]');
  const customerPhoneInput = form.querySelector('[data-manual-user-phone]');
  const customerLocationInput = form.querySelector('[data-manual-user-location]');
  const productSearchInput = form.querySelector('[data-manual-product-search]');
  const productResults = form.querySelector('[data-manual-product-results]');
  const productSearchWrap = productSearchInput?.closest('.form-group');
  const productsSummary = form.querySelector('[data-manual-products-selected]');
  const productsCountLabel = form.querySelector('[data-manual-products-count]');
  const couponInput = form.querySelector('[data-manual-coupon]');
  const discountTypeInput = form.querySelector('[data-manual-discount-type]');
  const discountValueInput = form.querySelector('[data-manual-discount-value]');
  const subtotalInput = form.querySelector('#manualSubtotal');
  const itemsInput = form.querySelector('#manualItemCount');
  const amountInput = form.querySelector('#manualAmount');
  const discountPreview = form.querySelector('[data-manual-discount-preview]');

  const statusClassMap = {
    Delivered: 'badge-success',
    'In Transit': 'badge-primary',
    'Ready to Dispatch': 'badge-info',
    'Payment Review': 'badge-warning',
    Delayed: 'badge-danger',
  };

  const statusProgressMap = {
    Delivered: 100,
    'In Transit': 82,
    'Ready to Dispatch': 64,
    'Payment Review': 28,
    Delayed: 51,
  };

  let manualSequence = 1;
  let createdCount = 0;
  let selectedExistingUser = null;
  const initialCount = Number.parseInt(countNode?.dataset.initialCount || '0', 10) || 0;
  const initialUniverseCount = Number.parseInt(
    filterNode?.dataset.universeCount || String(initialCount),
    10
  ) || initialCount;
  const productCatalog = [
    {
      id: 'SKU-TS-2109',
      name: 'Premium Cotton T-Shirt',
      price: 1150,
      image: '/assets/images/products/premium-cotton-tshirt.svg',
    },
    {
      id: 'SKU-HD-1231',
      name: 'Smart Casual Hoodie',
      price: 1890,
      image: '/assets/images/products/smart-casual-hoodie.svg',
    },
    {
      id: 'SKU-BP-9920',
      name: 'Leather Office Backpack',
      price: 2780,
      image: '/assets/images/products/leather-office-backpack.svg',
    },
    {
      id: 'SKU-EB-4412',
      name: 'Wireless Earbuds Pro',
      price: 3250,
      image: '/assets/images/products/wireless-earbuds-pro.svg',
    },
    {
      id: 'SKU-SH-3318',
      name: 'AirFlex Running Shoes',
      price: 2450,
      image: '/assets/images/products/airflex-running-shoes.svg',
    },
  ];
  const selectedProducts = new Map();
  const existingUsers = [
    {
      id: 'USR-1002',
      name: 'Ayesha Rahman',
      email: 'ayesha.rahman@example.com',
      phone: '+8801711223344',
      location: 'Dhanmondi, Dhaka',
    },
    {
      id: 'USR-1001',
      name: 'Mahmud Hasan',
      email: 'mahmud.hasan@example.com',
      phone: '+8801888556677',
      location: 'Uttara, Dhaka',
    },
    {
      id: 'USR-1004',
      name: 'Nusrat Jahan',
      email: 'nusrat.jahan@example.com',
      phone: '+8801799446655',
      location: 'Chawkbazar, Chattogram',
    },
    {
      id: 'USR-1003',
      name: 'Riad Karim',
      email: 'riad.karim@example.com',
      phone: '+8801677110099',
      location: 'Rajshahi Sadar',
    },
    {
      id: 'USR-1005',
      name: 'Sumi Akter',
      email: 'sumi.akter@example.com',
      phone: '+8801555223344',
      location: 'Sylhet Sadar',
    },
  ];

  const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');

  const formatBdt = (amount) => {
    const safeAmount = Math.max(0, Math.round(Number(amount) || 0));
    return `BDT ${new Intl.NumberFormat('en-BD', {maximumFractionDigits: 0}).format(safeAmount)}`;
  };

  const nextOrderId = () => {
    const now = new Date();
    const hour = String(now.getHours()).padStart(2, '0');
    const minute = String(now.getMinutes()).padStart(2, '0');
    const suffix = String(manualSequence).padStart(2, '0');
    manualSequence += 1;
    return `ORD-M${hour}${minute}${suffix}`;
  };

  const getMode = () => modeInputs.find((input) => input.checked)?.value || 'old';

  const getProductById = (productId) => productCatalog.find((product) => product.id === productId) || null;

  const getSelectedProducts = () => Array.from(selectedProducts.entries())
    .map(([productId, qty]) => {
      const product = getProductById(productId);
      if (!product) return null;

      return {
        ...product,
        qty,
        total: qty * product.price,
      };
    })
    .filter(Boolean);

  const getDiscountState = (subtotalAmount) => {
    const discountType = (discountTypeInput?.value || 'fixed') === 'percent' ? 'percent' : 'fixed';
    const rawDiscountValue = Math.max(0, Number.parseFloat(discountValueInput?.value || '0') || 0);
    const safeSubtotal = Math.max(0, Math.round(subtotalAmount));
    const calculated = discountType === 'percent'
      ? safeSubtotal * (rawDiscountValue / 100)
      : rawDiscountValue;
    const cappedAmount = Math.min(safeSubtotal, Math.max(0, Math.round(calculated)));

    return {
      type: discountType,
      value: rawDiscountValue,
      amount: cappedAmount,
    };
  };

  const updateProductsSummary = () => {
    const selected = getSelectedProducts();
    const totalItems = selected.reduce((sum, product) => sum + product.qty, 0);
    const subtotalAmount = selected.reduce((sum, product) => sum + product.total, 0);
    const discountState = getDiscountState(subtotalAmount);
    const grandTotalAmount = Math.max(0, subtotalAmount - discountState.amount);
    const couponCode = (couponInput?.value || '').trim().toUpperCase();

    if (itemsInput) {
      itemsInput.value = String(totalItems);
    }

    if (subtotalInput) {
      subtotalInput.value = String(Math.round(subtotalAmount));
    }

    if (amountInput) {
      amountInput.value = String(Math.round(grandTotalAmount));
    }

    if (discountPreview) {
      if (discountState.amount <= 0) {
        discountPreview.textContent = 'No discount applied.';
      } else {
        const discountLabel = discountState.type === 'percent'
          ? `${discountState.value}%`
          : `${formatBdt(discountState.value)}`;
        const couponLabel = couponCode ? ` with coupon ${couponCode}` : '';
        discountPreview.textContent = `Discount ${discountLabel}${couponLabel}: -${formatBdt(discountState.amount)} | Grand Total: ${formatBdt(grandTotalAmount)}`;
      }
    }

    if (productsCountLabel) {
      productsCountLabel.textContent = selected.length
        ? `${selected.length} product${selected.length > 1 ? 's' : ''} selected.`
        : 'No products selected.';
      productsCountLabel.classList.toggle('is-active', selected.length > 0);
    }

    if (!productsSummary) return;

    if (!selected.length) {
      productsSummary.innerHTML = `
        <div class="orders-manual-product-empty">
          No products selected. Search and add one or more products.
        </div>
      `;
      return;
    }

    productsSummary.innerHTML = selected
      .map((product) => `
        <div class="orders-manual-selected-item">
          <span class="orders-manual-selected-thumb">
            <img src="${escapeHtml(product.image)}" alt="${escapeHtml(product.name)}" loading="lazy">
          </span>
          <span class="orders-manual-selected-meta">
            <strong>${escapeHtml(product.name)}</strong>
            <small>${escapeHtml(product.id)} | ${escapeHtml(formatBdt(product.price))}</small>
          </span>
          <span class="orders-manual-selected-qty">
            <label for="selectedQty_${escapeHtml(product.id)}">Qty</label>
            <input
              id="selectedQty_${escapeHtml(product.id)}"
              type="number"
              class="form-input"
              min="1"
              value="${product.qty}"
              data-selected-product-qty="${escapeHtml(product.id)}"
            >
          </span>
          <strong>${escapeHtml(formatBdt(product.total))}</strong>
          <button type="button" class="btn btn-ghost btn-sm" data-selected-product-remove="${escapeHtml(product.id)}">Remove</button>
        </div>
      `)
      .join('');

    productsSummary.querySelectorAll('[data-selected-product-qty]').forEach((qtyInputNode) => {
      qtyInputNode.addEventListener('input', () => {
        const productId = qtyInputNode.getAttribute('data-selected-product-qty') || '';
        const qty = Math.max(1, Number.parseInt(qtyInputNode.value || '1', 10) || 1);
        qtyInputNode.value = String(qty);
        selectedProducts.set(productId, qty);
        updateProductsSummary();
      });
    });

    productsSummary.querySelectorAll('[data-selected-product-remove]').forEach((removeButtonNode) => {
      removeButtonNode.addEventListener('click', () => {
        const productId = removeButtonNode.getAttribute('data-selected-product-remove') || '';
        selectedProducts.delete(productId);
        updateProductsSummary();
      });
    });
  };

  const setProductResultsVisible = (isVisible) => {
    if (!productResults) return;
    productResults.classList.toggle('hidden', !isVisible);
  };

  const addProductById = (productId) => {
    if (!getProductById(productId)) return;
    const nextQty = (selectedProducts.get(productId) || 0) + 1;
    selectedProducts.set(productId, nextQty);
    updateProductsSummary();

    if (productSearchInput) {
      productSearchInput.value = '';
      productSearchInput.blur();
    }
    setProductResultsVisible(false);
  };

  const renderProductResults = (query = '') => {
    if (!productResults) return;

    const normalizedQuery = query.trim().toLowerCase();
    const matchedProducts = productCatalog
      .filter((product) => {
        if (!normalizedQuery) return true;
        return [product.name, product.id]
          .join(' ')
          .toLowerCase()
          .includes(normalizedQuery);
      })
      .slice(0, 8);

    if (!matchedProducts.length) {
      productResults.innerHTML = `
        <div class="orders-manual-user-empty">
          No products matched your search.
        </div>
      `;
      setProductResultsVisible(true);
      return;
    }

    productResults.innerHTML = matchedProducts
      .map((product) => `
        <button type="button" class="orders-manual-product-option" data-product-id="${escapeHtml(product.id)}">
          <span class="orders-manual-product-option-thumb">
            <img src="${escapeHtml(product.image)}" alt="${escapeHtml(product.name)}" loading="lazy">
          </span>
          <span class="orders-manual-product-option-meta">
            <strong>${escapeHtml(product.name)}</strong>
            <small>${escapeHtml(product.id)}</small>
          </span>
          <span class="orders-manual-product-option-price">${escapeHtml(formatBdt(product.price))}</span>
        </button>
      `)
      .join('');
    setProductResultsVisible(true);

    productResults.querySelectorAll('[data-product-id]').forEach((button) => {
      button.addEventListener('click', () => {
        const productId = button.getAttribute('data-product-id') || '';
        addProductById(productId);
      });
    });
  };

  const resetProductSelection = () => {
    selectedProducts.clear();
    if (productSearchInput) {
      productSearchInput.value = '';
    }
    setProductResultsVisible(false);
    updateProductsSummary();
  };

  const updateSelectedUserLabel = () => {
    if (!selectedUserLabel) return;

    if (selectedExistingUser) {
      selectedUserLabel.textContent = `Selected: ${selectedExistingUser.name} (${selectedExistingUser.phone})`;
      selectedUserLabel.classList.add('is-active');
      return;
    }

    selectedUserLabel.textContent = 'No user selected.';
    selectedUserLabel.classList.remove('is-active');
  };

  const fillCustomerFields = (user) => {
    if (!user) return;
    if (customerNameInput) customerNameInput.value = user.name;
    if (customerEmailInput) customerEmailInput.value = user.email;
    if (customerPhoneInput) customerPhoneInput.value = user.phone;
    if (customerLocationInput) customerLocationInput.value = user.location;
  };

  const clearCustomerFields = () => {
    if (customerNameInput) customerNameInput.value = '';
    if (customerEmailInput) customerEmailInput.value = '';
    if (customerPhoneInput) customerPhoneInput.value = '';
    if (customerLocationInput) customerLocationInput.value = '';
  };

  const setExistingUserResultsVisible = (isVisible) => {
    if (!existingUserResults) return;
    existingUserResults.classList.toggle('hidden', !isVisible);
  };

  const selectExistingUserById = (userId) => {
    const user = existingUsers.find((item) => item.id === userId) || null;
    if (!user) return;

    selectedExistingUser = user;
    fillCustomerFields(user);
    updateSelectedUserLabel();

    if (existingUserSearchInput) {
      existingUserSearchInput.value = `${user.name} (${user.phone})`;
    }
    setExistingUserResultsVisible(false);
  };

  const renderExistingUserResults = (query = '') => {
    if (!existingUserResults || getMode() !== 'old') return;

    const normalizedQuery = query.trim().toLowerCase();
    const matchedUsers = existingUsers
      .filter((user) => {
        if (!normalizedQuery) return true;

        return [user.name, user.email, user.phone]
          .join(' ')
          .toLowerCase()
          .includes(normalizedQuery);
      })
      .slice(0, 8);

    if (!matchedUsers.length) {
      existingUserResults.innerHTML = `
        <div class="orders-manual-user-empty">
          No users matched. Switch to <strong>New User</strong> to create one.
        </div>
      `;
      setExistingUserResultsVisible(true);
      return;
    }

    existingUserResults.innerHTML = matchedUsers
      .map((user) => `
        <button type="button" class="orders-manual-user-option" data-user-id="${escapeHtml(user.id)}">
          <span class="orders-manual-user-name">${escapeHtml(user.name)}</span>
          <span class="orders-manual-user-meta">${escapeHtml(user.email)} | ${escapeHtml(user.phone)}</span>
          <span class="orders-manual-user-location">${escapeHtml(user.location)}</span>
        </button>
      `)
      .join('');
    setExistingUserResultsVisible(true);

    existingUserResults.querySelectorAll('[data-user-id]').forEach((button) => {
      button.addEventListener('click', () => {
        const userId = button.getAttribute('data-user-id') || '';
        selectExistingUserById(userId);
      });
    });
  };

  const syncModeUi = () => {
    const mode = getMode();
    const isOldUser = mode === 'old';

    existingUserWrap?.classList.toggle('hidden', !isOldUser);
    setExistingUserResultsVisible(false);

    if (isOldUser) {
      updateSelectedUserLabel();
    } else {
      selectedExistingUser = null;
      updateSelectedUserLabel();
      if (existingUserSearchInput) {
        existingUserSearchInput.value = '';
      }
      clearCustomerFields();
    }
  };

  const updateCounters = () => {
    const totalVisible = initialCount + createdCount;
    if (countNode) {
      countNode.textContent = `${totalVisible} monitored orders`;
    }

    if (filterNode) {
      const nextUniverse = Math.max(initialUniverseCount, totalVisible);
      filterNode.textContent = `Showing ${totalVisible} of ${nextUniverse} orders`;
    }
  };

  modeInputs.forEach((input) => {
    input.addEventListener('change', () => {
      syncModeUi();
      if (getMode() === 'old' && existingUserSearchInput?.value.trim() === '') {
        renderExistingUserResults('');
      }
    });
  });

  existingUserSearchInput?.addEventListener('focus', () => {
    if (getMode() !== 'old') return;
    renderExistingUserResults(existingUserSearchInput.value);
  });

  existingUserSearchInput?.addEventListener('input', () => {
    selectedExistingUser = null;
    updateSelectedUserLabel();
    renderExistingUserResults(existingUserSearchInput.value);
  });

  productSearchInput?.addEventListener('focus', () => {
    renderProductResults(productSearchInput.value);
  });

  productSearchInput?.addEventListener('input', () => {
    renderProductResults(productSearchInput.value);
  });

  discountTypeInput?.addEventListener('change', () => {
    updateProductsSummary();
  });

  discountValueInput?.addEventListener('input', () => {
    const numericValue = Math.max(0, Number.parseFloat(discountValueInput.value || '0') || 0);
    discountValueInput.value = String(numericValue);
    updateProductsSummary();
  });

  couponInput?.addEventListener('input', () => {
    updateProductsSummary();
  });

  couponInput?.addEventListener('blur', () => {
    couponInput.value = couponInput.value.trim().toUpperCase();
    updateProductsSummary();
  });

  existingUserWrap?.addEventListener('focusout', () => {
    window.setTimeout(() => {
      if (!existingUserWrap.matches(':focus-within')) {
        setExistingUserResultsVisible(false);
      }
    }, 0);
  });

  productSearchWrap?.addEventListener('focusout', () => {
    window.setTimeout(() => {
      if (!productSearchWrap.matches(':focus-within')) {
        setProductResultsVisible(false);
      }
    }, 0);
  });

  document.addEventListener('click', (event) => {
    if (existingUserWrap && !existingUserWrap.contains(event.target)) {
      setExistingUserResultsVisible(false);
    }

    if (productSearchWrap && !productSearchWrap.contains(event.target)) {
      setProductResultsVisible(false);
    }
  });

  const resetManualOrderForm = () => {
    form.reset();
    selectedExistingUser = null;
    clearCustomerFields();
    resetProductSelection();
    updateSelectedUserLabel();
    syncModeUi();
  };

  resetButton?.addEventListener('click', () => {
    resetManualOrderForm();
  });

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;

    const mode = getMode();
    if (mode === 'old' && !selectedExistingUser) {
      showWarning('Select an old user from the search list before adding this order.');
      existingUserSearchInput?.focus();
      return;
    }

    const productsForOrder = getSelectedProducts();
    if (!productsForOrder.length) {
      showWarning('Select at least one product and quantity before adding this order.');
      productSearchInput?.focus();
      return;
    }

    const subtotalAmount = productsForOrder.reduce((sum, product) => sum + product.total, 0);
    const discountState = getDiscountState(subtotalAmount);
    const couponCode = (couponInput?.value || '').trim().toUpperCase();
    const amount = Math.max(0, subtotalAmount - discountState.amount);

    const formData = new FormData(form);
    const customerName = String(formData.get('customer_name') || '').trim();
    const customerEmail = String(formData.get('customer_email') || '').trim();
    const customerPhone = String(formData.get('customer_phone') || '').trim();
    const location = String(formData.get('location') || '').trim();
    const items = Math.max(1, Number.parseInt(String(formData.get('items') || '1'), 10) || 1);
    const payment = String(formData.get('payment') || 'COD');
    const channel = String(formData.get('channel') || 'Manual Entry');
    const status = String(formData.get('status') || 'Payment Review');

    const statusClass = statusClassMap[status] || 'badge-warning';
    const statusProgress = statusProgressMap[status] ?? 28;
    const paymentClass = payment === 'Paid' ? 'badge-success' : 'badge-warning';
    const orderId = nextOrderId();
    const customerInitial = customerName.charAt(0).toUpperCase() || 'M';
    const customerModeLabel = mode === 'old' ? 'Old User' : 'New User';
    const discountNote = discountState.amount > 0
      ? ` / Discount: -${formatBdt(discountState.amount)}${couponCode ? ` (${couponCode})` : ''}`
      : '';

    const row = document.createElement('tr');
    row.className = 'orders-manual-row';
    row.innerHTML = `
      <td>
        <div class="orders-order-cell">
          <strong>${escapeHtml(orderId)}</strong>
          <small>Just now (Manual / ${escapeHtml(customerModeLabel)}${escapeHtml(discountNote)})</small>
        </div>
      </td>
      <td>
        <div class="orders-customer-cell">
          <span class="orders-customer-avatar">${escapeHtml(customerInitial)}</span>
          <div>
            <strong title="${escapeHtml(customerEmail)}">${escapeHtml(customerName)}</strong>
            <small>${escapeHtml(location)} | ${escapeHtml(customerPhone)}</small>
          </div>
        </div>
      </td>
      <td>${items}</td>
      <td class="orders-cell-strong">${escapeHtml(formatBdt(amount))}</td>
      <td><span class="badge ${paymentClass}">${escapeHtml(payment)}</span></td>
      <td><span class="badge badge-primary">${escapeHtml(channel)}</span></td>
      <td>
        <div class="orders-status-wrap">
          <span class="badge ${statusClass}">${escapeHtml(status)}</span>
          <div class="orders-progress-track">
            <span style="width: ${statusProgress}%"></span>
          </div>
        </div>
      </td>
      <td>
        <div class="orders-table-actions">
          <button type="button" class="btn btn-info btn-sm" disabled title="Frontend demo row only">View</button>
          <button type="button" class="btn btn-success btn-sm" disabled title="Frontend demo row only">Confirm</button>
        </div>
      </td>
    `;

    tableBody.prepend(row);
    createdCount += 1;
    updateCounters();

    closeAllModals();
    resetManualOrderForm();
    showSuccess(`Manual order ${orderId} assigned to ${customerName} (frontend demo).`);
  });

  updateProductsSummary();
  syncModeUi();
  updateSelectedUserLabel();
  updateCounters();
}

// ══════════════════════════════════════════
// BOT SETTINGS
// ══════════════════════════════════════════
function createSectionLoader(surface) {
  if (!surface) {
    return {
      show() {},
      hide() {},
    };
  }

  const loader = surface.querySelector('[data-ui-loader]');
  const titleNode = loader?.querySelector('[data-ui-loader-title]');
  const messageNode = loader?.querySelector('[data-ui-loader-message]');

  return {
    show({title = null, message = null} = {}) {
      if (!loader) return;
      if (titleNode && typeof title === 'string' && title.trim()) {
        titleNode.textContent = title;
      }
      if (messageNode && typeof message === 'string' && message.trim()) {
        messageNode.textContent = message;
      }
      loader.hidden = false;
      surface.classList.add('is-loading');
    },
    hide() {
      if (!loader) return;
      loader.hidden = true;
      surface.classList.remove('is-loading');
    },
  };
}

function initBotSettings() {
  const form = document.querySelector('[data-bot-settings]');
  if (!form) return;

  const facebookCard = document.querySelector('[data-facebook-card]');
  const connectedArea = document.querySelector('[data-bot-settings-connected-area]');
  const lockedArea = document.querySelector('[data-bot-settings-locked-area]');
  const statusBadge = facebookCard?.querySelector('[data-facebook-status-badge]');
  const statusMessageNode = facebookCard?.querySelector('[data-facebook-status-message]');
  const connectedBlock = facebookCard?.querySelector('[data-facebook-connected]');
  const disconnectedBlock = facebookCard?.querySelector('[data-facebook-disconnected]');
  const pageNameNode = facebookCard?.querySelector('[data-facebook-page-name]');
  const pageIdNode = facebookCard?.querySelector('[data-facebook-page-id]');
  const activePageNode = facebookCard?.querySelector('[data-facebook-active-page]');
  const disconnectedMessageNode = facebookCard?.querySelector('[data-facebook-disconnected-message]');
  const serviceMessageNode = facebookCard?.querySelector('[data-facebook-service-message]');
  const serviceCommentNode = facebookCard?.querySelector('[data-facebook-service-comment]');
  const disconnectButton = facebookCard?.querySelector('[data-bot-disconnect]');

  const facebookLoader = createSectionLoader(facebookCard);
  const toggles = form.querySelectorAll('.bot-toggle-input');
  if (!toggles.length) return;

  const toBoolean = (value) => value === true || value === 1 || value === '1' || value === 'true';
  const isObjectPayload = (payload) => payload !== null && typeof payload === 'object' && !Array.isArray(payload);
  const messengerMasterToggle = form.querySelector('#setting_messenger_bot');
  const messengerFeatureToggles = Array.from(toggles).filter(toggle => toggle.dataset.group === 'messenger');
  const saveButton = form.querySelector('[data-bot-save]');
  const resetButton = form.querySelector('[data-bot-reset]');
  const unsavedBadge = form.querySelector('[data-unsaved-badge]');
  const enabledCountNode = document.querySelector('[data-enabled-count]');
  const enabledMeter = document.querySelector('[data-enabled-meter]');
  const totalEnabledNode = document.querySelector('[data-total-enabled]');
  const totalDisabledNode = document.querySelector('[data-total-disabled]');
  const totalFeatures = toggles.length;
  const totalChipFeatures = Number(enabledCountNode?.dataset.total || totalFeatures);
  const initialState = Array.from(toggles).map(toggle => toggle.checked);
  const toggleByName = new Map(Array.from(toggles).map(toggle => [toggle.name, toggle]));
  const apiToToggleName = {
    is_message: 'messenger_bot',
    is_comment: 'comments_bot',
    is_detect_emotion: 'detect_users_emotions',
    is_detect_interest: 'detect_price_vs_quality_buyer',
    is_suggest_product: 'analysis_and_suggest_products',
    is_bergain: 'product_bergain',
    is_detect_voice: 'voice_proccessing',
    is_detect_image: 'image_proccessing',
  };
  let isDirty = false;

  const setBadgeState = (label, tone = 'warning') => {
    if (!statusBadge) return;
    statusBadge.classList.remove('badge-success', 'badge-warning', 'badge-danger');

    if (tone === 'success') {
      statusBadge.classList.add('badge-success');
    } else if (tone === 'danger') {
      statusBadge.classList.add('badge-danger');
    } else {
      statusBadge.classList.add('badge-warning');
    }

    statusBadge.textContent = label;
  };

  const setConnectionPanels = (isConnected) => {
    if (connectedBlock) {
      connectedBlock.hidden = !isConnected;
    }
    if (disconnectedBlock) {
      disconnectedBlock.hidden = isConnected;
    }
    if (connectedArea) {
      connectedArea.hidden = !isConnected;
    }
    if (lockedArea) {
      lockedArea.hidden = isConnected;
    }
  };

  const markCurrentStateAsSaved = () => {
    Array.from(toggles).forEach((toggle, index) => {
      initialState[index] = toggle.checked;
    });

    isDirty = false;
    updateUi();
  };

  const clearAllToggles = () => {
    Array.from(toggles).forEach(toggle => {
      toggle.checked = false;
    });

    syncMessengerDependency();
    markCurrentStateAsSaved();
  };

  const applyRemoteToggleState = (payload) => {
    Object.entries(apiToToggleName).forEach(([apiField, toggleName]) => {
      const toggle = toggleByName.get(toggleName);
      if (!toggle || typeof payload[apiField] === 'undefined') return;
      toggle.checked = toBoolean(payload[apiField]);
    });

    syncMessengerDependency();
    markCurrentStateAsSaved();
  };

  const buildSettingsPayload = () => {
    const payload = {};

    Object.entries(apiToToggleName).forEach(([apiField, toggleName]) => {
      const toggle = toggleByName.get(toggleName);
      payload[apiField] = Boolean(toggle?.checked);
    });

    return payload;
  };

  const applyConnectedState = (payload) => {
    const pageId = String(payload.page_id || '').trim();
    const pageName = String(payload.page_name || '').trim();
    const resolvedPageName = pageName || 'Unnamed page';

    setBadgeState('Connected', 'success');

    if (statusMessageNode) {
      statusMessageNode.textContent = `Connected to "${resolvedPageName}" (${pageId || 'Unknown ID'}).`;
    }
    if (pageNameNode) {
      pageNameNode.textContent = resolvedPageName;
    }
    if (pageIdNode) {
      pageIdNode.textContent = pageId || '-';
    }
    if (activePageNode) {
      activePageNode.textContent = resolvedPageName;
    }
    if (serviceMessageNode) {
      serviceMessageNode.checked = toBoolean(payload.is_message);
    }
    if (serviceCommentNode) {
      serviceCommentNode.checked = toBoolean(payload.is_comment);
    }
    if (facebookCard && pageId) {
      facebookCard.dataset.pageId = pageId;
    }

    applyRemoteToggleState(payload);
    setConnectionPanels(true);
  };

  const applyDisconnectedState = (message) => {
    const nextMessage = message || 'please connect your facebook to enjoy this features';

    setBadgeState('Not Connected', 'warning');

    if (statusMessageNode) {
      statusMessageNode.textContent = nextMessage;
    }
    if (disconnectedMessageNode) {
      disconnectedMessageNode.textContent = nextMessage;
    }
    if (serviceMessageNode) {
      serviceMessageNode.checked = false;
    }
    if (serviceCommentNode) {
      serviceCommentNode.checked = false;
    }

    clearAllToggles();
    setConnectionPanels(false);
  };

  const applyRequestError = (message) => {
    const nextMessage = message || 'Unable to check Facebook connection right now.';

    setBadgeState('Unavailable', 'danger');

    if (statusMessageNode) {
      statusMessageNode.textContent = nextMessage;
    }
    if (disconnectedMessageNode) {
      disconnectedMessageNode.textContent = nextMessage;
    }

    setConnectionPanels(false);
  };

  const resolveRefreshToken = () => String(facebookCard?.dataset.refreshToken || '').trim();

  const syncMessengerDependency = () => {
    if (!messengerMasterToggle) return;

    const isMasterEnabled = messengerMasterToggle.checked;
    messengerFeatureToggles.forEach(toggle => {
      const row = toggle.closest('.bot-setting-row');

      if (!isMasterEnabled) {
        toggle.checked = false;
        toggle.disabled = true;
        row?.classList.add('frozen');
      } else {
        toggle.disabled = false;
        row?.classList.remove('frozen');
      }
    });
  };

  const updateUi = () => {
    let allEnabled = 0;

    toggles.forEach(toggle => {
      const isChecked = toggle.checked;
      const row = toggle.closest('.bot-setting-row');
      const stateNode = row?.querySelector(`[data-state-for="${toggle.id}"]`);

      if (stateNode) {
        stateNode.textContent = isChecked ? 'On' : 'Off';
        stateNode.classList.toggle('on', isChecked);
        stateNode.classList.toggle('off', !isChecked);
      }

      if (isChecked) {
        allEnabled++;
      }
    });

    if (enabledCountNode) {
      enabledCountNode.textContent = `${allEnabled}/${totalChipFeatures} enabled`;
    }

    if (enabledMeter && totalChipFeatures > 0) {
      const percentage = Math.round((allEnabled / totalChipFeatures) * 100);
      enabledMeter.style.width = `${percentage}%`;
    }

    if (totalEnabledNode) {
      totalEnabledNode.textContent = String(allEnabled);
    }

    if (totalDisabledNode) {
      totalDisabledNode.textContent = String(Math.max(0, totalFeatures - allEnabled));
    }

    if (unsavedBadge) {
      unsavedBadge.classList.toggle('visible', isDirty);
    }
  };

  const refreshDirtyState = () => {
    isDirty = Array.from(toggles).some((toggle, index) => toggle.checked !== initialState[index]);
    updateUi();
  };

  toggles.forEach(toggle => {
    toggle.addEventListener('change', () => {
      syncMessengerDependency();
      refreshDirtyState();
    });
  });

  saveButton?.addEventListener('click', async () => {
    syncMessengerDependency();
    refreshDirtyState();

    if (!facebookCard) {
      showError('Facebook settings card not found.');
      return;
    }

    const apiBaseUrl = String(facebookCard.dataset.apiBaseUrl || '').replace(/\/+$/, '');
    const refreshToken = resolveRefreshToken();
    const payload = buildSettingsPayload();

    if (!apiBaseUrl) {
      showError('Missing backend API URL.');
      return;
    }

    if (!refreshToken) {
      showError('Missing refresh token. Please login again.');
      return;
    }

    const endpoint = `${apiBaseUrl}/api/admin/facebook-auth`;
    const originalLabel = saveButton.textContent.trim();
    saveButton.disabled = true;
    saveButton.textContent = 'Saving...';

    facebookLoader.show({
      title: 'Saving bot settings',
      message: 'Updating /api/admin/facebook-auth',
    });

    const abortController = new AbortController();
    const timeoutId = window.setTimeout(() => abortController.abort(), 12000);
    const requestUpdate = async (token) => fetch(endpoint, {
      method: 'PUT',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'x-refresh-token': token,
      },
      body: JSON.stringify(payload),
      signal: abortController.signal,
    });

    try {
      let response = await requestUpdate(refreshToken);

      const contentType = response.headers.get('content-type') || '';
      const rawPayload = contentType.includes('application/json')
        ? await response.json()
        : {message: await response.text()};

      if (!isObjectPayload(rawPayload)) {
        throw new Error('Unexpected JSON shape returned from API.');
      }

      if (!response.ok) {
        if (response.status === 401) {
          throw new Error('Unauthorized (401). Server-provided refresh token is invalid or expired. Please re-login.');
        }

        throw new Error(rawPayload.message || rawPayload.error || 'Failed to update bot settings.');
      }

      if ((rawPayload.message || '').toLowerCase() !== 'updated') {
        throw new Error(rawPayload.message || 'Update response was not recognized.');
      }

      markCurrentStateAsSaved();
      showSuccess('Bot settings updated.');
      await fetchFacebookStatus();
    } catch (error) {
      if (error?.name === 'AbortError') {
        showError('Save request timed out. Please try again.');
        return;
      }

      showError(error.message || 'Unable to save bot settings right now.');
    } finally {
      window.clearTimeout(timeoutId);
      facebookLoader.hide();
      saveButton.disabled = false;
      saveButton.textContent = originalLabel;
    }
  });

  disconnectButton?.addEventListener('click', async () => {
    if (!facebookCard) {
      showError('Facebook settings card not found.');
      return;
    }

    const apiBaseUrl = String(facebookCard.dataset.apiBaseUrl || '').replace(/\/+$/, '');
    const refreshToken = resolveRefreshToken();

    if (!apiBaseUrl) {
      showError('Missing backend API URL.');
      return;
    }

    if (!refreshToken) {
      showError('Missing refresh token. Please login again.');
      return;
    }

    const endpoint = `${apiBaseUrl}/api/admin/facebook-auth`;
    const originalLabel = disconnectButton.textContent.trim();
    disconnectButton.disabled = true;
    disconnectButton.textContent = 'Disconnecting...';

    facebookLoader.show({
      title: 'Disconnecting Facebook',
      message: 'Removing account link from backend',
    });

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
      const rawPayload = contentType.includes('application/json')
        ? await response.json()
        : {message: await response.text()};

      if (!isObjectPayload(rawPayload)) {
        throw new Error('Unexpected JSON shape returned from API.');
      }

      if (!response.ok) {
        if (response.status === 401) {
          throw new Error('Unauthorized (401). Server-provided refresh token is invalid or expired. Please re-login.');
        }

        throw new Error(rawPayload.message || rawPayload.error || 'Failed to disconnect Facebook.');
      }

      if ((rawPayload.message || '').toLowerCase() !== 'deleted') {
        throw new Error(rawPayload.message || 'Disconnect response was not recognized.');
      }

      applyDisconnectedState('Facebook disconnected successfully.');
      showSuccess('Facebook disconnected successfully.');
    } catch (error) {
      if (error?.name === 'AbortError') {
        showError('Disconnect request timed out. Please try again.');
        return;
      }

      showError(error.message || 'Unable to disconnect Facebook right now.');
    } finally {
      window.clearTimeout(timeoutId);
      facebookLoader.hide();
      disconnectButton.disabled = false;
      disconnectButton.textContent = originalLabel;
    }
  });

  resetButton?.addEventListener('click', () => {
    Array.from(toggles).forEach((toggle, index) => {
      toggle.checked = initialState[index];
    });

    syncMessengerDependency();
    refreshDirtyState();
    showInfo('Toggles reset to the last saved preview state.');
  });

  form.addEventListener('submit', (e) => {
    e.preventDefault();
  });

  const fetchFacebookStatus = async () => {
    if (!facebookCard) {
      return;
    }

    const apiBaseUrl = String(facebookCard.dataset.apiBaseUrl || '').replace(/\/+$/, '');
    const queryPageId = new URLSearchParams(window.location.search).get('page_id') || '';
    const pageId = String(queryPageId || facebookCard.dataset.pageId || '').trim();
    const refreshToken = resolveRefreshToken();

    if (!apiBaseUrl) {
      applyRequestError('Missing backend API URL.');
      return;
    }

    if (!refreshToken) {
      applyRequestError('Missing refresh token. Please login again.');
      return;
    }

    const endpoint = `${apiBaseUrl}/api/admin/facebook-auth`;

    facebookLoader.show({
      title: 'Checking Facebook connection',
      message: `Calling ${endpoint.replace(apiBaseUrl, '')}`,
    });
    setBadgeState('Checking...', 'warning');

    const abortController = new AbortController();
    const timeoutId = window.setTimeout(() => abortController.abort(), 12000);

    const requestStatus = async (token) => fetch(endpoint, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'x-refresh-token': token,
        },
        signal: abortController.signal,
      });

    try {
      let response = await requestStatus(refreshToken);

      const contentType = response.headers.get('content-type') || '';
      const rawPayload = contentType.includes('application/json')
        ? await response.json()
        : {message: await response.text()};

      if (!isObjectPayload(rawPayload)) {
        throw new Error('Unexpected JSON shape returned from API.');
      }

      if (!response.ok) {
        if (response.status === 401) {
          throw new Error('Unauthorized (401). Server-provided refresh token is invalid or expired. Please re-login.');
        }

        if (rawPayload.status === 'disconnected' || rawPayload.error === 'not found') {
          applyDisconnectedState(rawPayload.msg || 'please connect your facebook to enjoy this features');
          return;
        }

        throw new Error(rawPayload.message || rawPayload.error || 'Failed to load Facebook status.');
      }

      if (rawPayload.status === 'disconnected' || rawPayload.error === 'not found') {
        applyDisconnectedState(rawPayload.msg || 'please connect your facebook to enjoy this features');
        return;
      }

      const requiredFields = [
        'page_id',
        'page_name',
        'is_message',
        'is_comment',
        'is_detect_emotion',
        'is_detect_interest',
        'is_suggest_product',
        'is_bergain',
        'is_detect_voice',
        'is_detect_image',
      ];

      const hasExpectedShape = requiredFields.every((field) =>
        Object.prototype.hasOwnProperty.call(rawPayload, field)
      );

      if (!hasExpectedShape) {
        throw new Error('Unexpected JSON shape returned from API.');
      }

      applyConnectedState(rawPayload);
    } catch (error) {
      if (error?.name === 'AbortError') {
        applyRequestError('Request timeout. Please try again.');
        return;
      }

      applyRequestError(error.message || 'Unable to load Facebook status.');
    } finally {
      window.clearTimeout(timeoutId);
      facebookLoader.hide();
    }
  };

  setConnectionPanels(false);
  syncMessengerDependency();
  markCurrentStateAsSaved();
  fetchFacebookStatus();
}

// ══════════════════════════════════════════
// PRODUCTS: NEEDS ATTENTION DRAWER
// ══════════════════════════════════════════
function initProductsAttentionPanel() {
  const toggle = document.querySelector('[data-products-attention-toggle]');
  const panel = document.getElementById('productsAttentionPanel');
  const close = document.querySelector('[data-products-attention-close]');
  const backdrop = document.querySelector('[data-products-attention-backdrop]');

  if (!toggle || !panel || !backdrop) return;

  const setOpen = (isOpen) => {
    panel.classList.toggle('is-open', isOpen);
    backdrop.classList.toggle('is-visible', isOpen);
    toggle.classList.toggle('is-active', isOpen);
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    panel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    backdrop.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    document.body.classList.toggle('products-attention-open', isOpen);
  };

  toggle.addEventListener('click', () => {
    setOpen(!panel.classList.contains('is-open'));
  });

  close?.addEventListener('click', () => {
    setOpen(false);
  });

  backdrop.addEventListener('click', () => {
    setOpen(false);
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && panel.classList.contains('is-open')) {
      setOpen(false);
    }
  });
}

// ══════════════════════════════════════════
// CATEGORIES: AI DESCRIPTION WRITER (DEMO UI)
// ══════════════════════════════════════════
function initProductCreateSliderControl() {
  const sliderInputs = Array.from(document.querySelectorAll('[data-product-slider-toggle]'));
  if (!sliderInputs.length) return;

  const sliderStatusBadge = document.querySelector('[data-product-slider-status]');
  const sliderConfig = document.querySelector('[data-product-slider-config]');
  const coverInput = document.querySelector('[data-product-cover-input]');
  const coverList = document.querySelector('[data-product-cover-list]');
  const sliderMediaTypeInputs = Array.from(document.querySelectorAll('[data-product-slider-media-type]'));
  const sliderItemInput = document.querySelector('[data-product-slider-item-input]');
  const sliderUploadGroup = document.querySelector('[data-product-slider-upload-group]');
  const sliderYoutubeGroup = document.querySelector('[data-product-slider-youtube-group]');
  const sliderYoutubeInput = document.querySelector('[data-product-slider-youtube-input]');
  const sliderYoutubeAddButton = document.querySelector('[data-product-slider-youtube-add]');
  const sliderList = document.querySelector('[data-product-slider-list]');
  const validationModal = document.getElementById('productsMediaValidationModal');
  const validationTitleNode = validationModal?.querySelector('[data-media-dialog-title]');
  const validationMessageNode = validationModal?.querySelector('[data-media-dialog-message]');
  const validationConfirmButton = validationModal?.querySelector('[data-product-media-dialog-confirm]');
  const validationCancelButton = validationModal?.querySelector('[data-product-media-dialog-cancel]');
  const validationCloseButton = validationModal?.querySelector('[data-product-media-dialog-close]');

  const coverRatio = 1;
  const sliderImageRatio = 16 / 9;
  const ratioTolerance = 0.08;
  const maxImageBytes = 2 * 1024 * 1024;
  const maxVideoBytes = 30 * 1024 * 1024;
  const maxSliderImages = 7;

  let coverItem = null;
  let sliderSequence = 1;
  const sliderItems = [];

  const formatFileSize = (bytes) => {
    const kb = Math.max(0, Number(bytes) || 0) / 1024;
    if (kb >= 1024) {
      return `${(kb / 1024).toFixed(2)} MB`;
    }

    return `${Math.max(1, Math.round(kb))} KB`;
  };

  const getCurrentSliderMediaType = () => sliderMediaTypeInputs.find((input) => input.checked)?.value || 'image';
  const getSliderImageCount = () => sliderItems.filter((item) => item.type === 'image').length;
  const getYouTubeEmbedUrl = (videoId) => `https://www.youtube.com/embed/${videoId}`;

  const parseYouTubeVideoId = (urlValue) => {
    const rawValue = String(urlValue || '').trim();
    if (!rawValue) return null;

    try {
      const parsedUrl = new URL(rawValue);
      const host = parsedUrl.hostname.toLowerCase().replace(/^www\./, '').replace(/^m\./, '');
      let videoId = '';

      if (host === 'youtu.be') {
        videoId = parsedUrl.pathname.split('/').filter(Boolean)[0] || '';
      } else if (host === 'youtube.com') {
        if (parsedUrl.pathname === '/watch') {
          videoId = parsedUrl.searchParams.get('v') || '';
        } else if (parsedUrl.pathname.startsWith('/shorts/')) {
          videoId = parsedUrl.pathname.split('/shorts/')[1]?.split('/')[0] || '';
        } else if (parsedUrl.pathname.startsWith('/embed/')) {
          videoId = parsedUrl.pathname.split('/embed/')[1]?.split('/')[0] || '';
        }
      }

      const normalizedId = String(videoId).trim();
      if (/^[A-Za-z0-9_-]{11}$/.test(normalizedId)) {
        return normalizedId;
      }
    } catch (error) {
      // Ignore parse errors and fallback to regex extraction below.
    }

    const regexMatch = rawValue.match(/(?:v=|\/)([A-Za-z0-9_-]{11})(?:[?&/]|$)/);
    return regexMatch ? regexMatch[1] : null;
  };

  const validateFileSize = (file, mediaType, sourceLabel) => {
    const maxBytes = mediaType === 'video' ? maxVideoBytes : maxImageBytes;
    const maxLabel = mediaType === 'video' ? '30MB' : '2MB';

    if (file.size < maxBytes) return true;

    showWarning(
      `${sourceLabel} ${mediaType} must be less than ${maxLabel}. ` +
      `Selected file is ${formatFileSize(file.size)}.`
    );
    return false;
  };

  const releaseMediaUrl = (item) => {
    if (!item?.url || typeof item.url !== 'string' || !item.url.startsWith('blob:')) return;
    window.URL.revokeObjectURL(item.url);
  };

  let activeValidationResolver = null;
  const resolveValidationDialog = (result) => {
    if (!activeValidationResolver) return;
    const resolver = activeValidationResolver;
    activeValidationResolver = null;
    closeAllModals();
    resolver(result);
  };

  const openValidationDialog = ({
    title,
    message,
    confirmLabel = 'Proceed Anyway',
    cancelLabel = 'Cancel Upload',
  }) => {
    if (!validationModal || !validationConfirmButton || !validationCancelButton) {
      return Promise.resolve(window.confirm(message));
    }

    if (validationTitleNode) {
      validationTitleNode.textContent = title;
    }

    if (validationMessageNode) {
      validationMessageNode.textContent = message;
    }

    validationConfirmButton.textContent = confirmLabel;
    validationCancelButton.textContent = cancelLabel;

    return new Promise((resolve) => {
      activeValidationResolver = resolve;
      openModal('productsMediaValidationModal');
      window.setTimeout(() => {
        validationConfirmButton.focus();
      }, 0);
    });
  };

  const readImageDimensions = (file) => new Promise((resolve, reject) => {
    const objectUrl = window.URL.createObjectURL(file);
    const image = new Image();

    image.onload = () => {
      const result = {
        width: image.naturalWidth,
        height: image.naturalHeight,
      };
      window.URL.revokeObjectURL(objectUrl);
      resolve(result);
    };

    image.onerror = () => {
      window.URL.revokeObjectURL(objectUrl);
      reject(new Error('Unable to read image dimensions.'));
    };

    image.src = objectUrl;
  });

  const confirmRatioIfMismatch = async (details) => {
    const {
      width,
      height,
      expectedRatio,
      expectedHint,
    } = details;

    const ratio = width / Math.max(1, height);
    const mismatch = Math.abs(ratio - expectedRatio) > ratioTolerance;

    if (!mismatch) return true;

    return openValidationDialog({
      title: 'Image Ratio Mismatch',
      message:
        `The image size does not match our expected ratio (${expectedHint}). ` +
        `Current size: ${width} x ${height}. ` +
        `If you proceed, it can break the beauty of your product layout. What do you want to do?`,
      confirmLabel: 'Proceed Anyway',
      cancelLabel: 'Cancel Upload',
    });
  };

  const renderCoverList = () => {
    if (!coverList) return;

    if (!coverItem) {
      coverList.innerHTML = '<div class="products-media-empty">No cover image selected yet.</div>';
      return;
    }

    coverList.innerHTML = `
      <div class="products-media-item">
        <span class="products-media-thumb">
          <img src="${coverItem.url}" alt="Cover image preview" loading="lazy">
        </span>
        <span class="products-media-meta">
          <strong>${coverItem.name}</strong>
          <small>${coverItem.width} x ${coverItem.height} | ${formatFileSize(coverItem.size)}</small>
        </span>
        <button type="button" class="btn btn-danger btn-sm" data-product-cover-remove>Remove</button>
      </div>
    `;

    coverList.querySelector('[data-product-cover-remove]')?.addEventListener('click', () => {
      releaseMediaUrl(coverItem);
      coverItem = null;
      renderCoverList();
    });
  };

  const renderSliderList = () => {
    if (!sliderList) return;

    if (!sliderItems.length) {
      sliderList.innerHTML = '<div class="products-media-empty">No slider items added yet.</div>';
      return;
    }

    sliderList.innerHTML = sliderItems
      .map((item, index) => {
        const preview = item.type === 'video'
          ? `<video src="${item.url}" controls preload="metadata"></video>`
          : item.type === 'youtube'
            ? `<iframe src="${item.embedUrl}" title="${item.name}" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>`
            : `<img src="${item.url}" alt="${item.name}" loading="lazy">`;
        const dimensionMeta = item.type === 'image'
          ? `${item.width} x ${item.height} | `
          : '';
        const fileMeta = item.type === 'youtube' ? 'YouTube link' : formatFileSize(item.size);
        const moveUpDisabled = index === 0 ? 'disabled' : '';
        const moveDownDisabled = index === sliderItems.length - 1 ? 'disabled' : '';

        return `
          <div class="products-media-item">
            <span class="products-media-thumb">${preview}</span>
            <span class="products-media-meta">
              <strong>${item.name}</strong>
              <small>#${index + 1} in slider | ${item.type.toUpperCase()} | ${dimensionMeta}${fileMeta}</small>
            </span>
            <span class="products-media-actions">
              <button type="button" class="btn btn-sm products-media-order-btn products-media-order-btn-up" data-product-slider-move-up="${item.id}" ${moveUpDisabled}>
                <span class="products-media-order-icon" aria-hidden="true">&uarr;</span>
                <span>Move Up</span>
              </button>
              <button type="button" class="btn btn-sm products-media-order-btn products-media-order-btn-down" data-product-slider-move-down="${item.id}" ${moveDownDisabled}>
                <span class="products-media-order-icon" aria-hidden="true">&darr;</span>
                <span>Move Down</span>
              </button>
              <button type="button" class="btn btn-danger btn-sm products-media-remove-btn" data-product-slider-remove="${item.id}">Remove</button>
            </span>
          </div>
        `;
      })
      .join('');

    sliderList.querySelectorAll('[data-product-slider-move-up]').forEach((button) => {
      button.addEventListener('click', () => {
        const itemId = Number.parseInt(button.getAttribute('data-product-slider-move-up') || '', 10);
        const index = sliderItems.findIndex((item) => item.id === itemId);
        if (index <= 0) return;

        const [movedItem] = sliderItems.splice(index, 1);
        sliderItems.splice(index - 1, 0, movedItem);
        renderSliderList();
      });
    });

    sliderList.querySelectorAll('[data-product-slider-move-down]').forEach((button) => {
      button.addEventListener('click', () => {
        const itemId = Number.parseInt(button.getAttribute('data-product-slider-move-down') || '', 10);
        const index = sliderItems.findIndex((item) => item.id === itemId);
        if (index < 0 || index >= sliderItems.length - 1) return;

        const [movedItem] = sliderItems.splice(index, 1);
        sliderItems.splice(index + 1, 0, movedItem);
        renderSliderList();
      });
    });

    sliderList.querySelectorAll('[data-product-slider-remove]').forEach((button) => {
      button.addEventListener('click', () => {
        const itemId = Number.parseInt(button.getAttribute('data-product-slider-remove') || '', 10);
        const index = sliderItems.findIndex((item) => item.id === itemId);
        if (index < 0) return;

        releaseMediaUrl(sliderItems[index]);
        sliderItems.splice(index, 1);
        renderSliderList();
      });
    });
  };

  const updateSliderTypeInput = () => {
    const sliderType = getCurrentSliderMediaType();
    const useYoutube = sliderType === 'youtube';

    sliderUploadGroup?.classList.toggle('hidden', useYoutube);
    sliderYoutubeGroup?.classList.toggle('hidden', !useYoutube);

    if (sliderItemInput) {
      sliderItemInput.disabled = useYoutube;
      sliderItemInput.accept = sliderType === 'video' ? 'video/*' : sliderType === 'image' ? 'image/*' : '';
      if (useYoutube) {
        sliderItemInput.value = '';
      }
    }

    if (sliderYoutubeInput) {
      sliderYoutubeInput.disabled = !useYoutube;
      if (!useYoutube) {
        sliderYoutubeInput.value = '';
      }
    }

    if (sliderYoutubeAddButton) {
      sliderYoutubeAddButton.disabled = !useYoutube;
    }
  };

  const updateSliderStatus = () => {
    const selectedValue = sliderInputs.find((input) => input.checked)?.value || 'enabled';
    const isEnabled = selectedValue === 'enabled';

    if (sliderStatusBadge) {
      sliderStatusBadge.textContent = isEnabled ? 'Enabled' : 'Disabled';
      sliderStatusBadge.classList.remove('badge-info', 'badge-success', 'badge-warning');
      sliderStatusBadge.classList.add(isEnabled ? 'badge-success' : 'badge-warning');
    }

    sliderConfig?.classList.toggle('hidden', !isEnabled);
  };

  sliderInputs.forEach((input) => {
    input.addEventListener('change', updateSliderStatus);
  });

  validationConfirmButton?.addEventListener('click', () => {
    resolveValidationDialog(true);
  });

  validationCancelButton?.addEventListener('click', () => {
    resolveValidationDialog(false);
  });

  validationCloseButton?.addEventListener('click', () => {
    resolveValidationDialog(false);
  });

  validationModal?.addEventListener('click', (event) => {
    if (event.target === validationModal) {
      resolveValidationDialog(false);
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && activeValidationResolver) {
      resolveValidationDialog(false);
    }
  });

  sliderMediaTypeInputs.forEach((input) => {
    input.addEventListener('change', updateSliderTypeInput);
  });

  const addYoutubeToSlider = () => {
    if (getCurrentSliderMediaType() !== 'youtube') return;

    const rawYoutubeUrl = (sliderYoutubeInput?.value || '').trim();
    if (!rawYoutubeUrl) {
      showWarning('Please paste a YouTube URL to add to the slider.');
      return;
    }

    const videoId = parseYouTubeVideoId(rawYoutubeUrl);
    if (!videoId) {
      showWarning('Invalid YouTube URL. Please use a valid YouTube video link.');
      return;
    }

    if (sliderItems.some((item) => item.type === 'youtube' && item.videoId === videoId)) {
      showInfo('This YouTube video is already in the slider list.');
      return;
    }

    sliderItems.push({
      id: sliderSequence,
      type: 'youtube',
      name: `YouTube Video (${videoId})`,
      size: 0,
      url: rawYoutubeUrl,
      videoId,
      embedUrl: getYouTubeEmbedUrl(videoId),
      width: null,
      height: null,
    });
    sliderSequence += 1;
    renderSliderList();

    if (sliderYoutubeInput) {
      sliderYoutubeInput.value = '';
      sliderYoutubeInput.focus();
    }
  };

  sliderYoutubeAddButton?.addEventListener('click', addYoutubeToSlider);
  sliderYoutubeInput?.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter') return;
    event.preventDefault();
    addYoutubeToSlider();
  });

  coverInput?.addEventListener('change', async () => {
    const file = coverInput.files?.[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
      showWarning('Main product cover supports image files only.');
      coverInput.value = '';
      return;
    }

    if (!validateFileSize(file, 'image', 'Cover')) {
      coverInput.value = '';
      return;
    }

    try {
      const dimensions = await readImageDimensions(file);
      const canProceed = await confirmRatioIfMismatch({
        width: dimensions.width,
        height: dimensions.height,
        expectedRatio: coverRatio,
        expectedHint: '1:1 (1080 x 1080)',
      });

      if (!canProceed) {
        coverInput.value = '';
        return;
      }

      releaseMediaUrl(coverItem);
      coverItem = {
        type: 'image',
        name: file.name,
        size: file.size,
        width: dimensions.width,
        height: dimensions.height,
        url: window.URL.createObjectURL(file),
      };
      renderCoverList();
    } catch (error) {
      showError('Unable to process cover image. Please try another file.');
    }

    coverInput.value = '';
  });

  sliderItemInput?.addEventListener('change', async () => {
    const file = sliderItemInput.files?.[0];
    if (!file) return;

    const sliderType = getCurrentSliderMediaType();
    if (sliderType === 'youtube') {
      showWarning('Switch slider media type to Image or Video Upload for file uploads.');
      sliderItemInput.value = '';
      return;
    }

    if (sliderType === 'image' && !file.type.startsWith('image/')) {
      showWarning('Selected slider media type is Image. Please choose an image file.');
      sliderItemInput.value = '';
      return;
    }

    if (sliderType === 'video' && !file.type.startsWith('video/')) {
      showWarning('Selected slider media type is Video Upload. Please choose a video file.');
      sliderItemInput.value = '';
      return;
    }

    if (!validateFileSize(file, sliderType, 'Slider')) {
      sliderItemInput.value = '';
      return;
    }

    if (sliderType === 'image' && getSliderImageCount() >= maxSliderImages) {
      showWarning(`You can add maximum ${maxSliderImages} slider images for a product.`);
      sliderItemInput.value = '';
      return;
    }

    let nextItem = {
      id: sliderSequence,
      type: sliderType,
      name: file.name,
      size: file.size,
      url: window.URL.createObjectURL(file),
      width: null,
      height: null,
    };

    if (sliderType === 'image') {
      try {
        const dimensions = await readImageDimensions(file);
        const canProceed = await confirmRatioIfMismatch({
          width: dimensions.width,
          height: dimensions.height,
          expectedRatio: sliderImageRatio,
          expectedHint: '16:9 (1600 x 900)',
        });

        if (!canProceed) {
          releaseMediaUrl(nextItem);
          sliderItemInput.value = '';
          return;
        }

        nextItem = {
          ...nextItem,
          width: dimensions.width,
          height: dimensions.height,
        };
      } catch (error) {
        releaseMediaUrl(nextItem);
        showError('Unable to process slider image. Please try another file.');
        sliderItemInput.value = '';
        return;
      }
    }

    sliderItems.push(nextItem);
    sliderSequence += 1;
    renderSliderList();
    sliderItemInput.value = '';
  });

  renderCoverList();
  renderSliderList();
  updateSliderTypeInput();
  updateSliderStatus();
}

function initProductCreateDiscountOfferControl() {
  const durationInput = document.querySelector('[data-discount-offer-duration]');
  const dateTimeGroups = Array.from(document.querySelectorAll('[data-discount-offer-datetime]'));

  if (!durationInput || !dateTimeGroups.length) return;

  const updateDiscountOfferMode = () => {
    const useDateTimeRange = durationInput.value === 'date_time';

    dateTimeGroups.forEach((group) => {
      group.classList.toggle('hidden', !useDateTimeRange);
      group.querySelectorAll('input').forEach((input) => {
        input.disabled = !useDateTimeRange;

        if (!useDateTimeRange) {
          input.value = '';
        }
      });
    });
  };

  durationInput.addEventListener('change', updateDiscountOfferMode);
  updateDiscountOfferMode();
}

function initProductCreateAiWriter() {
  const productNameInput = document.getElementById('productName');
  const shortDescriptionInput = document.getElementById('productShortDescription');
  const fullDescriptionInput = document.getElementById('productDescription');
  const fullDescriptionEditorHost = document.getElementById('productDescriptionEditor');
  const categoryInput = document.getElementById('productCategory');
  const slugInput = document.getElementById('productSlug');
  const metaTitleInput = document.getElementById('productMetaTitle');
  const metaDescriptionInput = document.getElementById('productMetaDescription');
  const tagsInput = document.getElementById('productTags');
  const shortDescriptionCounter = document.querySelector('[data-product-short-count]');
  const shortAiButton = document.querySelector('[data-product-ai-short]');
  const fullAiButton = document.querySelector('[data-product-ai-full]');
  const seoAiButton = document.querySelector('[data-product-ai-seo]');
  const shortDescriptionMaxLength = 150;

  if (!productNameInput || !shortAiButton || !fullAiButton || !seoAiButton) return;

  let fullDescriptionEditor = null;

  const htmlToPlainText = (value) => String(value || '')
    .replace(/<style[\s\S]*?<\/style>/gi, ' ')
    .replace(/<script[\s\S]*?<\/script>/gi, ' ')
    .replace(/<[^>]+>/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();

  const syncFullDescriptionInput = () => {
    if (!fullDescriptionInput) return;

    if (fullDescriptionEditor) {
      const contentText = fullDescriptionEditor.getText().trim();
      fullDescriptionInput.value = contentText ? fullDescriptionEditor.root.innerHTML : '';
      return;
    }

    fullDescriptionInput.value = fullDescriptionInput.value.trim();
  };

  const initFullDescriptionEditor = () => {
    if (!fullDescriptionInput) return;

    if (
      fullDescriptionEditorHost &&
      typeof window !== 'undefined' &&
      typeof window.Quill !== 'undefined'
    ) {
      if (fullDescriptionEditor) return;

      fullDescriptionInput.hidden = true;

      fullDescriptionEditor = new window.Quill(fullDescriptionEditorHost, {
        theme: 'snow',
        placeholder: fullDescriptionEditorHost.dataset.placeholder || fullDescriptionInput.placeholder || 'Write full description...',
        modules: {
          toolbar: [
            [{header: [2, 3, false]}],
            ['bold', 'italic', 'underline'],
            [{list: 'ordered'}, {list: 'bullet'}],
            ['link', 'blockquote'],
            [{align: []}],
            ['clean'],
          ],
        },
      });

      const initialContent = fullDescriptionInput.value.trim();
      if (initialContent) {
        fullDescriptionEditor.clipboard.dangerouslyPasteHTML(initialContent);
      }

      syncFullDescriptionInput();
      fullDescriptionEditor.on('text-change', () => {
        syncFullDescriptionInput();
        fullDescriptionInput.dispatchEvent(new Event('input', {bubbles: true}));
      });
      return;
    }

    if (fullDescriptionEditorHost) {
      fullDescriptionEditorHost.hidden = true;
    }
    fullDescriptionInput.hidden = false;
  };

  const getFullDescriptionText = () => {
    if (fullDescriptionEditor) {
      return fullDescriptionEditor.getText().trim();
    }

    return htmlToPlainText(fullDescriptionInput?.value || '');
  };

  const setFullDescriptionContent = (htmlContent, fallbackText) => {
    if (fullDescriptionEditor) {
      fullDescriptionEditor.clipboard.dangerouslyPasteHTML(htmlContent);
      syncFullDescriptionInput();
      fullDescriptionInput.dispatchEvent(new Event('input', {bubbles: true}));
      return;
    }

    if (fullDescriptionInput) {
      fullDescriptionInput.value = fallbackText;
      fullDescriptionInput.dispatchEvent(new Event('input', {bubbles: true}));
    }
  };

  const updateShortDescriptionCounter = () => {
    if (!shortDescriptionInput || !shortDescriptionCounter) return;
    const length = shortDescriptionInput.value.length;
    shortDescriptionCounter.textContent = `${length}/${shortDescriptionMaxLength} characters`;
    shortDescriptionCounter.classList.toggle('is-limit', length >= shortDescriptionMaxLength);
  };

  const setShortDescriptionValue = (value, notifyWhenTrimmed = false) => {
    if (!shortDescriptionInput) return;

    let nextValue = String(value || '').trim();
    if (nextValue.length > shortDescriptionMaxLength) {
      nextValue = nextValue.slice(0, shortDescriptionMaxLength).trimEnd();
      if (notifyWhenTrimmed) {
        showInfo('Short Description is limited to 150 characters. Text was trimmed.');
      }
    }

    shortDescriptionInput.value = nextValue;
    shortDescriptionInput.dispatchEvent(new Event('input', {bubbles: true}));
  };

  const slugify = (value) => String(value || '')
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '');

  const setButtonHint = (button, message) => {
    if (!button) return;
    button.title = message;
    button.setAttribute('aria-label', message);
  };

  const updateAiButtonState = () => {
    const hasProductName = productNameInput.value.trim() !== '';
    const hasShortDescription = (shortDescriptionInput?.value || '').trim() !== '';
    const hasFullDescription = getFullDescriptionText() !== '';

    shortAiButton.disabled = !hasProductName;
    fullAiButton.disabled = !hasProductName;
    seoAiButton.disabled = !(hasProductName && hasShortDescription && hasFullDescription);

    setButtonHint(
      shortAiButton,
      hasProductName ? 'Write short description with AI' : 'Add Product Name first'
    );
    setButtonHint(
      fullAiButton,
      hasProductName ? 'Write full description with AI' : 'Add Product Name first'
    );
    setButtonHint(
      seoAiButton,
      hasProductName && hasShortDescription && hasFullDescription
        ? 'Write SEO fields with AI'
        : 'Add Product Name, Short Description, and Full Description first'
    );
  };

  const runAiAction = (button, writer) => {
    if (!button || button.disabled) return;

    button.disabled = true;
    button.classList.add('is-processing');

    window.setTimeout(() => {
      try {
        writer();
      } finally {
        button.classList.remove('is-processing');
        updateAiButtonState();
      }
    }, 700);
  };

  shortAiButton.addEventListener('click', () => {
    runAiAction(shortAiButton, () => {
      const productName = productNameInput.value.trim();
      const category = (categoryInput?.value || '').trim();
      const categoryText = category ? category.toLowerCase() : 'product';

      setShortDescriptionValue(
        `${productName} is a premium ${categoryText} built for daily performance, long-lasting quality, and strong customer value.`,
        true
      );

      showSuccess('Short description written with AI preview.');
    });
  });

  fullAiButton.addEventListener('click', () => {
    runAiAction(fullAiButton, () => {
      const productName = productNameInput.value.trim();
      const category = (categoryInput?.value || '').trim() || 'Product';
      const htmlContent =
        `<p><strong>${productName}</strong> is a carefully prepared ${category.toLowerCase()} for customers who want consistent quality and comfort.</p>` +
        '<p>This item is designed to balance quality, usability, and customer satisfaction. It is suitable for daily use and built to keep performance stable over time.</p>' +
        '<p><strong>Key highlights:</strong></p>' +
        '<ul><li>Reliable build quality</li><li>Comfortable everyday experience</li><li>Easy to maintain and use</li></ul>';
      const textContent =
        `${productName} is a carefully prepared ${category.toLowerCase()} for customers who want consistent quality and comfort.\n\n` +
        'This item is designed to balance quality, usability, and customer satisfaction. It is suitable for daily use and built to keep performance stable over time.\n\n' +
        `Key highlights:\n` +
        `- Reliable build quality\n` +
        `- Comfortable everyday experience\n` +
        `- Easy to maintain and use`;

      setFullDescriptionContent(htmlContent, textContent);

      showSuccess('Full description written with AI preview.');
    });
  });

  seoAiButton.addEventListener('click', () => {
    runAiAction(seoAiButton, () => {
      const productName = productNameInput.value.trim();
      const shortDescription = (shortDescriptionInput?.value || '').trim();
      const fullDescription = getFullDescriptionText();
      const category = (categoryInput?.value || '').trim();
      const baseSlug = slugify(productName);
      const metaDescriptionBase = shortDescription || fullDescription;
      const keywordPool = [productName, category]
        .join(' ')
        .toLowerCase()
        .split(/[^a-z0-9]+/)
        .filter((part) => part.length > 2);
      const uniqueKeywords = [...new Set(keywordPool)].slice(0, 8);

      if (slugInput) {
        slugInput.value = baseSlug;
      }

      if (metaTitleInput) {
        metaTitleInput.value = `${productName} | Buy Online at A Metafy`;
      }

      if (metaDescriptionInput) {
        metaDescriptionInput.value = metaDescriptionBase.slice(0, 155);
      }

      if (tagsInput) {
        tagsInput.value = uniqueKeywords.join(', ');
      }

      showSuccess('Search & discoverability fields written with AI preview.');
    });
  });

  productNameInput.addEventListener('input', updateAiButtonState);

  shortDescriptionInput?.addEventListener('input', () => {
    if (shortDescriptionInput.value.length > shortDescriptionMaxLength) {
      shortDescriptionInput.value = shortDescriptionInput.value.slice(0, shortDescriptionMaxLength);
    }
    updateShortDescriptionCounter();
    updateAiButtonState();
  });

  fullDescriptionInput?.addEventListener('input', updateAiButtonState);

  initFullDescriptionEditor();
  updateShortDescriptionCounter();

  updateAiButtonState();
}

function initCategoryAiWriter() {
  const trigger = document.querySelector('[data-category-ai-generate]');
  const categoryNameInput = document.querySelector('[data-category-name-input]');
  const descriptionInput = document.querySelector('[data-category-description-input]');
  const addCategoryButton = document.querySelector('[data-category-add-button]');
  const statusNode = document.querySelector('[data-category-ai-status]');

  if (!trigger || !categoryNameInput || !descriptionInput) return;

  let processingTimer = null;
  const defaultAddButtonText = addCategoryButton?.textContent?.trim() || '+ Add Category';

  const setStatus = (message, tone = '') => {
    if (!statusNode) return;
    statusNode.textContent = message;
    statusNode.className = `categories-ai-status${tone ? ` is-${tone}` : ''}`;
  };

  const buildDemoDescription = (categoryName) => {
    const safeName = categoryName.trim();
    return `${safeName} brings curated, high-demand items with clear quality standards, consistent pricing, and fast delivery support for everyday buyers.`;
  };

  trigger.addEventListener('click', () => {
    const categoryName = categoryNameInput.value.trim();

    if (!categoryName) {
      setStatus('Please add category name to write this.', 'error');
      showWarning('Please add category name to write this.');
      return;
    }

    if (processingTimer) {
      clearTimeout(processingTimer);
      processingTimer = null;
    }

    trigger.disabled = true;
    trigger.classList.add('is-processing');

    if (addCategoryButton) {
      addCategoryButton.disabled = true;
      addCategoryButton.textContent = 'Processing...';
    }

    setStatus(`AI is generating a short description for "${categoryName}"...`, 'processing');

    processingTimer = window.setTimeout(() => {
      descriptionInput.value = buildDemoDescription(categoryName);
      trigger.disabled = false;
      trigger.classList.remove('is-processing');

      if (addCategoryButton) {
        addCategoryButton.disabled = false;
        addCategoryButton.textContent = defaultAddButtonText;
      }

      setStatus(`Description generated for "${categoryName}".`, 'success');
      showSuccess('AI description generated (demo).');
      processingTimer = null;
    }, 1400);
  });
}

// ══════════════════════════════════════════
// CATEGORIES: EDIT PANEL (DEMO UI)
// ══════════════════════════════════════════
function initCategoryEditor() {
  const createPanel = document.querySelector('[data-category-create-panel]');
  const editPanel = document.querySelector('[data-category-edit-panel]');
  const editButtons = Array.from(document.querySelectorAll('[data-category-edit]'));

  if (!createPanel || !editPanel || !editButtons.length) return;

  const titleNode = editPanel.querySelector('[data-category-edit-title]');
  const nameInput = editPanel.querySelector('[data-category-edit-name]');
  const slugInput = editPanel.querySelector('[data-category-edit-slug]');
  const statusInput = editPanel.querySelector('[data-category-edit-status]');
  const parentInput = editPanel.querySelector('[data-category-edit-parent]');
  const descriptionInput = editPanel.querySelector('[data-category-edit-description]');
  const productsNode = editPanel.querySelector('[data-category-edit-products]');
  const shareNode = editPanel.querySelector('[data-category-edit-share]');
  const updatedNode = editPanel.querySelector('[data-category-edit-updated]');
  const cancelButton = editPanel.querySelector('[data-category-edit-cancel]');
  const saveButton = editPanel.querySelector('[data-category-edit-save]');

  let activeRow = null;

  const setEditMode = (enabled) => {
    createPanel.classList.toggle('is-hidden', enabled);
    editPanel.classList.toggle('is-visible', enabled);
    editPanel.setAttribute('aria-hidden', enabled ? 'false' : 'true');
  };

  const setSelectValue = (selectNode, value, fallback = '') => {
    if (!selectNode) return;
    const nextValue = value ?? fallback;
    const hasOption = Array.from(selectNode.options).some(option => option.value === nextValue);
    selectNode.value = hasOption ? nextValue : fallback;
  };

  const readRowData = (row) => ({
    name: row.dataset.categoryName || '',
    slug: row.dataset.categorySlug || '',
    status: row.dataset.categoryStatus || 'Draft',
    products: Number.parseInt(row.dataset.categoryProducts || '0', 10) || 0,
    share: Number.parseInt(row.dataset.categoryShare || '0', 10) || 0,
    parent: row.dataset.categoryParent || '',
    updatedAt: row.dataset.categoryUpdated || '-',
    description: row.dataset.categoryDescription || '',
  });

  const populateEditor = (row) => {
    const category = readRowData(row);

    if (titleNode) {
      titleNode.textContent = `Edit Category: ${category.name}`;
    }

    if (nameInput) nameInput.value = category.name;
    if (slugInput) slugInput.value = category.slug;
    if (descriptionInput) descriptionInput.value = category.description;
    setSelectValue(statusInput, category.status, 'Draft');
    setSelectValue(parentInput, category.parent, '');

    if (productsNode) productsNode.textContent = `Products: ${category.products}`;
    if (shareNode) shareNode.textContent = `Share: ${category.share}%`;
    if (updatedNode) updatedNode.textContent = `Updated: ${category.updatedAt}`;
  };

  const closeEditor = () => {
    activeRow = null;
    setEditMode(false);
  };

  editButtons.forEach(button => {
    button.addEventListener('click', () => {
      const row = button.closest('[data-category-row]');
      if (!row) return;
      activeRow = row;
      populateEditor(row);
      setEditMode(true);
      editPanel.scrollIntoView({behavior: 'smooth', block: 'start'});
    });
  });

  cancelButton?.addEventListener('click', () => {
    closeEditor();
    showInfo('Edit cancelled.');
  });

  saveButton?.addEventListener('click', () => {
    if (!activeRow) return;

    const nextName = nameInput?.value.trim() || '';
    const nextSlug = slugInput?.value.trim() || '';
    const nextStatus = statusInput?.value || 'Draft';
    const nextParent = parentInput?.value || '';
    const nextDescription = descriptionInput?.value.trim() || '';

    if (!nextName) {
      showError('Category name is required.');
      nameInput?.focus();
      return;
    }

    const categoryCell = activeRow.cells[0];
    const slugCell = activeRow.cells[1];
    const statusCell = activeRow.cells[4];
    const updatedCell = activeRow.cells[5];

    if (categoryCell) {
      const titleNodeInRow = categoryCell.querySelector('strong');
      if (titleNodeInRow) {
        titleNodeInRow.textContent = nextName;
      }

      let parentNote = categoryCell.querySelector('.categories-parent-note');
      if (nextParent) {
        if (!parentNote) {
          parentNote = document.createElement('small');
          parentNote.className = 'categories-parent-note';
          categoryCell.appendChild(parentNote);
        }
        parentNote.textContent = `Parent: ${nextParent}`;
      } else if (parentNote) {
        parentNote.remove();
      }
    }

    if (slugCell) {
      slugCell.textContent = nextSlug;
    }

    if (statusCell) {
      statusCell.innerHTML = '';
      const badge = document.createElement('span');
      badge.className = `badge ${nextStatus === 'Active' ? 'badge-success' : 'badge-warning'}`;
      badge.textContent = nextStatus;
      statusCell.appendChild(badge);
    }

    if (updatedCell) {
      updatedCell.textContent = 'Just now';
    }

    activeRow.dataset.categoryName = nextName;
    activeRow.dataset.categorySlug = nextSlug;
    activeRow.dataset.categoryStatus = nextStatus;
    activeRow.dataset.categoryParent = nextParent;
    activeRow.dataset.categoryDescription = nextDescription;
    activeRow.dataset.categoryUpdated = 'Just now';

    closeEditor();
    showSuccess(`"${nextName}" updated (demo only).`);
  });
}

// ══════════════════════════════════════════
// CATEGORIES: DELETE GUARD (DEMO UI)
// ══════════════════════════════════════════
function initCategoryDeleteGuards() {
  const getRows = () => Array.from(document.querySelectorAll('[data-category-row]'));
  const deleteButtons = Array.from(document.querySelectorAll('[data-category-delete]'));
  const feedback = document.querySelector('[data-categories-delete-feedback]');
  const totalNode = document.querySelector('[data-categories-total]');

  if (!deleteButtons.length) return;

  const showFeedback = (message, type = 'error') => {
    if (!feedback) {
      if (type === 'success') {
        showSuccess(message);
      } else {
        showError(message);
      }
      return;
    }

    feedback.textContent = message;
    feedback.className = `categories-delete-feedback is-visible ${type === 'success' ? 'is-success' : 'is-error'}`;
  };

  const refreshTotal = () => {
    if (!totalNode) return;
    totalNode.textContent = `${getRows().length} total`;
  };

  deleteButtons.forEach(button => {
    button.addEventListener('click', () => {
      const row = button.closest('[data-category-row]');
      if (!row) return;

      const categoryName = row.dataset.categoryName || 'This category';
      const productsCount = Number.parseInt(row.dataset.categoryProducts || '0', 10) || 0;
      const childCount = getRows().filter(otherRow => {
        if (otherRow === row) return false;
        return (otherRow.dataset.categoryParent || '').trim() === categoryName;
      }).length;

      if (childCount > 0) {
        showFeedback(
          `Cannot delete "${categoryName}". It has ${childCount} child categories. Reassign child categories first.`,
          'error'
        );
        return;
      }

      if (productsCount > 0) {
        showFeedback(
          `Cannot delete "${categoryName}". It has ${productsCount} products. Move products to another category first.`,
          'error'
        );
        return;
      }

      row.remove();
      refreshTotal();
      showFeedback(`"${categoryName}" removed (demo only).`, 'success');
    });
  });
}

// ══════════════════════════════════════════
// FILE UPLOAD
// ══════════════════════════════════════════
function initFileUpload() {
  document.querySelectorAll('.file-upload').forEach(upload => {
    const input = upload.querySelector('input[type="file"]');
    const preview = upload.querySelector('.file-preview');

    input?.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (file && preview) {
        if (file.type.startsWith('image/')) {
          const reader = new FileReader();
          reader.onload = (e) => {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; border-radius: 8px;">`;
          };
          reader.readAsDataURL(file);
        } else {
          preview.innerHTML = `<div>File: ${file.name}</div>`;
        }
      }
    });
  });
}

// ══════════════════════════════════════════
// PAGINATION
// ══════════════════════════════════════════
function setupPagination(totalItems, itemsPerPage = 10) {
  const totalPages = Math.ceil(totalItems / itemsPerPage);
  let currentPage = 1;

  const pagination = document.createElement('div');
  pagination.className = 'pagination';

  function render() {
    pagination.innerHTML = '';

    // Previous button
    const prev = document.createElement('button');
    prev.textContent = '‹';
    prev.disabled = currentPage === 1;
    prev.onclick = () => {
      if (currentPage > 1) {
        currentPage--;
        render();
      }
    };
    pagination.appendChild(prev);

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
      const page = document.createElement('button');
      page.textContent = i;
      page.classList.toggle('active', i === currentPage);
      page.onclick = () => {
        currentPage = i;
        render();
      };
      pagination.appendChild(page);
    }

    // Next button
    const next = document.createElement('button');
    next.textContent = '›';
    next.disabled = currentPage === totalPages;
    next.onclick = () => {
      if (currentPage < totalPages) {
        currentPage++;
        render();
      }
    };
    pagination.appendChild(next);
  }

  render();
  return pagination;
}

// ══════════════════════════════════════════
// UTILITY FUNCTIONS
// ══════════════════════════════════════════
function formatCurrency(amount) {
  return new Intl.NumberFormat('en-BD', {
    style: 'currency',
    currency: 'BDT',
    minimumFractionDigits: 0
  }).format(amount);
}

function formatDate(date) {
  return new Intl.DateTimeFormat('en-BD', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  }).format(new Date(date));
}

function formatTime(date) {
  return new Intl.DateTimeFormat('en-BD', {
    hour: 'numeric',
    minute: 'numeric',
    hour12: true
  }).format(new Date(date));
}

function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// ══════════════════════════════════════════
// EXPORT FUNCTIONS
// ══════════════════════════════════════════
window.adminPanel = {
  openModal,
  closeAllModals,
  showToast,
  showSuccess,
  showError,
  showWarning,
  showInfo,
  validateForm,
  formatCurrency,
  formatDate,
  formatTime,
  debounce,
  richText: window.AdminCkeditor,
};

// ══════════════════════════════════════════
// ANIMATIONS
// ══════════════════════════════════════════
const slideOutAnimation = `
@keyframes slideOut {
  to { transform: translateX(400px); opacity: 0; }
}
`;

const style = document.createElement('style');
style.textContent = slideOutAnimation;
document.head.appendChild(style);

console.log('✓ A Metafy Admin Panel loaded');
