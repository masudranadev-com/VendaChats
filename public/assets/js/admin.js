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

const ORDER_CALL_PAGE_NAME_STORAGE_KEY = 'admin_order_call_page_name';
const ORDER_CALL_LANGUAGE_STORAGE_KEY = 'admin_order_call_language';
const ORDER_CALL_ENABLED_STORAGE_KEY = 'admin_order_call_enabled';
const ORDER_CALL_SCOPE_STORAGE_KEY = 'admin_order_call_scope';

// ── Initialize on DOM load ──
document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  initCkEditors();
  initSidebar();
  initSupportHelpFab();
  initDropdowns();
  initModals();
  initToasts();
  initAdminOnboarding();
  initTables();
  initCharts();
  initSearch();
  initCampaignBuilder();
  initOrdersCatalogPage();
  initOrderDetailsPage();
  initOrdersManualOrder();
  initBotSettings();
  initOrderCallPage();
  initDashboardPage();
  initPackagesPage();
  initProductsCatalogPage();
  initUsersCatalogPage();
  initProductsAttentionPanel();
  initProductTypeSelector();
  initDownloadableLinkType();
  initSubscriptionEntries();
  initFacilityEntries();
  initProductVariants();
  initProductCreateSliderControl();
  initProductCreateDiscountOfferControl();
  initProductCreateCategoryPicker();
  initProductDemoAutoFill();
  initCategoryCreateForm();
  initCategoryAiWriter();
  initCategoryEditor();
  initCategoryDeleteGuards();
  initProductCreateAiWriter();
  initProductCallVoicePreview();
  initProductCreateSubmit();
  initProductEditPrefill();
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
  const navAccordions = Array.from(document.querySelectorAll('[data-nav-accordion]'));

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

  navAccordions.forEach((accordion) => {
    if (!(accordion instanceof HTMLDetailsElement)) {
      return;
    }

    const summary = accordion.querySelector('summary');

    if (!(summary instanceof HTMLElement)) {
      return;
    }

    if (window.innerWidth > 1024 && sidebar?.classList.contains('collapsed') && accordion.open) {
      sidebar.classList.remove('collapsed');
      localStorage.setItem('sidebar-collapsed', 'false');
    }

    summary.addEventListener('click', () => {
      if (window.innerWidth > 1024 && sidebar?.classList.contains('collapsed')) {
        sidebar.classList.remove('collapsed');
        localStorage.setItem('sidebar-collapsed', 'false');
      }
    });
  });
}

function initSupportHelpFab() {
  const fab = document.querySelector('.support-help-fab');
  const bubble = fab?.querySelector('[data-support-help-bubble]');

  if (!(fab instanceof HTMLElement) || !(bubble instanceof HTMLElement)) {
    return;
  }

  const visibleDurationMs = 5000;
  const hiddenDurationMs = 20000;
  let showTimerId = 0;
  let hideTimerId = 0;

  const clearTimers = () => {
    window.clearTimeout(showTimerId);
    window.clearTimeout(hideTimerId);
  };

  const scheduleShow = () => {
    window.clearTimeout(showTimerId);
    showTimerId = window.setTimeout(showBubble, hiddenDurationMs);
  };

  const scheduleHide = () => {
    window.clearTimeout(hideTimerId);
    hideTimerId = window.setTimeout(hideBubble, visibleDurationMs);
  };

  const showBubble = () => {
    window.clearTimeout(showTimerId);
    bubble.classList.remove('is-hidden');
    bubble.classList.add('is-visible');
    bubble.setAttribute('aria-hidden', 'false');
    scheduleHide();
  };

  const hideBubble = () => {
    window.clearTimeout(hideTimerId);
    bubble.classList.remove('is-visible');
    bubble.classList.add('is-hidden');
    bubble.setAttribute('aria-hidden', 'true');
    scheduleShow();
  };

  fab.addEventListener('mouseenter', showBubble);
  fab.addEventListener('focusin', showBubble);

  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      clearTimers();
      return;
    }

    showBubble();
  });

  window.addEventListener('beforeunload', clearTimers, { once: true });

  showBubble();
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

function initAdminOnboarding() {
  const shell = document.querySelector('[data-admin-onboarding]');
  if (!(shell instanceof HTMLElement)) {
    return;
  }

  const text = (value) => String(value ?? '').trim();
  const parseJson = (value) => {
    try {
      return JSON.parse(value);
    } catch (error) {
      return {};
    }
  };
  const slugify = (value) => text(value)
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
    .replace(/-{2,}/g, '-');
  const normalizeProductType = (value) => {
    const normalized = text(value).toLowerCase();
    if (normalized === 'digital') {
      return 'subscription';
    }
    return ['physical', 'subscription', 'downloadable'].includes(normalized) ? normalized : 'physical';
  };
  const hasOwn = (value, key) => Boolean(value) && Object.prototype.hasOwnProperty.call(value, key);
  const normalizeCallingEnabled = (value) => {
    const normalized = text(value).toLowerCase();
    return ['1', 'true', 'yes', 'on'].includes(normalized);
  };
  const normalizeCallScope = (value) => text(value).toLowerCase() === 'all' ? 'all' : 'cod';
  const titleCase = (value) => {
    const normalized = text(value).replace(/[_-]+/g, ' ').toLowerCase();
    return normalized.replace(/\b\w/g, (char) => char.toUpperCase());
  };
  const readSessionValue = (key) => {
    try {
      return window.sessionStorage.getItem(key);
    } catch (error) {
      return null;
    }
  };
  const writeSessionValue = (key, value) => {
    try {
      window.sessionStorage.setItem(key, value);
    } catch (error) {
      return;
    }
  };

  const storageKey = text(shell.dataset.storageKey) || 'admin_onboarding_draft_v1';
  const hiddenKey = text(shell.dataset.hiddenKey) || 'admin_onboarding_completed_v1';
  const domainSuffix = text(shell.dataset.domainSuffix) || 'ametafy.shop';
  const continueUrl = text(shell.dataset.continueUrl);
  const dashboardUrl = text(shell.dataset.dashboardUrl);
  const initialCurrentStepValue = Number(shell.dataset.initialCurrentStep);
  const initialHighestCompletedStepValue = Number(shell.dataset.initialHighestCompletedStep);
  const persistHiddenState = shell.dataset.persistHidden !== '0';
  const csrfToken = text(document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));

  if (persistHiddenState && readSessionValue(hiddenKey) === '1') {
    shell.remove();
    return;
  }

  const productInputs = Array.from(shell.querySelectorAll('[data-admin-onboarding-product-type]'));
  const productCards = Array.from(shell.querySelectorAll('[data-admin-onboarding-product-card]'));
  const stepPanels = Array.from(shell.querySelectorAll('[data-admin-onboarding-step]'));
  const stepChips = Array.from(shell.querySelectorAll('[data-admin-onboarding-step-chip]'));
  const progressLabelNode = shell.querySelector('[data-admin-onboarding-progress-label]');
  const progressFillNode = shell.querySelector('[data-admin-onboarding-progress-fill]');
  const mainNode = shell.querySelector('.admin-onboarding-main');
  const backButton = shell.querySelector('[data-admin-onboarding-back]');
  const nextButton = shell.querySelector('[data-admin-onboarding-next]');
  const finishButton = shell.querySelector('[data-admin-onboarding-finish]');
  const dismissButton = shell.querySelector('[data-admin-onboarding-dismiss]');
  const subdomainInput = shell.querySelector('[data-admin-onboarding-subdomain]');
  const pageNameInput = shell.querySelector('[data-admin-onboarding-page-name]');
  const primaryLanguageSelect = shell.querySelector('[data-admin-onboarding-primary-language]');
  const callingToggleInput = shell.querySelector('[data-admin-onboarding-calling-toggle]');
  const callingToggleStateNode = shell.querySelector('[data-admin-onboarding-calling-state]');
  const callScopeInputs = Array.from(shell.querySelectorAll('[data-admin-onboarding-call-scope]'));
  const scopeCards = Array.from(shell.querySelectorAll('[data-admin-onboarding-scope-card]'));
  const timezoneSelect = shell.querySelector('[data-admin-onboarding-timezone]');
  const adminLanguageSelect = shell.querySelector('[data-admin-onboarding-admin-language]');
  const websiteLanguageSelect = shell.querySelector('[data-admin-onboarding-website-language]');
  const domainPreviewNode = shell.querySelector('[data-admin-onboarding-domain-preview]');

  if (
    !(subdomainInput instanceof HTMLInputElement)
    || !(pageNameInput instanceof HTMLInputElement)
    || !(primaryLanguageSelect instanceof HTMLSelectElement)
    || !(callingToggleInput instanceof HTMLInputElement)
    || !(timezoneSelect instanceof HTMLSelectElement)
    || !(adminLanguageSelect instanceof HTMLSelectElement)
    || !(websiteLanguageSelect instanceof HTMLSelectElement)
    || !(nextButton instanceof HTMLButtonElement)
    || !(finishButton instanceof HTMLButtonElement)
  ) {
    shell.remove();
    return;
  }

  const summaryNodes = {
    domain: shell.querySelector('[data-admin-onboarding-summary="domain"]'),
    productType: shell.querySelector('[data-admin-onboarding-summary="productType"]'),
    orderCall: shell.querySelector('[data-admin-onboarding-summary="orderCall"]'),
    locale: shell.querySelector('[data-admin-onboarding-summary="locale"]'),
  };
  const nextButtonLabels = [
    'Continue to subdomain',
    'Continue to order call',
    'Continue to locale',
  ];
  const primaryLanguageOptions = Array.from(primaryLanguageSelect.options).map((option) => ({
    value: text(option.value).toLowerCase(),
    label: text(option.textContent) || titleCase(option.value),
  }));
  const adminLanguageOptionMap = new Map(
    Array.from(adminLanguageSelect.options).map((option) => [
      text(option.value).toLowerCase(),
      text(option.textContent) || titleCase(option.value),
    ])
  );
  const websiteLanguageOptionMap = new Map(
    Array.from(websiteLanguageSelect.options).map((option) => [
      text(option.value).toLowerCase(),
      text(option.textContent) || titleCase(option.value),
    ])
  );
  const timezoneOptionMap = new Map(
    Array.from(timezoneSelect.options).map((option) => [
      text(option.value),
      text(option.textContent) || text(option.value),
    ])
  );
  const timezoneOptions = Array.from(timezoneSelect.options).map((option) => text(option.value));
  const adminLanguageOptions = Array.from(adminLanguageSelect.options).map((option) => text(option.value));
  const websiteLanguageOptions = Array.from(websiteLanguageSelect.options).map((option) => text(option.value));
  const normalizeSelectValue = (value, options, fallback) => {
    const target = text(value).toLowerCase();
    const matched = options.find((option) => text(option).toLowerCase() === target);
    return matched || fallback;
  };
  const languageLabel = (value) => {
    const target = text(value).toLowerCase();
    const matched = primaryLanguageOptions.find((option) => option.value === target);
    return matched?.label || titleCase(target || 'english');
  };
  const adminLanguageLabel = (value) => adminLanguageOptionMap.get(text(value).toLowerCase()) || titleCase(value || 'en');
  const websiteLanguageLabel = (value) => websiteLanguageOptionMap.get(text(value).toLowerCase()) || titleCase(value || 'en');
  const timezoneLabel = (value) => timezoneOptionMap.get(text(value)) || text(value) || 'Asia/Dhaka';
  const productTypeLabel = (value) => {
    const labels = {
      physical: 'Physical',
      subscription: 'Subscription',
      downloadable: 'Downloadable',
    };
    return labels[normalizeProductType(value)] || 'Physical';
  };
  const callScopeLabel = (value) => normalizeCallScope(value) === 'all' ? 'All buyers' : 'COD buyers';
  const domainPreview = (value) => `${slugify(value) || 'yourbrand'}.${domainSuffix}`;
  const stepChipStateMarkup = (stateName) => {
    const normalized = text(stateName).toLowerCase();
    const labels = {
      current: 'Current step',
      done: 'Completed step',
      next: 'Next step',
      locked: 'Locked step',
    };
    const icons = {
      current: `
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
          <circle cx="12" cy="12" r="4" fill="currentColor"></circle>
          <circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="2"></circle>
        </svg>
      `,
      done: `
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
          <path d="M7.5 12.5 10.5 15.5 16.5 9.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
      `,
      next: `
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
          <path d="M8 12h8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
          <path d="m12 8 4 4-4 4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
      `,
      locked: `
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
          <rect x="6" y="11" width="12" height="9" rx="2" fill="none" stroke="currentColor" stroke-width="2"></rect>
          <path d="M9 11V8a3 3 0 0 1 6 0v3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
        </svg>
      `,
    };
    const safeState = icons[normalized] ? normalized : 'locked';
    return `
      <span class="admin-onboarding-step-chip-state-icon" aria-hidden="true">
        ${icons[safeState]}
      </span>
      <span class="admin-onboarding-step-chip-state-label">${labels[safeState]}</span>
    `;
  };
  const persistedDraft = parseJson(readSessionValue(storageKey) || '{}');
  const initialDraft = parseJson(shell.dataset.initialState || '{}');
  const sanitizeState = (raw = {}) => {
    const basePageName = text(raw.pageName || initialDraft.pageName || 'A Metafy');
    const fallbackSubdomain = slugify(raw.subdomain || initialDraft.subdomain || basePageName) || 'my-store';

    return {
      productType: normalizeProductType(raw.productType || initialDraft.productType || 'physical'),
      subdomain: slugify(raw.subdomain || initialDraft.subdomain || fallbackSubdomain) || 'my-store',
      pageName: basePageName || 'A Metafy',
      primaryLanguage: normalizeSelectValue(
        text(raw.primaryLanguage || initialDraft.primaryLanguage || 'english').toLowerCase(),
        primaryLanguageOptions.map((option) => option.value),
        primaryLanguageOptions[0]?.value || 'english'
      ),
      isCalling: normalizeCallingEnabled(
        hasOwn(raw, 'isCalling') ? raw.isCalling : initialDraft.isCalling
      ),
      callScope: normalizeCallScope(raw.callScope || initialDraft.callScope || 'cod'),
      timezone: normalizeSelectValue(raw.timezone || initialDraft.timezone, timezoneOptions, timezoneOptions[0] || 'Asia/Dhaka'),
      adminLanguage: normalizeSelectValue(raw.adminLanguage || initialDraft.adminLanguage, adminLanguageOptions, adminLanguageOptions[0] || 'English'),
      websiteLanguage: normalizeSelectValue(raw.websiteLanguage || initialDraft.websiteLanguage, websiteLanguageOptions, websiteLanguageOptions[0] || 'English'),
    };
  };

  let state = sanitizeState({ ...initialDraft, ...persistedDraft });
  let currentStep = Number.isFinite(initialCurrentStepValue) ? initialCurrentStepValue : 0;
  let highestCompletedStep = Number.isFinite(initialHighestCompletedStepValue) ? initialHighestCompletedStepValue : -1;
  let submitting = false;

  const persistState = () => {
    writeSessionValue(storageKey, JSON.stringify(state));
  };
  const setSubmittingState = (value) => {
    submitting = value;
    if (backButton instanceof HTMLButtonElement) {
      backButton.disabled = value || currentStep === 0;
    }
    nextButton.disabled = value;
    finishButton.disabled = value;
  };
  const buildOnboardingPayload = (stepIndex) => {
    if (stepIndex === 0) {
      return {
        type: 'product',
        data: {
          product_type: normalizeProductType(state.productType),
        },
      };
    }

    if (stepIndex === 1) {
      return {
        type: 'sub_domain',
        data: {
          sub_domain: slugify(state.subdomain),
        },
      };
    }

    if (stepIndex === 2) {
      return {
        type: 'call_order',
        data: {
          is_calling: Boolean(state.isCalling),
          recording_page_name: text(state.pageName),
          recording_language: text(state.primaryLanguage).toLowerCase(),
          calling_scope: normalizeCallScope(state.callScope),
        },
      };
    }

    if (stepIndex === 3) {
      return {
        type: 'locale',
        data: {
          timezone: text(state.timezone),
          admin_language: text(state.adminLanguage).toLowerCase(),
          website_language: text(state.websiteLanguage).toLowerCase(),
        },
      };
    }

    return null;
  };
  const applyServerStepData = (typeName, serverData = {}, fallbackData = {}) => {
    const nextData = {
      ...(fallbackData && typeof fallbackData === 'object' ? fallbackData : {}),
      ...(serverData && typeof serverData === 'object' ? serverData : {}),
    };

    if (typeName === 'product' && text(nextData.product_type)) {
      state.productType = normalizeProductType(nextData.product_type);
    }

    if (typeName === 'sub_domain' && text(nextData.sub_domain)) {
      state.subdomain = slugify(nextData.sub_domain);
    }

    if (typeName === 'call_order') {
      if (hasOwn(nextData, 'is_calling')) {
        state.isCalling = normalizeCallingEnabled(nextData.is_calling);
      }
      if (text(nextData.recording_page_name)) {
        state.pageName = text(nextData.recording_page_name);
      }
      if (text(nextData.recording_language)) {
        state.primaryLanguage = normalizeSelectValue(
          text(nextData.recording_language).toLowerCase(),
          primaryLanguageOptions.map((option) => option.value),
          primaryLanguageOptions[0]?.value || 'english'
        );
      }
      if (text(nextData.calling_scope)) {
        state.callScope = normalizeCallScope(nextData.calling_scope);
      }
    }

    if (typeName === 'locale') {
      if (text(nextData.timezone)) {
        state.timezone = normalizeSelectValue(nextData.timezone, timezoneOptions, timezoneOptions[0] || 'Asia/Dhaka');
      }
      if (text(nextData.admin_language)) {
        state.adminLanguage = normalizeSelectValue(
          nextData.admin_language,
          adminLanguageOptions,
          adminLanguageOptions[0] || 'English'
        );
      }
      if (text(nextData.website_language)) {
        state.websiteLanguage = normalizeSelectValue(
          nextData.website_language,
          websiteLanguageOptions,
          websiteLanguageOptions[0] || 'English'
        );
      }
    }
  };
  const submitStep = async (stepIndex) => {
    const payload = buildOnboardingPayload(stepIndex);
    if (!payload) {
      return null;
    }

    if (!continueUrl) {
      throw new Error('Onboarding endpoint is not configured.');
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
    const result = await response.json().catch(() => ({}));

    if (!response.ok) {
      throw new Error(text(result?.error || result?.message) || `Request failed (${response.status}).`);
    }

    applyServerStepData(payload.type, result?.data, payload.data);
    return result;
  };
  const syncInputsFromState = () => {
    productInputs.forEach((input) => {
      if (input instanceof HTMLInputElement) {
        input.checked = input.value === state.productType;
      }
    });

    callScopeInputs.forEach((input) => {
      if (input instanceof HTMLInputElement) {
        input.checked = input.value === state.callScope;
      }
    });

    callingToggleInput.checked = Boolean(state.isCalling);
    subdomainInput.value = state.subdomain;
    pageNameInput.value = state.pageName;
    primaryLanguageSelect.value = state.primaryLanguage;
    timezoneSelect.value = state.timezone;
    adminLanguageSelect.value = state.adminLanguage;
    websiteLanguageSelect.value = state.websiteLanguage;
  };
  const renderCallingToggle = () => {
    callingToggleInput.checked = Boolean(state.isCalling);
    if (callingToggleStateNode instanceof HTMLElement) {
      callingToggleStateNode.textContent = state.isCalling ? 'On' : 'Off';
    }
  };
  const renderChoiceStates = () => {
    productCards.forEach((card) => {
      if (!(card instanceof HTMLElement)) {
        return;
      }

      const input = card.querySelector('[data-admin-onboarding-product-type]');
      const active = input instanceof HTMLInputElement && input.checked;
      card.classList.toggle('is-active', active);
    });

    scopeCards.forEach((card) => {
      if (!(card instanceof HTMLElement)) {
        return;
      }

      const input = card.querySelector('[data-admin-onboarding-call-scope]');
      const active = input instanceof HTMLInputElement && input.checked;
      card.classList.toggle('is-active', active);
    });
  };
  const renderSummary = () => {
    const nextDomain = domainPreview(state.subdomain);

    if (summaryNodes.domain instanceof HTMLElement) {
      summaryNodes.domain.textContent = nextDomain;
    }

    if (summaryNodes.productType instanceof HTMLElement) {
      summaryNodes.productType.textContent = productTypeLabel(state.productType);
    }

    if (summaryNodes.orderCall instanceof HTMLElement) {
      summaryNodes.orderCall.textContent = `${state.isCalling ? 'On' : 'Off'} / ${languageLabel(state.primaryLanguage)} / ${callScopeLabel(state.callScope)}`;
    }

    if (summaryNodes.locale instanceof HTMLElement) {
      summaryNodes.locale.textContent = `${timezoneLabel(state.timezone)} / ${adminLanguageLabel(state.adminLanguage)} admin / ${websiteLanguageLabel(state.websiteLanguage)} site`;
    }

    if (domainPreviewNode instanceof HTMLElement) {
      domainPreviewNode.textContent = nextDomain;
    }
  };
  const focusCurrentStep = () => {
    const panel = stepPanels[currentStep];
    if (!(panel instanceof HTMLElement)) {
      return;
    }

    const target = panel.querySelector('input:not([type="hidden"]), select, textarea, button');
    if (target instanceof HTMLElement) {
      window.requestAnimationFrame(() => target.focus({ preventScroll: true }));
    }
  };
  const resetStepScroll = () => {
    if (mainNode instanceof HTMLElement) {
      mainNode.scrollTop = 0;
    }

    shell.scrollTop = 0;
  };
  const validateStep = (index, showFeedback = false) => {
    if (index === 0) {
      if (!text(state.productType)) {
        if (showFeedback && typeof window.showError === 'function') {
          window.showError('Select a product type to continue.');
        }
        return false;
      }

      return true;
    }

    if (index === 1) {
      const nextSubdomain = slugify(state.subdomain);
      if (nextSubdomain.length < 3) {
        if (showFeedback && typeof window.showError === 'function') {
          window.showError('Subdomain username must be at least 3 characters.');
        }
        if (showFeedback) {
          subdomainInput.focus();
        }
        return false;
      }

      state.subdomain = nextSubdomain;
      subdomainInput.value = nextSubdomain;
      return true;
    }

    if (index === 2) {
      if (!text(state.pageName)) {
        if (showFeedback && typeof window.showError === 'function') {
          window.showError('Page name is required for order call confirmation.');
        }
        if (showFeedback) {
          pageNameInput.focus();
        }
        return false;
      }

      if (!text(state.primaryLanguage)) {
        if (showFeedback && typeof window.showError === 'function') {
          window.showError('Select a primary language.');
        }
        if (showFeedback) {
          primaryLanguageSelect.focus();
        }
        return false;
      }

      return true;
    }

    if (index === 3) {
      if (!text(state.timezone) || !text(state.adminLanguage) || !text(state.websiteLanguage)) {
        if (showFeedback && typeof window.showError === 'function') {
          window.showError('Complete timezone and language preferences to finish setup.');
        }
        return false;
      }

      return true;
    }

    return true;
  };
  const firstInvalidStepBefore = (targetIndex) => {
    for (let index = 0; index < targetIndex; index += 1) {
      if (!validateStep(index, false)) {
        return index;
      }
    }

    return -1;
  };
  const render = () => {
    const totalSteps = stepPanels.length || 1;
    const nextStepIndex = Math.min(Math.max(currentStep + 1, highestCompletedStep + 1), totalSteps - 1);

    stepPanels.forEach((panel, index) => {
      if (!(panel instanceof HTMLElement)) {
        return;
      }

      const active = index === currentStep;
      panel.classList.toggle('is-active', active);
      panel.setAttribute('aria-hidden', active ? 'false' : 'true');
    });

    stepChips.forEach((chip, index) => {
      if (!(chip instanceof HTMLElement)) {
        return;
      }

      const isActive = index === currentStep;
      const isComplete = index <= highestCompletedStep && !isActive;
      const isNext = !isActive && !isComplete && index === nextStepIndex;
      const isLocked = !isActive && !isComplete && !isNext;
      chip.classList.toggle('is-active', isActive);
      chip.classList.toggle('is-complete', isComplete);
      chip.classList.toggle('is-next', isNext);
      chip.classList.toggle('is-locked', isLocked);
      if (isActive) {
        chip.setAttribute('aria-current', 'step');
      } else {
        chip.removeAttribute('aria-current');
      }

      const stateNode = chip.querySelector('.admin-onboarding-step-chip-state');
      if (stateNode instanceof HTMLElement) {
        let stateName = 'locked';
        if (isActive) {
          stateName = 'current';
        } else if (isComplete) {
          stateName = 'done';
        } else if (isNext) {
          stateName = 'next';
        }
        stateNode.dataset.state = stateName;
        stateNode.title = titleCase(stateName) + ' step';
        stateNode.innerHTML = stepChipStateMarkup(stateName);
      }
    });

    if (progressLabelNode instanceof HTMLElement) {
      progressLabelNode.textContent = `Step ${currentStep + 1} of ${totalSteps}`;
    }

    if (progressFillNode instanceof HTMLElement) {
      progressFillNode.style.width = `${((currentStep + 1) / totalSteps) * 100}%`;
    }

    if (backButton instanceof HTMLButtonElement) {
      backButton.disabled = currentStep === 0;
    }
    nextButton.classList.toggle('hidden', currentStep === totalSteps - 1);
    finishButton.classList.toggle('hidden', currentStep !== totalSteps - 1);
    nextButton.textContent = nextButtonLabels[currentStep] || 'Continue';

    renderCallingToggle();
    renderChoiceStates();
    renderSummary();
    persistState();
  };
  const goToStep = (targetIndex) => {
    if (submitting) {
      return;
    }

    const boundedIndex = Math.max(0, Math.min(targetIndex, stepPanels.length - 1));

    if (boundedIndex > currentStep) {
      const blockingStep = firstInvalidStepBefore(boundedIndex);
      if (blockingStep !== -1) {
        currentStep = blockingStep;
        render();
        resetStepScroll();
        validateStep(blockingStep, true);
        focusCurrentStep();
        return;
      }
    }

    if (boundedIndex > currentStep) {
      highestCompletedStep = Math.max(highestCompletedStep, boundedIndex - 1);
    }

    currentStep = boundedIndex;
    render();
    resetStepScroll();
    focusCurrentStep();
  };
  const hideWizard = (mode, successMessage = '') => {
    if (persistHiddenState) {
      writeSessionValue(hiddenKey, '1');
    }
    persistState();

    if (mode === 'finish') {
      document.body.classList.remove('admin-onboarding-open');
      document.body.style.overflow = '';

      if (dashboardUrl) {
        window.location.assign(dashboardUrl);
        return;
      }

      shell.remove();
      if (typeof window.showSuccess === 'function') {
        window.showSuccess(text(successMessage) || 'Setup flow completed.');
      }
      return;
    }

    shell.classList.remove('is-visible');
    document.body.classList.remove('admin-onboarding-open');
    document.body.style.overflow = '';

    window.setTimeout(() => {
      shell.remove();
    }, 220);

    if (typeof window.showInfo === 'function') {
      window.showInfo('Setup wizard hidden for now.');
    }
  };

  syncInputsFromState();

  productInputs.forEach((input) => {
    if (!(input instanceof HTMLInputElement)) {
      return;
    }

    input.addEventListener('change', () => {
      state.productType = normalizeProductType(input.value);
      render();
    });
  });

  subdomainInput.addEventListener('input', () => {
    state.subdomain = slugify(subdomainInput.value);
    subdomainInput.value = state.subdomain;
    renderSummary();
    persistState();
  });

  pageNameInput.addEventListener('input', () => {
    state.pageName = text(pageNameInput.value);
    renderSummary();
    persistState();
  });

  primaryLanguageSelect.addEventListener('change', () => {
    state.primaryLanguage = normalizeSelectValue(
      primaryLanguageSelect.value,
      primaryLanguageOptions.map((option) => option.value),
      primaryLanguageOptions[0]?.value || 'english'
    );
    renderSummary();
    persistState();
  });

  callingToggleInput.addEventListener('change', () => {
    state.isCalling = Boolean(callingToggleInput.checked);
    render();
  });

  callScopeInputs.forEach((input) => {
    if (!(input instanceof HTMLInputElement)) {
      return;
    }

    input.addEventListener('change', () => {
      state.callScope = normalizeCallScope(input.value);
      render();
    });
  });

  timezoneSelect.addEventListener('change', () => {
    state.timezone = normalizeSelectValue(timezoneSelect.value, timezoneOptions, timezoneOptions[0] || 'Asia/Dhaka');
    renderSummary();
    persistState();
  });

  adminLanguageSelect.addEventListener('change', () => {
    state.adminLanguage = normalizeSelectValue(adminLanguageSelect.value, adminLanguageOptions, adminLanguageOptions[0] || 'English');
    renderSummary();
    persistState();
  });

  websiteLanguageSelect.addEventListener('change', () => {
    state.websiteLanguage = normalizeSelectValue(websiteLanguageSelect.value, websiteLanguageOptions, websiteLanguageOptions[0] || 'English');
    renderSummary();
    persistState();
  });

  if (backButton instanceof HTMLButtonElement) {
    backButton.addEventListener('click', () => {
      goToStep(currentStep - 1);
    });
  }

  nextButton.addEventListener('click', async () => {
    if (submitting) {
      return;
    }

    if (!validateStep(currentStep, true)) {
      renderSummary();
      persistState();
      return;
    }

    setSubmittingState(true);

    try {
      const response = await submitStep(currentStep);
      highestCompletedStep = Math.max(highestCompletedStep, currentStep);
      currentStep = Math.min(currentStep + 1, stepPanels.length - 1);
      render();
      resetStepScroll();
      focusCurrentStep();

      if (text(response?.message) && typeof window.showSuccess === 'function') {
        window.showSuccess(response.message);
      }
    } catch (error) {
      if (typeof window.showError === 'function') {
        window.showError(error?.message || 'Failed to save onboarding step.');
      }
    } finally {
      setSubmittingState(false);
      render();
    }
  });

  finishButton.addEventListener('click', async () => {
    if (submitting) {
      return;
    }

    if (!validateStep(currentStep, true)) {
      renderSummary();
      persistState();
      return;
    }

    setSubmittingState(true);

    try {
      const response = await submitStep(currentStep);
      highestCompletedStep = Math.max(highestCompletedStep, currentStep);
      render();
      hideWizard('finish', text(response?.message));
    } catch (error) {
      if (typeof window.showError === 'function') {
        window.showError(error?.message || 'Failed to complete onboarding.');
      }
    } finally {
      setSubmittingState(false);
      render();
    }
  });

  if (dismissButton instanceof HTMLButtonElement) {
    dismissButton.addEventListener('click', () => {
      hideWizard('dismiss');
    });
  }

  document.body.classList.add('admin-onboarding-open');
  document.body.style.overflow = 'hidden';
  render();

  window.requestAnimationFrame(() => {
    shell.classList.add('is-visible');
  });
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
      setStatus('Instant mode enabled. Campaign will launch active.', '');
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

function initOrdersCatalogPage() {
  const section = document.getElementById('ordersCatalogSection');
  if (!section) return;

  const searchInput = section.querySelector('[data-orders-search]');
  const statusSelect = section.querySelector('[data-orders-status]');
  const paymentTypeSelect = section.querySelector('[data-orders-payment-type]');
  const channelSelect = section.querySelector('[data-orders-channel]');
  const sortBySelect = section.querySelector('[data-orders-sort-by]');
  const applyBtn = section.querySelector('[data-orders-apply]');
  const resetBtn = section.querySelector('[data-orders-reset]');
  const countNode = section.querySelector('[data-orders-count]');
  const filterNode = section.querySelector('[data-orders-filter-result]');
  const tableBody = section.querySelector('[data-orders-table-body]');
  const pageWrap = section.querySelector('[data-orders-pagination-wrap]');
  const pageSummary = section.querySelector('[data-orders-pagination-summary]');
  const pageControls = section.querySelector('[data-orders-pagination-controls]');

  const kpiOrdersTodayValue = document.querySelector('[data-orders-kpi-orders-today-value]');
  const kpiOrdersTodayMeta = document.querySelector('[data-orders-kpi-orders-today-meta]');
  const kpiGrossRevenueValue = document.querySelector('[data-orders-kpi-gross-revenue-value]');
  const kpiGrossRevenueMeta = document.querySelector('[data-orders-kpi-gross-revenue-meta]');
  const kpiPendingDispatchValue = document.querySelector('[data-orders-kpi-pending-dispatch-value]');
  const kpiPendingDispatchMeta = document.querySelector('[data-orders-kpi-pending-dispatch-meta]');

  const heroProcessingTime = document.querySelector('[data-orders-hero-processing-time]');
  const heroDispatchSla = document.querySelector('[data-orders-hero-dispatch-sla]');
  const heroReturnRisk = document.querySelector('[data-orders-hero-return-risk]');

  const pipelineTotal = document.querySelector('[data-orders-pipeline-total]');
  const pipelineRejected = document.querySelector('[data-orders-pipeline-rejected]');
  const pipelinePending = document.querySelector('[data-orders-pipeline-pending]');
  const pipelineCompleted = document.querySelector('[data-orders-pipeline-completed]');
  const actionModal = document.getElementById('ordersActionModal');
  const actionModalTitle = actionModal?.querySelector('[data-orders-action-title]');
  const actionModalMessage = actionModal?.querySelector('[data-orders-action-message]');
  const actionModalOrderId = actionModal?.querySelector('[data-orders-action-order-id]');
  const actionModalStatus = actionModal?.querySelector('[data-orders-action-status]');
  const actionModalConfirmBtn = actionModal?.querySelector('[data-orders-action-confirm]');

  if (
    !searchInput || !statusSelect || !paymentTypeSelect || !channelSelect || !sortBySelect ||
    !applyBtn || !resetBtn || !countNode || !filterNode || !tableBody ||
    !pageWrap || !pageSummary || !pageControls
  ) {
    return;
  }

  if (!window.API?.Admin?.OrderHistory || typeof window.API.Admin.OrderHistory.list !== 'function') {
    return;
  }

  const apiBase = String(section.dataset.apiBaseUrl || '').replace(/\/+$/, '');
  const perPage = Math.max(1, Number.parseInt(section.dataset.perPage || '10', 10) || 10);
  const sessionToken = String(section.dataset.refreshToken || '').trim();
  const orderViewUrlTemplate = String(section.dataset.orderViewUrlTemplate || '/admin/orders/__ORDER_ID__').trim();
  const orderInvoiceUrlTemplate = String(section.dataset.orderInvoiceUrlTemplate || '/admin/orders/__ORDER_ID__/invoice').trim();
  const pageProductType = String(section.dataset.productType || '').trim().toLowerCase();
  let storageToken = '';
  try {
    storageToken = String(window.localStorage.getItem('refresh_token') || '').trim();
  } catch {
    storageToken = '';
  }
  const token = sessionToken || storageToken;

  const toInt = (value, fallback = 0) => {
    const parsed = Number.parseInt(String(value ?? ''), 10);
    return Number.isFinite(parsed) ? parsed : fallback;
  };
  const toNumber = (value, fallback = NaN) => {
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : fallback;
  };
  const text = (value) => String(value ?? '').trim();
  const titleCase = (value) => text(value)
    .replace(/[_-]+/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
    .replace(/\b\w/g, (char) => char.toUpperCase());
  const escapeHtml = (value) => text(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
  const formatCount = (value) => toInt(value, 0).toLocaleString('en-US');
  const formatBdt = (value) => {
    const amount = toNumber(value, NaN);
    if (!Number.isFinite(amount)) return '-';
    return `BDT ${amount.toLocaleString('en-US', {
      minimumFractionDigits: Number.isInteger(amount) ? 0 : 2,
      maximumFractionDigits: 2,
    })}`;
  };
  const slugify = (value) => text(value)
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '') || 'default';
  const formatOrderDate = (value) => {
    const raw = text(value);
    if (!raw) return '';

    const parsed = new Date(raw);
    if (Number.isNaN(parsed.getTime())) return raw;

    return parsed.toLocaleString('en-US', {
      year: 'numeric',
      month: 'short',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
    });
  };
  const progressOf = (status) => {
    const normalized = text(status).toLowerCase().replace(/[\s-]+/g, '_');
    if (normalized === 'waiting_for_payment' || normalized === 'payment_review') {
      return pageProductType === 'physical' ? 50 : 16;
    }
    if (normalized === 'waiting_for_call') return 18;
    if (normalized === 'waiting_for_confirmation') return 34;
    if (normalized === 'ready_to_dispatch') return 62;
    if (normalized === 'in_transit') return 84;
    if (normalized === 'success') return 100;
    if (normalized.startsWith('cancel_')) return 100;
    return 24;
  };

  const state = {
    page: (() => {
      const params = new URLSearchParams(window.location.search);
      const parsed = toInt(params.get('page'), 1);
      return parsed > 0 ? parsed : 1;
    })(),
    perPage,
    total: 0,
    from: 0,
    to: 0,
    lastPage: 1,
    orders: [],
    loading: false,
    requestId: 0,
  };

  const setSelectValue = (select, value, fallback = 'all') => {
    const target = text(value);
    if (!target) {
      select.value = fallback;
      return;
    }

    const options = Array.from(select.options);
    const match = options.find((option) => text(option.value).toLowerCase() === target.toLowerCase());
    if (match) {
      select.value = match.value;
      return;
    }

    select.value = fallback;
  };

  const urlParams = new URLSearchParams(window.location.search);
  const initialSearch = text(urlParams.get('search') || urlParams.get('q'));
  if (initialSearch) {
    searchInput.value = initialSearch;
  }
  setSelectValue(statusSelect, urlParams.get('status'), 'all');
  setSelectValue(paymentTypeSelect, urlParams.get('payment_type'), 'all');
  setSelectValue(channelSelect, urlParams.get('channel'), 'all');
  setSelectValue(sortBySelect, urlParams.get('sort_by'), 'newest_first');

  const normalizeFilter = (value) => {
    const normalized = text(value);
    return (!normalized || normalized.toLowerCase() === 'all') ? '' : normalized;
  };
  const readFilters = () => ({
    search: text(searchInput.value),
    status: normalizeFilter(statusSelect.value),
    paymentType: normalizeFilter(paymentTypeSelect.value),
    channel: normalizeFilter(channelSelect.value),
    sortBy: normalizeFilter(sortBySelect.value) || 'newest_first',
  });
  const buildOrderRouteUrl = (template, orderId) => {
    const cleanedOrderId = text(orderId);
    if (!cleanedOrderId) return '';

    const encodedOrderId = encodeURIComponent(cleanedOrderId);
    const sanitizedTemplate = text(template).split('?')[0];
    if (!sanitizedTemplate) return '';

    const templateUrl = sanitizedTemplate.includes('__ORDER_ID__')
      ? sanitizedTemplate.replace('__ORDER_ID__', encodedOrderId)
      : sanitizedTemplate;

    if (sanitizedTemplate.includes('__ORDER_ID__')) {
      return templateUrl;
    }

    return `${templateUrl.replace(/\/+$/, '')}/${encodedOrderId}`;
  };
  const buildOrderDetailsUrl = (orderId) => buildOrderRouteUrl(orderViewUrlTemplate, orderId);
  const buildOrderInvoiceUrl = (orderId) => buildOrderRouteUrl(orderInvoiceUrlTemplate, orderId);
  const actionIcon = (name) => {

    if (name === 'details') {
      return `
        <svg class="orders-action-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
          <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z" fill="none" stroke="currentColor" stroke-width="2"></path>
          <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="2"></circle>
        </svg>
      `;
    }

    if (name === 'invoice') {
      return `
        <svg class="orders-action-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
          <path d="M7 3h8l5 5v13H7z" fill="none" stroke="currentColor" stroke-width="2"></path>
          <path d="M15 3v5h5" fill="none" stroke="currentColor" stroke-width="2"></path>
          <path d="M10 13h7M10 17h7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
        </svg>
      `;
    }

    if (name === 'cancelld') {
      return `
        <svg class="orders-action-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
          <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"></circle>
          <path d="M9 9l6 6M15 9l-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
        </svg>
      `;
    }

    return `
      <svg class="orders-action-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"></circle>
        <path d="M8 12.5l2.5 2.5 5.5-5.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
      </svg>
    `;
  };
  const actionMenuItem = (orderId, action, label, tone = 'default') => `
    <button
      type="button"
      class="orders-actions-menu-item is-${tone}"
      data-orders-row-action="${action}"
      data-order-id="${escapeHtml(orderId)}"
      role="menuitem"
    >
      ${actionIcon(action)}
      <span class="orders-action-label">${label}</span>
    </button>
  `;

  const statusMetaOf = (status) => {
    const normalized = text(status).toLowerCase().replace(/[\s-]+/g, '_');
    if (normalized === 'success') return {label: 'Success', css: 'badge-success'};
    if (normalized === 'in_transit') return {label: 'In Transit', css: 'badge-primary'};
    if (normalized === 'ready_to_dispatch') return {label: 'Ready to Dispatch', css: 'badge-info'};
    if (normalized === 'waiting_for_payment' || normalized === 'payment_review') {
      return {label: 'Waiting For Payment', css: 'badge-warning'};
    }
    if (normalized === 'waiting_for_call' || normalized === 'waiting_for_confirmation') {
      return {label: titleCase(normalized), css: 'badge-warning'};
    }
    if (normalized.startsWith('cancel_')) return {label: titleCase(normalized), css: 'badge-danger'};
    return {label: titleCase(normalized || 'unknown'), css: 'badge-info'};
  };

  const paymentMetaOf = (method) => {
    const normalized = text(method).toLowerCase();
    if (normalized === 'cod') {
      return {label: 'COD', css: 'badge-warning'};
    }
    return {label: titleCase(normalized || 'unknown'), css: 'badge-success'};
  };

  const setMessage = (message) => {
    tableBody.innerHTML = `
      <tr>
        <td colspan="8" class="users-empty">${escapeHtml(message)}</td>
      </tr>
    `;
  };

  const rowNode = (order) => {
    const orderId = text(order?.order_id || order?.id || 'N/A');
    const fullName = text(order?.full_name || order?.customer_name || 'Unknown');
    const address = text(order?.address || order?.location || 'N/A');
    const qty = Math.max(0, toInt(order?.qty, 0));
    const rawAmount = order?.amount ?? order?.total_amount ?? order?.grand_total ?? null;
    const amountText = Number.isFinite(toNumber(rawAmount, NaN))
      ? formatBdt(rawAmount)
      : (text(rawAmount) || '-');
    const method = paymentMetaOf(order?.method || order?.payment_type);
    const channelLabel = titleCase(order?.channel || 'N/A');
    const status = statusMetaOf(order?.status);
    const normalizedStatus = normalizeStatus(order?.status);
    const progress = progressOf(order?.status);
    const placedAt = formatOrderDate(order?.order_date || order?.created_at || order?.placed_at || '');
    const image = text(order?.image || order?.profile || '');

    const avatarHtml = image
      ? `<img src="${escapeHtml(image)}" class="users-avatar" alt="${escapeHtml(fullName)}" loading="lazy">`
      : `<span class="orders-customer-avatar">${escapeHtml(fullName.charAt(0).toUpperCase() || 'U')}</span>`;

    const isWaitingForPayment = normalizedStatus === 'waiting_for_payment' || normalizedStatus === 'payment_review';
    const isWaitingForCall = normalizedStatus === 'waiting_for_call';
    const isWaitingForConfirmation = normalizedStatus === 'waiting_for_confirmation';
    const isReadyToDispatch = normalizedStatus === 'ready_to_dispatch';
    const isCancelOnCalled = normalizedStatus === 'cancel_on_called';
    const isCancelOnConfirmation = normalizedStatus === 'cancel_on_confirmation';
    const shouldShowInvoice = !isWaitingForPayment && !isWaitingForCall && !isWaitingForConfirmation && !isCancelOnCalled && !isCancelOnConfirmation;
    const shouldShowCancelControl = isWaitingForCall || isWaitingForConfirmation || isReadyToDispatch;
    const shouldShowConfirmControl = isWaitingForPayment || isWaitingForCall || isWaitingForConfirmation;

    const actions = [
      actionMenuItem(orderId, 'details', 'Details', 'info'),
    ];
    if (shouldShowInvoice) {
      actions.push(actionMenuItem(orderId, 'invoice', 'Invoice'));
    }
    if (shouldShowCancelControl) {
      actions.push(actionMenuItem(orderId, 'cancelld', 'Cancelled', 'danger'));
    }
    if (shouldShowConfirmControl) {
      actions.push(actionMenuItem(orderId, 'confirm', 'Confirm', 'success'));
    }

    const row = document.createElement('tr');
    row.innerHTML = `
      <td>
        <div class="orders-order-cell">
          <strong>${escapeHtml(orderId)}</strong>
          <small>${escapeHtml(placedAt || 'Latest')}</small>
        </div>
      </td>
      <td>
        <div class="orders-customer-cell">
          ${avatarHtml}
          <div>
            <strong>${escapeHtml(fullName)}</strong>
            <small>${escapeHtml(address)}</small>
          </div>
        </div>
      </td>
      <td>${formatCount(qty)}</td>
      <td class="orders-cell-strong">${escapeHtml(amountText)}</td>
      <td><span class="badge ${method.css}">${escapeHtml(method.label)}</span></td>
      <td>
        <span class="badge users-channel-badge users-channel-${escapeHtml(slugify(channelLabel))}">
          ${escapeHtml(channelLabel)}
        </span>
      </td>
      <td>
        <div class="orders-status-wrap">
          <span class="badge ${status.css}">${escapeHtml(status.label)}</span>
          <div class="orders-progress-track">
            <span style="width: ${progress}%"></span>
          </div>
        </div>
      </td>
      <td>
        <div class="orders-table-actions">
          <div class="orders-actions-wrap" data-orders-actions-wrap>
            <button
              type="button"
              class="orders-actions-trigger"
              data-orders-actions-trigger
              aria-haspopup="menu"
              aria-expanded="false"
              aria-label="Open actions for ${escapeHtml(orderId)}"
            >
              <span class="orders-actions-trigger-dots" aria-hidden="true">⋯</span>
            </button>
            <div class="orders-actions-menu" data-orders-actions-menu role="menu" hidden>
              ${actions.join('')}
            </div>
          </div>
        </div>
      </td>
    `;

    return row;
  };
  const closeAllActionMenus = (exceptWrap = null) => {
    const wraps = tableBody.querySelectorAll('[data-orders-actions-wrap]');
    wraps.forEach((wrap) => {
      if (exceptWrap !== null && wrap === exceptWrap) return;

      const menu = wrap.querySelector('[data-orders-actions-menu]');
      const trigger = wrap.querySelector('[data-orders-actions-trigger]');
      if (menu instanceof HTMLElement) {
        menu.setAttribute('hidden', '');
      }
      if (trigger instanceof HTMLButtonElement) {
        trigger.setAttribute('aria-expanded', 'false');
      }
    });
  };
  const toggleActionMenu = (trigger) => {
    const wrap = trigger.closest('[data-orders-actions-wrap]');
    if (!(wrap instanceof HTMLElement)) return;

    const menu = wrap.querySelector('[data-orders-actions-menu]');
    if (!(menu instanceof HTMLElement)) return;

    const isOpen = !menu.hasAttribute('hidden');
    if (isOpen) {
      closeAllActionMenus();
      return;
    }

    closeAllActionMenus(wrap);
    menu.removeAttribute('hidden');
    trigger.setAttribute('aria-expanded', 'true');
  };

  const renderTable = () => {
    const list = Array.isArray(state.orders) ? state.orders : [];
    tableBody.innerHTML = '';

    if (!list.length) {
      setMessage('No orders found for the selected filters.');
      filterNode.textContent = 'No orders found';
      return;
    }

    const fragment = document.createDocumentFragment();
    list.forEach((order) => fragment.appendChild(rowNode(order)));
    tableBody.appendChild(fragment);

    if (state.total > 0 && state.from > 0 && state.to > 0) {
      filterNode.textContent = `Showing ${state.from}-${state.to} of ${state.total} orders`;
      return;
    }

    filterNode.textContent = `Showing ${list.length} orders`;
  };

  const renderPagination = () => {
    const current = Math.max(1, toInt(state.page, 1));
    const last = Math.max(1, toInt(state.lastPage, 1));

    if (last <= 1) {
      pageSummary.textContent = 'Page 1 of 1';
      pageControls.innerHTML = '';
      pageWrap.hidden = true;
      return;
    }

    pageWrap.hidden = false;
    pageSummary.textContent = `Page ${current} of ${last}`;
    pageControls.innerHTML = '';

    const btn = (label, page, disabled = false, active = false) => {
      const node = document.createElement('button');
      node.type = 'button';
      node.className = `orders-page-btn${disabled ? ' is-disabled' : ''}${active ? ' is-active' : ''}`;
      node.textContent = label;

      if (disabled || active) {
        node.disabled = true;
        if (disabled) node.setAttribute('aria-disabled', 'true');
        if (active) node.setAttribute('aria-current', 'page');
      } else {
        node.addEventListener('click', () => {
          if (!state.loading) loadOrders(page);
        });
      }

      return node;
    };

    const pages = [];
    if (last <= 7) {
      for (let page = 1; page <= last; page += 1) pages.push(page);
    } else {
      pages.push(1);
      if (current > 3) pages.push('...');
      for (let page = Math.max(2, current - 1); page <= Math.min(last - 1, current + 1); page += 1) pages.push(page);
      if (current < last - 2) pages.push('...');
      pages.push(last);
    }

    pageControls.appendChild(btn('Prev', current - 1, current <= 1, false));
    pages.forEach((page) => pageControls.appendChild(page === '...' ? btn('...', 0, true, false) : btn(String(page), page, false, page === current)));
    pageControls.appendChild(btn('Next', current + 1, current >= last, false));
  };

  const updateOverview = (others = {}) => {
    const totalOrder = toInt(others?.total_order, state.total);
    const rejectedOrder = toInt(others?.rejected_order, 0);
    const pendingOrder = toInt(others?.pending_order, 0);
    const completedOrder = toInt(others?.completed_order, 0);
    const ordersToday = toInt(others?.orders_today, 0);
    const ordersTodayDelta = toInt(others?.orders_today_delta_vs_yesterday, 0);
    const grossRevenueValue = text(others?.gross_revenue_display) || formatBdt(others?.gross_revenue);
    const grossRevenueChange = toNumber(others?.gross_revenue_change_this_week_percent, 0);
    const pendingDispatch = toInt(others?.pending_dispatch, 0);
    const pendingDispatchNeedAction = toInt(others?.pending_dispatch_need_action_in_2h, 0);
    const avgProcessingTime = text(others?.avg_processing_time_display) || '--';
    const successRate = toNumber(others?.success_rate_percent, 0);
    const returnRisk = toNumber(others?.return_risk_percent, 0);

    countNode.textContent = `${formatCount(totalOrder)} monitored orders`;
    countNode.dataset.initialCount = String(totalOrder);
    filterNode.dataset.universeCount = String(totalOrder);

    if (kpiOrdersTodayValue) kpiOrdersTodayValue.textContent = formatCount(ordersToday);
    if (kpiOrdersTodayMeta) {
      const deltaPrefix = ordersTodayDelta > 0 ? '+' : '';
      kpiOrdersTodayMeta.textContent = `${deltaPrefix}${formatCount(ordersTodayDelta)} vs yesterday`;
    }
    if (kpiGrossRevenueValue) kpiGrossRevenueValue.textContent = grossRevenueValue || '--';
    if (kpiGrossRevenueMeta) kpiGrossRevenueMeta.textContent = `${grossRevenueChange.toFixed(1)}% this week`;
    if (kpiPendingDispatchValue) kpiPendingDispatchValue.textContent = formatCount(pendingDispatch);
    if (kpiPendingDispatchMeta) kpiPendingDispatchMeta.textContent = `${formatCount(pendingDispatchNeedAction)} need action in 2h`;

    if (heroProcessingTime) heroProcessingTime.textContent = avgProcessingTime;
    if (heroDispatchSla) heroDispatchSla.textContent = `${successRate.toFixed(1)}%`;
    if (heroReturnRisk) heroReturnRisk.textContent = `${returnRisk.toFixed(1)}%`;

    if (pipelineTotal) pipelineTotal.textContent = formatCount(totalOrder);
    if (pipelineRejected) pipelineRejected.textContent = formatCount(rejectedOrder);
    if (pipelinePending) pipelinePending.textContent = formatCount(pendingOrder);
    if (pipelineCompleted) pipelineCompleted.textContent = formatCount(completedOrder);
  };

  const setLoading = (loading) => {
    state.loading = loading;
    [searchInput, statusSelect, paymentTypeSelect, channelSelect, sortBySelect, applyBtn, resetBtn].forEach((node) => {
      if (node) node.disabled = loading;
    });
  };

  const updateUrlState = (page) => {
    const filters = readFilters();
    const url = new URL(window.location.href);

    if (filters.search) url.searchParams.set('search', filters.search);
    else {
      url.searchParams.delete('search');
      url.searchParams.delete('q');
    }

    if (filters.status) url.searchParams.set('status', filters.status);
    else url.searchParams.delete('status');

    if (filters.paymentType) url.searchParams.set('payment_type', filters.paymentType);
    else url.searchParams.delete('payment_type');

    if (filters.channel) url.searchParams.set('channel', filters.channel);
    else url.searchParams.delete('channel');

    if (filters.sortBy) url.searchParams.set('sort_by', filters.sortBy);
    else url.searchParams.delete('sort_by');

    if (page > 1) url.searchParams.set('page', String(page));
    else url.searchParams.delete('page');

    window.history.replaceState({}, '', url.toString());
  };

  const findOrderById = (orderId) => {
    const target = text(orderId);
    if (!target) return null;

    return state.orders.find((entry) => text(entry?.order_id || entry?.id) === target) || null;
  };

  const normalizeStatus = (status) => text(status).toLowerCase().replace(/[\s-]+/g, '_');
  const transitionStatusForAction = (action, currentStatus) => {
    const normalizedAction = text(action).toLowerCase();
    const normalizedStatus = normalizeStatus(currentStatus);

    if (normalizedAction === 'confirm') {
      if (normalizedStatus === 'waiting_for_payment' || normalizedStatus === 'payment_review') return 'success';
      if (normalizedStatus === 'waiting_for_call') return 'waiting_for_confirmation';
      if (normalizedStatus === 'waiting_for_confirmation') return 'ready_to_dispatch';
      if (normalizedStatus === 'ready_to_dispatch') return 'in_transit';
      if (normalizedStatus === 'in_transit') return 'success';
      return '';
    }

    if (normalizedAction === 'cancelld') {
      if (normalizedStatus === 'waiting_for_call') return 'cancel_on_called';
      if (normalizedStatus === 'waiting_for_confirmation') return 'cancel_on_confirmation';
      if (normalizedStatus === 'ready_to_dispatch') return 'cancel_on_dispatch';
      if (normalizedStatus === 'in_transit') return 'cancel_on_delivered';
      return '';
    }

    return '';
  };

  const hideActionConfirmButton = () => {
    if (!(actionModalConfirmBtn instanceof HTMLButtonElement)) return;
    actionModalConfirmBtn.hidden = true;
    actionModalConfirmBtn.disabled = false;
    actionModalConfirmBtn.dataset.action = '';
    actionModalConfirmBtn.dataset.orderId = '';
    actionModalConfirmBtn.dataset.currentStatus = '';
    actionModalConfirmBtn.dataset.targetStatus = '';
    actionModalConfirmBtn.classList.remove('btn-danger', 'btn-success');
    if (!actionModalConfirmBtn.classList.contains('btn-primary')) {
      actionModalConfirmBtn.classList.add('btn-primary');
    }
    actionModalConfirmBtn.textContent = 'Confirm';
  };

  const openOrderActionModal = (action, orderId) => {
    if (!actionModal || typeof window.openModal !== 'function') {
      if (typeof window.showInfo === 'function') window.showInfo(`${titleCase(action)} clicked for ${orderId}.`);
      return;
    }

    const normalizedAction = text(action).toLowerCase();
    const matchedOrder = findOrderById(orderId);
    const currentStatus = normalizeStatus(matchedOrder?.status || '');
    const statusMeta = statusMetaOf(currentStatus);
    const targetStatus = transitionStatusForAction(normalizedAction, currentStatus);

    if (actionModalTitle) actionModalTitle.textContent = 'Order Action';
    if (actionModalMessage) actionModalMessage.textContent = 'Review this order before taking action.';
    if (actionModalOrderId) actionModalOrderId.textContent = orderId || '--';
    if (actionModalStatus) actionModalStatus.textContent = statusMeta.label;
    hideActionConfirmButton();

    if (normalizedAction === 'details') {
      const destinationUrl = buildOrderDetailsUrl(orderId);
      if (destinationUrl) {
        window.location.assign(destinationUrl);
        return;
      }

      if (actionModalTitle) actionModalTitle.textContent = 'Order Details';
      if (actionModalMessage) actionModalMessage.textContent = 'Unable to open details page.';
      window.openModal('ordersActionModal');
      return;
    }

    if (normalizedAction === 'invoice') {
      const destinationUrl = buildOrderInvoiceUrl(orderId);
      if (destinationUrl) {
        window.location.assign(destinationUrl);
        return;
      }

      if (actionModalTitle) actionModalTitle.textContent = 'Order Invoice';
      if (actionModalMessage) actionModalMessage.textContent = 'Unable to open invoice page.';
      window.openModal('ordersActionModal');
      return;
    }

    if (!(actionModalConfirmBtn instanceof HTMLButtonElement)) {
      window.openModal('ordersActionModal');
      return;
    }

    actionModalConfirmBtn.hidden = false;
    actionModalConfirmBtn.dataset.action = normalizedAction;
    actionModalConfirmBtn.dataset.orderId = orderId;
    actionModalConfirmBtn.dataset.currentStatus = currentStatus;
    actionModalConfirmBtn.dataset.targetStatus = targetStatus;
    actionModalConfirmBtn.classList.remove('btn-primary', 'btn-danger', 'btn-success');

    if (normalizedAction === 'cancelld') {
      if (actionModalTitle) actionModalTitle.textContent = 'Cancel Order';
      if (actionModalMessage) {
        actionModalMessage.textContent = 'Do you want to cancel this order now?';
      }
      actionModalConfirmBtn.textContent = 'Cancelled';
      actionModalConfirmBtn.classList.add('btn-danger');
    } else if (normalizedAction === 'confirm') {
      if (currentStatus === 'waiting_for_payment' || currentStatus === 'payment_review') {
        if (actionModalTitle) actionModalTitle.textContent = 'Confirm Payment';
        if (actionModalMessage) {
          actionModalMessage.textContent = 'Do you want to confirm this payment now?';
        }
        actionModalConfirmBtn.textContent = 'Confirm Payment';
      } else {
        if (actionModalTitle) actionModalTitle.textContent = 'Confirm Order';
        if (actionModalMessage) {
          actionModalMessage.textContent = 'Do you want to confirm this order now?';
        }
        actionModalConfirmBtn.textContent = 'Confirm';
      }
      actionModalConfirmBtn.classList.add('btn-success');
    } else {
      actionModalConfirmBtn.hidden = true;
      actionModalConfirmBtn.classList.add('btn-primary');
    }

    if (!targetStatus) {
      actionModalConfirmBtn.hidden = true;
      if (actionModalMessage) {
        actionModalMessage.textContent = `Invalid transition from "${statusMeta.label}" for this action.`;
      }
    }

    window.openModal('ordersActionModal');
  };

  async function loadOrders(page) {
    if (!apiBase) {
      const message = 'Backend API URL is missing.';
      countNode.textContent = 'Unavailable';
      filterNode.textContent = message;
      setMessage(message);
      if (typeof window.showError === 'function') window.showError(message);
      return;
    }

    const requestId = ++state.requestId;
    const filters = readFilters();
    setLoading(true);
    pageWrap.hidden = true;
    pageControls.innerHTML = '';
    pageSummary.textContent = 'Page 1 of 1';
    filterNode.textContent = 'Loading orders...';
    setMessage('Loading orders...');

    try {
      const payload = await window.API.Admin.OrderHistory.list({
        apiBaseUrl: apiBase,
        refreshToken: token || undefined,
        page,
        perPage: state.perPage,
        search: filters.search,
        status: filters.status,
        paymentType: filters.paymentType,
        channel: filters.channel,
        sortBy: filters.sortBy,
        timeoutMs: 12000,
      });
      if (requestId !== state.requestId) return;

      const ordersPayload = payload?.orders && typeof payload.orders === 'object' ? payload.orders : {};
      const list = Array.isArray(ordersPayload?.data) ? ordersPayload.data : [];
      const pagination = ordersPayload?.pagination_info && typeof ordersPayload.pagination_info === 'object'
        ? ordersPayload.pagination_info
        : {};
      const others = payload?.others_data && typeof payload.others_data === 'object' ? payload.others_data : {};

      const total = Math.max(0, toInt(pagination.total, list.length));
      const perPageFromApi = Math.max(1, toInt(pagination.per_page, state.perPage));
      const derivedLastPage = Math.max(1, toInt(pagination.last_page, Math.ceil(total / perPageFromApi)));

      state.orders = list;
      state.total = total;
      state.perPage = perPageFromApi;
      state.lastPage = derivedLastPage;
      state.page = Math.min(Math.max(1, toInt(pagination.current_page, page)), state.lastPage);
      state.from = Math.max(0, toInt(pagination.from, list.length ? ((state.page - 1) * state.perPage) + 1 : 0));
      state.to = Math.max(0, toInt(pagination.to, state.from ? state.from + list.length - 1 : 0));

      updateOverview(others);
      renderTable();
      renderPagination();
      updateUrlState(state.page);
    } catch (error) {
      if (requestId !== state.requestId) return;

      const message = error?.isTimeout
        ? 'Request timed out. Please try again.'
        : (error?.message || 'Failed to load orders.');

      state.orders = [];
      state.total = 0;
      state.from = 0;
      state.to = 0;
      state.lastPage = 1;
      countNode.textContent = 'Unavailable';
      filterNode.textContent = message;
      setMessage(message);
      pageControls.innerHTML = '';
      pageSummary.textContent = 'Page 1 of 1';
      pageWrap.hidden = true;
      if (typeof window.showError === 'function') window.showError(message);
    } finally {
      if (requestId === state.requestId) {
        setLoading(false);
      }
    }
  }

  applyBtn.addEventListener('click', () => {
    if (state.loading) return;
    loadOrders(1);
  });

  resetBtn.addEventListener('click', () => {
    searchInput.value = '';
    statusSelect.value = 'all';
    paymentTypeSelect.value = 'all';
    channelSelect.value = 'all';
    sortBySelect.value = 'newest_first';
    if (state.loading) return;
    loadOrders(1);
  });

  searchInput.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter') return;
    event.preventDefault();
    if (state.loading) return;
    loadOrders(1);
  });

  tableBody.addEventListener('click', (event) => {
    const menuTrigger = event.target instanceof Element
      ? event.target.closest('[data-orders-actions-trigger]')
      : null;
    if (menuTrigger instanceof HTMLButtonElement) {
      event.preventDefault();
      event.stopPropagation();
      toggleActionMenu(menuTrigger);
      return;
    }

    const trigger = event.target instanceof Element
      ? event.target.closest('[data-orders-row-action]')
      : null;
    if (!(trigger instanceof HTMLButtonElement)) return;
    event.preventDefault();
    closeAllActionMenus();

    const action = text(trigger.dataset.ordersRowAction).toLowerCase();
    const orderId = text(trigger.dataset.orderId);
    if (!action || !orderId) return;
    if (action !== 'details' && action !== 'invoice' && action !== 'cancelld' && action !== 'confirm') return;
    openOrderActionModal(action, orderId);
  });

  document.addEventListener('click', (event) => {
    if (!(event.target instanceof Element)) {
      closeAllActionMenus();
      return;
    }

    if (event.target.closest('[data-orders-actions-wrap]')) return;
    closeAllActionMenus();
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeAllActionMenus();
    }
  });

  actionModalConfirmBtn?.addEventListener('click', async () => {
    const action = text(actionModalConfirmBtn.dataset.action).toLowerCase();
    const orderId = text(actionModalConfirmBtn.dataset.orderId);
    const currentStatus = text(actionModalConfirmBtn.dataset.currentStatus);
    const targetStatus = text(actionModalConfirmBtn.dataset.targetStatus);
    if (!action || !orderId || !targetStatus) return;
    if (!window.API?.Admin?.OrderHistory || typeof window.API.Admin.OrderHistory.updateStatus !== 'function') {
      if (typeof window.showError === 'function') {
        window.showError('Status update API is not configured.');
      }
      return;
    }

    const idleLabel = action === 'cancelld' ? 'Cancelled' : 'Confirm';
    actionModalConfirmBtn.disabled = true;
    actionModalConfirmBtn.textContent = 'Updating...';

    try {
      const payload = await window.API.Admin.OrderHistory.updateStatus({
        apiBaseUrl: apiBase,
        refreshToken: token || undefined,
        orderId,
        status: targetStatus,
        timeoutMs: 12000,
      });

      hideActionConfirmButton();
      if (typeof window.closeAllModals === 'function') {
        window.closeAllModals();
      }

      const responseStatus = text(payload?.status || targetStatus);
      const successMessage = `Order ${orderId}: ${titleCase(currentStatus)} -> ${titleCase(responseStatus)}.`;
      if (typeof window.showSuccess === 'function') {
        window.showSuccess(successMessage);
      }

      await loadOrders(state.page);
    } catch (error) {
      if (typeof window.showError === 'function') {
        window.showError(error?.message || 'Failed to update order status.');
      }
      actionModalConfirmBtn.disabled = false;
      actionModalConfirmBtn.textContent = idleLabel;
    }
  });

  loadOrders(state.page);
}

function initOrderDetailsPage() {
  const section = document.querySelector('[data-order-details-page]');
  if (!section) return;

  const apiBase = String(section.dataset.apiBaseUrl || '').replace(/\/+$/, '');
  const sessionToken = String(section.dataset.refreshToken || '').trim();
  const configuredOrderId = String(section.dataset.orderId || '').trim();
  const invoiceUrlTemplate = String(section.dataset.orderInvoiceUrlTemplate || '').trim();
  const apiToken = String(window.API?.getToken?.() || '').trim();
  let accessToken = '';
  let storageToken = '';
  try {
    accessToken = String(window.localStorage.getItem('access_token') || '').trim();
    storageToken = String(window.localStorage.getItem('refresh_token') || '').trim();
  } catch {
    accessToken = '';
    storageToken = '';
  }

  const refreshToken = sessionToken || apiToken || storageToken || accessToken;
  const bearerToken = accessToken || apiToken || sessionToken || storageToken;

  const text = (value) => String(value ?? '').trim();
  const toInt = (value, fallback = 0) => {
    const parsed = Number.parseInt(String(value ?? ''), 10);
    return Number.isFinite(parsed) ? parsed : fallback;
  };
  const toNumber = (value, fallback = NaN) => {
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : fallback;
  };
  const normalizeStatus = (value) => text(value)
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '_')
    .replace(/^_+|_+$/g, '');
  const titleCase = (value) => text(value)
    .replace(/[_-]+/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
    .replace(/\b\w/g, (char) => char.toUpperCase());
  const escapeHtml = (value) => text(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
  const formatBdt = (value) => {
    const amount = Math.max(0, toNumber(value, 0));
    return `BDT ${amount.toLocaleString('en-US', {
      minimumFractionDigits: Number.isInteger(amount) ? 0 : 2,
      maximumFractionDigits: 2,
    })}`;
  };
  const inferOrderIdFromPath = () => {
    const parts = window.location.pathname.split('/').filter(Boolean);
    return text(parts[parts.length - 1] || '');
  };

  const orderId = text(configuredOrderId || inferOrderIdFromPath());
  const normalizeDetailsUrl = () => {
    if (typeof window.history?.replaceState !== 'function') return;

    const currentUrl = new URL(window.location.href);
    const pathParts = currentUrl.pathname.split('/').filter(Boolean);
    const adminOrdersIndex = pathParts.findIndex((part, index) => part === 'admin' && pathParts[index + 1] === 'orders');
    const basePath = adminOrdersIndex >= 0
      ? `/${pathParts.slice(0, adminOrdersIndex + 2).join('/')}`
      : '/admin/orders';
    const canonicalPath = `${basePath}/${encodeURIComponent(orderId)}`;

    let currentOrderSegment = text(pathParts[pathParts.length - 1]);
    try {
      currentOrderSegment = decodeURIComponent(currentOrderSegment);
    } catch {
      // Keep original segment when decoding fails.
    }

    if (currentUrl.search || currentUrl.pathname !== canonicalPath || currentOrderSegment !== orderId) {
      window.history.replaceState(window.history.state, '', `${canonicalPath}${currentUrl.hash}`);
    }
  };
  const messageNode = section.querySelector('[data-order-details-fetch-message]');
  const loaderNode = section.querySelector('[data-ui-loader]');
  const titleNode = section.querySelector('[data-order-details-order-title]');
  const userProfileLinkNode = section.querySelector('[data-order-details-user-profile-link]');
  const statusPillNode = section.querySelector('[data-order-details-status-pill]');
  const customerIdNode = section.querySelector('[data-order-details-customer-id]');
  const avatarWrap = section.querySelector('[data-order-details-avatar-wrap]');
  const nameNode = section.querySelector('[data-order-details-name]');
  const emailNode = section.querySelector('[data-order-details-email]');
  const phoneNode = section.querySelector('[data-order-details-phone]');
  const addressNode = section.querySelector('[data-order-details-address]');
  const methodNode = section.querySelector('[data-order-details-method]');
  const channelNode = section.querySelector('[data-order-details-channel]');
  const productsCountNode = section.querySelector('[data-order-details-products-count]');
  const productsBody = section.querySelector('[data-order-details-products-body]');
  const fraudTotalOrderNode = section.querySelector('[data-order-details-fraud-total-order]');
  const fraudTotalCancelledNode = section.querySelector('[data-order-details-fraud-total-cancelled]');
  const fraudCodCancelledNode = section.querySelector('[data-order-details-fraud-cod-cancelled]');
  const fraudSuccessOrderNode = section.querySelector('[data-order-details-fraud-success-order]');
  const totalSubtotalNode = section.querySelector('[data-order-details-total-subtotal]');
  const totalShippingNode = section.querySelector('[data-order-details-total-shipping]');
  const totalDiscountNode = section.querySelector('[data-order-details-total-discount]');
  const totalPaymentNode = section.querySelector('[data-order-details-total-payment]');
  const totalGrandNode = section.querySelector('[data-order-details-total-grand]');
  const historyCountNode = section.querySelector('[data-order-details-history-count]');
  const historyBody = section.querySelector('[data-order-details-history-body]');
  const discountForm = section.querySelector('[data-order-details-discount-form]');
  const discountAmountInput = section.querySelector('[data-order-details-discount-input]');
  const discountReasonInput = section.querySelector('[data-order-details-discount-reason]');
  const discountSubmitButton = section.querySelector('[data-order-details-discount-submit]');
  const partialForm = section.querySelector('[data-order-details-partial-form]');
  const partialAmountInput = section.querySelector('[data-order-details-partial-input]');
  const partialSubmitButton = section.querySelector('[data-order-details-partial-submit]');
  const confirmActionForm = section.querySelector('[data-order-details-confirm-form]');
  const confirmSubmitButton = confirmActionForm?.querySelector('button[type="submit"]');
  const discountSection = section.querySelector('[data-order-details-discount-section]');
  const partialSection = section.querySelector('[data-order-details-partial-section]');
  const allowedOrderActionStatuses = new Set([
    'waiting_for_call',
    'waiting_for_confirmation',
    'ready_to_dispatch',
  ]);
  const confirmOrderActionStatuses = new Set([
    'waiting_for_payment',
    'payment_review',
    'waiting_for_call',
    'waiting_for_confirmation',
  ]);
  const orderActionStatusHint = 'Waiting For Call, Waiting For Confirmation, or Ready To Dispatch';
  let orderActionsAllowed = false;
  let currentOrderStatus = '';
  let currentResolvedOrderId = orderId;

  const setMessage = (message, tone = 'info') => {
    if (!(messageNode instanceof HTMLElement)) return;
    messageNode.hidden = false;
    messageNode.innerHTML = `<span class="badge badge-${escapeHtml(tone)}">${escapeHtml(message)}</span>`;
  };
  const statusMetaOf = (statusValue) => {
    const normalized = normalizeStatus(statusValue);
    if (normalized === 'success') return {label: 'Success', css: 'badge-success'};
    if (normalized === 'in_transit') return {label: 'In Transit', css: 'badge-primary'};
    if (normalized === 'ready_to_dispatch') return {label: 'Ready to Dispatch', css: 'badge-info'};
    if (normalized === 'waiting_for_payment' || normalized === 'payment_review') {
      return {label: 'Waiting For Payment', css: 'badge-warning'};
    }
    if (normalized === 'waiting_for_call' || normalized === 'waiting_for_confirmation') {
      return {label: titleCase(normalized), css: 'badge-warning'};
    }
    if (normalized.startsWith('cancel_')) return {label: titleCase(normalized), css: 'badge-danger'};
    return {label: titleCase(normalized || 'unknown'), css: 'badge-info'};
  };
  const clearMessage = () => {
    if (!(messageNode instanceof HTMLElement)) return;
    messageNode.hidden = true;
    messageNode.innerHTML = '';
  };
  const renderCurrentStatus = (statusValue) => {
    if (!(statusPillNode instanceof HTMLElement)) return;

    const statusMeta = statusMetaOf(statusValue);
    statusPillNode.classList.remove('badge-success', 'badge-primary', 'badge-info', 'badge-warning', 'badge-danger');
    statusPillNode.classList.add(statusMeta.css);
    statusPillNode.textContent = statusMeta.label;
  };
  const setLoading = (loading) => {
    if (loaderNode instanceof HTMLElement) {
      loaderNode.hidden = !loading;
    }
    section.classList.toggle('is-loading', loading);
  };
  const parsePositiveAmount = (value) => {
    const numeric = Number.parseFloat(String(value ?? '').trim());
    return Number.isFinite(numeric) && numeric > 0 ? numeric : NaN;
  };
  const setButtonLoading = (button, loading, loadingLabel = 'Applying...') => {
    if (!(button instanceof HTMLButtonElement)) return;
    if (!button.dataset.idleLabel) {
      button.dataset.idleLabel = button.textContent || 'Apply';
    }

    button.disabled = loading;
    button.textContent = loading ? loadingLabel : (button.dataset.idleLabel || 'Apply');
  };
  const resolveErrorMessage = (error, fallbackMessage) => {
    const payloadMessage = text(error?.payload?.message || error?.payload?.error);
    if (payloadMessage) return payloadMessage;
    if (error?.isTimeout) return 'Request timed out. Please try again.';
    return text(error?.message || fallbackMessage) || fallbackMessage;
  };
  const transitionStatusForConfirm = (statusValue) => {
    const normalized = normalizeStatus(statusValue);
    if (normalized === 'waiting_for_payment' || normalized === 'payment_review') return 'success';
    if (normalized === 'waiting_for_call') return 'waiting_for_confirmation';
    if (normalized === 'waiting_for_confirmation') return 'ready_to_dispatch';
    if (normalized === 'ready_to_dispatch') return 'in_transit';
    if (normalized === 'in_transit') return 'success';
    return '';
  };
  const shouldGenerateInvoiceOnConfirm = (statusValue) => normalizeStatus(statusValue) === 'waiting_for_confirmation';
  const confirmButtonLabelForStatus = (statusValue) => {
    const normalized = normalizeStatus(statusValue);
    if (normalized === 'waiting_for_payment' || normalized === 'payment_review') {
      return 'Confirm Payment';
    }

    return shouldGenerateInvoiceOnConfirm(statusValue)
      ? 'Confirm + Invoice'
      : 'Confirm';
  };
  const syncConfirmButtonLabel = (statusValue) => {
    if (!(confirmSubmitButton instanceof HTMLButtonElement)) return;

    const nextLabel = confirmButtonLabelForStatus(statusValue);
    confirmSubmitButton.dataset.idleLabel = nextLabel;
    if (!confirmSubmitButton.disabled) {
      confirmSubmitButton.textContent = nextLabel;
    }
  };
  const buildInvoiceUrl = (nextOrderId = '', statusValue = '') => {
    const resolvedId = text(nextOrderId || currentResolvedOrderId || orderId);
    const normalizedStatusHint = normalizeStatus(statusValue);
    let invoiceUrl = '';

    if (invoiceUrlTemplate.includes('__ORDER_ID__')) {
      invoiceUrl = invoiceUrlTemplate.replace('__ORDER_ID__', encodeURIComponent(resolvedId));
    } else if (invoiceUrlTemplate) {
      invoiceUrl = invoiceUrlTemplate;
    } else {
      invoiceUrl = `${window.location.pathname.replace(/\/+$/, '')}/invoice`;
    }

    if (!normalizedStatusHint) {
      return invoiceUrl;
    }

    try {
      const parsedUrl = new URL(invoiceUrl, window.location.origin);
      parsedUrl.searchParams.set('status', normalizedStatusHint);
      return parsedUrl.toString();
    } catch {
      const separator = invoiceUrl.includes('?') ? '&' : '?';
      return `${invoiceUrl}${separator}status=${encodeURIComponent(normalizedStatusHint)}`;
    }
  };
  const setOrderActionsAvailability = (statusValue) => {
    const normalized = normalizeStatus(statusValue);
    currentOrderStatus = normalized;
    orderActionsAllowed = allowedOrderActionStatuses.has(normalized);
    const confirmActionAllowed = confirmOrderActionStatuses.has(normalized);
    syncConfirmButtonLabel(normalized);

    if (confirmActionForm instanceof HTMLFormElement) {
      confirmActionForm.hidden = !confirmActionAllowed;
    }
    if (discountSection instanceof HTMLElement) {
      discountSection.hidden = !orderActionsAllowed;
    }
    if (partialSection instanceof HTMLElement) {
      partialSection.hidden = !orderActionsAllowed;
    }

    return orderActionsAllowed;
  };

  const setUserProfileLink = (userId = '') => {
    if (!(userProfileLinkNode instanceof HTMLAnchorElement)) return;

    const baseUrlRaw = text(userProfileLinkNode.dataset.userProfileBaseUrl || userProfileLinkNode.getAttribute('href') || '/admin/users/views');
    const baseUrl = baseUrlRaw || '/admin/users/views';

    if (!userId) {
      userProfileLinkNode.href = baseUrl;
      return;
    }

    const parsedBase = new URL(baseUrl, window.location.origin);
    parsedBase.searchParams.set('user_id', userId);
    userProfileLinkNode.href = `${parsedBase.pathname}${parsedBase.search}`;
  };
  const resetUiForLoading = () => {
    if (titleNode) titleNode.textContent = `Order ${orderId || '--'}`;
    syncConfirmButtonLabel('');
    if (statusPillNode instanceof HTMLElement) {
      statusPillNode.classList.remove('badge-success', 'badge-primary', 'badge-warning', 'badge-danger');
      statusPillNode.classList.add('badge-info');
      statusPillNode.textContent = 'Loading...';
    }
    if (customerIdNode) customerIdNode.textContent = '--';
    if (avatarWrap instanceof HTMLElement) avatarWrap.textContent = '-';
    if (nameNode) nameNode.textContent = '--';
    if (emailNode) emailNode.textContent = '--';
    if (phoneNode) phoneNode.textContent = '--';
    if (addressNode) addressNode.textContent = '--';
    if (methodNode) methodNode.textContent = 'Payment: --';
    if (channelNode) channelNode.textContent = 'Channel: --';
    if (productsCountNode) productsCountNode.textContent = '0 products';
    if (productsBody instanceof HTMLElement) {
      productsBody.innerHTML = `
        <tr>
          <td colspan="5" class="users-empty"><span class="ui-skeleton-line is-lg"></span><span class="ui-skeleton-line is-sm"></span></td>
        </tr>
      `;
    }
    if (totalSubtotalNode) totalSubtotalNode.textContent = 'BDT 0';
    if (totalShippingNode) totalShippingNode.textContent = 'BDT 0';
    if (totalDiscountNode) totalDiscountNode.textContent = '- BDT 0';
    if (totalPaymentNode) totalPaymentNode.textContent = 'BDT 0';
    if (totalGrandNode) totalGrandNode.textContent = 'BDT 0';
    if (historyCountNode) historyCountNode.textContent = '0 history';
    if (historyBody instanceof HTMLElement) {
      historyBody.innerHTML = `
        <tr>
          <td colspan="5" class="users-empty"><span class="ui-skeleton-line is-lg"></span><span class="ui-skeleton-line is-sm"></span></td>
        </tr>
      `;
    }

    updateFraudUi({
      total_order: 0,
      total_cancelled: 0,
      cod_cancelled: 0,
      success_order: 0,
    });
    currentResolvedOrderId = orderId;
    setUserProfileLink('');
    setOrderActionsAvailability('');
  };

  const updateFraudUi = (fraudSignals) => {

    const totalOrder = Math.max(0, toInt(fraudSignals?.total_order, 0));
    const totalCancelled = Math.max(0, toInt(fraudSignals?.total_cancelled, 0));
    const codCancelled = Math.max(0, toInt(fraudSignals?.cod_cancelled, 0));
    const successOrder = Math.max(0, toInt(fraudSignals?.success_order, 0));

    if (fraudTotalOrderNode instanceof HTMLElement) {
      fraudTotalOrderNode.textContent = String(totalOrder);
    }
    if (fraudTotalCancelledNode instanceof HTMLElement) {
      fraudTotalCancelledNode.textContent = String(totalCancelled);
    }
    if (fraudCodCancelledNode instanceof HTMLElement) {
      fraudCodCancelledNode.textContent = String(codCancelled);
    }
    if (fraudSuccessOrderNode instanceof HTMLElement) {
      fraudSuccessOrderNode.textContent = String(successOrder);
    }
  };

  const renderHistoryRows = (historyRows) => {
    if (!(historyBody instanceof HTMLElement)) return;

    if (!historyRows.length) {
      historyBody.innerHTML = `
        <tr>
          <td colspan="5" class="users-empty">No previous orders found.</td>
        </tr>
      `;
      if (historyCountNode instanceof HTMLElement) {
        historyCountNode.textContent = '0 history';
      }
      return;
    }

    if (historyCountNode instanceof HTMLElement) {
      historyCountNode.textContent = `${historyRows.length} history`;
    }

    historyBody.innerHTML = historyRows.map((entry) => {
      const historyId = text(entry?.order_id || entry?.id || 'N/A');
      const date = text(entry?.date || entry?.order_date || entry?.created_at || entry?.placed_at || '-');
      const statusText = titleCase(entry?.status || entry?.order_status || entry?.state || 'Unknown');
      const statusNormalized = normalizeStatus(statusText);
      const amount = toNumber(entry?.amount ?? entry?.grand_total ?? entry?.total ?? entry?.subtotal, 0);
      const issue = text(entry?.issue || entry?.reason || entry?.cancel_reason || 'None');
      const statusClass = ['completed', 'delivered', 'success'].includes(statusNormalized)
        ? 'badge-success'
        : 'badge-warning';
      const issueClass = normalizeStatus(issue) === 'none' ? 'badge-success' : 'badge-danger';

      return `
        <tr>
          <td>${escapeHtml(historyId)}</td>
          <td>${escapeHtml(date)}</td>
          <td><span class="badge ${statusClass}">${escapeHtml(statusText)}</span></td>
          <td>${escapeHtml(formatBdt(amount))}</td>
          <td><span class="badge ${issueClass}">${escapeHtml(issue)}</span></td>
        </tr>
      `;
    }).join('');
  };

  const extractDetailsPayload = (payload) => {
    const raw = payload && typeof payload === 'object' ? payload : {};

    if (raw.data && typeof raw.data === 'object') {
      if (raw.data.order && typeof raw.data.order === 'object') return raw.data.order;
      if (raw.data.details && typeof raw.data.details === 'object') return raw.data.details;
      return raw.data;
    }

    if (raw.order && typeof raw.order === 'object') return raw.order;
    if (raw.details && typeof raw.details === 'object') return raw.details;

    return raw;
  };

  const fetchWithJquery = () => {
    if (!window.jQuery || typeof window.jQuery.ajax !== 'function') {
      return Promise.reject(new Error('__NO_JQUERY__'));
    }

    return new Promise((resolve, reject) => {
      const headers = {
        Accept: 'application/json',
        'ngrok-skip-browser-warning': 'true',
      };

      if (refreshToken) {
        headers['x-refresh-token'] = refreshToken;
      }
      if (bearerToken) {
        headers.Authorization = bearerToken.startsWith('Bearer ')
          ? bearerToken
          : `Bearer ${bearerToken}`;
      }

      window.jQuery.ajax({
        url: `${apiBase}/api/admin/order-history/${encodeURIComponent(orderId)}/details`,
        method: 'GET',
        headers,
        timeout: 12000,
      }).done(resolve).fail((xhr, statusText, errorThrown) => {
        const error = new Error(errorThrown || statusText || 'Failed to load order details.');
        error.status = xhr?.status || 0;
        error.payload = xhr?.responseJSON || null;
        error.isTimeout = statusText === 'timeout';
        reject(error);
      });
    });
  };

  const fetchWithApiModule = () => {
    if (!window.API?.Admin?.OrderHistory || typeof window.API.Admin.OrderHistory.details !== 'function') {
      return Promise.reject(new Error('Order details API is not configured.'));
    }

    return window.API.Admin.OrderHistory.details({
      apiBaseUrl: apiBase,
      refreshToken: refreshToken || bearerToken || undefined,
      orderId,
      timeoutMs: 12000,
    });
  };
  const putWithFetch = async (path, body) => {
    const headers = {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'ngrok-skip-browser-warning': 'true',
    };

    const tokenForHeader = refreshToken || bearerToken;
    if (tokenForHeader) {
      headers['x-refresh-token'] = tokenForHeader;
    }
    if (bearerToken) {
      headers.Authorization = bearerToken.startsWith('Bearer ')
        ? bearerToken
        : `Bearer ${bearerToken}`;
    }

    const response = await fetch(`${apiBase}${path}`, {
      method: 'PUT',
      headers,
      body: JSON.stringify(body),
    });
    const contentType = text(response.headers.get('content-type')).toLowerCase();
    const payload = contentType.includes('application/json')
      ? await response.json().catch(() => ({}))
      : await response.text().catch(() => '');

    if (!response.ok) {
      const message = typeof payload === 'string'
        ? payload
        : text(payload?.message || payload?.error || '');
      const error = new Error(message || `Request failed (${response.status}).`);
      error.status = response.status;
      error.payload = payload;
      throw error;
    }

    return payload;
  };
  const requestOrderMutationByPath = async (path, body) => {
    const tokenForRequest = refreshToken || bearerToken || undefined;
    if (window.API && typeof window.API.request === 'function') {
      return window.API.request({
        baseUrl: apiBase,
        path,
        method: 'PUT',
        token: tokenForRequest,
        body,
        timeoutMs: 12000,
        includeNgrokHeader: true,
      });
    }

    return putWithFetch(path, body);
  };
  const mutateOrderHistory = async (suffix, body, apiModuleCall) => {
    const encodedOrderId = encodeURIComponent(orderId);
    const fallbackPath = `/api/admin/order-history/${encodedOrderId}/${suffix}`;

    if (typeof apiModuleCall === 'function') {
      try {
        return await apiModuleCall();
      } catch (error) {
        if (error?.status !== 404 && error?.message !== '__NO_API_METHOD__') throw error;
      }
    }

    return requestOrderMutationByPath(fallbackPath, body);
  };

  const hydrate = (details) => {
    const resolvedOrderId = text(details.order_id || details.id || orderId);
    currentResolvedOrderId = resolvedOrderId || orderId;
    const customerName = text(details.name || details.customer_name || 'Unknown');
    const profile = text(details.profile || '');
    const email = text(details.email || '-');
    const phone = text(details.phone || '-');
    const address = text(details.address || '-');
    const method = titleCase(details.method || details.payment || 'N/A');
    const channel = titleCase(details.channel || 'N/A');
    const status = text(details.status || details.order_status || details.state || '');

    const products = Array.isArray(details.products)
      ? details.products
      : (Array.isArray(details.products?.data) ? details.products.data : []);
    const historyRows = Array.isArray(details.previous_orders)
      ? details.previous_orders
      : (Array.isArray(details.previous_orders?.data) ? details.previous_orders.data : []);
    const fraudSignals = details.fraud_signals && typeof details.fraud_signals === 'object'
      ? details.fraud_signals
      : {};

    const subtotal = Math.max(0, toNumber(details.subtotal, 0));
    const shipping = Math.max(0, toNumber(details.delivery_charge ?? details.shipping_fee, 0));
    const discount = Math.max(0, toNumber(details.discount, 0));
    const grandTotal = Math.max(0, toNumber(details.grand_total, subtotal + shipping - discount));
    const totalPayment = Math.max(0, toNumber(
      details.total_payment
      ?? details.total_paid
      ?? details.paid_amount
      ?? details.partial_paid
      ?? details.partial_payment,
      grandTotal
    ));

    if (titleNode) titleNode.textContent = `Order ${resolvedOrderId}`;
    const userProfileId = text(details.customer_id || details.user_id || details.user_client_id || '');
    if (customerIdNode) {
      customerIdNode.textContent = userProfileId || resolvedOrderId;
    }
    setUserProfileLink(userProfileId);
    if (avatarWrap instanceof HTMLElement) {
      if (profile) {
        avatarWrap.innerHTML = `<img src="${escapeHtml(profile)}" class="users-avatar" alt="${escapeHtml(customerName)}" loading="lazy">`;
      } else {
        avatarWrap.textContent = (customerName.charAt(0) || 'U').toUpperCase();
      }
    }
    if (nameNode) nameNode.textContent = customerName;
    if (emailNode) emailNode.textContent = email;
    if (phoneNode) phoneNode.textContent = phone;
    if (addressNode) addressNode.textContent = address;
    if (methodNode) methodNode.textContent = `Payment: ${method}`;
    if (channelNode) channelNode.textContent = `Channel: ${channel}`;
    renderCurrentStatus(status);
    setOrderActionsAvailability(status);

    if (productsCountNode) productsCountNode.textContent = `${products.length} products`;

    if (productsBody instanceof HTMLElement) {
      if (!products.length) {
        productsBody.innerHTML = `
          <tr>
            <td colspan="5" class="users-empty">No products found for this order.</td>
          </tr>
        `;
      } else {
        productsBody.innerHTML = products.map((item) => {
          const title = text(item?.title || item?.name || 'Unnamed Product');
          const sku = text(item?.sku || '-');
          const qty = Math.max(0, toInt(item?.qty, 0));
          const image = text(item?.image || '');
          const unitPrice = toNumber(item?.unit_price, NaN);
          const total = toNumber(item?.total, Number.isFinite(unitPrice) ? unitPrice * qty : NaN);
          const unitPriceText = Number.isFinite(unitPrice) ? formatBdt(unitPrice) : '-';
          const totalText = Number.isFinite(total) ? formatBdt(total) : '-';

          return `
            <tr>
              <td>
                <div class="orders-line-product">
                  <span class="orders-line-product-thumb">
                    ${image
                      ? `<img src="${escapeHtml(image)}" alt="${escapeHtml(title)}" loading="lazy">`
                      : '<span class="orders-customer-avatar">P</span>'}
                  </span>
                  <div>
                    <strong>${escapeHtml(title)}</strong>
                  </div>
                </div>
              </td>
              <td>${escapeHtml(sku)}</td>
              <td>${qty}</td>
              <td>${escapeHtml(unitPriceText)}</td>
              <td class="orders-cell-strong">${escapeHtml(totalText)}</td>
            </tr>
          `;
        }).join('');
      }
    }

    if (totalSubtotalNode) totalSubtotalNode.textContent = formatBdt(subtotal);
    if (totalShippingNode) totalShippingNode.textContent = formatBdt(shipping);
    if (totalDiscountNode) totalDiscountNode.textContent = `- ${formatBdt(discount)}`;
    if (totalPaymentNode) totalPaymentNode.textContent = formatBdt(totalPayment);
    if (totalGrandNode) totalGrandNode.textContent = formatBdt(grandTotal);

    renderHistoryRows(historyRows);
    updateFraudUi(fraudSignals);
  };

  const loadOrderDetails = ({showLoader = true, infoMessage = 'Loading order details...'} = {}) => {
    if (showLoader) {
      resetUiForLoading();
      setLoading(true);
    }
    if (infoMessage) {
      setMessage(infoMessage, 'info');
    }

    return fetchWithJquery()
      .catch((error) => {
        if (error?.message === '__NO_JQUERY__') {
          return fetchWithApiModule();
        }

        return fetchWithApiModule().catch(() => Promise.reject(error));
      })
      .then((payload) => {
        const details = extractDetailsPayload(payload);
        if (!details || typeof details !== 'object') {
          throw new Error('Order details payload is invalid.');
        }

        hydrate(details);
        clearMessage();
        return details;
      })
      .catch((error) => {
        const message = resolveErrorMessage(error, 'Failed to load order details.');
        setMessage(message, 'danger');
        if (typeof window.showError === 'function') {
          window.showError(message);
        }
        throw error;
      })
      .finally(() => {
        if (showLoader) {
          setLoading(false);
        }
      });
  };

  if (!apiBase) {
    setMessage('Backend API URL is missing.', 'danger');
    return;
  }
  if (!orderId) {
    setMessage('Order ID is missing from URL.', 'danger');
    return;
  }

  normalizeDetailsUrl();

  if (confirmActionForm instanceof HTMLFormElement) {
    confirmActionForm.addEventListener('submit', async (event) => {
      event.preventDefault();

      if (!confirmOrderActionStatuses.has(currentOrderStatus)) {
        return;
      }

      const sourceStatus = currentOrderStatus;
      const targetStatus = transitionStatusForConfirm(sourceStatus);
      const shouldGenerateInvoice = shouldGenerateInvoiceOnConfirm(sourceStatus);
      if (!targetStatus) {
        setMessage('This order cannot be confirmed from its current status.', 'danger');
        return;
      }

      const confirmPrompt = shouldGenerateInvoice
        ? 'Confirm this order and generate invoice now?'
        : 'Confirm this order now?';
      if (typeof window.confirm === 'function' && !window.confirm(confirmPrompt)) {
        return;
      }

      setButtonLoading(confirmSubmitButton, true, shouldGenerateInvoice ? 'Confirming + Invoice...' : 'Confirming...');
      setMessage(shouldGenerateInvoice ? 'Confirming order and preparing invoice...' : 'Confirming order...', 'info');

      try {
        const payload = await mutateOrderHistory(
          'status',
          {status: targetStatus},
          () => {
            if (!window.API?.Admin?.OrderHistory || typeof window.API.Admin.OrderHistory.updateStatus !== 'function') {
              return Promise.reject(new Error('__NO_API_METHOD__'));
            }

            return window.API.Admin.OrderHistory.updateStatus({
              apiBaseUrl: apiBase,
              refreshToken: refreshToken || bearerToken || undefined,
              orderId,
              status: targetStatus,
              timeoutMs: 12000,
            });
          }
        );

        const responseStatus = normalizeStatus(
          payload?.status
          || payload?.data?.status
          || payload?.order?.status
          || targetStatus
        ) || targetStatus;

        currentOrderStatus = responseStatus;

        if (typeof window.showSuccess === 'function') {
          window.showSuccess(`Order ${currentResolvedOrderId}: ${titleCase(sourceStatus)} -> ${titleCase(responseStatus)}.`);
        }

        renderCurrentStatus(responseStatus);
        setOrderActionsAvailability(responseStatus);

        if (shouldGenerateInvoice) {
          window.location.assign(buildInvoiceUrl(currentResolvedOrderId, sourceStatus || responseStatus));
          return;
        }

        clearMessage();
      } catch (error) {
        const message = resolveErrorMessage(error, 'Failed to confirm order.');
        setMessage(message, 'danger');
        if (typeof window.showError === 'function') {
          window.showError(message);
        }
      } finally {
        setButtonLoading(confirmSubmitButton, false);
      }
    });
  }

  if (discountForm instanceof HTMLFormElement) {
    discountForm.addEventListener('submit', async (event) => {
      event.preventDefault();

      if (!orderActionsAllowed) {
        setMessage(`Discount update is available only for ${orderActionStatusHint}.`, 'danger');
        return;
      }

      if (!(discountAmountInput instanceof HTMLInputElement) || !discountForm.reportValidity()) {
        return;
      }

      const discountAmount = parsePositiveAmount(discountAmountInput.value);
      if (!Number.isFinite(discountAmount)) {
        setMessage('Enter a valid discount amount greater than 0.', 'danger');
        discountAmountInput.focus();
        return;
      }

      setButtonLoading(discountSubmitButton, true);
      setMessage('Applying discount...', 'info');

      try {
        await mutateOrderHistory(
          'discount',
          {discount: discountAmount},
          () => {
            if (!window.API?.Admin?.OrderHistory || typeof window.API.Admin.OrderHistory.updateDiscount !== 'function') {
              return Promise.reject(new Error('__NO_API_METHOD__'));
            }

            return window.API.Admin.OrderHistory.updateDiscount({
              apiBaseUrl: apiBase,
              refreshToken: refreshToken || bearerToken || undefined,
              orderId,
              discount: discountAmount,
              timeoutMs: 12000,
            });
          }
        );

        if (discountAmountInput instanceof HTMLInputElement) {
          discountAmountInput.value = '';
        }
        if (discountReasonInput instanceof HTMLInputElement) {
          discountReasonInput.value = '';
        }

        if (typeof window.showSuccess === 'function') {
          window.showSuccess(`Discount updated for ${orderId}.`);
        }
        await loadOrderDetails({
          showLoader: false,
          infoMessage: 'Discount applied. Refreshing order details...',
        });
      } catch (error) {
        const message = resolveErrorMessage(error, 'Failed to apply discount.');
        setMessage(message, 'danger');
        if (typeof window.showError === 'function') {
          window.showError(message);
        }
      } finally {
        setButtonLoading(discountSubmitButton, false);
      }
    });
  }

  if (partialForm instanceof HTMLFormElement) {
    partialForm.addEventListener('submit', async (event) => {
      event.preventDefault();

      if (!orderActionsAllowed) {
        setMessage(`Partial payment is available only for ${orderActionStatusHint}.`, 'danger');
        return;
      }

      if (!(partialAmountInput instanceof HTMLInputElement) || !partialForm.reportValidity()) {
        return;
      }

      const partialPaid = parsePositiveAmount(partialAmountInput.value);
      if (!Number.isFinite(partialPaid)) {
        setMessage('Enter a valid partial payment amount greater than 0.', 'danger');
        partialAmountInput.focus();
        return;
      }

      setButtonLoading(partialSubmitButton, true);
      setMessage('Applying partial payment...', 'info');

      try {
        await mutateOrderHistory(
          'partial-payment',
          {partial_paid: partialPaid},
          () => {
            if (!window.API?.Admin?.OrderHistory || typeof window.API.Admin.OrderHistory.updatePartialPayment !== 'function') {
              return Promise.reject(new Error('__NO_API_METHOD__'));
            }

            return window.API.Admin.OrderHistory.updatePartialPayment({
              apiBaseUrl: apiBase,
              refreshToken: refreshToken || bearerToken || undefined,
              orderId,
              partialPaid,
              timeoutMs: 12000,
            });
          }
        );

        if (partialAmountInput instanceof HTMLInputElement) {
          partialAmountInput.value = '';
        }

        if (typeof window.showSuccess === 'function') {
          window.showSuccess(`Partial payment updated for ${orderId}.`);
        }
        await loadOrderDetails({
          showLoader: false,
          infoMessage: 'Partial payment applied. Refreshing order details...',
        });
      } catch (error) {
        const message = resolveErrorMessage(error, 'Failed to apply partial payment.');
        setMessage(message, 'danger');
        if (typeof window.showError === 'function') {
          window.showError(message);
        }
      } finally {
        setButtonLoading(partialSubmitButton, false);
      }
    });
  }

  loadOrderDetails().catch(() => {
    // Error is already handled in loadOrderDetails.
  });
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
  const readInitialCount = () => Number.parseInt(countNode?.dataset.initialCount || '0', 10) || 0;
  const readUniverseCount = () => {
    const baseCount = readInitialCount();
    return Number.parseInt(filterNode?.dataset.universeCount || String(baseCount), 10) || baseCount;
  };
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
    const totalVisible = readInitialCount() + createdCount;
    if (countNode) {
      countNode.textContent = `${totalVisible} monitored orders`;
    }

    if (filterNode) {
      const nextUniverse = Math.max(readUniverseCount(), totalVisible);
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
          <div class="orders-actions-wrap">
            <button
              type="button"
              class="orders-actions-trigger"
              disabled
              title="Frontend demo row only"
              aria-label="Actions unavailable for demo row"
            >
              <span class="orders-actions-trigger-dots" aria-hidden="true">⋯</span>
            </button>
          </div>
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

    const originalLabel = saveButton.textContent.trim();
    saveButton.disabled = true;
    saveButton.textContent = 'Saving...';

    facebookLoader.show({
      title: 'Saving bot settings',
      message: 'Updating Facebook settings',
    });

    try {
      const rawPayload = await window.API.Admin.FacebookAuth.updateSettings({
        apiBaseUrl,
        refreshToken,
        payload,
        timeoutMs: 12000,
      });

      if (!isObjectPayload(rawPayload)) {
        throw new Error('Unexpected JSON shape returned from API.');
      }

      if ((rawPayload.message || '').toLowerCase() !== 'updated') {
        throw new Error(rawPayload.message || 'Update response was not recognized.');
      }

      markCurrentStateAsSaved();
      showSuccess('Bot settings updated.');
      await fetchFacebookStatus();
    } catch (error) {
      if (error?.isTimeout) {
        showError('Save request timed out. Please try again.');
        return;
      }

      if (error?.status === 401) {
        showError('Unauthorized (401). Server-provided refresh token is invalid or expired. Please re-login.');
        return;
      }

      showError(error.message || 'Unable to save bot settings right now.');
    } finally {
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

    const originalLabel = disconnectButton.textContent.trim();
    disconnectButton.disabled = true;
    disconnectButton.textContent = 'Disconnecting...';

    facebookLoader.show({
      title: 'Disconnecting Facebook',
      message: 'Removing account link from backend',
    });

    try {
      const rawPayload = await window.API.Admin.FacebookAuth.disconnect({
        apiBaseUrl,
        refreshToken,
        timeoutMs: 12000,
      });

      if (!isObjectPayload(rawPayload)) {
        throw new Error('Unexpected JSON shape returned from API.');
      }

      if ((rawPayload.message || '').toLowerCase() !== 'deleted') {
        throw new Error(rawPayload.message || 'Disconnect response was not recognized.');
      }

      applyDisconnectedState('Facebook disconnected successfully.');
      showSuccess('Facebook disconnected successfully.');
    } catch (error) {
      if (error?.isTimeout) {
        showError('Disconnect request timed out. Please try again.');
        return;
      }

      if (error?.status === 401) {
        showError('Unauthorized (401). Server-provided refresh token is invalid or expired. Please re-login.');
        return;
      }

      showError(error.message || 'Unable to disconnect Facebook right now.');
    } finally {
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

    facebookLoader.show({
      title: 'Checking Facebook connection',
      message: 'Calling Facebook status endpoint',
    });
    setBadgeState('Checking...', 'warning');

    try {
      const rawPayload = await window.API.Admin.FacebookAuth.getStatus({
        apiBaseUrl,
        refreshToken,
        timeoutMs: 12000,
      });

      if (!isObjectPayload(rawPayload)) {
        throw new Error('Unexpected JSON shape returned from API.');
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
      if (error?.isTimeout) {
        applyRequestError('Request timeout. Please try again.');
        return;
      }

      if (error?.status === 401) {
        applyRequestError('Unauthorized (401). Server-provided refresh token is invalid or expired. Please re-login.');
        return;
      }

      if ((error?.payload?.status === 'disconnected') || (error?.payload?.error === 'not found')) {
        applyDisconnectedState(error?.payload?.msg || 'please connect your facebook to enjoy this features');
        return;
      }

      applyRequestError(error.message || 'Unable to load Facebook status.');
    } finally {
      facebookLoader.hide();
    }
  };

  setConnectionPanels(false);
  syncMessengerDependency();
  markCurrentStateAsSaved();
  fetchFacebookStatus();
}

function initDashboardPage() {
  const section = document.getElementById('adminDashboardSection');
  if (!section) return;

  const statusSelect = section.querySelector('[data-dashboard-status]');
  const paymentTypeSelect = section.querySelector('[data-dashboard-payment-type]');
  const channelSelect = section.querySelector('[data-dashboard-channel]');
  const sortBySelect = section.querySelector('[data-dashboard-sort-by]');
  const applyBtn = section.querySelector('[data-dashboard-apply]');
  const resetBtn = section.querySelector('[data-dashboard-reset]');
  const resultNode = section.querySelector('[data-dashboard-result]');

  const totalBadge = section.querySelector('[data-dashboard-total-badge]');
  const ordersTodayValue = section.querySelector('[data-dashboard-orders-today-value]');
  const ordersTodayMeta = section.querySelector('[data-dashboard-orders-today-meta]');
  const revenueValue = section.querySelector('[data-dashboard-revenue-value]');
  const revenueMeta = section.querySelector('[data-dashboard-revenue-meta]');
  const pendingDispatchValue = section.querySelector('[data-dashboard-pending-dispatch-value]');
  const pendingDispatchMeta = section.querySelector('[data-dashboard-pending-dispatch-meta]');
  const successRateValue = section.querySelector('[data-dashboard-success-rate-value]');
  const successRateMeta = section.querySelector('[data-dashboard-success-rate-meta]');
  const overviewHeadline = section.querySelector('[data-dashboard-overview-headline]');
  const overviewNote = section.querySelector('[data-dashboard-overview-note]');
  const totalOrdersValue = section.querySelector('[data-dashboard-total-orders-value]');
  const pendingOrdersValue = section.querySelector('[data-dashboard-pending-orders-value]');
  const completedOrdersValue = section.querySelector('[data-dashboard-completed-orders-value]');
  const rejectedOrdersValue = section.querySelector('[data-dashboard-rejected-orders-value]');

  const tbody = section.querySelector('[data-dashboard-orders-tbody]');
  const pageWrap = section.querySelector('[data-dashboard-pagination-wrap]');
  const pageSummary = section.querySelector('[data-dashboard-pagination-summary]');
  const pageControls = section.querySelector('[data-dashboard-pagination-controls]');

  if (
    !statusSelect || !paymentTypeSelect || !channelSelect || !sortBySelect || !applyBtn || !resetBtn || !resultNode ||
    !totalBadge || !ordersTodayValue || !ordersTodayMeta || !revenueValue || !revenueMeta || !pendingDispatchValue ||
    !pendingDispatchMeta || !successRateValue || !successRateMeta || !overviewHeadline || !overviewNote ||
    !totalOrdersValue || !pendingOrdersValue || !completedOrdersValue || !rejectedOrdersValue ||
    !tbody || !pageWrap || !pageSummary || !pageControls
  ) {
    return;
  }

  if (!window.API?.Admin?.OrderHistory || typeof window.API.Admin.OrderHistory.list !== 'function') {
    return;
  }

  const apiBase = String(section.dataset.apiBaseUrl || '').replace(/\/+$/, '');
  const perPage = Math.max(1, Number.parseInt(section.dataset.perPage || '10', 10) || 10);
  const sessionToken = String(section.dataset.refreshToken || '').trim();
  let storageToken = '';
  try {
    storageToken = String(window.localStorage.getItem('refresh_token') || '').trim();
  } catch {
    storageToken = '';
  }
  const token = sessionToken || storageToken;

  const toInt = (value, fallback = 0) => {
    const parsed = Number.parseInt(String(value ?? ''), 10);
    return Number.isFinite(parsed) ? parsed : fallback;
  };
  const toNumber = (value, fallback = 0) => {
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : fallback;
  };
  const text = (value) => String(value ?? '').trim();
  const escapeHtml = (value) => text(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
  const titleCase = (value) => text(value)
    .replace(/[_-]+/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
    .replace(/\b\w/g, (char) => char.toUpperCase());
  const formatCount = (value) => toInt(value, 0).toLocaleString('en-US');
  const slugify = (value) => text(value)
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '') || 'default';
  const formatBdt = (value) => `BDT ${toNumber(value, 0).toLocaleString('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  })}`;

  const state = {
    page: (() => {
      const params = new URLSearchParams(window.location.search);
      const parsed = toInt(params.get('page'), 1);
      return parsed > 0 ? parsed : 1;
    })(),
    perPage,
    total: 0,
    from: 0,
    to: 0,
    lastPage: 1,
    orders: [],
    loading: false,
    requestId: 0,
  };

  const setSelectValue = (select, value, fallback = 'all') => {
    const target = text(value);
    if (!target) {
      select.value = fallback;
      return;
    }

    const options = Array.from(select.options);
    const match = options.find((option) => text(option.value).toLowerCase() === target.toLowerCase());
    select.value = match ? match.value : fallback;
  };

  const urlParams = new URLSearchParams(window.location.search);
  setSelectValue(statusSelect, urlParams.get('status'), 'all');
  setSelectValue(paymentTypeSelect, urlParams.get('payment_type'), 'all');
  setSelectValue(channelSelect, urlParams.get('channel'), 'all');
  setSelectValue(sortBySelect, urlParams.get('sort_by'), 'newest_first');

  const normalizeFilter = (value) => {
    const normalized = text(value);
    return (!normalized || normalized.toLowerCase() === 'all') ? '' : normalized;
  };
  const readFilters = () => ({
    status: normalizeFilter(statusSelect.value),
    paymentType: normalizeFilter(paymentTypeSelect.value),
    channel: normalizeFilter(channelSelect.value),
    sortBy: normalizeFilter(sortBySelect.value) || 'newest_first',
  });

  const statusMetaOf = (order) => {
    const normalized = text(order?.status).toLowerCase();
    if (normalized === 'success') return {label: 'Success', css: 'badge-success'};
    if (normalized === 'in_transit') return {label: 'In Transit', css: 'badge-primary'};
    if (normalized === 'ready_to_dispatch') return {label: 'Ready to Dispatch', css: 'badge-info'};
    if (normalized === 'waiting_for_call' || normalized === 'waiting_for_confirmation') {
      return {label: titleCase(normalized), css: 'badge-warning'};
    }
    if (normalized.startsWith('cancel_')) return {label: titleCase(normalized), css: 'badge-danger'};
    return {label: titleCase(normalized || 'unknown'), css: 'badge-info'};
  };

  const setMessage = (message) => {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="users-empty">${escapeHtml(message)}</td>
      </tr>
    `;
  };

  const rowNode = (order) => {
    const orderId = text(order?.order_id || 'N/A');
    const name = text(order?.full_name || 'Unknown');
    const image = text(order?.image || order?.profile || '');
    const address = text(order?.address || 'N/A');
    const qty = Math.max(0, toInt(order?.qty, 0));
    const methodLabel = titleCase(order?.method || 'N/A');
    const channelLabel = titleCase(order?.channel || 'N/A');
    const status = statusMetaOf(order);

    const avatarHtml = image
      ? `<img src="${escapeHtml(image)}" class="users-avatar" alt="${escapeHtml(name)}" loading="lazy">`
      : `<span class="users-avatar users-avatar-fallback" aria-hidden="true">${escapeHtml(name.charAt(0).toUpperCase() || 'U')}</span>`;

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <div class="dashboard-order-cell">
          <strong>${escapeHtml(orderId)}</strong>
        </div>
      </td>
      <td>
        <div class="dashboard-customer-cell">
          ${avatarHtml}
          <div class="users-name-block">
            <div class="users-name">${escapeHtml(name)}</div>
          </div>
        </div>
      </td>
      <td>
        <div class="dashboard-address">${escapeHtml(address)}</div>
      </td>
      <td class="dashboard-order-amount">${formatCount(qty)}</td>
      <td><span class="badge badge-info">${escapeHtml(methodLabel)}</span></td>
      <td>
        <span class="badge users-channel-badge users-channel-${escapeHtml(slugify(channelLabel))}">
          ${escapeHtml(channelLabel)}
        </span>
      </td>
      <td><span class="badge ${status.css}">${escapeHtml(status.label)}</span></td>
    `;

    return tr;
  };

  const renderTable = () => {
    const list = Array.isArray(state.orders) ? state.orders : [];
    tbody.innerHTML = '';

    if (!list.length) {
      setMessage('No orders found for the selected filters.');
      resultNode.textContent = 'No orders found';
      return;
    }

    const fragment = document.createDocumentFragment();
    list.forEach((order) => fragment.appendChild(rowNode(order)));
    tbody.appendChild(fragment);

    if (state.total > 0 && state.from > 0 && state.to > 0) {
      resultNode.textContent = `Showing ${state.from}-${state.to} of ${state.total} orders`;
    } else {
      resultNode.textContent = `Showing ${list.length} orders`;
    }
  };

  const renderPagination = () => {
    const current = Math.max(1, toInt(state.page, 1));
    const last = Math.max(1, toInt(state.lastPage, 1));

    if (last <= 1) {
      pageSummary.textContent = 'Page 1 of 1';
      pageControls.innerHTML = '';
      pageWrap.hidden = true;
      return;
    }

    pageWrap.hidden = false;
    pageSummary.textContent = `Page ${current} of ${last}`;
    pageControls.innerHTML = '';

    const btn = (label, page, disabled = false, active = false) => {
      const node = document.createElement('button');
      node.type = 'button';
      node.className = `users-page-btn${disabled ? ' is-disabled' : ''}${active ? ' is-active' : ''}`;
      node.textContent = label;

      if (disabled || active) {
        node.disabled = true;
        if (disabled) node.setAttribute('aria-disabled', 'true');
        if (active) node.setAttribute('aria-current', 'page');
      } else {
        node.addEventListener('click', () => {
          if (!state.loading) loadDashboardOrders(page);
        });
      }

      return node;
    };

    const pages = [];
    if (last <= 7) {
      for (let page = 1; page <= last; page += 1) pages.push(page);
    } else {
      pages.push(1);
      if (current > 3) pages.push('...');
      for (let page = Math.max(2, current - 1); page <= Math.min(last - 1, current + 1); page += 1) pages.push(page);
      if (current < last - 2) pages.push('...');
      pages.push(last);
    }

    pageControls.appendChild(btn('Prev', current - 1, current <= 1, false));
    pages.forEach((page) => pageControls.appendChild(page === '...' ? btn('...', 0, true, false) : btn(String(page), page, false, page === current)));
    pageControls.appendChild(btn('Next', current + 1, current >= last, false));
  };

  const updateKpis = (others = {}) => {
    const ordersToday = toInt(others?.orders_today, 0);
    const delta = toInt(others?.orders_today_delta_vs_yesterday, 0);
    const deltaPrefix = delta > 0 ? '+' : '';
    const grossRevenueDisplay = text(others?.gross_revenue_display) || formatBdt(others?.gross_revenue);
    const grossRevenueChange = toNumber(others?.gross_revenue_change_this_week_percent, 0);
    const pendingDispatch = toInt(others?.pending_dispatch, 0);
    const pendingDispatchNeedAction = toInt(others?.pending_dispatch_need_action_in_2h, 0);
    const successRate = toNumber(others?.success_rate_percent, 0);
    const returnRisk = toNumber(others?.return_risk_percent, 0);
    const avgProcessingDisplay = text(others?.avg_processing_time_display) || '--';
    const totalOrders = toInt(others?.total_order, state.total);
    const pendingOrders = toInt(others?.pending_order, 0);
    const completedOrders = toInt(others?.completed_order, 0);
    const rejectedOrders = toInt(others?.rejected_order, 0);

    totalBadge.textContent = `${formatCount(totalOrders)} total`;
    ordersTodayValue.textContent = formatCount(ordersToday);
    ordersTodayMeta.textContent = `${deltaPrefix}${formatCount(delta)} vs yesterday`;
    revenueValue.textContent = grossRevenueDisplay || '--';
    revenueMeta.textContent = `${grossRevenueChange.toFixed(1)}% this week`;
    pendingDispatchValue.textContent = formatCount(pendingDispatch);
    pendingDispatchMeta.textContent = `${formatCount(pendingDispatchNeedAction)} need action in 2h`;
    successRateValue.textContent = `${successRate.toFixed(1)}%`;
    successRateMeta.textContent = `Return risk ${returnRisk.toFixed(1)}%`;

    overviewHeadline.textContent = delta >= 0
      ? `Orders are moving up by ${deltaPrefix}${formatCount(delta)} today.`
      : `Orders are down by ${formatCount(Math.abs(delta))} compared to yesterday.`;
    overviewNote.textContent = `Avg processing time is ${avgProcessingDisplay}. Keep pending dispatch queue under control.`;
    totalOrdersValue.textContent = formatCount(totalOrders);
    pendingOrdersValue.textContent = formatCount(pendingOrders);
    completedOrdersValue.textContent = formatCount(completedOrders);
    rejectedOrdersValue.textContent = formatCount(rejectedOrders);
  };

  const setLoading = (loading) => {
    state.loading = loading;
    applyBtn.disabled = loading;
    resetBtn.disabled = loading;
  };

  const updateUrlState = (page) => {
    const filters = readFilters();
    const url = new URL(window.location.href);

    if (filters.status) url.searchParams.set('status', filters.status);
    else url.searchParams.delete('status');

    if (filters.paymentType) url.searchParams.set('payment_type', filters.paymentType);
    else url.searchParams.delete('payment_type');

    if (filters.channel) url.searchParams.set('channel', filters.channel);
    else url.searchParams.delete('channel');

    if (filters.sortBy) url.searchParams.set('sort_by', filters.sortBy);
    else url.searchParams.delete('sort_by');

    if (page > 1) url.searchParams.set('page', String(page));
    else url.searchParams.delete('page');

    window.history.replaceState({}, '', url.toString());
  };

  async function loadDashboardOrders(page) {
    if (!apiBase) {
      const message = 'Backend API URL is missing.';
      resultNode.textContent = message;
      totalBadge.textContent = 'Unavailable';
      setMessage(message);
      if (typeof window.showError === 'function') window.showError(message);
      return;
    }

    const requestId = ++state.requestId;
    const filters = readFilters();
    setLoading(true);
    pageWrap.hidden = true;
    pageControls.innerHTML = '';
    pageSummary.textContent = 'Page 1 of 1';
    setMessage('Loading orders...');
    resultNode.textContent = 'Loading orders...';

    try {
      const payload = await window.API.Admin.OrderHistory.list({
        apiBaseUrl: apiBase,
        refreshToken: token || undefined,
        page,
        perPage: state.perPage,
        status: filters.status,
        paymentType: filters.paymentType,
        channel: filters.channel,
        sortBy: filters.sortBy,
        timeoutMs: 12000,
      });
      if (requestId !== state.requestId) return;

      const ordersPayload = payload?.orders && typeof payload.orders === 'object' ? payload.orders : {};
      const list = Array.isArray(ordersPayload?.data) ? ordersPayload.data : [];
      const pagination = ordersPayload?.pagination_info && typeof ordersPayload.pagination_info === 'object'
        ? ordersPayload.pagination_info
        : {};
      const others = payload?.others_data && typeof payload.others_data === 'object' ? payload.others_data : {};

      const total = Math.max(0, toInt(pagination.total, list.length));
      const perPageFromApi = Math.max(1, toInt(pagination.per_page, state.perPage));
      const derivedLastPage = Math.max(1, toInt(pagination.last_page, Math.ceil(total / perPageFromApi)));

      state.orders = list;
      state.total = total;
      state.perPage = perPageFromApi;
      state.lastPage = derivedLastPage;
      state.page = Math.min(Math.max(1, toInt(pagination.current_page, page)), state.lastPage);
      state.from = Math.max(0, toInt(pagination.from, list.length ? ((state.page - 1) * state.perPage) + 1 : 0));
      state.to = Math.max(0, toInt(pagination.to, state.from ? state.from + list.length - 1 : 0));

      updateKpis(others);
      renderTable();
      renderPagination();
      updateUrlState(state.page);
    } catch (error) {
      if (requestId !== state.requestId) return;

      const message = error?.isTimeout
        ? 'Request timed out. Please try again.'
        : (error?.message || 'Failed to load dashboard orders.');

      state.orders = [];
      state.total = 0;
      state.from = 0;
      state.to = 0;
      state.lastPage = 1;
      totalBadge.textContent = 'Unavailable';
      resultNode.textContent = message;
      setMessage(message);
      pageControls.innerHTML = '';
      pageSummary.textContent = 'Page 1 of 1';
      pageWrap.hidden = true;
      updateKpis({});
      if (typeof window.showError === 'function') window.showError(message);
    } finally {
      if (requestId === state.requestId) {
        setLoading(false);
      }
    }
  }

  applyBtn.addEventListener('click', () => {
    if (state.loading) return;
    loadDashboardOrders(1);
  });

  resetBtn.addEventListener('click', () => {
    statusSelect.value = 'all';
    paymentTypeSelect.value = 'all';
    channelSelect.value = 'all';
    sortBySelect.value = 'newest_first';
    if (state.loading) return;
    loadDashboardOrders(1);
  });

  loadDashboardOrders(state.page);
}

function initPackagesPage() {
  const section = document.querySelector('[data-packages-page]');
  if (!section) return;

  const apiBaseUrl = String(section.dataset.apiBaseUrl || '').replace(/\/+$/, '');
  const reloadButtons = Array.from(section.querySelectorAll('[data-packages-reload]'));
  const cycleButtons = Array.from(section.querySelectorAll('[data-package-cycle-tab]'));
  const gridNode = section.querySelector('[data-packages-grid]');
  const introCopyNode = section.querySelector('[data-packages-intro-copy]');

  const summaryCard = section.querySelector('[data-packages-summary-card]');
  const currentTitleNode = section.querySelector('[data-packages-current-title]');
  const currentDescriptionNode = section.querySelector('[data-packages-current-description]');
  const currentStatusNode = section.querySelector('[data-packages-current-status]');
  const currentPriceNode = section.querySelector('[data-packages-current-price]');
  const currentCycleNode = section.querySelector('[data-packages-current-cycle]');
  const currentPriceNoteNode = section.querySelector('[data-packages-current-price-note]');
  const summaryMessageNode = section.querySelector('[data-packages-summary-message]');
  const metaNameNode = section.querySelector('[data-packages-meta-name]');
  const metaActivatedNode = section.querySelector('[data-packages-meta-activated]');
  const metaExpiresNode = section.querySelector('[data-packages-meta-expires]');
  const metaCycleNode = section.querySelector('[data-packages-meta-cycle]');

  const insightsCard = section.querySelector('[data-packages-insights-card]');
  const insightsTitleNode = section.querySelector('[data-packages-insights-title]');
  const insightsCopyNode = section.querySelector('[data-packages-insights-copy]');
  const insightsProductsNode = section.querySelector('[data-packages-insight-products]');
  const insightsCallsNode = section.querySelector('[data-packages-insight-calls]');
  const insightsSmsNode = section.querySelector('[data-packages-insight-sms]');
  const insightsModulesNode = section.querySelector('[data-packages-insight-modules]');
  const insightsMessageNode = section.querySelector('[data-packages-insights-message]');

  const purchaseModal = document.getElementById('packagePurchaseModal');
  const purchaseCloseButtons = Array.from(document.querySelectorAll('[data-package-purchase-close]'));
  const purchaseNameInput = purchaseModal?.querySelector('[data-package-purchase-name]');
  const purchasePackageIdInput = purchaseModal?.querySelector('[data-package-purchase-package-id]');
  const purchaseValiditySelect = purchaseModal?.querySelector('[data-package-purchase-validity]');
  const purchaseMethodSelect = purchaseModal?.querySelector('[data-package-purchase-method]');
  const purchaseNumberInput = purchaseModal?.querySelector('[data-package-purchase-number]');
  const purchaseSubmitButton = purchaseModal?.querySelector('[data-package-purchase-submit]');
  const purchaseMessageNode = purchaseModal?.querySelector('[data-package-purchase-message]');
  const purchaseResultWrap = purchaseModal?.querySelector('[data-package-purchase-result]');
  const purchaseReferenceNode = purchaseModal?.querySelector('[data-package-purchase-reference-label]');
  const purchaseResultCopyNode = purchaseModal?.querySelector('[data-package-purchase-result-copy]');
  const purchaseChecklistNode = purchaseModal?.querySelector('[data-package-purchase-checklist]');

  if (
    !gridNode ||
    !introCopyNode ||
    !currentTitleNode ||
    !currentDescriptionNode ||
    !currentStatusNode ||
    !currentPriceNode ||
    !currentCycleNode ||
    !currentPriceNoteNode ||
    !summaryMessageNode ||
    !metaNameNode ||
    !metaActivatedNode ||
    !metaExpiresNode ||
    !metaCycleNode ||
    !insightsTitleNode ||
    !insightsCopyNode ||
    !insightsProductsNode ||
    !insightsCallsNode ||
    !insightsSmsNode ||
    !insightsModulesNode ||
    !insightsMessageNode
  ) {
    return;
  }

  const text = (value) => String(value ?? '').trim();
  const escapeHtml = (value) => text(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
  const formatCount = (value) => {
    const parsed = Number(value);
    const normalized = Number.isFinite(parsed) ? Math.max(0, Math.round(parsed)) : 0;
    return normalized.toLocaleString('en-BD');
  };
  const toDate = (value) => {
    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? null : date;
  };
  const formatDateSafe = (value) => {
    const date = toDate(value);
    return date ? formatDate(date) : '--';
  };
  const titleCase = (value) => text(value)
    .toLowerCase()
    .split(/[\s_-]+/g)
    .filter(Boolean)
    .map((chunk) => chunk.charAt(0).toUpperCase() + chunk.slice(1))
    .join(' ');
  const normalizeCycle = (value) => {
    const normalized = text(value).toLowerCase();
    if (normalized === 'yearly' || normalized === 'annual') return 'yearly';
    if (normalized === 'quarterly' || normalized === 'quarter') return 'quarterly';
    return 'monthly';
  };
  const cycleMonths = {
    monthly: 1,
    quarterly: 3,
    yearly: 12,
  };
  const cycleLabel = (value) => {
    const normalized = normalizeCycle(value);
    if (normalized === 'yearly') return 'Yearly';
    if (normalized === 'quarterly') return 'Quarterly';
    return 'Monthly';
  };
  const cycleBillingLabel = (value) => {
    const normalized = normalizeCycle(value);
    if (normalized === 'yearly') return 'Billed yearly';
    if (normalized === 'quarterly') return 'Billed quarterly';
    return 'Billed monthly';
  };
  const cycleUnitLabel = (value) => {
    const normalized = normalizeCycle(value);
    if (normalized === 'yearly') return '/year';
    if (normalized === 'quarterly') return '/quarter';
    return '/month';
  };
  const trialDays = (pkg) => {
    const parsed = Number(pkg?.trial_days);
    return Number.isFinite(parsed) ? Math.max(0, Math.round(parsed)) : 0;
  };
  const isTrialPackage = (pkg) => trialDays(pkg) > 0;
  const accentForPackage = (pkg, index = 0) => {
    const normalizedName = text(pkg?.package_name).toLowerCase();
    if (isTrialPackage(pkg) || normalizedName.includes('free')) return 'starter';
    if (normalizedName.includes('starter')) return 'starter';
    if (normalizedName.includes('growth')) return 'growth';
    if (normalizedName.includes('scale')) return 'scale';
    return ['starter', 'growth', 'scale'][index % 3];
  };
  const resolveRefreshToken = () => text(
    window.API?.getToken?.()
    || (typeof window.getToken === 'function' ? window.getToken() : '')
  );
  const resolveRequestError = (error, fallbackMessage) => {
    if (error?.isTimeout) return 'Request timed out. Please try again.';
    if (error?.status === 401) {
      return 'Unauthorized (401). Server-provided refresh token is invalid or expired. Please re-login.';
    }
    return text(error?.message) || fallbackMessage;
  };
  const setBadgeState = (node, label, tone = 'info') => {
    if (!(node instanceof HTMLElement)) return;
    node.textContent = text(label);
    node.classList.remove('is-paid', 'is-unpaid', 'is-warning', 'is-info');
    node.classList.add(
      tone === 'paid'
        ? 'is-paid'
        : tone === 'unpaid'
          ? 'is-unpaid'
          : tone === 'warning'
            ? 'is-warning'
            : 'is-info'
    );
  };
  const setMessageState = (node, message, tone = 'info') => {
    if (!(node instanceof HTMLElement)) return;
    node.textContent = text(message);
    node.classList.remove('is-info', 'is-success', 'is-warning', 'is-danger');
    node.classList.add(
      tone === 'success'
        ? 'is-success'
        : tone === 'warning'
          ? 'is-warning'
          : tone === 'danger'
            ? 'is-danger'
            : 'is-info'
    );
  };
  const openPurchaseModal = () => {
    if (!(purchaseModal instanceof HTMLElement)) return;
    purchaseModal.classList.add('active');
    purchaseModal.setAttribute('aria-hidden', 'false');
  };
  const closePurchaseModal = () => {
    if (!(purchaseModal instanceof HTMLElement)) return;
    purchaseModal.classList.remove('active');
    purchaseModal.setAttribute('aria-hidden', 'true');
  };
  const resetPurchaseResult = () => {
    if (purchaseResultWrap instanceof HTMLElement) purchaseResultWrap.hidden = true;
    if (purchaseReferenceNode instanceof HTMLElement) purchaseReferenceNode.textContent = 'Reference: -----';
    if (purchaseResultCopyNode instanceof HTMLElement) {
      purchaseResultCopyNode.textContent = 'Payment instructions will appear here after the request is created.';
    }
    if (purchaseChecklistNode instanceof HTMLElement) purchaseChecklistNode.innerHTML = '';
    setMessageState(purchaseMessageNode, 'We will create a unique 5-digit reference for this package purchase request.', 'info');
  };
  const setPurchaseSubmitState = (loading) => {
    if (!(purchaseSubmitButton instanceof HTMLButtonElement)) return;
    if (!purchaseSubmitButton.dataset.defaultLabel) {
      purchaseSubmitButton.dataset.defaultLabel = text(purchaseSubmitButton.textContent) || 'Create Payment Request';
    }
    purchaseSubmitButton.disabled = loading;
    purchaseSubmitButton.textContent = loading ? 'Creating...' : purchaseSubmitButton.dataset.defaultLabel;
  };
  const getVariant = (pkg, cycle) => {
    const variants = Array.isArray(pkg?.variant_prices) ? pkg.variant_prices : [];
    const normalizedCycle = normalizeCycle(cycle);
    return variants.find((variant) => normalizeCycle(variant?.validity) === normalizedCycle) || variants[0] || null;
  };
  const resolvePricing = (pkg, cycle) => {
    if (isTrialPackage(pkg)) {
      const days = trialDays(pkg);
      return {
        basePrice: 0,
        finalPrice: 0,
        hasPrice: true,
        note: `${days}-day free trial`,
        unitLabel: '/trial',
        billingLabel: `${days}-day trial`,
        priceLabel: 'Free',
      };
    }

    const variant = getVariant(pkg, cycle);
    const normalizedCycle = normalizeCycle(cycle);
    const basePrice = Number(variant?.price);
    const discountType = text(variant?.discount_type).toLowerCase();
    const discountValue = Number(variant?.discount_value);
    const hasBasePrice = Number.isFinite(basePrice) && basePrice >= 0;
    let finalPrice = hasBasePrice ? basePrice : NaN;
    let savings = 0;
    let discountLabel = '';

    if (hasBasePrice && Number.isFinite(discountValue) && discountValue > 0) {
      if (discountType === 'fixed') {
        savings = Math.min(basePrice, discountValue);
        finalPrice = Math.max(0, basePrice - savings);
        discountLabel = `Save ${formatCurrency(savings)}`;
      } else if (discountType === 'percentage') {
        savings = Math.max(0, (basePrice * discountValue) / 100);
        finalPrice = Math.max(0, basePrice - savings);
        discountLabel = `Save ${discountValue}%`;
      }
    }

    const averagePerMonth = Number.isFinite(finalPrice)
      ? finalPrice / (cycleMonths[normalizedCycle] || 1)
      : NaN;
    const noteParts = [];
    if (discountLabel && hasBasePrice) {
      noteParts.push(`Regular ${formatCurrency(basePrice)}`);
      noteParts.push(discountLabel);
    }
    if (Number.isFinite(averagePerMonth) && (cycleMonths[normalizedCycle] || 1) > 1) {
      noteParts.push(`Avg ${formatCurrency(averagePerMonth)}/mo`);
    }

    return {
      basePrice,
      finalPrice,
      hasPrice: Number.isFinite(finalPrice),
      note: noteParts.join(' • '),
      unitLabel: cycleUnitLabel(normalizedCycle),
      billingLabel: cycleBillingLabel(normalizedCycle),
      priceLabel: '',
    };
  };
  const capabilityLabels = [
    ['is_bargain', 'Bargaining'],
    ['is_campaign', 'Campaigns'],
    ['is_courier', 'Courier'],
    ['is_process_voice', 'Voice'],
    ['is_process_image', 'Image'],
    ['is_live_compititor', 'Competitor'],
    ['is_custom_getway', 'Custom Gateway'],
    ['is_domain', 'Domain'],
    ['is_wordpress', 'WordPress'],
  ];
  const enabledCapabilities = (pkg) => capabilityLabels
    .filter(([key]) => Boolean(pkg?.[key]))
    .map(([, label]) => label);
  const resolvePackageStatus = (pkg) => {
    const expiresAt = toDate(pkg?.expired_in);
    if (!expiresAt) {
      return {
        label: 'Active',
        tone: 'paid',
        copy: 'Package info loaded successfully.',
      };
    }

    const diffMs = expiresAt.getTime() - Date.now();
    const daysLeft = Math.ceil(diffMs / 86400000);
    if (daysLeft < 0) {
      return {
        label: 'Expired',
        tone: 'unpaid',
        copy: `Expired on ${formatDate(expiresAt)}.`,
      };
    }

    if (daysLeft <= 7) {
      return {
        label: 'Renew Soon',
        tone: 'warning',
        copy: `${daysLeft} day${daysLeft === 1 ? '' : 's'} left before expiry.`,
      };
    }

    return {
      label: 'Active',
      tone: 'paid',
      copy: `${daysLeft} day${daysLeft === 1 ? '' : 's'} remaining in the current cycle.`,
    };
  };
  const buildIntroCopy = () => {
    const packageCount = Array.isArray(allPackages) ? allPackages.length : 0;
    const parts = [`Showing ${cycleLabel(activeCycle).toLowerCase()} pricing for ${packageCount} available package${packageCount === 1 ? '' : 's'}.`];
    if (currentPackage) {
      parts.push(
        isTrialPackage(currentPackage)
          ? `Your active package is running on a ${trialDays(currentPackage)}-day trial.`
          : `Your active package runs on a ${cycleLabel(currentPackage.package_type).toLowerCase()} cycle.`
      );
    }
    return parts.join(' ');
  };
  const syncCycleButtons = () => {
    cycleButtons.forEach((button) => {
      if (!(button instanceof HTMLButtonElement)) return;
      const isActive = normalizeCycle(button.dataset.packageCycleTab) === activeCycle;
      button.classList.toggle('is-active', isActive);
      button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
  };
  const setCycleButtonsDisabled = (disabled) => {
    cycleButtons.forEach((button) => {
      if (!(button instanceof HTMLButtonElement)) return;
      button.disabled = disabled;
    });
  };
  const setReloadState = (loading) => {
    reloadButtons.forEach((button) => {
      if (!(button instanceof HTMLButtonElement)) return;
      if (!button.dataset.defaultLabel) {
        button.dataset.defaultLabel = text(button.textContent) || 'Refresh Data';
      }
      button.disabled = loading;
      button.textContent = loading ? 'Refreshing...' : button.dataset.defaultLabel;
    });
  };
  const buildSkeletonCardsHtml = (count = 3) => Array.from({length: count}).map(() => `
    <article class="billing-package-card is-skeleton" aria-hidden="true">
      <div class="billing-package-head">
        <span class="billing-package-badge billing-skeleton billing-skeleton-block"></span>
        <div class="billing-skeleton billing-skeleton-block is-title"></div>
        <div class="billing-skeleton billing-skeleton-block is-copy"></div>
        <div class="billing-skeleton billing-skeleton-block is-copy"></div>
      </div>
      <div class="billing-package-price-stack">
        <div class="billing-package-price">
          <strong class="billing-skeleton billing-skeleton-block is-price"></strong>
          <span class="billing-skeleton billing-skeleton-block is-inline"></span>
        </div>
        <div class="billing-skeleton billing-skeleton-block is-copy"></div>
      </div>
      <div class="billing-package-credit-grid">
        <article class="billing-package-credit">
          <span class="billing-skeleton billing-skeleton-block is-copy"></span>
          <strong class="billing-skeleton billing-skeleton-block is-copy"></strong>
        </article>
        <article class="billing-package-credit">
          <span class="billing-skeleton billing-skeleton-block is-copy"></span>
          <strong class="billing-skeleton billing-skeleton-block is-copy"></strong>
        </article>
        <article class="billing-package-credit">
          <span class="billing-skeleton billing-skeleton-block is-copy"></span>
          <strong class="billing-skeleton billing-skeleton-block is-copy"></strong>
        </article>
      </div>
      <div class="billing-package-capabilities">
        <span class="billing-package-capability billing-skeleton billing-skeleton-block is-chip"></span>
        <span class="billing-package-capability billing-skeleton billing-skeleton-block is-chip"></span>
        <span class="billing-package-capability billing-skeleton billing-skeleton-block is-chip"></span>
      </div>
      <ul class="billing-package-features">
        <li><span class="billing-skeleton billing-skeleton-block is-copy"></span></li>
        <li><span class="billing-skeleton billing-skeleton-block is-copy"></span></li>
        <li><span class="billing-skeleton billing-skeleton-block is-copy"></span></li>
        <li><span class="billing-skeleton billing-skeleton-block is-copy"></span></li>
      </ul>
      <div class="billing-package-footer">
        <span class="billing-skeleton billing-skeleton-block is-button"></span>
      </div>
    </article>
  `).join('');
  const buildEmptyStateHtml = (title, copy, tone = 'info') => `
    <div class="billing-empty-state billing-empty-state-${escapeHtml(tone)}">
      <strong>${escapeHtml(title)}</strong>
      <p>${escapeHtml(copy)}</p>
    </div>
  `;
  const renderSummaryLoading = () => {
    if (summaryCard instanceof HTMLElement) summaryCard.setAttribute('aria-busy', 'true');
    if (insightsCard instanceof HTMLElement) insightsCard.setAttribute('aria-busy', 'true');
    currentTitleNode.textContent = 'Loading current package...';
    currentDescriptionNode.textContent = 'Fetching your active package details from the live package API.';
    currentPriceNode.textContent = '--';
    currentCycleNode.textContent = 'Checking active billing cycle...';
    currentPriceNoteNode.textContent = 'Preparing live pricing details...';
    metaNameNode.textContent = '--';
    metaActivatedNode.textContent = '--';
    metaExpiresNode.textContent = '--';
    metaCycleNode.textContent = '--';
    insightsTitleNode.textContent = 'Loading package snapshot...';
    insightsCopyNode.textContent = 'Reading credits, enabled modules, and validity information.';
    insightsProductsNode.textContent = '--';
    insightsCallsNode.textContent = '--';
    insightsSmsNode.textContent = '--';
    insightsModulesNode.textContent = '--';
    setBadgeState(currentStatusNode, 'Loading', 'info');
    setMessageState(summaryMessageNode, 'Live package details will appear here after the API response arrives.', 'info');
    setMessageState(insightsMessageNode, 'Waiting for the active package snapshot.', 'info');
  };
  const renderSummaryError = (message) => {
    if (summaryCard instanceof HTMLElement) summaryCard.setAttribute('aria-busy', 'false');
    if (insightsCard instanceof HTMLElement) insightsCard.setAttribute('aria-busy', 'false');
    currentTitleNode.textContent = 'Package info unavailable';
    currentDescriptionNode.textContent = text(message);
    currentPriceNode.textContent = '--';
    currentCycleNode.textContent = 'Unable to load active billing cycle';
    currentPriceNoteNode.textContent = 'Check the API connection and refresh token.';
    metaNameNode.textContent = '--';
    metaActivatedNode.textContent = '--';
    metaExpiresNode.textContent = '--';
    metaCycleNode.textContent = '--';
    insightsTitleNode.textContent = 'Current snapshot unavailable';
    insightsCopyNode.textContent = 'The package info endpoint did not return the active package details.';
    insightsProductsNode.textContent = '--';
    insightsCallsNode.textContent = '--';
    insightsSmsNode.textContent = '--';
    insightsModulesNode.textContent = '--';
    setBadgeState(currentStatusNode, 'Load Failed', 'unpaid');
    setMessageState(summaryMessageNode, message, 'danger');
    setMessageState(insightsMessageNode, message, 'danger');
  };
  const renderCurrentPackage = (pkg) => {
    if (!pkg || typeof pkg !== 'object') {
      renderSummaryError('Active package data is not available.');
      return;
    }

    const normalizedCycle = normalizeCycle(pkg.package_type);
    const pricing = resolvePricing(pkg, normalizedCycle);
    const status = resolvePackageStatus(pkg);
    const activeModules = enabledCapabilities(pkg);
    const activatedText = formatDateSafe(pkg.activated_in);
    const expiresText = formatDateSafe(pkg.expired_in);

    if (summaryCard instanceof HTMLElement) summaryCard.setAttribute('aria-busy', 'false');
    if (insightsCard instanceof HTMLElement) insightsCard.setAttribute('aria-busy', 'false');

    currentTitleNode.textContent = `${text(pkg.package_name) || 'Unknown'} Package`;
    currentDescriptionNode.textContent = text(pkg.short_description) || 'No package description provided by the API.';
    currentPriceNode.textContent = pricing.hasPrice ? (pricing.priceLabel || formatCurrency(pricing.finalPrice)) : '--';
    currentCycleNode.textContent = pricing.billingLabel;
    currentPriceNoteNode.textContent = pricing.note || 'Live pricing is based on your current active billing cycle.';
    metaNameNode.textContent = text(pkg.package_name) || '--';
    metaActivatedNode.textContent = activatedText;
    metaExpiresNode.textContent = expiresText;
    metaCycleNode.textContent = isTrialPackage(pkg) ? 'Trial' : cycleLabel(normalizedCycle);

    insightsTitleNode.textContent = `${text(pkg.package_name) || 'Current'} package snapshot`;
    insightsCopyNode.textContent = `${status.copy} Activated on ${activatedText} and valid until ${expiresText}.`;
    insightsProductsNode.textContent = `${formatCount(pkg.products_credits)} products`;
    insightsCallsNode.textContent = `${formatCount(pkg.calling_credits)} calls`;
    insightsSmsNode.textContent = `${formatCount(pkg.sms_credits)} SMS`;
    insightsModulesNode.textContent = `${activeModules.length} module${activeModules.length === 1 ? '' : 's'}`;

    setBadgeState(currentStatusNode, status.label, status.tone);
    setMessageState(
      summaryMessageNode,
      `${status.copy} Current billing cycle: ${isTrialPackage(pkg) ? `${trialDays(pkg)}-day trial` : cycleLabel(normalizedCycle)}.`,
      status.tone === 'paid' ? 'success' : status.tone === 'warning' ? 'warning' : 'danger'
    );
    setMessageState(
      insightsMessageNode,
      activeModules.length
        ? `Enabled modules: ${activeModules.join(', ')}.`
        : 'This package currently exposes only the base toolkit modules.',
      'info'
    );
  };
  const renderCatalogLoading = () => {
    gridNode.setAttribute('aria-busy', 'true');
    gridNode.innerHTML = buildSkeletonCardsHtml(3);
    introCopyNode.textContent = 'Loading package catalog and billing-cycle pricing...';
  };
  const renderCatalogError = (message) => {
    gridNode.setAttribute('aria-busy', 'false');
    gridNode.innerHTML = buildEmptyStateHtml('Unable to load packages', message, 'danger');
    introCopyNode.textContent = 'Package catalog could not be loaded from the API.';
  };
  const buildPackageCardHtml = (pkg, index) => {
    const isCurrent = Number(pkg?.package_id) === Number(currentPackage?.package_id);
    const pricing = resolvePricing(pkg, activeCycle);
    const features = Array.isArray(pkg?.features) ? pkg.features.filter((feature) => text(feature)) : [];
    const activeModules = enabledCapabilities(pkg);
    const isTrial = isTrialPackage(pkg);
    const badgeLabel = isCurrent
      ? 'Current package'
      : isTrial
        ? 'Trial'
      : index === 0
        ? 'Start here'
        : index === 1
          ? 'Popular'
          : 'Advanced';
    const buttonLabel = isCurrent
      ? 'Current Package'
      : isTrial
        ? 'Start Trial'
        : `Upgrade to ${text(pkg?.package_name) || 'Package'}`;

    return `
      <article class="billing-package-card ${isCurrent ? 'is-featured is-current' : ''} billing-package-card-${escapeHtml(accentForPackage(pkg, index))}">
        <div class="billing-package-head">
          <span class="billing-package-badge">${escapeHtml(badgeLabel)}</span>
          <h3>${escapeHtml(text(pkg?.package_name) || 'Unnamed Package')}</h3>
          <p>${escapeHtml(text(pkg?.short_description) || 'No description available.')}</p>
        </div>

        <div class="billing-package-price-stack">
          <div class="billing-package-price">
            <strong>${pricing.hasPrice ? escapeHtml(pricing.priceLabel || formatCurrency(pricing.finalPrice)) : '--'}</strong>
            <span>${escapeHtml(pricing.unitLabel)}</span>
          </div>
          <div class="billing-package-price-note">
            ${escapeHtml(pricing.note || `${cycleBillingLabel(activeCycle)} pricing loaded from API.`)}
          </div>
        </div>

        <div class="billing-package-credit-grid">
          <article class="billing-package-credit">
            <span>Products</span>
            <strong>${escapeHtml(formatCount(pkg?.products_credits))}</strong>
          </article>
          <article class="billing-package-credit">
            <span>Calls</span>
            <strong>${escapeHtml(formatCount(pkg?.calling_credits))}</strong>
          </article>
          <article class="billing-package-credit">
            <span>SMS</span>
            <strong>${escapeHtml(formatCount(pkg?.sms_credits))}</strong>
          </article>
        </div>

        <div class="billing-package-capabilities">
          ${
            activeModules.length
              ? activeModules.map((label) => `<span class="billing-package-capability">${escapeHtml(label)}</span>`).join('')
              : '<span class="billing-package-capability is-empty">Base toolkit</span>'
          }
        </div>

        <ul class="billing-package-features">
          ${
            features.length
              ? features.map((feature) => `<li>${escapeHtml(feature)}</li>`).join('')
              : '<li>No features were returned by the API.</li>'
          }
        </ul>

        <div class="billing-package-footer">
          <button
            type="button"
            class="btn ${isCurrent ? 'btn-secondary' : 'btn-primary'}"
            ${isCurrent ? 'disabled' : ''}
            data-package-upgrade="${escapeHtml(text(pkg?.package_name) || '')}"
            data-package-id="${escapeHtml(String(pkg?.package_id ?? ''))}"
            data-package-cycle="${escapeHtml(activeCycle)}"
          >
            ${escapeHtml(buttonLabel)}
          </button>
        </div>
      </article>
    `;
  };
  const renderCatalog = () => {
    gridNode.setAttribute('aria-busy', 'false');

    if (!Array.isArray(allPackages) || !allPackages.length) {
      gridNode.innerHTML = buildEmptyStateHtml('No packages available', 'The package catalog API returned an empty list.', 'info');
      introCopyNode.textContent = 'No package plans were returned from the catalog API.';
      return;
    }

    introCopyNode.textContent = buildIntroCopy();
    gridNode.innerHTML = allPackages.map((pkg, index) => buildPackageCardHtml(pkg, index)).join('');
  };
  const openPurchaseFlow = (pkg, cycle = activeCycle) => {
    if (!(purchaseModal instanceof HTMLElement) || !pkg) return;

    selectedPurchasePackage = pkg;
    if (purchaseNameInput instanceof HTMLInputElement) {
      purchaseNameInput.value = text(pkg?.package_name) || 'Selected package';
    }
    if (purchasePackageIdInput instanceof HTMLInputElement) {
      purchasePackageIdInput.value = String(pkg?.package_id ?? '');
    }
    if (purchaseValiditySelect instanceof HTMLSelectElement) {
      purchaseValiditySelect.value = normalizeCycle(cycle);
    }
    if (purchaseMethodSelect instanceof HTMLSelectElement && !purchaseMethodSelect.value) {
      purchaseMethodSelect.value = 'bkash';
    }
    resetPurchaseResult();
    openPurchaseModal();
    if (purchaseNumberInput instanceof HTMLInputElement) {
      window.setTimeout(() => purchaseNumberInput.focus(), 40);
    }
  };
  const submitPurchaseRequest = async () => {
    if (!window.API?.Admin?.Packages?.createPurchaseRequest) {
      setMessageState(purchaseMessageNode, 'Package purchase API client is unavailable.', 'danger');
      return;
    }

    const packageID = Number(purchasePackageIdInput instanceof HTMLInputElement ? purchasePackageIdInput.value : 0);
    const validity = purchaseValiditySelect instanceof HTMLSelectElement ? normalizeCycle(purchaseValiditySelect.value) : '';
    const paymentMethod = purchaseMethodSelect instanceof HTMLSelectElement ? text(purchaseMethodSelect.value).toLowerCase() : '';
    const customerNumber = purchaseNumberInput instanceof HTMLInputElement ? text(purchaseNumberInput.value) : '';

    if (!packageID || !validity || !paymentMethod || !customerNumber) {
      setMessageState(purchaseMessageNode, 'Package, billing cycle, payment method, and your payment number are required.', 'danger');
      return;
    }

    const refreshToken = resolveRefreshToken();
    if (!refreshToken) {
      setMessageState(purchaseMessageNode, 'Missing refresh token. Please login again.', 'danger');
      return;
    }

    setPurchaseSubmitState(true);
    setMessageState(purchaseMessageNode, 'Creating your payment request...', 'info');

    try {
      const response = await window.API.Admin.Packages.createPurchaseRequest({
        apiBaseUrl,
        refreshToken,
        payload: {
          package_id: packageID,
          validity,
          payment_method: paymentMethod,
          customer_number: customerNumber,
        },
        timeoutMs: 12000,
      });

      const request = response?.request || {};
      const method = response?.method || {};
      const checklist = Array.isArray(response?.checklist) ? response.checklist : [];
      if (purchaseResultWrap instanceof HTMLElement) purchaseResultWrap.hidden = false;
      if (purchaseReferenceNode instanceof HTMLElement) {
        purchaseReferenceNode.textContent = `Reference: ${text(request.reference_code) || '-----'}`;
      }
      if (purchaseResultCopyNode instanceof HTMLElement) {
        purchaseResultCopyNode.textContent = text(response?.message)
          || `Send the payment to ${text(method.account_number) || 'the configured payment number'} and use the reference code shown above.`;
      }
      if (purchaseChecklistNode instanceof HTMLElement) {
        purchaseChecklistNode.innerHTML = [
          `Receiver number: ${escapeHtml(text(method.account_number) || 'Not configured yet')}`,
          `Payment method: ${escapeHtml(text(method.label) || text(paymentMethod))}`,
          `Request amount: ${escapeHtml(formatCurrency(request.amount || 0))}`,
          ...checklist.map((item) => escapeHtml(text(item))),
          text(method.instructions) ? escapeHtml(text(method.instructions)) : '',
        ].filter(Boolean).map((item) => `<li>${item}</li>`).join('');
      }
      setMessageState(purchaseMessageNode, 'Payment request created. Use the reference exactly as shown before sending money.', 'success');
      if (typeof window.showSuccess === 'function') {
        window.showSuccess('Payment request created successfully.');
      }
    } catch (error) {
      const message = resolveRequestError(error, 'Unable to create package payment request.');
      setMessageState(purchaseMessageNode, message, 'danger');
      if (typeof window.showError === 'function') window.showError(message);
    } finally {
      setPurchaseSubmitState(false);
    }
  };

  let allPackages = [];
  let currentPackage = null;
  let activeCycle = 'monthly';
  let selectedPurchasePackage = null;

  const loadPackages = async () => {
    if (!apiBaseUrl) {
      const message = 'Missing backend API URL.';
      renderSummaryError(message);
      renderCatalogError(message);
      if (typeof window.showError === 'function') window.showError(message);
      return;
    }

    if (!window.API?.Admin?.Packages?.listAll || !window.API?.Admin?.Packages?.getInfo) {
      const message = 'Package API client is unavailable.';
      renderSummaryError(message);
      renderCatalogError(message);
      if (typeof window.showError === 'function') window.showError(message);
      return;
    }

    const refreshToken = resolveRefreshToken();
    if (!refreshToken) {
      const message = 'Missing refresh token. Please login again.';
      renderSummaryError(message);
      renderCatalogError(message);
      if (typeof window.showError === 'function') window.showError(message);
      return;
    }

    renderSummaryLoading();
    renderCatalogLoading();
    setReloadState(true);
    setCycleButtonsDisabled(true);

    const [catalogResult, currentResult] = await Promise.allSettled([
      window.API.Admin.Packages.listAll({
        apiBaseUrl,
        refreshToken,
        timeoutMs: 12000,
      }),
      window.API.Admin.Packages.getInfo({
        apiBaseUrl,
        refreshToken,
        timeoutMs: 12000,
      }),
    ]);

    setReloadState(false);
    setCycleButtonsDisabled(false);

    const errors = [];

    if (currentResult.status === 'fulfilled' && currentResult.value && typeof currentResult.value === 'object') {
      currentPackage = currentResult.value;
      activeCycle = normalizeCycle(currentPackage.package_type || activeCycle);
      syncCycleButtons();
      renderCurrentPackage(currentPackage);
    } else {
      currentPackage = null;
      const message = resolveRequestError(
        currentResult.reason,
        'Unable to load active package details.'
      );
      renderSummaryError(message);
      errors.push(message);
    }

    if (catalogResult.status === 'fulfilled' && Array.isArray(catalogResult.value)) {
      allPackages = catalogResult.value;
      renderCatalog();
    } else {
      allPackages = [];
      const message = resolveRequestError(
        catalogResult.reason,
        'Unable to load package catalog.'
      );
      renderCatalogError(message);
      errors.push(message);
    }

    if (errors.length === 1) {
      if (typeof window.showError === 'function') window.showError(errors[0]);
    } else if (errors.length > 1) {
      if (typeof window.showError === 'function') {
        window.showError('Some package data could not be loaded. Please refresh and try again.');
      }
    }
  };

  cycleButtons.forEach((button) => {
    if (!(button instanceof HTMLButtonElement)) return;
    button.addEventListener('click', () => {
      const nextCycle = normalizeCycle(button.dataset.packageCycleTab);
      if (nextCycle === activeCycle) return;
      activeCycle = nextCycle;
      syncCycleButtons();
      renderCatalog();
    });
  });

  reloadButtons.forEach((button) => {
    if (!(button instanceof HTMLButtonElement)) return;
    button.addEventListener('click', () => {
      void loadPackages();
    });
  });

  gridNode.addEventListener('click', (event) => {
    const button = event.target.closest('[data-package-upgrade]');
    if (!(button instanceof HTMLButtonElement) || button.disabled) return;

    const packageID = Number(button.dataset.packageId || 0);
    const pkg = allPackages.find((item) => Number(item?.package_id) === packageID);
    openPurchaseFlow(pkg, button.dataset.packageCycle || activeCycle);
  });

  purchaseCloseButtons.forEach((button) => {
    if (!(button instanceof HTMLButtonElement)) return;
    button.addEventListener('click', closePurchaseModal);
  });

  if (purchaseModal instanceof HTMLElement) {
    purchaseModal.addEventListener('click', (event) => {
      if (event.target === purchaseModal) {
        closePurchaseModal();
      }
    });
  }

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && purchaseModal instanceof HTMLElement && purchaseModal.classList.contains('active')) {
      closePurchaseModal();
    }
  });

  if (purchaseSubmitButton instanceof HTMLButtonElement) {
    purchaseSubmitButton.addEventListener('click', () => {
      void submitPurchaseRequest();
    });
  }

  syncCycleButtons();
  setCycleButtonsDisabled(true);
  renderSummaryLoading();
  renderCatalogLoading();
  void loadPackages();
}

// ══════════════════════════════════════════
// PRODUCTS: NEEDS ATTENTION DRAWER
// ══════════════════════════════════════════
function initProductsCatalogPage() {
  const section = document.getElementById('productsCatalogSection');
  if (!section) return;

  const kpiGrid = document.querySelector('[data-products-kpi-grid]');
  const tbody = section.querySelector('[data-products-tbody]');
  const totalBadge = section.querySelector('[data-products-total-badge]');
  const resultNode = section.querySelector('[data-products-result]');
  const pageWrap = section.querySelector('[data-products-pagination-wrap]');
  const pageSummary = section.querySelector('[data-products-pagination-summary]');
  const pageControls = section.querySelector('[data-products-pagination-controls]');
  const searchInput = section.querySelector('[data-products-search]');
  const typeSelect = section.querySelector('[data-products-type]');
  const categorySelect = section.querySelector('[data-products-category]');
  const statusSelect = section.querySelector('[data-products-status]');
  const sortSelect = section.querySelector('[data-products-sort]');
  const importTrigger = document.querySelector('[data-products-import-trigger]');
  const importInput = document.querySelector('[data-products-import-input]');
  const applyBtn = section.querySelector('[data-products-apply]');
  const resetBtn = section.querySelector('[data-products-reset]');
  const deleteModal = document.getElementById('productsDeleteConfirmModal');
  const deleteNameNode = deleteModal?.querySelector('[data-products-delete-name]');
  const deleteConfirmBtn = deleteModal?.querySelector('[data-products-delete-confirm]');
  const voiceReadyModal = document.getElementById('productsVoiceReadyModal');
  const voiceQueueModal = document.getElementById('productsVoiceQueueModal');
  const voiceReadyProductNode = voiceReadyModal?.querySelector('[data-products-voice-ready-product]');
  const voiceReadyTitleNode = voiceReadyModal?.querySelector('[data-products-voice-ready-title]');
  const voiceReadyLanguageNode = voiceReadyModal?.querySelector('[data-products-voice-ready-language]');
  const voiceReadyDurationNode = voiceReadyModal?.querySelector('[data-products-voice-ready-duration]');
  const voiceTitleInput = voiceReadyModal?.querySelector('[data-products-voice-title-input]');
  const voiceSaveBtn = voiceReadyModal?.querySelector('[data-products-voice-save]');
  const voicePlayToggle = voiceReadyModal?.querySelector('[data-products-voice-play-toggle]');
  const voicePlayIcon = voiceReadyModal?.querySelector('[data-products-voice-play-icon]');
  const voiceAudioNode = voiceReadyModal?.querySelector('[data-products-voice-audio]');
  const voicePlayerCard = voiceReadyModal?.querySelector('[data-products-voice-player-card]');
  const voiceCurrentTimeNode = voiceReadyModal?.querySelector('[data-products-voice-current-time]');
  const voiceTotalTimeNode = voiceReadyModal?.querySelector('[data-products-voice-total-time]');
  const voiceQueueProductNode = voiceQueueModal?.querySelector('[data-products-voice-queue-product]');
  const voiceQueueTitleNode = voiceQueueModal?.querySelector('[data-products-voice-queue-title]');
  const voiceQueuePositionNode = voiceQueueModal?.querySelector('[data-products-voice-queue-position]');
  const voiceQueueElapsedNode = voiceQueueModal?.querySelector('[data-products-voice-queue-elapsed]');
  const voiceQueueEtaNode = voiceQueueModal?.querySelector('[data-products-voice-queue-eta]');
  const voiceQueueLanguageNode = voiceQueueModal?.querySelector('[data-products-voice-queue-language]');
  const voiceQueueProgressNode = voiceQueueModal?.querySelector('[data-products-voice-queue-progress]');

  if (
    !tbody || !totalBadge || !resultNode || !pageWrap || !pageSummary || !pageControls ||
    !searchInput || !typeSelect || !categorySelect || !statusSelect || !sortSelect || !applyBtn || !resetBtn
  ) {
    return;
  }

  const apiBase = String(section.dataset.apiBaseUrl || '').replace(/\/+$/, '');
  const perPage = Math.max(1, Number.parseInt(section.dataset.perPage || '10', 10) || 10);
  const sessionToken = String(section.dataset.refreshToken || '').trim();
  let storageToken = '';
  try {
    storageToken = String(window.localStorage.getItem('refresh_token') || '').trim();
  } catch {
    storageToken = '';
  }
  const token = sessionToken || storageToken;
  const tableColumnCount = 7;

  const state = {
    page: (() => {
      const params = new URLSearchParams(window.location.search);
      const parsed = Number.parseInt(params.get('page') || '1', 10);
      return Number.isFinite(parsed) && parsed > 0 ? parsed : 1;
    })(),
    perPage,
    total: 0,
    from: 0,
    to: 0,
    lastPage: 1,
    products: [],
    categories: [],
    categoryById: {},
    voiceByProductId: {},
    info: [],
    loading: false,
    requestId: 0,
  };
  const deleteState = {
    pendingId: '',
    pendingName: '',
    inFlight: false,
  };
  const voiceUiState = {
    activeProductId: '',
    activePreviewDuration: 0,
    queueIntervalId: 0,
  };

  const toInt = (value, fallback = 0) => {
    const parsed = Number.parseInt(String(value ?? ''), 10);
    return Number.isFinite(parsed) ? parsed : fallback;
  };

  const toNumber = (value, fallback = NaN) => {
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : fallback;
  };

  const text = (value) => String(value ?? '').trim();
  const titleCase = (value) => text(value).replace(/[_-]+/g, ' ').replace(/\s+/g, ' ').trim().replace(/\b\w/g, (char) => char.toUpperCase());
  const formatCount = (value) => toInt(value, 0).toLocaleString('en-US');
  const formatAmount = (value) => {
    const amount = toNumber(value, NaN);
    if (!Number.isFinite(amount)) return '-';
    return amount.toLocaleString('en-US', {
      minimumFractionDigits: Number.isInteger(amount) ? 0 : 2,
      maximumFractionDigits: 2,
    });
  };
  const escapeHtml = (value) => text(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
  const formatKpiValue = (value) => {
    const number = Number(value);
    if (Number.isFinite(number)) {
      return number.toLocaleString('en-US');
    }
    const raw = text(value);
    return raw || '0';
  };
  const categoryIdOf = (product) => {
    const candidates = [product?.category_id, product?.categoryId, product?.category];
    for (let i = 0; i < candidates.length; i += 1) {
      const id = toInt(candidates[i], 0);
      if (id > 0) return id;
    }
    return 0;
  };
  const categoryOf = (product) => {
    const direct = text(product?.category_name) || text(product?.category_title);
    if (direct) return direct;

    if (typeof product?.category === 'string') {
      const categoryText = text(product.category);
      if (categoryText && !/^\d+$/.test(categoryText)) return categoryText;
    }

    const categoryId = categoryIdOf(product);
    if (categoryId > 0) {
      return text(state.categoryById?.[categoryId]) || `Category #${categoryId}`;
    }
    return '';
  };
  const parseCategoryOption = (item) => {
    if (typeof item === 'string') {
      const label = text(item);
      if (!label) return null;
      return {value: label, label};
    }
    if (item && typeof item === 'object') {
      const label = text(item.name || item.title || item.category || item.label || item.slug || item.value);
      if (!label) return null;
      const id = toInt(item.id ?? item.category_id ?? item.categoryId, 0);
      return id > 0 ? {value: String(id), label} : {value: label, label};
    }
    return null;
  };
  const resolveKpiClass = (name, index) => {
    const normalized = text(name).toLowerCase();
    if (normalized.includes('live')) return 'is-success';
    if (normalized.includes('stock')) return 'is-warning';
    if (normalized.includes('visitor')) return 'is-info';
    if (normalized.includes('product') && index === 0) return 'is-primary';
    const fallback = ['is-primary', 'is-success', 'is-warning', 'is-info'];
    return fallback[index % fallback.length];
  };
  const renderKpis = (items = []) => {
    if (!kpiGrid) return;

    const list = Array.isArray(items) ? items : [];
    if (!list.length) {
      kpiGrid.innerHTML = `
        <article class="products-kpi-card is-primary">
          <span>Total Products</span>
          <strong>0</strong>
        </article>
        <article class="products-kpi-card is-success">
          <span>Total Live Products</span>
          <strong>0</strong>
        </article>
        <article class="products-kpi-card is-warning">
          <span>Low Stock Products</span>
          <strong>0</strong>
        </article>
        <article class="products-kpi-card is-info">
          <span>Total Visitors</span>
          <strong>0</strong>
        </article>
      `;
      return;
    }

    kpiGrid.innerHTML = list.map((entry, index) => {
      const name = text(entry?.name) || `Info ${index + 1}`;
      const value = formatKpiValue(entry?.value);
      const css = resolveKpiClass(name, index);
      return `
        <article class="products-kpi-card ${css}">
          <span>${escapeHtml(name)}</span>
          <strong>${escapeHtml(value)}</strong>
        </article>
      `;
    }).join('');
  };
  const renderCategoryFilter = (categories = []) => {
    const selectedCategory = text(categorySelect.value);
    const optionsMap = new Map();

    (Array.isArray(categories) ? categories : []).forEach((item) => {
      const option = parseCategoryOption(item);
      if (!option) return;
      if (!optionsMap.has(option.value)) {
        optionsMap.set(option.value, option.label);
      }
    });

    if (selectedCategory && !optionsMap.has(selectedCategory)) {
      const selectedId = toInt(selectedCategory, 0);
      const selectedLabel = selectedId > 0
        ? (text(state.categoryById?.[selectedId]) || `Category #${selectedId}`)
        : selectedCategory;
      optionsMap.set(selectedCategory, selectedLabel);
    }

    const options = Array.from(optionsMap.entries())
      .sort((a, b) => a[1].localeCompare(b[1]));

    categorySelect.innerHTML = ['<option value="">All Categories</option>']
      .concat(options.map(([value, label]) => `<option value="${escapeHtml(value)}">${escapeHtml(label)}</option>`))
      .join('');

    if (selectedCategory && optionsMap.has(selectedCategory)) {
      categorySelect.value = selectedCategory;
      return;
    }
    categorySelect.value = '';
  };

  const setMessage = (message, showAdd = false) => {
    tbody.innerHTML = `
      <tr>
        <td colspan="${tableColumnCount}">
          <div class="products-catalog-empty">
            <p>${escapeHtml(message)}</p>
            ${showAdd ? '<a href="/admin/products/create" class="btn btn-primary btn-sm">+ Add First Product</a>' : ''}
          </div>
        </td>
      </tr>
    `;
  };

  const priceOf = (product) => toNumber(product?.price ?? product?.product_price, NaN);
  const bargainingOf = (product) => toNumber(product?.bargaining_price, NaN);
  const stockOf = (product) => toInt(product?.total_stock ?? product?.available_qty, 0);
  const statusOf = (product) => {
    const rawStatus = text(product?.status || product?.visibility).toLowerCase();
    if (rawStatus === 'visible') return 'active';
    if (rawStatus === 'hidden') return 'draft';
    return rawStatus;
  };
  const voiceLanguages = ['English', 'Bangla', 'Hindi', 'Arabic', 'Spanish', 'Portuguese'];
  const previewAudioCache = new Map();
  const hashSeed = (value) => text(value).split('').reduce((sum, char, index) => ((sum * 33) + char.charCodeAt(0) + index) % 104729, 17);
  const formatClock = (value) => {
    const totalSeconds = Math.max(0, Math.floor(toNumber(value, 0)));
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;
    if (hours > 0) {
      return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }
    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
  };
  const formatElapsed = (value) => {
    const totalSeconds = Math.max(0, Math.floor(toNumber(value, 0)));
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;
    if (hours > 0) {
      return `${hours}h ${String(minutes).padStart(2, '0')}m ${String(seconds).padStart(2, '0')}s`;
    }
    return `${String(minutes).padStart(2, '0')}m ${String(seconds).padStart(2, '0')}s`;
  };
  const getProductById = (productId) => state.products.find((product) => text(product?.id) === text(productId)) || null;
  const voiceRecordFor = (product) => {
    const productId = text(product?.id);
    if (!productId) return null;

    const existing = state.voiceByProductId[productId];
    if (existing) return existing;

    const productName = text(product?.name) || 'Product';
    const seed = hashSeed(`${productId}|${productName}|${statusOf(product)}|${product?.sales_7d}|${product?.visitors_7d}`);
    const productStatus = statusOf(product);
    const totalDurationSeconds = 24 + (seed % 24);
    const previewDurationSeconds = 5 + (seed % 3);
    const elapsedSeconds = 120 + (seed % 900);
    const estimatedTotalSeconds = 300 + (seed % 420);
    const isReady = productStatus === 'active'
      ? (seed % 5 !== 0)
      : (productStatus === 'draft' || productStatus === 'pending' ? seed % 4 === 0 : seed % 7 === 0);

    const record = {
      productId,
      seed,
      status: isReady ? 'ready' : 'queue',
      title: `${productName} confirmation voice`,
      language: voiceLanguages[seed % voiceLanguages.length],
      totalDurationSeconds,
      previewDurationSeconds,
      queuePosition: 1 + (seed % 4),
      startedAt: Date.now() - (elapsedSeconds * 1000),
      estimatedTotalSeconds,
      progressBase: isReady ? 100 : Math.min(84, Math.max(18, Math.round((elapsedSeconds / estimatedTotalSeconds) * 100))),
    };

    state.voiceByProductId[productId] = record;
    return record;
  };
  const queueProgressMeta = (record) => {
    const elapsedSeconds = Math.max(0, Math.floor((Date.now() - toNumber(record?.startedAt, Date.now())) / 1000));
    const estimatedTotalSeconds = Math.max(180, toInt(record?.estimatedTotalSeconds, 300));
    const progressBase = Math.max(8, Math.min(96, toInt(record?.progressBase, 12)));
    const progress = record?.status === 'ready'
      ? 100
      : Math.min(96, Math.max(progressBase, Math.round((elapsedSeconds / estimatedTotalSeconds) * 100)));
    const remainingMinutes = progress >= 96 ? 1 : Math.max(1, Math.ceil(((100 - progress) / 100) * estimatedTotalSeconds / 60));
    return {elapsedSeconds, progress, remainingMinutes};
  };
  const createVoicePreviewSource = (record) => {
    const cacheKey = `${record.seed}:${record.previewDurationSeconds}`;
    if (previewAudioCache.has(cacheKey)) {
      return previewAudioCache.get(cacheKey);
    }

    const durationSeconds = Math.max(4, Math.min(8, toInt(record?.previewDurationSeconds, 6)));
    const sampleRate = 8000;
    const sampleCount = sampleRate * durationSeconds;
    const dataSize = sampleCount * 2;
    const buffer = new ArrayBuffer(44 + dataSize);
    const view = new DataView(buffer);
    const writeString = (offset, value) => {
      for (let index = 0; index < value.length; index += 1) {
        view.setUint8(offset + index, value.charCodeAt(index));
      }
    };

    writeString(0, 'RIFF');
    view.setUint32(4, 36 + dataSize, true);
    writeString(8, 'WAVE');
    writeString(12, 'fmt ');
    view.setUint32(16, 16, true);
    view.setUint16(20, 1, true);
    view.setUint16(22, 1, true);
    view.setUint32(24, sampleRate, true);
    view.setUint32(28, sampleRate * 2, true);
    view.setUint16(32, 2, true);
    view.setUint16(34, 16, true);
    writeString(36, 'data');
    view.setUint32(40, dataSize, true);

    const seed = toInt(record?.seed, 37);
    const modulation = 2 + (seed % 4);
    for (let index = 0; index < sampleCount; index += 1) {
      const timeValue = index / sampleRate;
      const phrase = Math.floor(timeValue * 3.1);
      const baseFrequency = 165 + ((seed + (phrase * 29)) % 120);
      const rhythm = 0.62 + (0.38 * Math.sin(timeValue * Math.PI * (4 + (seed % 3))));
      const wobble = Math.sin(timeValue * Math.PI * modulation) * 0.16;
      const fadeIn = Math.min(1, timeValue * 4);
      const fadeOut = Math.min(1, Math.max(0, durationSeconds - timeValue) * 4);
      const envelope = Math.max(0, Math.min(fadeIn, fadeOut));
      const sample = Math.sin((2 * Math.PI * baseFrequency * timeValue) + wobble) * rhythm * envelope * 0.22;
      view.setInt16(44 + (index * 2), Math.max(-1, Math.min(1, sample)) * 0x7fff, true);
    }

    const bytes = new Uint8Array(buffer);
    const chunkSize = 0x8000;
    let binary = '';
    for (let index = 0; index < bytes.length; index += chunkSize) {
      binary += String.fromCharCode(...bytes.subarray(index, index + chunkSize));
    }

    const source = `data:audio/wav;base64,${window.btoa(binary)}`;
    previewAudioCache.set(cacheKey, source);
    return source;
  };
  const updateVoicePlayerVisualState = (isPlaying) => {
    if (voicePlayerCard) {
      voicePlayerCard.classList.toggle('is-playing', Boolean(isPlaying));
    }
    if (voicePlayToggle) {
      voicePlayToggle.setAttribute('aria-pressed', isPlaying ? 'true' : 'false');
    }
    if (voicePlayIcon) {
      voicePlayIcon.textContent = isPlaying ? '||' : '>';
    }
  };
  const updateVoiceCurrentTime = (seconds) => {
    if (voiceCurrentTimeNode) {
      voiceCurrentTimeNode.textContent = formatClock(seconds);
    }
  };
  const stopVoicePreview = () => {
    if (voiceAudioNode) {
      voiceAudioNode.pause();
      voiceAudioNode.currentTime = 0;
    }
    updateVoiceCurrentTime(0);
    updateVoicePlayerVisualState(false);
  };
  const stopQueueTimer = () => {
    if (voiceUiState.queueIntervalId) {
      window.clearInterval(voiceUiState.queueIntervalId);
      voiceUiState.queueIntervalId = 0;
    }
  };
  const updateQueueModal = () => {
    if (!voiceQueueModal?.classList.contains('active')) return;

    const record = state.voiceByProductId[voiceUiState.activeProductId];
    if (!record) return;

    const {elapsedSeconds, progress, remainingMinutes} = queueProgressMeta(record);
    if (voiceQueueElapsedNode) {
      voiceQueueElapsedNode.textContent = formatElapsed(elapsedSeconds);
    }
    if (voiceQueueEtaNode) {
      voiceQueueEtaNode.textContent = progress >= 96 ? 'Final pass in progress' : `About ${remainingMinutes} min left`;
    }
    if (voiceQueueProgressNode) {
      voiceQueueProgressNode.style.width = `${progress}%`;
    }
  };
  const startQueueTimer = (productId) => {
    voiceUiState.activeProductId = text(productId);
    stopQueueTimer();
    updateQueueModal();
    voiceUiState.queueIntervalId = window.setInterval(updateQueueModal, 1000);
  };
  const openVoiceReadyModal = (product, record) => {
    if (!voiceReadyModal || !record) return;

    stopQueueTimer();
    stopVoicePreview();
    voiceUiState.activeProductId = text(record.productId);
    voiceUiState.activePreviewDuration = toInt(record.previewDurationSeconds, 6);

    if (voiceReadyProductNode) {
      voiceReadyProductNode.textContent = text(product?.name) || 'Product Voice Preview';
    }
    if (voiceReadyTitleNode) {
      voiceReadyTitleNode.textContent = record.title;
    }
    if (voiceReadyLanguageNode) {
      voiceReadyLanguageNode.textContent = record.language;
    }
    if (voiceReadyDurationNode) {
      voiceReadyDurationNode.textContent = `${formatClock(record.totalDurationSeconds)} total`;
    }
    if (voiceTitleInput) {
      voiceTitleInput.value = record.title;
    }
    if (voiceTotalTimeNode) {
      voiceTotalTimeNode.textContent = formatClock(record.previewDurationSeconds);
    }
    updateVoiceCurrentTime(0);
    updateVoicePlayerVisualState(false);

    if (voiceAudioNode) {
      voiceAudioNode.src = createVoicePreviewSource(record);
      voiceAudioNode.currentTime = 0;
      voiceAudioNode.load();
    }

    openModal('productsVoiceReadyModal');
    if (voiceTitleInput) {
      voiceTitleInput.focus();
      voiceTitleInput.select();
    }
  };
  const openVoiceQueueModal = (product, record) => {
    if (!voiceQueueModal || !record) return;

    stopVoicePreview();
    stopQueueTimer();
    voiceUiState.activeProductId = text(record.productId);

    if (voiceQueueProductNode) {
      voiceQueueProductNode.textContent = text(product?.name) || 'Voice is being prepared';
    }
    if (voiceQueueTitleNode) {
      voiceQueueTitleNode.textContent = record.title;
    }
    if (voiceQueuePositionNode) {
      voiceQueuePositionNode.textContent = `Queue slot #${record.queuePosition}`;
    }
    if (voiceQueueLanguageNode) {
      voiceQueueLanguageNode.textContent = record.language;
    }

    openModal('productsVoiceQueueModal');
    updateQueueModal();
    startQueueTimer(record.productId);
  };
  const cleanupVoiceUi = () => {
    stopVoicePreview();
    stopQueueTimer();
    voiceUiState.activeProductId = '';
    voiceUiState.activePreviewDuration = 0;
  };
  const voiceHtml = (product) => {
    const record = voiceRecordFor(product);
    if (!record) {
      return '<span class="products-voice-inline-note">Unavailable</span>';
    }

    if (record.status === 'ready') {
      return `
        <div class="products-voice-cell">
          <button
            type="button"
            class="products-voice-action products-voice-action--ready"
            data-products-voice-trigger
            data-product-id="${escapeHtml(record.productId)}"
          >
            <span class="products-voice-action-icon">></span>
            <span>Play Voice</span>
          </button>
        </div>
      `;
    }

    const {elapsedSeconds} = queueProgressMeta(record);
    return `
      <div class="products-voice-cell">
        <button
          type="button"
          class="products-voice-action products-voice-action--queue"
          data-products-voice-trigger
          data-product-id="${escapeHtml(record.productId)}"
        >
          <span class="products-voice-action-pulse" aria-hidden="true"></span>
          <span>In Queue</span>
        </button>
        <small>${escapeHtml(formatElapsed(elapsedSeconds))} so far</small>
      </div>
    `;
  };

  const stockMeta = (product) => {
    const stock = stockOf(product);
    const alert = toInt(product.stock_alert, 0);
    if (stock <= 0) return { label: 'Out of Stock', css: 'badge-danger' };
    if (alert > 0 && stock <= alert) return { label: 'Low Stock', css: 'badge-warning' };
    return { label: 'In Stock', css: 'badge-success' };
  };

  const priceHtml = (product) => {
    const priceValue = priceOf(product);
    const bargainingValue = bargainingOf(product);
    const price = formatAmount(priceValue);
    const bargaining = Number.isFinite(bargainingValue)
      ? formatAmount(bargainingValue)
      : '';
    const offer = text(product.is_discount_offer).toLowerCase();
    const discountType = text(product.is_discount_type).toLowerCase();
    const discountValue = toNumber(product.discount_value, NaN);
    const hasDiscount = (offer === 'limited' || offer === 'lifetime') && Number.isFinite(discountValue);
    const discountLabel = !hasDiscount ? '' : (discountType === 'percentage' ? `-${formatAmount(discountValue)}%` : `-${formatAmount(discountValue)}`);

    return `
      <div class="products-catalog-price">
        <strong>${escapeHtml(price)}</strong>
        ${discountLabel || bargaining ? `
          <div class="products-catalog-price-sub">
            ${discountLabel ? `<span class="products-catalog-discount-badge">${escapeHtml(discountLabel)}</span>` : ''}
            ${bargaining ? `<small class="products-catalog-original">Bargain ${escapeHtml(bargaining)}</small>` : ''}
          </div>
        ` : ''}
      </div>
    `;
  };

  const stockHtml = (product, type) => {
    if (type === 'downloadable') {
      return '<div class="products-catalog-access"><span class="products-catalog-access-label products-catalog-access-label--digital">Unlimited</span><small class="products-catalog-access-note">No stock tracking</small></div>';
    }
    if (type === 'subscription') {
      return `<div class="products-catalog-access"><span class="products-catalog-access-label products-catalog-access-label--slots">${formatCount(product.subscription_slots)} Slots available</span><small class="products-catalog-access-note">Seller managed account slots</small></div>`;
    }
    if (type === 'package') {
      return `<div class="products-catalog-access"><span class="products-catalog-access-label products-catalog-access-label--package">${formatCount(product.package_facilities)} Facilities included</span><small class="products-catalog-access-note">Bundle package</small></div>`;
    }

    const meta = stockMeta(product);
    const stock = stockOf(product);
    if (Boolean(product.is_variants)) {
      const variants = toInt(product.variant_count, 0);
      return `<div class="products-catalog-stock"><div class="products-catalog-stock-top"><span class="products-catalog-stock-num">${formatCount(stock)} units total</span><span class="badge badge-xs ${meta.css}">${meta.label}</span></div><div class="products-catalog-variants-info"><span class="products-catalog-variants-badge">${formatCount(variants)} Variant${variants === 1 ? '' : 's'}</span></div></div>`;
    }

    const progress = Math.max(0, Math.min(100, stock));
    return `<div class="products-catalog-stock"><div class="products-catalog-stock-top"><span class="products-catalog-stock-num">${formatCount(stock)} units</span><span class="badge badge-xs ${meta.css}">${meta.label}</span></div><div class="products-catalog-progress"><div class="products-catalog-progress-fill ${meta.css}" style="width: ${progress}%"></div></div></div>`;
  };

  const rowNode = (product) => {
    const type = text(product.type).toLowerCase() || 'physical';
    const typeLabelMap = { physical: 'Physical', downloadable: 'Downloadable', subscription: 'Subscription', package: 'Package' };
    const typeCssMap = { physical: 'products-type-tag--physical', downloadable: 'products-type-tag--downloadable', subscription: 'products-type-tag--subscription', package: 'products-type-tag--package' };
    const name = text(product.name) || 'Unnamed Product';
    const productId = text(product.id);
    const image = text(product.cover || product.cover_image);
    const initial = escapeHtml(name.charAt(0).toUpperCase() || 'P');
    const category = categoryOf(product) || 'Uncategorized';
    const status = statusOf(product);
    const statusCss = status === 'active' ? 'badge-success' : (status === 'draft' || status === 'pending' ? 'badge-warning' : (status === 'inactive' ? 'badge-danger' : 'badge-info'));

    const deleteButton = productId
      ? `<button type="button" class="btn btn-danger btn-sm" data-products-delete-trigger data-product-id="${escapeHtml(productId)}" data-product-name="${escapeHtml(name)}">Delete</button>`
      : '';

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <div class="products-catalog-cell">
          ${image ? `<span class="products-catalog-thumb"><img src="${escapeHtml(image)}" alt="${escapeHtml(name)}" loading="lazy"></span>` : `<span class="products-catalog-thumb products-catalog-thumb--initial products-catalog-thumb--${escapeHtml(type)}">${initial}</span>`}
          <div class="products-catalog-meta">
            <strong class="products-catalog-name" title="${escapeHtml(name)}">${escapeHtml(name)}</strong>
            <div class="products-catalog-tags">
              <span class="products-type-tag ${typeCssMap[type] || ''}">${escapeHtml(typeLabelMap[type] || titleCase(type))}</span>
              <span class="products-catalog-category">${escapeHtml(category)}</span>
            </div>
          </div>
        </div>
      </td>
      <td>${priceHtml(product)}</td>
      <td>${stockHtml(product, type)}</td>
      <td><div class="products-catalog-perf"><strong>${formatCount(product.sales_7d)} sales</strong><small>${formatCount(product.visitors_7d)} visitors</small></div></td>
      <td><span class="badge ${statusCss}">${escapeHtml(titleCase(status || 'unknown'))}</span></td>
      <td>${voiceHtml(product)}</td>
      <td><div class="products-table-actions"><a href="/admin/products/${encodeURIComponent(productId)}/edit" class="btn btn-secondary btn-sm">Edit</a>${deleteButton}</div></td>
    `;
    return tr;
  };

  const normalizeStatusFilter = (value) => {
    const normalized = text(value).toLowerCase();
    if (!normalized) return '';

    if (normalized === 'out of stock') return 'out_of_stock';
    if (normalized === 'active' || normalized === 'draft' || normalized === 'inactive' || normalized === 'pending' || normalized === 'out_of_stock') {
      return normalized;
    }
    return normalized.replace(/\s+/g, '_');
  };

  const readFilters = () => ({
    search: text(searchInput.value),
    productType: text(typeSelect.value).toLowerCase(),
    category: text(categorySelect.value),
    status: normalizeStatusFilter(statusSelect.value),
    sortBy: text(sortSelect.value).toLowerCase() || 'latest',
  });

  const buildCategoryOptionsFromProducts = (products = []) => (
    (Array.isArray(products) ? products : []).map((product) => {
      const categoryId = categoryIdOf(product);
      const label = categoryOf(product);
      if (!label) return null;
      return categoryId > 0
        ? {id: categoryId, name: label}
        : {name: label};
    }).filter(Boolean)
  );

  const syncCategoryLookup = (categories = [], products = []) => {
    const lookup = {};

    (Array.isArray(categories) ? categories : []).forEach((item) => {
      const option = parseCategoryOption(item);
      if (!option) return;
      const id = toInt(option.value, 0);
      if (id > 0 && option.label) {
        lookup[id] = option.label;
      }
    });

    (Array.isArray(products) ? products : []).forEach((product) => {
      const id = categoryIdOf(product);
      if (id <= 0 || lookup[id]) return;
      const label = text(product?.category_name) || text(product?.category_title);
      if (label) {
        lookup[id] = label;
      }
    });

    state.categoryById = lookup;
  };

  const renderTable = () => {
    const list = Array.isArray(state.products) ? state.products : [];
    tbody.innerHTML = '';

    if (!list.length) {
      setMessage('No products found for current filters.', state.total === 0 && state.page <= 1);
      resultNode.textContent = 'No products found';
      return;
    }

    const fragment = document.createDocumentFragment();
    list.forEach((product) => fragment.appendChild(rowNode(product)));
    tbody.appendChild(fragment);

    if (state.total > 0 && state.from > 0 && state.to > 0) {
      resultNode.textContent = `Showing ${state.from}-${state.to} of ${state.total} products`;
    } else {
      resultNode.textContent = `Showing ${list.length} products`;
    }
  };

  const renderPagination = () => {
    const current = Math.max(1, toInt(state.page, 1));
    const last = Math.max(1, toInt(state.lastPage, 1));
    if (last <= 1) {
      pageSummary.textContent = 'Page 1 of 1';
      pageControls.innerHTML = '';
      pageWrap.hidden = true;
      return;
    }

    pageWrap.hidden = false;
    pageSummary.textContent = `Page ${current} of ${last}`;
    pageControls.innerHTML = '';

    const btn = (label, page, disabled = false, active = false) => {
      const node = document.createElement('button');
      node.type = 'button';
      node.className = `products-page-btn${disabled ? ' is-disabled' : ''}${active ? ' is-active' : ''}`;
      node.textContent = label;
      if (disabled || active) {
        node.disabled = true;
        if (disabled) node.setAttribute('aria-disabled', 'true');
        if (active) node.setAttribute('aria-current', 'page');
      } else {
        node.addEventListener('click', () => !state.loading && loadProducts(page));
      }
      return node;
    };

    const pages = [];
    if (last <= 7) {
      for (let page = 1; page <= last; page += 1) pages.push(page);
    } else {
      pages.push(1);
      if (current > 3) pages.push('...');
      for (let page = Math.max(2, current - 1); page <= Math.min(last - 1, current + 1); page += 1) pages.push(page);
      if (current < last - 2) pages.push('...');
      pages.push(last);
    }

    pageControls.appendChild(btn('Prev', current - 1, current <= 1, false));
    pages.forEach((page) => pageControls.appendChild(page === '...' ? btn('...', 0, true, false) : btn(String(page), page, false, page === current)));
    pageControls.appendChild(btn('Next', current + 1, current >= last, false));
  };

  const updateUrlPage = (page) => {
    const url = new URL(window.location.href);
    if (page > 1) url.searchParams.set('page', String(page));
    else url.searchParams.delete('page');
    window.history.replaceState({}, '', url.toString());
  };

  const setLoading = (loading) => {
    state.loading = loading;
    applyBtn.disabled = loading;
    resetBtn.disabled = loading;
  };

  const setDeletePending = (id = '', name = '') => {
    deleteState.pendingId = text(id);
    deleteState.pendingName = text(name);
    if (deleteNameNode) {
      deleteNameNode.textContent = deleteState.pendingName || 'this product';
    }
  };

  const setDeleteLoading = (loading) => {
    deleteState.inFlight = Boolean(loading);
    if (!deleteConfirmBtn) return;
    deleteConfirmBtn.disabled = deleteState.inFlight;
    deleteConfirmBtn.textContent = deleteState.inFlight ? 'Deleting...' : 'Delete Product';
  };

  async function loadProducts(page) {
    if (!token) {
      const message = 'Missing refresh token. Please login again.';
      totalBadge.textContent = 'Unavailable';
      resultNode.textContent = message;
      setMessage(message);
      pageWrap.hidden = true;
      if (typeof window.showError === 'function') window.showError(message);
      return;
    }

    if (!apiBase) {
      const message = 'Backend API URL is missing.';
      totalBadge.textContent = 'Unavailable';
      resultNode.textContent = message;
      setMessage(message);
      pageWrap.hidden = true;
      return;
    }

    const requestId = ++state.requestId;
    const filters = readFilters();
    setLoading(true);
    pageWrap.hidden = true;
    pageControls.innerHTML = '';
    pageSummary.textContent = 'Page 1 of 1';
    setMessage('Loading products...');
    resultNode.textContent = 'Loading products...';

    try {
      const payload = await window.API.Admin.Products.list({
        apiBaseUrl: apiBase,
        refreshToken: token,
        page,
        perPage: state.perPage,
        search: filters.search,
        productType: filters.productType,
        category: filters.category,
        status: filters.status,
        sortBy: filters.sortBy,
        timeoutMs: 12000,
      });
      if (requestId !== state.requestId) return;

      const info = Array.isArray(payload?.info) ? payload.info : [];
      const payloadCategories = Array.isArray(payload?.categories) ? payload.categories : [];
      const productsPayload = payload?.products && typeof payload.products === 'object' ? payload.products : {};
      const list = Array.isArray(productsPayload?.data) ? productsPayload.data : [];
      const pagination = productsPayload?.pagination && typeof productsPayload.pagination === 'object'
        ? productsPayload.pagination
        : {};

      state.info = info;
      state.categories = payloadCategories;
      state.products = list;
      const total = Math.max(0, toInt(pagination.total, list.length));
      const perPageFromApi = Math.max(1, toInt(pagination.per_page, state.perPage));
      const derivedLastPage = Math.max(1, Math.ceil(total / perPageFromApi));

      state.perPage = perPageFromApi;
      state.total = total;
      state.lastPage = derivedLastPage;
      state.page = Math.min(
        Math.max(1, toInt(pagination.current_page, page)),
        state.lastPage
      );
      state.from = Math.max(0, toInt(pagination.from, list.length ? ((state.page - 1) * state.perPage) + 1 : 0));
      state.to = Math.max(0, toInt(pagination.to, state.from ? state.from + list.length - 1 : 0));

      syncCategoryLookup(payloadCategories, list);
      const mergedCategories = payloadCategories.length
        ? payloadCategories
        : buildCategoryOptionsFromProducts(list);
      renderCategoryFilter(mergedCategories);
      renderKpis(info);

      totalBadge.textContent = `${formatCount(state.total)} Products`;
      renderTable();
      renderPagination();
      updateUrlPage(state.page);
    } catch (error) {
      if (requestId !== state.requestId) return;
      const message = error?.isTimeout
        ? 'Request timed out. Please try again.'
        : (error?.message || 'Failed to load products.');
      state.products = [];
      state.info = [];
      state.categories = [];
      state.total = 0;
      state.from = 0;
      state.to = 0;
      state.lastPage = 1;
      totalBadge.textContent = 'Unavailable';
      renderKpis([]);
      renderCategoryFilter([]);
      resultNode.textContent = message;
      setMessage(message);
      pageControls.innerHTML = '';
      pageSummary.textContent = 'Page 1 of 1';
      pageWrap.hidden = true;
      if (typeof window.showError === 'function') window.showError(message);
    } finally {
      if (requestId === state.requestId) setLoading(false);
    }
  }

  const readImportPayload = async (file) => {
    const raw = await file.text();
    const parsed = JSON.parse(raw);
    if (Array.isArray(parsed)) {
      return { products: parsed };
    }
    if (Array.isArray(parsed?.products)) {
      return { products: parsed.products };
    }
    throw new Error('Invalid backup file. Expected a JSON object with a products array.');
  };

  importTrigger?.addEventListener('click', () => {
    if (!(importInput instanceof HTMLInputElement)) return;
    importInput.click();
  });

  importInput?.addEventListener('change', async () => {
    if (!(importInput instanceof HTMLInputElement)) return;

    const file = importInput.files?.[0];
    importInput.value = '';
    if (!file) return;

    if (!token) {
      if (typeof window.showError === 'function') window.showError('Missing refresh token. Please login again.');
      return;
    }

    if (!apiBase) {
      if (typeof window.showError === 'function') window.showError('Backend API URL is missing.');
      return;
    }

    if (!window.API?.Admin?.Products?.importAll) {
      if (typeof window.showError === 'function') window.showError('Import API is not configured.');
      return;
    }

    const originalLabel = importTrigger instanceof HTMLButtonElement ? importTrigger.textContent : 'Import Backup';
    if (importTrigger instanceof HTMLButtonElement) {
      importTrigger.disabled = true;
      importTrigger.textContent = 'Importing...';
    }

    try {
      const payload = await readImportPayload(file);
      const result = await window.API.Admin.Products.importAll({
        apiBaseUrl: apiBase,
        refreshToken: token,
        payload,
        timeoutMs: 30000,
      });

      if (typeof window.showSuccess === 'function') {
        window.showSuccess(text(result?.message) || 'Products imported successfully.');
      }

      await loadProducts(1);
    } catch (error) {
      if (typeof window.showError === 'function') {
        window.showError(error?.message || 'Failed to import products.');
      }
    } finally {
      if (importTrigger instanceof HTMLButtonElement) {
        importTrigger.disabled = false;
        importTrigger.textContent = originalLabel || 'Import Backup';
      }
    }
  });

  applyBtn.addEventListener('click', () => {
    if (state.loading) return;
    loadProducts(1);
  });
  resetBtn.addEventListener('click', () => {
    searchInput.value = '';
    typeSelect.value = '';
    categorySelect.value = '';
    statusSelect.value = '';
    sortSelect.value = 'latest';
    if (state.loading) return;
    loadProducts(1);
  });
  sortSelect.addEventListener('change', () => {
    if (state.loading) return;
    loadProducts(1);
  });
  searchInput.addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
      event.preventDefault();
      if (state.loading) return;
      loadProducts(1);
    }
  });
  voicePlayToggle?.addEventListener('click', async () => {
    if (!voiceAudioNode?.src) return;

    try {
      if (voiceAudioNode.paused) {
        await voiceAudioNode.play();
        updateVoicePlayerVisualState(true);
      } else {
        voiceAudioNode.pause();
        updateVoicePlayerVisualState(false);
      }
    } catch (error) {
      updateVoicePlayerVisualState(false);
      if (typeof window.showError === 'function') {
        window.showError(error?.message || 'Unable to play voice preview.');
      }
    }
  });
  voiceAudioNode?.addEventListener('timeupdate', () => {
    updateVoiceCurrentTime(voiceAudioNode.currentTime);
  });
  voiceAudioNode?.addEventListener('play', () => {
    updateVoicePlayerVisualState(true);
  });
  voiceAudioNode?.addEventListener('pause', () => {
    updateVoicePlayerVisualState(false);
  });
  voiceAudioNode?.addEventListener('ended', () => {
    updateVoiceCurrentTime(voiceUiState.activePreviewDuration);
    updateVoicePlayerVisualState(false);
  });
  voiceSaveBtn?.addEventListener('click', () => {
    const productId = text(voiceUiState.activeProductId);
    const product = getProductById(productId);
    const record = state.voiceByProductId[productId];
    if (!product || !record) {
      if (typeof window.showError === 'function') window.showError('Unable to update this voice right now.');
      return;
    }

    const nextTitle = text(voiceTitleInput?.value) || record.title;
    const updatedRecord = {
      ...record,
      title: nextTitle,
      status: 'queue',
      queuePosition: 1 + (hashSeed(`${productId}|${nextTitle}`) % 4),
      startedAt: Date.now(),
      estimatedTotalSeconds: Math.max(240, toInt(record.estimatedTotalSeconds, 300)),
      progressBase: 10,
    };

    state.voiceByProductId[productId] = updatedRecord;
    cleanupVoiceUi();
    closeAllModals();
    renderTable();
    if (typeof window.showSuccess === 'function') {
      window.showSuccess('Voice title saved and moved to queue.');
    }
    openVoiceQueueModal(product, updatedRecord);
  });

  document.addEventListener('click', (event) => {
    const target = event.target;
    if (target === voiceReadyModal || target === voiceQueueModal) {
      cleanupVoiceUi();
      return;
    }

    const closeTrigger = target?.closest?.('.modal-close, [data-modal-close]');
    if (!closeTrigger) return;
    if (
      (voiceReadyModal && voiceReadyModal.contains(closeTrigger)) ||
      (voiceQueueModal && voiceQueueModal.contains(closeTrigger))
    ) {
      cleanupVoiceUi();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    if (
      voiceReadyModal?.classList.contains('active') ||
      voiceQueueModal?.classList.contains('active')
    ) {
      window.setTimeout(cleanupVoiceUi, 0);
    }
  });

  tbody.addEventListener('click', (event) => {
    const voiceTrigger = event.target.closest('[data-products-voice-trigger]');
    if (voiceTrigger && tbody.contains(voiceTrigger)) {
      event.preventDefault();

      const productId = text(voiceTrigger.dataset.productId);
      const product = getProductById(productId);
      const record = product ? voiceRecordFor(product) : null;
      if (!product || !record) {
        if (typeof window.showError === 'function') window.showError('Voice preview is not available.');
        return;
      }

      if (record.status === 'ready') {
        openVoiceReadyModal(product, record);
      } else {
        openVoiceQueueModal(product, record);
      }
      return;
    }

    const trigger = event.target.closest('[data-products-delete-trigger]');
    if (!trigger || !tbody.contains(trigger)) return;
    event.preventDefault();

    const productId = text(trigger.dataset.productId);
    if (!productId) {
      if (typeof window.showError === 'function') window.showError('Invalid product id.');
      return;
    }

    const productName = text(trigger.dataset.productName) || 'this product';
    setDeletePending(productId, productName);
    setDeleteLoading(false);
    if (deleteModal) openModal('productsDeleteConfirmModal');
  });

  deleteConfirmBtn?.addEventListener('click', async () => {
    if (deleteState.inFlight) return;

    const productId = text(deleteState.pendingId);
    if (!productId) {
      if (typeof window.showError === 'function') window.showError('Please select a product to delete.');
      return;
    }

    if (!token) {
      if (typeof window.showError === 'function') window.showError('Missing refresh token. Please login again.');
      return;
    }

    if (!apiBase) {
      if (typeof window.showError === 'function') window.showError('Backend API URL is missing.');
      return;
    }

    setDeleteLoading(true);
    try {
      const payload = await window.API.Admin.Products.remove({
        apiBaseUrl: apiBase,
        refreshToken: token,
        productId,
        timeoutMs: 12000,
      });

      closeAllModals();
      setDeletePending();
      if (typeof window.showSuccess === 'function') {
        window.showSuccess(text(payload.message) || 'product deleted');
      }

      const nextPage = state.page > 1 && state.products.length <= 1 ? state.page - 1 : state.page;
      await loadProducts(nextPage);
    } catch (error) {
      const message = error?.isTimeout
        ? 'Delete request timed out. Please try again.'
        : (error?.message || 'Failed to delete product.');
      if (typeof window.showError === 'function') window.showError(message);
    } finally {
      setDeleteLoading(false);
    }
  });

  renderKpis([]);
  loadProducts(state.page);
}

function initUsersCatalogPage() {
  const section = document.getElementById('usersCatalogSection');
  if (!section) return;

  const totalUsersNode = document.querySelector('[data-users-total-users]');
  const totalWhatsAppNode = document.querySelector('[data-users-total-whatsapp]');
  const totalPriceSensitiveNode = document.querySelector('[data-users-total-price-sensitive]');

  const filteredBadge = section.querySelector('[data-users-filtered-badge]');
  const searchInput = section.querySelector('[data-users-search]');
  const channelSelect = section.querySelector('[data-users-channel]');
  const emotionSelect = section.querySelector('[data-users-emotion]');
  const userTypeSelect = section.querySelector('[data-users-user-type]');
  const statusSelect = section.querySelector('[data-users-status]');
  const applyBtn = section.querySelector('[data-users-apply]');
  const resetBtn = section.querySelector('[data-users-reset]');
  const resultNode = section.querySelector('[data-users-result]');
  const tbody = section.querySelector('[data-users-tbody]');
  const pageWrap = section.querySelector('[data-users-pagination-wrap]');
  const pageSummary = section.querySelector('[data-users-pagination-summary]');
  const pageControls = section.querySelector('[data-users-pagination-controls]');
  const banModal = document.getElementById('usersBanConfirmModal');
  const banModalTitle = banModal?.querySelector('[data-users-ban-modal-title]');
  const banModalDescription = banModal?.querySelector('[data-users-ban-modal-description]');
  const banConfirmBtn = banModal?.querySelector('[data-users-ban-confirm]');
  const banCloseButtons = banModal ? Array.from(banModal.querySelectorAll('[data-users-ban-close], [data-users-ban-cancel]')) : [];

  if (
    !filteredBadge || !searchInput || !channelSelect || !emotionSelect || !userTypeSelect || !statusSelect ||
    !applyBtn || !resetBtn || !resultNode || !tbody || !pageWrap || !pageSummary || !pageControls
  ) {
    return;
  }

  if (
    !window.API?.Admin?.Users ||
    typeof window.API.Admin.Users.list !== 'function' ||
    typeof window.API.Admin.Users.ban !== 'function' ||
    typeof window.API.Admin.Users.unban !== 'function'
  ) {
    return;
  }

  const apiBase = String(section.dataset.apiBaseUrl || '').replace(/\/+$/, '');
  const viewUrl = String(section.dataset.viewUrl || '').trim();
  const perPage = Math.max(1, Number.parseInt(section.dataset.perPage || '10', 10) || 10);
  const sessionToken = String(section.dataset.refreshToken || '').trim();
  let storageToken = '';
  try {
    storageToken = String(window.localStorage.getItem('refresh_token') || '').trim();
  } catch {
    storageToken = '';
  }
  const token = sessionToken || storageToken;

  const toInt = (value, fallback = 0) => {
    const parsed = Number.parseInt(String(value ?? ''), 10);
    return Number.isFinite(parsed) ? parsed : fallback;
  };
  const text = (value) => String(value ?? '').trim();
  const escapeHtml = (value) => text(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
  const formatCount = (value) => toInt(value, 0).toLocaleString('en-US');
  const slugify = (value) => text(value)
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '') || 'default';
  const clientIdOf = (user) => text(user?.client_id);
  const activeStateOf = (user) => {
    if (typeof user?.is_active === 'boolean') {
      return user.is_active;
    }

    if (typeof user?.is_active === 'number') {
      if (user.is_active === 1) return true;
      if (user.is_active === 0) return false;
    }

    const rawIsActive = text(user?.is_active).toLowerCase();
    if (rawIsActive === 'active' || rawIsActive === 'true' || rawIsActive === '1') {
      return true;
    }
    if (rawIsActive === 'inactive' || rawIsActive === 'false' || rawIsActive === '0') {
      return false;
    }

    const rawStatus = text(user?.status).toLowerCase();
    if (rawStatus === 'active' || rawStatus === 'true' || rawStatus === '1') {
      return true;
    }
    if (rawStatus === 'inactive' || rawStatus === 'false' || rawStatus === '0') {
      return false;
    }

    return null;
  };

  const state = {
    page: (() => {
      const params = new URLSearchParams(window.location.search);
      const parsed = toInt(params.get('page'), 1);
      return parsed > 0 ? parsed : 1;
    })(),
    perPage,
    total: 0,
    from: 0,
    to: 0,
    lastPage: 1,
    users: [],
    loading: false,
    requestId: 0,
  };
  const banState = {
    pendingClientId: '',
    pendingUserName: '',
    pendingAction: 'ban',
    inFlight: false,
  };

  const setSelectValue = (select, value, fallback = 'all') => {
    const target = text(value);
    if (!target) {
      select.value = fallback;
      return;
    }

    const options = Array.from(select.options);
    const match = options.find((option) => text(option.value).toLowerCase() === target.toLowerCase());
    if (match) {
      select.value = match.value;
      return;
    }

    if (target.toLowerCase() === 'all') {
      select.value = fallback;
      return;
    }

    const dynamic = document.createElement('option');
    dynamic.value = target;
    dynamic.textContent = target;
    select.appendChild(dynamic);
    select.value = target;
  };

  const urlParams = new URLSearchParams(window.location.search);
  const initialSearch = text(urlParams.get('search') || urlParams.get('q'));
  if (initialSearch) {
    searchInput.value = initialSearch;
  }
  setSelectValue(channelSelect, urlParams.get('channel'), 'all');
  setSelectValue(emotionSelect, urlParams.get('emotion'), 'all');
  setSelectValue(userTypeSelect, urlParams.get('user_type'), 'all');
  setSelectValue(statusSelect, urlParams.get('status'), 'all');

  const normalizeFilter = (value) => {
    const normalized = text(value);
    if (!normalized || normalized.toLowerCase() === 'all') return '';
    return normalized;
  };

  const readFilters = () => ({
    search: text(searchInput.value),
    channel: normalizeFilter(channelSelect.value),
    emotion: normalizeFilter(emotionSelect.value),
    userType: normalizeFilter(userTypeSelect.value),
    status: normalizeFilter(statusSelect.value),
  });

  const setMessage = (message) => {
    tbody.innerHTML = `
      <tr>
        <td colspan="7" class="users-empty">${escapeHtml(message)}</td>
      </tr>
    `;
  };

  const channelsOf = (user) => {
    const raw = user?.connected_channel ?? user?.channels;
    if (Array.isArray(raw)) {
      return raw.map((entry) => text(entry)).filter(Boolean);
    }
    if (typeof raw === 'string') {
      return raw.split(/[,\|\/]+/).map((entry) => text(entry)).filter(Boolean);
    }
    return [];
  };

  const emotionsOf = (user) => {
    const raw = user?.emotions;
    if (Array.isArray(raw)) {
      return raw.map((entry) => text(entry)).filter(Boolean);
    }
    const emotionText = text(raw);
    if (!emotionText) return [];
    return emotionText.split(/[,\|\/]+/).map((entry) => text(entry)).filter(Boolean);
  };

  const statusMetaOf = (user) => {
    const activeState = activeStateOf(user);
    if (activeState === true) {
      return {label: 'Active', css: 'badge-success'};
    }
    if (activeState === false) {
      return {label: 'Inactive', css: 'badge-warning'};
    }

    return {
      label: text(user?.status) || 'Unknown',
      css: 'badge-info',
    };
  };

  const renderChannels = (channels) => {
    if (!channels.length) {
      return '<span class="badge">N/A</span>';
    }

    return channels.map((channel) => `
      <span class="badge users-channel-badge users-channel-${escapeHtml(slugify(channel))}">
        ${escapeHtml(channel)}
      </span>
    `).join('');
  };

  const renderEmotions = (emotions) => {
    if (!emotions.length) {
      return '<span class="badge emotion-badge emotion-neutral">N/A</span>';
    }

    return emotions.map((emotion) => `
      <span class="badge emotion-badge emotion-${escapeHtml(slugify(emotion))}">
        ${escapeHtml(emotion)}
      </span>
    `).join('');
  };

  const rowNode = (user) => {
    const name = text(user?.full_name || user?.name) || 'Unknown User';
    const clientId = clientIdOf(user);
    const displayId = clientId || text(user?.user_id || user?.id);
    const profile = text(user?.profile || user?.profile_pic);
    const channels = channelsOf(user);
    const emotions = emotionsOf(user);
    const userType = text(user?.user_type) || 'N/A';
    const userTypeCss = userType.toLowerCase() === 'price-sensitive' ? 'badge-warning' : 'badge-primary';
    const status = statusMetaOf(user);
    const activeState = activeStateOf(user);

    const viewHref = viewUrl
      ? `${viewUrl}${viewUrl.includes('?') ? '&' : '?'}user_id=${encodeURIComponent(displayId || name)}`
      : '';

    const banAction = activeState === false ? 'unban' : 'ban';
    const banLabel = banAction === 'unban' ? 'Unban' : 'Ban';
    const banClass = banAction === 'unban' ? 'btn-success' : 'btn-danger';
    const banButton = clientId
      ? `<button type="button" class="btn ${banClass} btn-sm" data-user-ban-toggle data-client-id="${escapeHtml(clientId)}" data-ban-action="${banAction}" data-user-name="${escapeHtml(name)}">${banLabel}</button>`
      : '<button type="button" class="btn btn-secondary btn-sm" disabled title="Missing client ID">Ban</button>';

    const profileHtml = profile
      ? `<img src="${escapeHtml(profile)}" class="users-avatar" alt="${escapeHtml(name)}" loading="lazy">`
      : `<span class="users-avatar users-avatar-fallback" aria-hidden="true">${escapeHtml(name.charAt(0).toUpperCase() || 'U')}</span>`;

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <div class="users-table-profile">${profileHtml}</div>
      </td>
      <td>
        <div class="users-name-block">
          <div class="users-name">${escapeHtml(name)}</div>
          <div class="users-sub-id">${escapeHtml(displayId || 'N/A')}</div>
        </div>
      </td>
      <td>
        <div class="users-channels">${renderChannels(channels)}</div>
      </td>
      <td>
        <div class="users-emotions">${renderEmotions(emotions)}</div>
      </td>
      <td>
        <span class="badge ${userTypeCss}">${escapeHtml(userType)}</span>
      </td>
      <td>
        <span class="badge ${status.css}">${escapeHtml(status.label)}</span>
      </td>
      <td>
        <div class="users-actions">
          ${banButton}
          ${viewHref ? `<a href="${escapeHtml(viewHref)}" class="btn btn-info btn-sm">View</a>` : ''}
        </div>
      </td>
    `;

    return tr;
  };

  const renderTable = () => {
    const list = Array.isArray(state.users) ? state.users : [];
    tbody.innerHTML = '';

    if (!list.length) {
      setMessage('No users found for the selected filters.');
      resultNode.textContent = 'No users found';
      return;
    }

    const fragment = document.createDocumentFragment();
    list.forEach((user) => fragment.appendChild(rowNode(user)));
    tbody.appendChild(fragment);

    if (state.total > 0 && state.from > 0 && state.to > 0) {
      resultNode.textContent = `Showing ${state.from}-${state.to} of ${state.total} users`;
    } else {
      resultNode.textContent = `Showing ${list.length} users`;
    }
  };

  const renderPagination = () => {
    const current = Math.max(1, toInt(state.page, 1));
    const last = Math.max(1, toInt(state.lastPage, 1));

    if (last <= 1) {
      pageSummary.textContent = 'Page 1 of 1';
      pageControls.innerHTML = '';
      pageWrap.hidden = true;
      return;
    }

    pageWrap.hidden = false;
    pageSummary.textContent = `Page ${current} of ${last}`;
    pageControls.innerHTML = '';

    const btn = (label, page, disabled = false, active = false) => {
      const node = document.createElement('button');
      node.type = 'button';
      node.className = `users-page-btn${disabled ? ' is-disabled' : ''}${active ? ' is-active' : ''}`;
      node.textContent = label;

      if (disabled || active) {
        node.disabled = true;
        if (disabled) node.setAttribute('aria-disabled', 'true');
        if (active) node.setAttribute('aria-current', 'page');
      } else {
        node.addEventListener('click', () => {
          if (!state.loading) loadUsers(page);
        });
      }

      return node;
    };

    const pages = [];
    if (last <= 7) {
      for (let page = 1; page <= last; page += 1) pages.push(page);
    } else {
      pages.push(1);
      if (current > 3) pages.push('...');
      for (let page = Math.max(2, current - 1); page <= Math.min(last - 1, current + 1); page += 1) pages.push(page);
      if (current < last - 2) pages.push('...');
      pages.push(last);
    }

    pageControls.appendChild(btn('Prev', current - 1, current <= 1, false));
    pages.forEach((page) => pageControls.appendChild(page === '...' ? btn('...', 0, true, false) : btn(String(page), page, false, page === current)));
    pageControls.appendChild(btn('Next', current + 1, current >= last, false));
  };

  const updateTotals = (others = {}) => {
    const totalUsers = toInt(others?.total_users, state.total);
    const totalWhatsAppUsers = toInt(others?.total_whatsapp_users, 0);
    const totalPriceSensitiveUsers = toInt(others?.total_price_sensitive_users, 0);

    if (totalUsersNode) {
      totalUsersNode.textContent = formatCount(totalUsers);
    }
    if (totalWhatsAppNode) {
      totalWhatsAppNode.textContent = formatCount(totalWhatsAppUsers);
    }
    if (totalPriceSensitiveNode) {
      totalPriceSensitiveNode.textContent = formatCount(totalPriceSensitiveUsers);
    }
  };

  const setLoading = (loading) => {
    state.loading = loading;
    applyBtn.disabled = loading;
    resetBtn.disabled = loading;
  };

  const updateUrlState = (page) => {
    const filters = readFilters();
    const url = new URL(window.location.href);

    if (filters.search) url.searchParams.set('search', filters.search);
    else {
      url.searchParams.delete('search');
      url.searchParams.delete('q');
    }

    if (filters.channel) url.searchParams.set('channel', filters.channel);
    else url.searchParams.delete('channel');

    if (filters.emotion) url.searchParams.set('emotion', filters.emotion);
    else url.searchParams.delete('emotion');

    if (filters.userType) url.searchParams.set('user_type', filters.userType);
    else url.searchParams.delete('user_type');

    if (filters.status) url.searchParams.set('status', filters.status);
    else url.searchParams.delete('status');

    if (page > 1) url.searchParams.set('page', String(page));
    else url.searchParams.delete('page');

    window.history.replaceState({}, '', url.toString());
  };

  async function loadUsers(page) {
    if (!token) {
      const message = 'Missing refresh token. Please login again.';
      filteredBadge.textContent = 'Unavailable';
      resultNode.textContent = message;
      setMessage(message);
      pageWrap.hidden = true;
      if (typeof window.showError === 'function') window.showError(message);
      return;
    }

    if (!apiBase) {
      const message = 'Backend API URL is missing.';
      filteredBadge.textContent = 'Unavailable';
      resultNode.textContent = message;
      setMessage(message);
      pageWrap.hidden = true;
      if (typeof window.showError === 'function') window.showError(message);
      return;
    }

    const requestId = ++state.requestId;
    const filters = readFilters();
    setLoading(true);
    pageWrap.hidden = true;
    pageControls.innerHTML = '';
    pageSummary.textContent = 'Page 1 of 1';
    setMessage('Loading users...');
    resultNode.textContent = 'Loading users...';

    try {
      const payload = await window.API.Admin.Users.list({
        apiBaseUrl: apiBase,
        refreshToken: token,
        page,
        perPage: state.perPage,
        search: filters.search,
        channel: filters.channel,
        emotion: filters.emotion,
        userType: filters.userType,
        status: filters.status,
        timeoutMs: 12000,
      });
      if (requestId !== state.requestId) return;

      const usersPayload = payload?.users && typeof payload.users === 'object' ? payload.users : {};
      const list = Array.isArray(usersPayload?.data) ? usersPayload.data : [];
      const pagination = usersPayload?.pagination_info && typeof usersPayload.pagination_info === 'object'
        ? usersPayload.pagination_info
        : (usersPayload?.pagination && typeof usersPayload.pagination === 'object' ? usersPayload.pagination : {});
      const others = payload?.others_data && typeof payload.others_data === 'object' ? payload.others_data : {};

      const total = Math.max(0, toInt(pagination.total, list.length));
      const perPageFromApi = Math.max(1, toInt(pagination.per_page, state.perPage));
      const derivedLastPage = Math.max(1, toInt(pagination.last_page, Math.ceil(total / perPageFromApi)));

      state.users = list;
      state.total = total;
      state.perPage = perPageFromApi;
      state.lastPage = derivedLastPage;
      state.page = Math.min(Math.max(1, toInt(pagination.current_page, page)), state.lastPage);
      state.from = Math.max(0, toInt(pagination.from, list.length ? ((state.page - 1) * state.perPage) + 1 : 0));
      state.to = Math.max(0, toInt(pagination.to, state.from ? state.from + list.length - 1 : 0));

      filteredBadge.textContent = `${formatCount(state.total)} shown`;
      updateTotals(others);
      renderTable();
      renderPagination();
      updateUrlState(state.page);
    } catch (error) {
      if (requestId !== state.requestId) return;

      const message = error?.isTimeout
        ? 'Request timed out. Please try again.'
        : (error?.message || 'Failed to load users.');

      state.users = [];
      state.total = 0;
      state.from = 0;
      state.to = 0;
      state.lastPage = 1;
      filteredBadge.textContent = 'Unavailable';
      updateTotals({});
      resultNode.textContent = message;
      setMessage(message);
      pageControls.innerHTML = '';
      pageSummary.textContent = 'Page 1 of 1';
      pageWrap.hidden = true;
      if (typeof window.showError === 'function') window.showError(message);
    } finally {
      if (requestId === state.requestId) {
        setLoading(false);
      }
    }
  }

  const setBanPending = (action = 'ban', clientId = '', userName = '') => {
    const nextAction = text(action).toLowerCase() === 'unban' ? 'unban' : 'ban';
    banState.pendingAction = nextAction;
    banState.pendingClientId = text(clientId);
    banState.pendingUserName = text(userName);

    if (banModalTitle) {
      banModalTitle.textContent = nextAction === 'unban' ? 'Unban User' : 'Ban User';
    }
    if (banModalDescription) {
      const identity = banState.pendingUserName
        ? `${banState.pendingUserName} (${banState.pendingClientId || 'N/A'})`
        : (banState.pendingClientId || 'this user');
      banModalDescription.textContent = nextAction === 'unban'
        ? `Are you sure you want to unban ${identity}?`
        : `Are you sure you want to ban ${identity}?`;
    }
    if (banConfirmBtn) {
      banConfirmBtn.textContent = nextAction === 'unban' ? 'Unban User' : 'Ban User';
      banConfirmBtn.classList.remove('btn-danger', 'btn-success');
      banConfirmBtn.classList.add(nextAction === 'unban' ? 'btn-success' : 'btn-danger');
      banConfirmBtn.disabled = false;
    }
  };

  const clearBanPending = () => {
    banState.pendingAction = 'ban';
    banState.pendingClientId = '';
    banState.pendingUserName = '';
  };

  const setBanLoading = (loading) => {
    banState.inFlight = loading;
    tbody.querySelectorAll('[data-user-ban-toggle]').forEach((node) => {
      if (node instanceof HTMLButtonElement) {
        node.disabled = loading;
      }
    });
    if (banConfirmBtn) {
      const action = banState.pendingAction === 'unban' ? 'unban' : 'ban';
      banConfirmBtn.disabled = loading;
      banConfirmBtn.textContent = loading
        ? (action === 'unban' ? 'Unbanning...' : 'Banning...')
        : (action === 'unban' ? 'Unban User' : 'Ban User');
    }
    banCloseButtons.forEach((node) => {
      if (node instanceof HTMLButtonElement) {
        node.disabled = loading;
      }
    });
  };

  tbody.addEventListener('click', (event) => {
    const trigger = event.target instanceof Element
      ? event.target.closest('[data-user-ban-toggle]')
      : null;

    if (!(trigger instanceof HTMLButtonElement)) return;
    event.preventDefault();

    if (state.loading || banState.inFlight || trigger.disabled) return;

    const clientId = text(trigger.dataset.clientId);
    const action = text(trigger.dataset.banAction).toLowerCase() === 'unban' ? 'unban' : 'ban';
    const userName = text(trigger.dataset.userName);
    if (!clientId) {
      if (typeof window.showError === 'function') window.showError('client_id is required');
      return;
    }

    if (!banModal) {
      if (typeof window.showError === 'function') window.showError('Ban confirmation modal is unavailable.');
      return;
    }

    setBanPending(action, clientId, userName);
    openModal('usersBanConfirmModal');
  });

  banCloseButtons.forEach((node) => {
    if (!(node instanceof HTMLButtonElement)) return;
    node.addEventListener('click', () => {
      if (banState.inFlight) return;
      clearBanPending();
    });
  });

  banModal?.addEventListener('click', (event) => {
    if (banState.inFlight) return;
    if (event.target === banModal) {
      clearBanPending();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    if (banState.inFlight) return;
    if (banModal?.classList.contains('active')) {
      clearBanPending();
    }
  });

  banConfirmBtn?.addEventListener('click', async () => {
    if (banState.inFlight) return;

    const clientId = text(banState.pendingClientId);
    const action = banState.pendingAction === 'unban' ? 'unban' : 'ban';
    if (!clientId) {
      if (typeof window.showError === 'function') window.showError('client_id is required');
      return;
    }

    setBanLoading(true);
    try {
      const payload = action === 'unban'
        ? await window.API.Admin.Users.unban({
            apiBaseUrl: apiBase,
            refreshToken: token,
            clientId,
            timeoutMs: 12000,
          })
        : await window.API.Admin.Users.ban({
            apiBaseUrl: apiBase,
            refreshToken: token,
            clientId,
            timeoutMs: 12000,
          });

      closeAllModals();
      clearBanPending();

      if (typeof window.showSuccess === 'function') {
        const fallbackMessage = action === 'unban'
          ? 'user unbanned successfully'
          : 'user banned successfully';
        window.showSuccess(text(payload?.message) || fallbackMessage);
      }

      await loadUsers(state.page);
    } catch (error) {
      const message = error?.isTimeout
        ? 'Request timed out. Please try again.'
        : (error?.message || (action === 'unban' ? 'Failed to unban user.' : 'Failed to ban user.'));
      if (typeof window.showError === 'function') window.showError(message);
    } finally {
      setBanLoading(false);
    }
  });

  applyBtn.addEventListener('click', () => {
    if (state.loading) return;
    loadUsers(1);
  });

  resetBtn.addEventListener('click', () => {
    searchInput.value = '';
    channelSelect.value = 'all';
    emotionSelect.value = 'all';
    userTypeSelect.value = 'all';
    statusSelect.value = 'all';
    if (state.loading) return;
    loadUsers(1);
  });

  searchInput.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter') return;
    event.preventDefault();
    if (state.loading) return;
    loadUsers(1);
  });

  loadUsers(state.page);
}

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
// PRODUCTS: TYPE SELECTOR
// ══════════════════════════════════════════
function initProductCreateCategoryPicker() {
  const form = document.getElementById('createProductForm');
  if (!form) return;

  const picker = form.querySelector('[data-product-category-picker]');
  const valueInput = form.querySelector('#productCategory[data-product-category-value]');
  const toggleButton = form.querySelector('[data-product-category-toggle]');
  const labelNode = form.querySelector('[data-product-category-label]');
  const panel = form.querySelector('[data-product-category-panel]');
  const searchInput = form.querySelector('[data-product-category-search]');
  const optionsWrap = form.querySelector('[data-product-category-options]');

  if (!picker || !valueInput || !toggleButton || !labelNode || !panel || !searchInput || !optionsWrap) {
    return;
  }

  const text = (value) => String(value ?? '').trim();
  const toInt = (value, fallback = 0) => {
    const parsed = Number.parseInt(String(value ?? ''), 10);
    return Number.isFinite(parsed) ? parsed : fallback;
  };
  const escapeHtml = (value) => text(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');

  const apiBase = String(form.dataset.apiBaseUrl || 'http://localhost:8082').replace(/\/+$/, '');
  const getToken = () =>
    String(form.dataset.refreshToken || '').trim()
    || localStorage.getItem('refresh_token')
    || (window.API && typeof window.API.getToken === 'function' ? window.API.getToken() : '')
    || '';

  const optionByValue = new Map();
  const optionByName = new Map();

  const state = {
    loading: false,
    options: [],
    isOpen: false,
    selectedValue: text(valueInput.value),
    selectedLabel: text(valueInput.dataset.categoryLabel),
  };

  const registerOptionAlias = (alias, option) => {
    const normalized = text(alias).toLowerCase();
    if (!normalized || optionByName.has(normalized)) return;
    optionByName.set(normalized, option);
  };

  const registerOptionMaps = (options = []) => {
    optionByValue.clear();
    optionByName.clear();

    options.forEach((option) => {
      const value = text(option?.value);
      if (!value) return;
      optionByValue.set(value, option);
      registerOptionAlias(value, option);
      registerOptionAlias(option.name, option);
      registerOptionAlias(option.displayLabel, option);
      registerOptionAlias(`${option.parentName} ${option.name}`, option);
      registerOptionAlias(`${option.parentName}/${option.name}`, option);
    });
  };

  const buildCategoryOptions = (tree = []) => {
    const options = [];

    (Array.isArray(tree) ? tree : []).forEach((parentRaw) => {
      const parentName = text(parentRaw?.name);
      if (!parentName) return;

      const parentId = toInt(parentRaw?.id, 0);
      const parentValue = parentId > 0 ? String(parentId) : parentName;
      options.push({
        value: parentValue,
        name: parentName,
        parentName,
        level: 0,
        note: 'All sub-categories',
        displayLabel: parentName,
        searchText: `${parentName} all sub-categories parent`,
      });

      const childs = Array.isArray(parentRaw?.childs) ? parentRaw.childs : [];
      childs.forEach((childRaw) => {
        const childName = text(childRaw?.name);
        if (!childName) return;

        const childId = toInt(childRaw?.id, 0);
        const childValue = childId > 0 ? String(childId) : childName;
        options.push({
          value: childValue,
          name: childName,
          parentName,
          level: 1,
          note: `Sub-category of ${parentName}`,
          displayLabel: `${parentName} / ${childName}`,
          searchText: `${parentName} ${childName} sub-category child`,
        });
      });
    });

    return options;
  };

  const setPanelOpen = (nextOpen) => {
    state.isOpen = Boolean(nextOpen);
    panel.classList.toggle('hidden', !state.isOpen);
    toggleButton.classList.toggle('is-open', state.isOpen);
    toggleButton.setAttribute('aria-expanded', state.isOpen ? 'true' : 'false');

    if (state.isOpen) {
      window.setTimeout(() => {
        searchInput.focus();
      }, 0);
    }
  };

  const clearInvalid = () => {
    toggleButton.classList.remove('is-invalid');
  };

  const markInvalid = () => {
    toggleButton.classList.add('is-invalid');
  };

  const updateLabel = () => {
    const value = text(state.selectedLabel);
    if (value) {
      labelNode.textContent = value;
      labelNode.classList.remove('is-placeholder');
      clearInvalid();
      return;
    }

    labelNode.textContent = 'Select category';
    labelNode.classList.add('is-placeholder');
  };

  const resolveOption = (rawValue) => {
    const normalized = text(rawValue);
    if (!normalized) return null;
    if (optionByValue.has(normalized)) return optionByValue.get(normalized) || null;
    return optionByName.get(normalized.toLowerCase()) || null;
  };

  const renderOptions = () => {
    const keyword = text(searchInput.value).toLowerCase();
    const visibleOptions = keyword
      ? state.options.filter((option) => option.searchText.includes(keyword))
      : state.options;

    if (!visibleOptions.length) {
      const message = state.loading
        ? 'Loading categories...'
        : (keyword ? 'No category matched your search.' : 'No categories available.');
      optionsWrap.innerHTML = `<div class="products-category-picker-empty">${escapeHtml(message)}</div>`;
      return;
    }

    optionsWrap.innerHTML = visibleOptions.map((option) => {
      const isSelected = text(state.selectedValue) === option.value;
      const rowClass = [
        'products-category-option',
        option.level > 0 ? 'is-child' : 'is-parent',
        isSelected ? 'is-selected' : '',
      ].filter(Boolean).join(' ');

      return `
        <button
          type="button"
          class="${rowClass}"
          data-product-category-option
          data-category-value="${escapeHtml(option.value)}"
          role="option"
          aria-selected="${isSelected ? 'true' : 'false'}"
        >
          <span class="products-category-option-check">${isSelected ? '&#10003;' : ''}</span>
          <span class="products-category-option-meta">
            <span class="products-category-option-name">${escapeHtml(option.name)}</span>
            <span class="products-category-option-note">${escapeHtml(option.note)}</span>
          </span>
        </button>
      `;
    }).join('');
  };

  const selectCategoryValue = (rawValue, {emit = true, closePanel = true, fallbackLabel = ''} = {}) => {
    const previousValue = text(valueInput.value);
    const option = resolveOption(rawValue);

    if (option) {
      state.selectedValue = option.value;
      state.selectedLabel = option.displayLabel;
      valueInput.value = option.value;
      valueInput.dataset.categoryLabel = option.displayLabel;
    } else {
      const fallbackValue = text(rawValue);
      state.selectedValue = fallbackValue;
      state.selectedLabel = text(fallbackLabel) || fallbackValue;
      valueInput.value = fallbackValue;
      if (state.selectedLabel) {
        valueInput.dataset.categoryLabel = state.selectedLabel;
      } else {
        delete valueInput.dataset.categoryLabel;
      }
    }

    updateLabel();
    renderOptions();

    if (closePanel) {
      setPanelOpen(false);
    }

    const currentValue = text(valueInput.value);
    if (emit && previousValue !== currentValue) {
      valueInput.dispatchEvent(new Event('input', {bubbles: true}));
      valueInput.dispatchEvent(new Event('change', {bubbles: true}));
    }
  };

  const syncSelectionFromInput = () => {
    const rawValue = text(valueInput.value);
    const rawLabel = text(valueInput.dataset.categoryLabel);
    selectCategoryValue(rawValue, {
      emit: false,
      closePanel: false,
      fallbackLabel: rawLabel,
    });
  };

  const loadCategories = async () => {
    if (!window.API?.Admin?.Products?.categoriesTree || !apiBase) {
      optionsWrap.innerHTML = '<div class="products-category-picker-empty">Category API is unavailable.</div>';
      return;
    }

    const token = getToken();
    if (!token) {
      optionsWrap.innerHTML = '<div class="products-category-picker-empty">Missing refresh token. Please login again.</div>';
      return;
    }

    state.loading = true;
    renderOptions();

    try {
      const payload = await window.API.Admin.Products.categoriesTree({
        apiBaseUrl: apiBase,
        refreshToken: token,
        timeoutMs: 12000,
      });

      state.options = buildCategoryOptions(payload);
      registerOptionMaps(state.options);
      syncSelectionFromInput();
      renderOptions();
    } catch (error) {
      state.options = [];
      registerOptionMaps([]);
      optionsWrap.innerHTML = `<div class="products-category-picker-empty">${escapeHtml(error?.message || 'Unable to load categories.')}</div>`;
    } finally {
      state.loading = false;
    }
  };

  toggleButton.addEventListener('click', () => {
    setPanelOpen(!state.isOpen);
    if (state.isOpen) {
      searchInput.value = '';
      renderOptions();
    }
  });

  picker.addEventListener('click', (event) => {
    const optionButton = event.target.closest('[data-product-category-option]');
    if (!optionButton || !picker.contains(optionButton)) return;

    const value = text(optionButton.getAttribute('data-category-value'));
    selectCategoryValue(value, {emit: true, closePanel: true});
  });

  searchInput.addEventListener('input', () => {
    renderOptions();
  });

  searchInput.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      event.preventDefault();
      setPanelOpen(false);
      toggleButton.focus();
      return;
    }

    if (event.key === 'Enter') {
      const firstOption = optionsWrap.querySelector('[data-product-category-option]');
      if (!firstOption) return;
      event.preventDefault();
      const value = text(firstOption.getAttribute('data-category-value'));
      selectCategoryValue(value, {emit: true, closePanel: true});
    }
  });

  document.addEventListener('click', (event) => {
    if (!state.isOpen) return;
    if (picker.contains(event.target)) return;
    setPanelOpen(false);
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && state.isOpen) {
      setPanelOpen(false);
      toggleButton.focus();
    }
  });

  valueInput.addEventListener('change', syncSelectionFromInput);

  window._productCategoryPicker = {
    setValue(value, label = '') {
      clearInvalid();
      selectCategoryValue(value, {emit: true, closePanel: false, fallbackLabel: label});
    },
    getValue() {
      return text(valueInput.value);
    },
    focus() {
      toggleButton.focus();
    },
    markInvalid,
    clearInvalid,
  };

  syncSelectionFromInput();
  loadCategories();
}

function initProductTypeSelector() {
  const form = document.getElementById('createProductForm');
  if (!form) return;

  const cards = Array.from(document.querySelectorAll('[data-product-type-card]'));
  if (!cards.length) return;

  const typeSections = Array.from(document.querySelectorAll('[data-product-type-section]'));
  const physicalOnlyGroups = Array.from(document.querySelectorAll('[data-physical-only]'));
  const nonPhysicalOnlyGroups = Array.from(document.querySelectorAll('[data-non-physical-only]'));
  const typeBadge = document.querySelector('[data-product-type-badge]');
  const checklistContainer = document.querySelector('[data-product-type-checklist]');
  const saveButton = document.querySelector('[data-product-save-button]');
  const settingsLink = document.querySelector('[data-product-settings-link]');
  const settingsWarning = document.querySelector('[data-product-settings-warning]');
  const configuredTypeLabelNode = document.querySelector('[data-product-configured-type-label]');
  const switchCopyNode = document.querySelector('[data-product-type-switch-copy]');
  const isEditMode = String(form.dataset.formMode || '').trim().toLowerCase() === 'edit';
  const lockProductType = form.dataset.lockProductType === '1' || isEditMode;

  const checklistData = {
    physical: [
      'Add product name & category',
      'Upload cover image (1080 × 1080)',
      'Select shipping profile',
      'Choose: No variants or Has variants',
      'Set pricing & inventory for each variant',
      'Add weight for variants / simple product',
      'Fill SEO fields for discoverability',
    ],
    downloadable: [
      'Add product name & category',
      'Upload an attractive cover image',
      'Set product price & bargaining price',
      'Set file sharing to "Anyone with the link"',
      'Paste the Google Drive share link',
      'Add access instructions if needed',
      'Fill SEO fields for discoverability',
    ],
    subscription: [
      'Add product name & category',
      'Upload an attractive cover image',
      'Set subscription price & bargaining price',
      'Add at least one subscription slot',
      'Fill credentials — leave empty if not available',
      'Fill SEO fields for discoverability',
    ],
  };

  const renderChecklist = (type) => {
    if (!checklistContainer) return;
    const items = checklistData[type] || [];
    checklistContainer.innerHTML = `<ul class="products-create-checklist">${
      items.map((item) => `<li>${item}</li>`).join('')
    }</ul>`;
  };

  const badgeClasses = {
    physical: 'badge-info',
    downloadable: 'badge-primary',
    subscription: 'badge-warning',
    package: 'badge-success',
  };

  const badgeLabels = {
    physical: 'Physical',
    downloadable: 'Downloadable',
    subscription: 'Subscription',
    package: 'Package',
  };
  const validTypes = ['physical', 'downloadable', 'subscription', 'package'];
  const configuredTypeRaw = String(form?.dataset.configuredProductType || '').trim().toLowerCase();
  const configuredType = validTypes.includes(configuredTypeRaw) ? configuredTypeRaw : 'physical';

  const syncProductTypeLockState = (disabled) => {
    cards.forEach((card) => {
      const input = card.querySelector('input[type="radio"]');
      card.classList.toggle('is-locked', disabled);
      card.setAttribute('aria-disabled', disabled ? 'true' : 'false');

      if (input) {
        input.disabled = disabled;
      }
    });
  };

  const renderTypeSwitchState = (type) => {
    const configuredLabel = badgeLabels[configuredType] || configuredType;
    const selectedLabel = badgeLabels[type] || type;
    const isMismatch = !lockProductType && type !== configuredType;

    if (configuredTypeLabelNode) {
      configuredTypeLabelNode.textContent = lockProductType
        ? `Product type: ${selectedLabel} (locked)`
        : `Current store mode: ${configuredLabel}`;
    }

    if (switchCopyNode) {
      if (lockProductType) {
        switchCopyNode.textContent = type === configuredType
          ? `Product type is fixed after creation and cannot be changed here. This product will remain ${selectedLabel.toLowerCase()}.`
          : `Product type is fixed after creation and cannot be changed here. This product remains ${selectedLabel.toLowerCase()}, even though the current store mode is ${configuredLabel}.`;
      } else {
        switchCopyNode.textContent = isMismatch
          ? `Previewing ${selectedLabel} inputs while the store is still set to ${configuredLabel}. Open Shop Settings to switch the real store type before saving.`
          : `This form matches your current ${configuredLabel.toLowerCase()} store setup, so you can continue and save normally.`;
      }
    }

    if (saveButton) {
      saveButton.classList.toggle('hidden', isMismatch);
    }

    if (settingsLink) {
      settingsLink.classList.toggle('hidden', lockProductType || !isMismatch);
    }

    if (settingsWarning) {
      settingsWarning.classList.toggle('hidden', lockProductType || !isMismatch);
      settingsWarning.textContent = (!lockProductType && isMismatch)
        ? `Selected form type: ${selectedLabel}. Current store type: ${configuredLabel}. Change the store type in Shop Settings before saving products in this mode.`
        : '';
    }

    if (form) {
      form.dataset.selectedProductType = type;
      form.dataset.productTypeMismatch = isMismatch ? '1' : '0';
      form.dataset.productTypeLocked = lockProductType ? '1' : '0';
    }
  };

  const setActiveType = (type) => {
    // Update card active states
    cards.forEach((card) => {
      const input = card.querySelector('input[type="radio"]');
      const isActive = input?.value === type;
      card.classList.toggle('is-active', isActive);
    });

    // Show / hide type-specific sections
    typeSections.forEach((section) => {
      const isMatch = section.dataset.productTypeSection === type;
      section.classList.toggle('hidden', !isMatch);
    });

    // Show / hide physical-only fields
    const isPhysical = type === 'physical';
    physicalOnlyGroups.forEach((group) => {
      group.classList.toggle('hidden', !isPhysical);
      group.querySelectorAll('input, select, textarea').forEach((input) => {
        input.disabled = !isPhysical;
        if (!isPhysical) {
          input.value = '';
        }
      });
    });

    // Show / hide non-physical-only fields (for downloadable, subscription, package)
    const isNonPhysical = type !== 'physical';
    nonPhysicalOnlyGroups.forEach((group) => {
      group.classList.toggle('hidden', !isNonPhysical);
      group.querySelectorAll('input, select, textarea').forEach((input) => {
        input.disabled = !isNonPhysical;
        if (!isNonPhysical) {
          input.value = '';
        }
      });
    });

    // Update sidebar badge
    if (typeBadge) {
      typeBadge.className = `badge ${badgeClasses[type] || 'badge-info'}`;
      typeBadge.textContent = badgeLabels[type] || type;
    }

    // Render type checklist
    renderChecklist(type);
    renderTypeSwitchState(type);
  };

  cards.forEach((card) => {
    const input = card.querySelector('input[type="radio"]');
    if (input) {
      input.addEventListener('change', () => {
        if (input.checked) {
          setActiveType(input.value);
        }
      });
    }

    card.addEventListener('click', (event) => {
      event.preventDefault();

      if (lockProductType) {
        return;
      }

      const input = card.querySelector('input[type="radio"]');
      if (!input) return;
      input.checked = true;
      input.dispatchEvent(new Event('change', { bubbles: true }));
      sessionStorage.setItem('product_create_type', input.value);
    });
  });

  const initialType = configuredType || 'physical';

  // Sync the radio to match the restored type
  cards.forEach((card) => {
    const input = card.querySelector('input[type="radio"]');
    if (input) input.checked = input.value === initialType;
  });

  syncProductTypeLockState(lockProductType);
  setActiveType(initialType);
}

// ══════════════════════════════════════════
// PRODUCTS: DOWNLOADABLE LINK TYPE TOGGLE
// ══════════════════════════════════════════
function initDownloadableLinkType() {
  const cards = Array.from(document.querySelectorAll('[data-drive-link-type-card]'));
  if (!cards.length) return;

  const publicInfo = document.querySelector('[data-drive-public-info]');
  const privateInfo = document.querySelector('[data-drive-private-info]');
  const publicHelp = document.querySelector('[data-drive-link-help-public]');
  const privateHelp = document.querySelector('[data-drive-link-help-private]');
  const linkInput = document.getElementById('productDriveLink');

  const setLinkType = (type) => {
    const isPrivate = type === 'private';

    cards.forEach((card) => {
      const input = card.querySelector('input[type="radio"]');
      card.classList.toggle('is-active', input?.value === type);
    });

    publicInfo?.classList.toggle('hidden', isPrivate);
    privateInfo?.classList.toggle('hidden', !isPrivate);
    publicHelp?.classList.toggle('hidden', isPrivate);
    privateHelp?.classList.toggle('hidden', !isPrivate);

    if (linkInput) {
      linkInput.placeholder = isPrivate
        ? 'https://drive.google.com/file/d/XXXXXXXXXXXXXXXXXXXX/view'
        : 'https://drive.google.com/file/d/XXXXXXXXXXXXXXXXXXXX/view?usp=sharing';
    }
  };

  cards.forEach((card) => {
    card.addEventListener('click', () => {
      const input = card.querySelector('input[type="radio"]');
      if (!input) return;
      input.checked = true;
      sessionStorage.setItem('product_drive_link_type', input.value);
      setLinkType(input.value);
    });
  });

  // Restore from session, fall back to checked radio, then default
  const savedLinkType = sessionStorage.getItem('product_drive_link_type');
  const validLinkTypes = ['public', 'private'];
  const initialLinkType = (savedLinkType && validLinkTypes.includes(savedLinkType))
    ? savedLinkType
    : (cards.find((c) => c.querySelector('input[type="radio"]')?.checked)?.querySelector('input[type="radio"]')?.value || 'public');

  cards.forEach((card) => {
    const input = card.querySelector('input[type="radio"]');
    if (input) input.checked = input.value === initialLinkType;
  });

  setLinkType(initialLinkType);
}

// ══════════════════════════════════════════
// PRODUCTS: SUBSCRIPTION ENTRY MANAGER
// ══════════════════════════════════════════
function initSubscriptionEntries() {
  const addButton = document.querySelector('[data-subscription-add]');
  const list = document.querySelector('[data-subscription-list]');
  const countLabel = document.querySelector('[data-subscription-count]');

  if (!addButton || !list) return;

  let entrySequence = 0;

  const updateCountLabel = () => {
    const total = list.querySelectorAll('[data-subscription-entry]').length;
    if (!countLabel) return;
    if (total === 0) {
      countLabel.classList.add('hidden');
      countLabel.textContent = '';
    } else {
      countLabel.classList.remove('hidden');
      countLabel.textContent = `${total} slot${total > 1 ? 's' : ''} added`;
    }
  };

  const renumberEntries = () => {
    const entries = Array.from(list.querySelectorAll('[data-subscription-entry]'));
    entries.forEach((entry, index) => {
      const numEl = entry.querySelector('[data-subscription-num]');
      if (numEl) numEl.textContent = `Subscription #${index + 1}`;
    });
    updateCountLabel();
  };

  const showEmpty = () => {
    list.innerHTML = `
      <div class="products-subscription-empty">
        No subscription slots yet. Click <strong>Add Subscription</strong> below to get started.
      </div>
    `;
  };

  const addEntry = (seed = null) => {
    entrySequence += 1;
    const id = entrySequence;

    // Remove empty state if present
    const emptyEl = list.querySelector('.products-subscription-empty');
    if (emptyEl) emptyEl.remove();

    const entry = document.createElement('div');
    entry.className = 'products-subscription-entry';
    entry.dataset.subscriptionEntry = id;

    entry.innerHTML = `
      <div class="products-subscription-entry-header">
        <span class="products-subscription-entry-num" data-subscription-num>Subscription #1</span>
        <button type="button" class="btn btn-ghost btn-sm" data-subscription-remove="${id}" aria-label="Remove subscription slot">
          Remove
        </button>
      </div>
      <div class="products-subscription-entry-body">
        <div class="form-group">
          <label class="form-label">
            Email
            <span class="products-type-optional">— leave empty if not available</span>
          </label>
          <input
            type="email"
            class="form-input"
            name="subscriptions[${id}][email]"
            placeholder="user@example.com"
            autocomplete="off"
          >
        </div>
        <div class="form-group">
          <label class="form-label">
            Mobile Number
            <span class="products-type-optional">— leave empty if not available</span>
          </label>
          <input
            type="tel"
            class="form-input"
            name="subscriptions[${id}][mobile]"
            placeholder="+8801XXXXXXXXX"
            autocomplete="off"
          >
        </div>
        <div class="form-group">
          <label class="form-label">
            Username
            <span class="products-type-optional">— leave empty if not available</span>
          </label>
          <input
            type="text"
            class="form-input"
            name="subscriptions[${id}][username]"
            placeholder="e.g. john_doe"
            autocomplete="off"
          >
        </div>
        <div class="form-group">
          <label class="form-label">
            Password
            <span class="products-type-optional">— leave empty if not available</span>
          </label>
          <input
            type="text"
            class="form-input"
            name="subscriptions[${id}][password]"
            placeholder="Enter password"
            autocomplete="new-password"
          >
        </div>
      </div>
    `;

    entry.querySelector(`[data-subscription-remove="${id}"]`)?.addEventListener('click', () => {
      entry.remove();
      if (!list.querySelector('[data-subscription-entry]')) {
        showEmpty();
      }
      renumberEntries();
    });

    list.appendChild(entry);

    if (seed && typeof seed === 'object') {
      const setValue = (selector, value) => {
        const field = entry.querySelector(selector);
        if (!field) return;
        field.value = String(value ?? '');
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('change', { bubbles: true }));
      };

      setValue(`[name="subscriptions[${id}][email]"]`, seed.email || '');
      setValue(`[name="subscriptions[${id}][mobile]"]`, seed.number || seed.mobile || '');
      setValue(`[name="subscriptions[${id}][username]"]`, seed.username || '');
      setValue(`[name="subscriptions[${id}][password]"]`, seed.password || '');
    }

    renumberEntries();

    // Scroll the new entry into view smoothly
    entry.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  };

  addButton.addEventListener('click', addEntry);
  window._productCreateSubscriptions = {
    clear() {
      entrySequence = 0;
      showEmpty();
      renumberEntries();
    },
    add(seed = null) {
      addEntry(seed);
    },
    setEntries(items = []) {
      this.clear();
      if (!Array.isArray(items) || !items.length) return;
      items.forEach((item) => addEntry(item));
    },
  };
  showEmpty();
}

// ══════════════════════════════════════════
// PRODUCTS: PACKAGE FACILITY MANAGER
// ══════════════════════════════════════════
function initFacilityEntries() {
  const addButton = document.querySelector('[data-facility-add]');
  const list = document.querySelector('[data-facilities-list]');
  const countLabel = document.querySelector('[data-facilities-count]');

  if (!addButton || !list) return;

  let entrySequence = 0;

  const updateCountLabel = () => {
    const total = list.querySelectorAll('[data-facility-entry]').length;
    if (!countLabel) return;
    if (total === 0) {
      countLabel.classList.add('hidden');
      countLabel.textContent = '';
    } else {
      countLabel.classList.remove('hidden');
      countLabel.textContent = `${total} facilit${total > 1 ? 'ies' : 'y'} added`;
    }
  };

  const renumberEntries = () => {
    const entries = Array.from(list.querySelectorAll('[data-facility-entry]'));
    entries.forEach((entry, index) => {
      const numEl = entry.querySelector('[data-facility-num]');
      if (numEl) numEl.textContent = `Facility #${index + 1}`;
    });
    updateCountLabel();
  };

  const showEmpty = () => {
    list.innerHTML = `
      <div class="products-subscription-empty">
        No facilities added yet. Click <strong>Add Facility</strong> below to get started.
      </div>
    `;
  };

  const addEntry = () => {
    entrySequence += 1;
    const id = entrySequence;

    // Remove empty state if present
    const emptyEl = list.querySelector('.products-subscription-empty');
    if (emptyEl) emptyEl.remove();

    const entry = document.createElement('div');
    entry.className = 'products-subscription-entry';
    entry.dataset.facilityEntry = id;

    entry.innerHTML = `
      <div class="products-subscription-entry-header">
        <span class="products-subscription-entry-num" data-facility-num>Facility #1</span>
        <button type="button" class="btn btn-ghost btn-sm" data-facility-remove="${id}" aria-label="Remove facility">
          Remove
        </button>
      </div>
      <div class="products-subscription-entry-body">
        <div class="form-group">
          <label class="form-label">
            Facility Name
          </label>
          <input
            type="text"
            class="form-input"
            name="facilities[${id}][name]"
            placeholder="e.g. Free Shipping, 24/7 Support, Warranty"
            required
            autocomplete="off"
          >
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <div class="products-publish-options mt-sm">
            <label class="products-radio-item">
              <input type="radio" name="facilities[${id}][status]" value="enabled" checked>
              <span>Enabled</span>
            </label>
            <label class="products-radio-item">
              <input type="radio" name="facilities[${id}][status]" value="disabled">
              <span>Disabled</span>
            </label>
          </div>
          <small class="form-help">Disabled facilities will not be shown to customers.</small>
        </div>
      </div>
    `;

    entry.querySelector(`[data-facility-remove="${id}"]`)?.addEventListener('click', () => {
      entry.remove();
      if (!list.querySelector('[data-facility-entry]')) {
        showEmpty();
      }
      renumberEntries();
    });

    list.appendChild(entry);
    renumberEntries();

    // Scroll the new entry into view smoothly
    entry.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  };

  addButton.addEventListener('click', addEntry);
  showEmpty();
}

// ══════════════════════════════════════════
// PRODUCTS: VARIANT SYSTEM (NEW BEAUTIFUL UI)
// ══════════════════════════════════════════
function initProductVariants() {
  const toggleCards = Array.from(document.querySelectorAll('[data-variant-toggle-card]'));
  const toggleInputs = Array.from(document.querySelectorAll('[data-has-variants-toggle]'));
  const simpleInventory = document.querySelector('[data-simple-inventory]');
  const variantManagement = document.querySelector('[data-variant-management]');
  const variantList = document.querySelector('[data-variant-list]');
  const addVariantBtn = document.querySelector('[data-add-variant-btn]');

  if (!toggleInputs.length || !simpleInventory || !variantManagement) return;

  let variantCounter = 0;
  let variants = [];

  // Handle toggle between simple and variant mode
  const handleToggle = (value) => {
    // Update active card state — ensure only the selected card is active
    toggleCards.forEach(card => {
      const input = card.querySelector('input[type="radio"]');
      card.classList.toggle('is-active', input?.value === value);
    });

    // Show/hide appropriate sections
    if (value === 'no') {
      simpleInventory.classList.remove('hidden');
      variantManagement.classList.add('hidden');
      // Remove all variant cards when switching back to "No Variants"
      variants = [];
      variantCounter = 0;
      if (variantList) {
        variantList.innerHTML = `
          <div class="products-variant-empty-state">
            <span class="products-variant-empty-icon">📦</span>
            <p>No variants added yet</p>
            <small>Click the button below to add your first variant</small>
          </div>
        `;
      }
    } else {
      simpleInventory.classList.add('hidden');
      variantManagement.classList.remove('hidden');
    }
  };

  // Add variant card
  const addVariant = (seed = null) => {
    variantCounter += 1;
    const id = variantCounter;

    // Remove empty state if present
    const emptyState = variantList.querySelector('.products-variant-empty-state');
    if (emptyState) emptyState.remove();

    // Create variant card
    const card = document.createElement('div');
    card.className = 'products-variant-card';
    card.dataset.variantCard = id;

    card.innerHTML = `
      <div class="products-variant-card-header">
        <span class="products-variant-card-title">
          <span style="color: var(--brand-primary);">📦</span> Variant #${id}
        </span>
        <button type="button" class="btn btn-ghost btn-sm" data-remove-variant="${id}">
          Remove
        </button>
      </div>
      <div class="products-variant-card-body">

        <!-- Section: Attributes -->
        <div class="products-variant-section-divider">
          <span class="products-variant-section-title">🎨 Attributes</span>
        </div>

        <!-- Color Attribute -->
        <div class="products-variant-attribute-group">
          <div class="products-variant-attribute-header">
            <span class="products-variant-attribute-label">
              <span style="font-size: 16px;">🎨</span> Color
            </span>
            <label class="products-variant-switch">
              <input type="checkbox" data-variant-color-toggle="${id}">
              <span class="products-variant-switch-track">
                <span class="products-variant-switch-thumb"></span>
              </span>
              <span class="products-variant-switch-label">Enable</span>
            </label>
          </div>
          <div class="products-variant-attribute-input is-disabled" data-variant-color-input-group="${id}">
            <input
              type="text"
              class="form-input"
              name="variants[${id}][color]"
              placeholder="e.g. Red, Blue, Black"
              disabled
              data-variant-color-field="${id}"
            >
          </div>
        </div>

        <!-- Size Attribute -->
        <div class="products-variant-attribute-group">
          <div class="products-variant-attribute-header">
            <span class="products-variant-attribute-label">
              <span style="font-size: 16px;">📏</span> Size
            </span>
            <label class="products-variant-switch">
              <input type="checkbox" data-variant-size-toggle="${id}">
              <span class="products-variant-switch-track">
                <span class="products-variant-switch-thumb"></span>
              </span>
              <span class="products-variant-switch-label">Enable</span>
            </label>
          </div>
          <div class="products-variant-attribute-input is-disabled" data-variant-size-input-group="${id}">
            <input
              type="text"
              class="form-input"
              name="variants[${id}][size]"
              placeholder="e.g. S, M, L, XL, 32, 34"
              disabled
              data-variant-size-field="${id}"
            >
          </div>
        </div>

        <!-- Section: Pricing -->
        <div class="products-variant-section-divider">
          <span class="products-variant-section-title">💰 Pricing</span>
        </div>

        <div class="products-create-grid">
          <div class="form-group">
            <label class="form-label">
              <span class="products-label-with-icon">
                💵 Price (BDT)
              </span>
            </label>
            <input
              type="number"
              class="form-input"
              name="variants[${id}][price]"
              min="0"
              step="0.01"
              placeholder="e.g. 1500"
              data-variant-price="${id}"
            >
          </div>
          <div class="form-group">
            <label class="form-label">
              <span class="products-label-with-icon">
                💸 Bargaining Price (BDT)
              </span>
            </label>
            <input
              type="number"
              class="form-input"
              name="variants[${id}][bargaining_price]"
              min="0"
              step="0.01"
              placeholder="e.g. 1350"
              data-variant-bargaining="${id}"
            >
          </div>
        </div>

        <!-- Section: Inventory & Shipping -->
        <div class="products-variant-section-divider">
          <span class="products-variant-section-title">📦 Inventory & Shipping</span>
        </div>

        <div class="products-create-grid">
          <div class="form-group">
            <label class="form-label">
              <span class="products-label-with-icon">
                📦 Quantity
              </span>
            </label>
            <input
              type="number"
              class="form-input"
              name="variants[${id}][quantity]"
              min="0"
              step="1"
              placeholder="e.g. 50"
              required
              data-variant-qty="${id}"
            >
          </div>
          <div class="form-group">
            <label class="form-label">
              <span class="products-label-with-icon">
                🔔 Alert Quantity
              </span>
            </label>
            <input
              type="number"
              class="form-input"
              name="variants[${id}][alert_qty]"
              min="1"
              step="1"
              placeholder="e.g. 10"
              data-variant-alert="${id}"
            >
          </div>
          <div class="products-variant-attribute-input">
            <label class="form-label">
              <span class="products-label-with-icon">
                ⚖️ Weight (kg)
              </span>
            </label>
            <input
              type="number"
              class="form-input"
              name="variants[${id}][weight]"
              min="0"
              step="0.01"
              placeholder="e.g. 0.40"
              data-variant-weight="${id}"
            >
          </div>
        </div>

      </div>
    `;

    // Attach event listeners for this variant
    const colorToggle = card.querySelector(`[data-variant-color-toggle="${id}"]`);
    const colorInputGroup = card.querySelector(`[data-variant-color-input-group="${id}"]`);
    const colorField = card.querySelector(`[data-variant-color-field="${id}"]`);

    const sizeToggle = card.querySelector(`[data-variant-size-toggle="${id}"]`);
    const sizeInputGroup = card.querySelector(`[data-variant-size-input-group="${id}"]`);
    const sizeField = card.querySelector(`[data-variant-size-field="${id}"]`);

    const removeBtn = card.querySelector(`[data-remove-variant="${id}"]`);

    // Color toggle
    colorToggle?.addEventListener('change', () => {
      const isEnabled = colorToggle.checked;
      colorInputGroup?.classList.toggle('is-disabled', !isEnabled);
      if (colorField) colorField.disabled = !isEnabled;
      if (!isEnabled && colorField) colorField.value = '';
    });

    // Size toggle
    sizeToggle?.addEventListener('change', () => {
      const isEnabled = sizeToggle.checked;
      sizeInputGroup?.classList.toggle('is-disabled', !isEnabled);
      if (sizeField) sizeField.disabled = !isEnabled;
      if (!isEnabled && sizeField) sizeField.value = '';
    });

    // Remove variant
    removeBtn?.addEventListener('click', () => {
      card.remove();
      variants = variants.filter(v => v.id !== id);

      // Show empty state if no variants left
      if (!variantList.querySelector('[data-variant-card]')) {
        showEmptyState();
      }
    });

    variants.push({ id });
    variantList.appendChild(card);

    if (seed && typeof seed === 'object') {
      const setValue = (selector, value) => {
        const field = card.querySelector(selector);
        if (!field) return;
        field.value = String(value ?? '');
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('change', { bubbles: true }));
      };

      const enableColor = Boolean(seed.have_color);
      const enableSize = Boolean(seed.have_size);
      if (colorToggle) {
        colorToggle.checked = enableColor;
        colorToggle.dispatchEvent(new Event('change', { bubbles: true }));
      }
      if (sizeToggle) {
        sizeToggle.checked = enableSize;
        sizeToggle.dispatchEvent(new Event('change', { bubbles: true }));
      }

      setValue(`[data-variant-color-field="${id}"]`, enableColor ? (seed.color || '') : '');
      setValue(`[data-variant-size-field="${id}"]`, enableSize ? (seed.size || '') : '');
      setValue(`[data-variant-price="${id}"]`, seed.price ?? seed.product_price ?? '');
      setValue(`[data-variant-bargaining="${id}"]`, seed.bargaining_price ?? '');
      setValue(`[data-variant-qty="${id}"]`, seed.qty ?? seed.quantity ?? 0);
      setValue(`[data-variant-alert="${id}"]`, seed.alert_qty ?? 0);
      setValue(`[data-variant-weight="${id}"]`, seed.weight ?? '');
    }

    // Scroll into view
    card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  };

  // Show empty state
  const showEmptyState = () => {
    variantList.innerHTML = `
      <div class="products-variant-empty-state">
        <span class="products-variant-empty-icon">📦</span>
        <p>No variants added yet</p>
        <small>Click the button below to add your first variant</small>
      </div>
    `;
  };

  // Toggle card click handlers
  // Guard against the radio's bubbled click re-triggering the label's click listener
  toggleCards.forEach(card => {
    card.addEventListener('click', (e) => {
      if (e.target.type === 'radio') return; // radio change handler will take care of it
      const input = card.querySelector('input[type="radio"]');
      if (!input) return;
      input.checked = true;
      handleToggle(input.value);
    });
  });

  // Toggle input change handlers
  toggleInputs.forEach(input => {
    input.addEventListener('change', () => {
      if (input.checked) {
        handleToggle(input.value);
      }
    });
  });

  // Add variant button
  addVariantBtn?.addEventListener('click', addVariant);

  // Initialize with default state
  const checkedInput = toggleInputs.find(input => input.checked);
  if (checkedInput) {
    handleToggle(checkedInput.value);
  }

  window._productCreateVariants = {
    setMode(hasVariants) {
      const targetValue = hasVariants ? 'yes' : 'no';
      const targetInput = toggleInputs.find((input) => input.value === targetValue);
      if (targetInput) {
        targetInput.checked = true;
        targetInput.dispatchEvent(new Event('change', { bubbles: true }));
      } else {
        handleToggle(targetValue);
      }
    },
    clear() {
      variants = [];
      variantCounter = 0;
      showEmptyState();
    },
    add(seed = null) {
      addVariant(seed);
    },
    setVariants(items = []) {
      this.clear();
      if (!Array.isArray(items) || !items.length) return;
      items.forEach((item) => addVariant(item));
    },
  };
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

  const readFileAsDataUrl = (file) => new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = (event) => resolve(String(event?.target?.result || '').trim());
    reader.onerror = () => reject(new Error('Unable to read media file.'));
    reader.readAsDataURL(file);
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

  const SLIDER_STORAGE_KEY = 'product_slider_status';

  sliderInputs.forEach((input) => {
    input.addEventListener('change', () => {
      sessionStorage.setItem(SLIDER_STORAGE_KEY, input.value);
      updateSliderStatus();
    });
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
      const sourceDataUrl = await readFileAsDataUrl(file);
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
        sourceUrl: sourceDataUrl,
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
      sourceUrl: '',
      width: null,
      height: null,
    };

    try {
      nextItem.sourceUrl = await readFileAsDataUrl(file);
    } catch {
      releaseMediaUrl(nextItem);
      showError('Unable to process selected media file. Please try another file.');
      sliderItemInput.value = '';
      return;
    }

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

  const savedSliderStatus = sessionStorage.getItem(SLIDER_STORAGE_KEY);
  if (savedSliderStatus) {
    const input = sliderInputs.find((i) => i.value === savedSliderStatus);
    if (input) input.checked = true;
  }

  updateSliderStatus();

  window._productCreateMedia = {
    getCoverImage: () => {
      const source = String(coverItem?.sourceUrl || '').trim();
      return source.startsWith('data:image/') ? source : null;
    },
    getSliderItems: () => sliderItems
      .map((item) => {
        const sourceUrl = String(item.sourceUrl || item.url || '').trim();
        return {
          media_type: item.type === 'youtube' ? 'yt_video' : item.type === 'video' ? 'upload_video' : 'image',
          source_url: sourceUrl.startsWith('blob:') ? '' : sourceUrl,
        };
      })
      .filter((item) => Boolean(item.source_url)),
    isSliderEnabled: () => sliderInputs.find((i) => i.checked)?.value !== 'disabled',
    setCoverFromUrl(url) {
      releaseMediaUrl(coverItem);
      const sourceUrl = String(url || '').trim();
      if (!sourceUrl) {
        coverItem = null;
        renderCoverList();
        return;
      }

      coverItem = {
        type: 'image',
        name: 'Existing Cover',
        size: 0,
        width: '-',
        height: '-',
        url: sourceUrl,
        sourceUrl,
      };
      renderCoverList();
    },
    setSliderEnabled(isEnabled) {
      const value = isEnabled ? 'enabled' : 'disabled';
      const input = sliderInputs.find((item) => item.value === value);
      if (!input) return;
      input.checked = true;
      input.dispatchEvent(new Event('change', { bubbles: true }));
    },
    setSliderItemsFromApi(items = []) {
      while (sliderItems.length) {
        releaseMediaUrl(sliderItems[0]);
        sliderItems.shift();
      }
      sliderSequence = 1;

      if (!Array.isArray(items)) {
        renderSliderList();
        return;
      }

      items.forEach((item) => {
        const mediaType = String(item?.media_type || '').toLowerCase();
        const sourceUrl = String(item?.source_url || '').trim();
        if (!sourceUrl) return;

        let type = 'image';
        let name = 'Slider Image';
        let embedUrl = null;
        let videoId = null;

        if (mediaType === 'upload_video' || mediaType === 'video') {
          type = 'video';
          name = 'Slider Video';
        } else if (mediaType === 'yt_video' || mediaType === 'youtube') {
          type = 'youtube';
          name = 'YouTube Video';
          const parsedVideoId = parseYouTubeVideoId(sourceUrl);
          videoId = parsedVideoId || null;
          embedUrl = parsedVideoId ? getYouTubeEmbedUrl(parsedVideoId) : sourceUrl;
        }

        sliderItems.push({
          id: sliderSequence,
          type,
          name,
          size: 0,
          width: null,
          height: null,
          url: sourceUrl,
          sourceUrl,
          videoId,
          embedUrl,
        });
        sliderSequence += 1;
      });

      renderSliderList();
    },
  };
}

function initProductCreateDiscountOfferControl() {
  const durationInput = document.querySelector('[data-discount-offer-duration]');
  if (!durationInput) return;

  const discountFieldGroups = Array.from(document.querySelectorAll('[data-discount-offer-fields]'));
  const dateTimeGroups = Array.from(document.querySelectorAll('[data-discount-offer-datetime]'));

  const updateDiscountOfferMode = () => {
    const value = durationInput.value;
    const hasDiscount = value === 'lifetime' || value === 'date_time';
    const useDateTimeRange = value === 'date_time';

    // Show/hide discount type + amount fields
    discountFieldGroups.forEach((group) => {
      group.classList.toggle('hidden', !hasDiscount);
      group.querySelectorAll('input, select').forEach((el) => {
        el.disabled = !hasDiscount;
        if (!hasDiscount) el.value = '';
      });
    });

    // Show/hide date & time fields
    dateTimeGroups.forEach((group) => {
      group.classList.toggle('hidden', !useDateTimeRange);
      group.querySelectorAll('input').forEach((input) => {
        input.disabled = !useDateTimeRange;
        if (!useDateTimeRange) input.value = '';
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
  const shortDescriptionMaxLength = 250;
  const form = document.getElementById('createProductForm');
  const apiBase = String(form?.dataset.apiBaseUrl || 'http://localhost:8082').replace(/\/+$/, '');
  const getToken = () =>
    String(form?.dataset.refreshToken || '').trim() ||
    localStorage.getItem('refresh_token') ||
    (window.API && typeof window.API.getToken === 'function' ? window.API.getToken() : '') ||
    '';

  if (!productNameInput || !shortAiButton || !fullAiButton || !seoAiButton) return;

  let fullDescriptionEditor = null;
  const aiButtons = [shortAiButton, fullAiButton, seoAiButton].filter(Boolean);
  let aiRequestInFlight = false;

  const htmlToPlainText = (value) => String(value || '')
    .replace(/<style[\s\S]*?<\/style>/gi, ' ')
    .replace(/<script[\s\S]*?<\/script>/gi, ' ')
    .replace(/<[^>]+>/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();

  const syncFullDescriptionInput = () => {
    if (!fullDescriptionInput) return;

    if (fullDescriptionEditor) {
      const html = fullDescriptionEditor.summernote('code');
      const isBlank = !html || html === '<p><br></p>' || html.trim() === '';
      fullDescriptionInput.value = isBlank ? '' : html;
      return;
    }

    fullDescriptionInput.value = fullDescriptionInput.value.trim();
  };

  const initFullDescriptionEditor = () => {
    if (!fullDescriptionInput || !fullDescriptionEditorHost) return;

    if (typeof window.$ === 'undefined' || typeof window.$.fn.summernote === 'undefined') {
      fullDescriptionEditorHost.hidden = true;
      fullDescriptionInput.hidden = false;
      return;
    }

    if (fullDescriptionEditor) return;

    fullDescriptionInput.hidden = true;

    const placeholder = fullDescriptionEditorHost.dataset.placeholder || fullDescriptionInput.placeholder || 'Write full description...';

    window.$(fullDescriptionEditorHost).summernote({
      placeholder,
      height: 250,
      toolbar: [
        ['style', ['style']],
        ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
        ['para', ['ul', 'ol', 'paragraph']],
        ['insert', ['link']],
        ['view', ['codeview']],
      ],
      callbacks: {
        onChange: () => {
          syncFullDescriptionInput();
          fullDescriptionInput.dispatchEvent(new Event('input', {bubbles: true}));
        },
      },
    });

    fullDescriptionEditor = window.$(fullDescriptionEditorHost);

    const initialContent = fullDescriptionInput.value.trim();
    if (initialContent) {
      fullDescriptionEditor.summernote('code', initialContent);
    }

    syncFullDescriptionInput();
  };

  const getFullDescriptionText = () => {
    if (fullDescriptionEditor) {
      return htmlToPlainText(fullDescriptionEditor.summernote('code'));
    }

    return htmlToPlainText(fullDescriptionInput?.value || '');
  };

  const setFullDescriptionContent = (htmlContent, fallbackText) => {
    if (fullDescriptionEditor) {
      fullDescriptionEditor.summernote('code', htmlContent);
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

  const setAiButtonText = (button, nextText) => {
    if (!button) return;
    const textNode = button.querySelector('.products-ai-btn-text');
    if (!textNode) return;
    textNode.textContent = String(nextText || '').trim() || 'AI Write';
  };

  const setAiButtonProcessing = (button, isProcessing, processingLabel = 'Generating...') => {
    if (!button) return;

    if (isProcessing) {
      const textNode = button.querySelector('.products-ai-btn-text');
      const currentLabel = String(textNode?.textContent || 'AI Write').trim() || 'AI Write';
      button.dataset.aiLabelBeforeProcessing = currentLabel;
      button.classList.add('is-processing');
      button.setAttribute('aria-busy', 'true');
      setAiButtonText(button, processingLabel);
      return;
    }

    button.classList.remove('is-processing');
    button.removeAttribute('aria-busy');
    const previousLabel = String(button.dataset.aiLabelBeforeProcessing || 'AI Write').trim() || 'AI Write';
    setAiButtonText(button, previousLabel);
    delete button.dataset.aiLabelBeforeProcessing;
  };

  const flashAiButtonState = (button, state) => {
    if (!button) return;
    const safeState = state === 'success' ? 'success' : 'error';
    const className = safeState === 'success' ? 'is-success' : 'is-error';
    const timeoutMs = safeState === 'success' ? 1200 : 1600;

    button.classList.remove('is-success', 'is-error');
    button.classList.add(className);
    window.setTimeout(() => {
      button.classList.remove(className);
    }, timeoutMs);
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

  const runAiAction = async (button, writer, processingLabel) => {
    if (!button || button.disabled || aiRequestInFlight) return;

    aiRequestInFlight = true;
    aiButtons.forEach((node) => {
      node.classList.remove('is-success', 'is-error');
      if (node !== button) {
        node.disabled = true;
      }
    });

    button.disabled = true;
    setAiButtonProcessing(button, true, processingLabel);

    try {
      await writer();
      flashAiButtonState(button, 'success');
    } catch (error) {
      flashAiButtonState(button, 'error');
      showError(error?.message || 'AI request failed. Please try again.');
    } finally {
      setAiButtonProcessing(button, false);
      aiRequestInFlight = false;
      updateAiButtonState();
    }
  };

  shortAiButton.addEventListener('click', () => {
    runAiAction(shortAiButton, async () => {
      const productName = productNameInput.value.trim();
      const description = (shortDescriptionInput?.value || '').trim();

      const result = await window.API.Admin.Products.aiContent({
        apiBaseUrl: apiBase,
        refreshToken: getToken(),
        payload: {
          prompt_type: 'short_description',
          name: productName,
          description,
        },
      });

      setShortDescriptionValue(result.short_description || '', true);
      showSuccess('Short description written with AI.');
    }, 'Writing...');
  });

  fullAiButton.addEventListener('click', () => {
    runAiAction(fullAiButton, async () => {
      const productName = productNameInput.value.trim();
      const description = (shortDescriptionInput?.value || '').trim();

      const result = await window.API.Admin.Products.aiContent({
        apiBaseUrl: apiBase,
        refreshToken: getToken(),
        payload: {
          prompt_type: 'description',
          name: productName,
          description,
        },
      });

      const htmlContent = result.description || '';
      setFullDescriptionContent(htmlContent, htmlToPlainText(htmlContent));
      showSuccess('Full description written with AI.');
    }, 'Generating...');
  });

  seoAiButton.addEventListener('click', () => {
    runAiAction(seoAiButton, async () => {
      const productName = productNameInput.value.trim();
      const description = getFullDescriptionText();

      const result = await window.API.Admin.Products.aiContent({
        apiBaseUrl: apiBase,
        refreshToken: getToken(),
        payload: {
          prompt_type: 'search_discover',
          name: productName,
          description,
        },
      });

      if (slugInput) {
        slugInput.value = slugify(productName);
      }
      if (metaTitleInput) {
        metaTitleInput.value = result.meta_title || '';
      }
      if (metaDescriptionInput) {
        metaDescriptionInput.value = result.meta_description || '';
      }
      if (tagsInput && Array.isArray(result.seo_tags)) {
        tagsInput.value = result.seo_tags.join(', ');
      }

      showSuccess('Search & discoverability fields written with AI.');
    }, 'Optimizing...');
  });

  productNameInput.addEventListener('input', () => {
    if (slugInput) {
      slugInput.value = slugify(productNameInput.value);
    }
    updateAiButtonState();
  });

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

  window._productCreateEditor = {
    setFullDescription(htmlValue) {
      const html = String(htmlValue || '').trim();
      const fallback = htmlToPlainText(html);
      setFullDescriptionContent(html, fallback);
      updateAiButtonState();
    },
  };

  updateAiButtonState();
}

function initCategoryCreateForm() {
  const tableBody = document.querySelector('[data-categories-table-body]');
  const totalNode = document.querySelector('[data-categories-total]');
  const addButton = document.querySelector('[data-category-add-button]');
  const nameInput = document.querySelector('[data-category-name-input]');
  const slugInput = document.querySelector('[data-category-slug-input]');
  const statusInput = document.querySelector('[data-category-status-input]');
  const parentInput = document.querySelector('[data-category-parent-input]');
  const descriptionInput = document.querySelector('[data-category-description-input]');
  const imageInput = document.querySelector('[data-category-image-input]');
  const imagePreview = document.querySelector('[data-category-image-preview]');
  const categoryValidationModal = document.getElementById('categoriesValidationModal');
  const categoryValidationTitleNode = categoryValidationModal?.querySelector('[data-category-validation-title]');
  const categoryValidationMessageNode = categoryValidationModal?.querySelector('[data-category-validation-message]');
  const categoryValidationCloseButton = categoryValidationModal?.querySelector('[data-category-validation-close]');

  if (
    !tableBody ||
    !addButton ||
    !nameInput ||
    !slugInput ||
    !statusInput ||
    !parentInput ||
    !descriptionInput
  ) {
    return;
  }

  const text = (value) => String(value ?? '').trim();
  const toInt = (value, fallback = 0) => {
    const parsed = Number.parseInt(String(value ?? ''), 10);
    return Number.isFinite(parsed) ? parsed : fallback;
  };
  const slugify = (value) => String(value || '')
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '');
  const escapeHtml = (value) => text(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
  const statusToApi = (statusLabel) => (text(statusLabel).toLowerCase() === 'active' ? 'visible' : 'hidden');
  const statusToLabel = (visibility) => (text(visibility).toLowerCase() === 'visible' ? 'Active' : 'Draft');
  const normalizeApiBase = (value) => text(value).replace(/\/+$/, '');
  const now = () => Date.now();
  const showCategoryValidationModal = (message, title = 'Category Image Error') => {
    const dialogMessage = text(message) || 'Please check your image and try again.';
    const dialogTitle = text(title) || 'Category Image Error';

    if (!categoryValidationModal) {
      showError(dialogMessage);
      return;
    }

    if (categoryValidationTitleNode) {
      categoryValidationTitleNode.textContent = dialogTitle;
    }

    if (categoryValidationMessageNode) {
      categoryValidationMessageNode.textContent = dialogMessage;
    }

    openModal('categoriesValidationModal');
    window.setTimeout(() => {
      categoryValidationCloseButton?.focus();
    }, 20);
  };

  const formatDateTime = (value) => {
    const rawValue = text(value);
    if (!rawValue) return '-';
    const parsed = Date.parse(rawValue);
    if (!Number.isFinite(parsed)) return rawValue;
    return new Date(parsed).toLocaleString();
  };

  const formatRelativeTime = (value) => {
    const rawValue = text(value);
    if (!rawValue) return '-';
    const parsed = Date.parse(rawValue);
    if (!Number.isFinite(parsed)) return rawValue;

    const diffMs = now() - parsed;
    if (!Number.isFinite(diffMs) || diffMs < 0) return formatDateTime(rawValue);
    const minutes = Math.floor(diffMs / 60000);
    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes}m ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours}h ago`;
    const days = Math.floor(hours / 24);
    if (days < 7) return `${days}d ago`;
    return new Date(parsed).toLocaleDateString();
  };

  const setTableMessage = (message) => {
    tableBody.innerHTML = `
      <tr>
        <td colspan="8">${escapeHtml(message)}</td>
      </tr>
    `;
  };

  const renderImagePreview = (src, alt = 'Selected image') => {
    if (!imagePreview) return;

    const imageSrc = text(src);
    if (!imageSrc) {
      imagePreview.innerHTML = '<span class="categories-image-upload-placeholder">No image selected</span>';
      return;
    }

    imagePreview.innerHTML = `<img src="${escapeHtml(imageSrc)}" alt="${escapeHtml(alt)}" loading="lazy">`;
  };

  const renderTableImage = (src, alt = 'Category image') => {
    const imageSrc = text(src);
    if (!imageSrc) {
      return '<span class="categories-table-image categories-table-image--placeholder">No image</span>';
    }

    return `<span class="categories-table-image"><img src="${escapeHtml(imageSrc)}" alt="${escapeHtml(alt)}" loading="lazy"></span>`;
  };

  const runtime = window.__adminCategoriesRuntime || {};
  window.__adminCategoriesRuntime = runtime;

  const sessionRefreshToken = text(tableBody.dataset.refreshToken);
  let storageRefreshToken = '';
  try {
    storageRefreshToken = text(window.localStorage.getItem('refresh_token') || '');
  } catch {
    storageRefreshToken = '';
  }

  runtime.apiBase = normalizeApiBase(tableBody.dataset.apiBaseUrl || 'http://localhost:8082');
  runtime.refreshToken = sessionRefreshToken || storageRefreshToken;
  runtime.categories = [];
  runtime.pagination = {total: 0, current_page: 1, per_page: 0, last_page: 1};
  runtime.requestId = 0;
  runtime.loading = false;

  runtime.refreshTotal = () => {
    if (!totalNode) return;
    const total = Math.max(0, toInt(runtime.pagination?.total, runtime.categories.length));
    totalNode.textContent = `${total} total`;
  };

  runtime.setCreateParentOptions = (selectedValue = '') => {
    const currentValue = text(selectedValue || parentInput.value);
    const options = ['<option value="">None (Top level)</option>'];
    const categories = Array.isArray(runtime.categories) ? runtime.categories : [];

    categories
      .filter((category) => toInt(category?.id, 0) > 0)
      .sort((a, b) => text(a?.name).localeCompare(text(b?.name)))
      .forEach((category) => {
        const id = String(toInt(category.id, 0));
        const label = text(category.name) || `Category #${id}`;
        options.push(`<option value="${escapeHtml(id)}">${escapeHtml(label)}</option>`);
      });

    parentInput.innerHTML = options.join('');
    if (currentValue && Array.from(parentInput.options).some((opt) => opt.value === currentValue)) {
      parentInput.value = currentValue;
      return;
    }
    parentInput.value = '';
  };

  runtime.renderRows = () => {
    const categories = Array.isArray(runtime.categories) ? runtime.categories : [];
    const idToName = new Map();
    categories.forEach((category) => {
      const id = toInt(category?.id, 0);
      if (id > 0) {
        idToName.set(id, text(category?.name));
      }
    });

    tableBody.innerHTML = '';

    if (!categories.length) {
      setTableMessage('No categories found.');
      runtime.refreshTotal();
      runtime.setCreateParentOptions('');
      return;
    }

    const fragment = document.createDocumentFragment();

    categories.forEach((category) => {
      const categoryId = toInt(category?.id, 0);
      const categoryName = text(category?.name) || `Category #${categoryId || '-'}`;
      const categorySlug = text(category?.slug);
      const categoryVisibility = text(category?.visibility).toLowerCase() || 'visible';
      const categoryStatus = statusToLabel(categoryVisibility);
      const parentId = toInt(category?.parent_category, 0);
      const parentName = parentId > 0 ? (idToName.get(parentId) || `Category #${parentId}`) : '';
      const cover = text(category?.cover);
      const shortDescription = text(category?.short_description);
      const updatedAt = formatRelativeTime(category?.created_at);
      const createdAtDisplay = formatDateTime(category?.created_at);

      const row = document.createElement('tr');
      row.setAttribute('data-category-row', '');
      row.dataset.categoryId = String(categoryId);
      row.dataset.categoryName = categoryName;
      row.dataset.categorySlug = categorySlug;
      row.dataset.categoryStatus = categoryStatus;
      row.dataset.categoryVisibility = categoryVisibility;
      row.dataset.categoryProducts = '0';
      row.dataset.categoryShare = '0';
      row.dataset.categoryParentId = parentId > 0 ? String(parentId) : '';
      row.dataset.categoryParent = parentName;
      row.dataset.categoryUpdated = updatedAt;
      row.dataset.categoryDescription = shortDescription;
      row.dataset.categoryImage = cover;
      row.dataset.categoryCreatedAt = text(category?.created_at);

      row.innerHTML = `
        <td data-category-cell="image">
          ${renderTableImage(cover, `${categoryName} image`)}
        </td>
        <td data-category-cell="category">
          <strong>${escapeHtml(categoryName)}</strong>
          ${parentName ? `<small class="categories-parent-note">Parent: ${escapeHtml(parentName)}</small>` : ''}
        </td>
        <td data-category-cell="slug">${escapeHtml(categorySlug || '-')}</td>
        <td data-category-cell="products">-</td>
        <td class="categories-share-cell" data-category-cell="share">
          <span class="categories-share-value">-</span>
        </td>
        <td data-category-cell="status">
          <span class="badge ${categoryStatus === 'Active' ? 'badge-success' : 'badge-warning'}">${escapeHtml(categoryStatus)}</span>
        </td>
        <td data-category-cell="updated" title="${escapeHtml(createdAtDisplay)}">${escapeHtml(updatedAt)}</td>
        <td data-category-cell="action">
          <div class="products-table-actions">
            <button type="button" class="btn btn-secondary btn-sm" data-category-edit>Edit</button>
            <button type="button" class="btn btn-danger btn-sm" data-category-delete>Delete</button>
          </div>
        </td>
      `;

      fragment.appendChild(row);
    });

    tableBody.appendChild(fragment);
    runtime.refreshTotal();
    runtime.setCreateParentOptions(parentInput.value);
  };

  runtime.loadCategories = async (opts = {}) => {
    const showLoading = opts.showLoading !== false;
    const requestId = ++runtime.requestId;
    runtime.loading = true;

    if (showLoading) {
      setTableMessage('Loading categories...');
    }

    try {
      const payload = await window.API.Admin.Categories.list({
        apiBaseUrl: runtime.apiBase,
        refreshToken: runtime.refreshToken,
        page: 1,
        perPage: 200,
        timeoutMs: 15000,
      });

      if (requestId !== runtime.requestId) return;

      runtime.categories = Array.isArray(payload?.data) ? payload.data : [];
      runtime.pagination = payload?.pagination && typeof payload.pagination === 'object'
        ? payload.pagination
        : {total: runtime.categories.length, current_page: 1, per_page: runtime.categories.length, last_page: 1};

      runtime.renderRows();
    } catch (error) {
      if (requestId !== runtime.requestId) return;
      runtime.categories = [];
      runtime.pagination = {total: 0, current_page: 1, per_page: 0, last_page: 1};
      setTableMessage(error?.message || 'Failed to load categories.');
      runtime.refreshTotal();
      runtime.setCreateParentOptions('');
      showError(error?.message || 'Failed to load categories.');
    } finally {
      if (requestId === runtime.requestId) {
        runtime.loading = false;
      }
    }
  };

  let slugUserEdited = text(slugInput.value) !== '';
  let selectedImageUrl = '';
  let selectedImageName = '';
  const maxCategoryImageBytes = 2 * 1024 * 1024;
  const categoryImageMinWidth = 600;
  const categoryImageMinHeight = 600;
  const categoryImageExpectedRatio = 1; // 1:1 square
  const categoryImageRatioTolerance = 0.03;

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

  nameInput.addEventListener('input', () => {
    if (!slugUserEdited) {
      slugInput.value = slugify(nameInput.value);
    }
  });

  slugInput.addEventListener('input', () => {
    slugUserEdited = text(slugInput.value) !== '';
  });

  imageInput?.addEventListener('change', async (event) => {
    const file = event.target?.files?.[0];
    if (!file) {
      selectedImageUrl = '';
      selectedImageName = '';
      renderImagePreview('');
      return;
    }

    if (!file.type.startsWith('image/')) {
      showCategoryValidationModal(
        'Please select an image file for category image.',
        'Invalid File Type'
      );
      imageInput.value = '';
      selectedImageUrl = '';
      selectedImageName = '';
      renderImagePreview('');
      return;
    }

    if (file.size >= maxCategoryImageBytes) {
      const sizeInMb = (file.size / (1024 * 1024)).toFixed(2);
      showCategoryValidationModal(
        `Category image must be smaller than 2MB. Selected file size: ${sizeInMb}MB.`,
        'File Too Large'
      );
      imageInput.value = '';
      selectedImageUrl = '';
      selectedImageName = '';
      renderImagePreview('');
      return;
    }

    try {
      const {width, height} = await readImageDimensions(file);
      const ratio = width / Math.max(1, height);
      const ratioDiff = Math.abs(ratio - categoryImageExpectedRatio);

      if (width < categoryImageMinWidth || height < categoryImageMinHeight) {
        showCategoryValidationModal(
          `Category image is too small. Minimum ${categoryImageMinWidth}x${categoryImageMinHeight}px required. ` +
          `Selected: ${width}x${height}px.`,
          'Image Dimensions Too Small'
        );
        imageInput.value = '';
        selectedImageUrl = '';
        selectedImageName = '';
        renderImagePreview('');
        return;
      }

      if (ratioDiff > categoryImageRatioTolerance) {
        showCategoryValidationModal(
          `Category image must be square (1:1 ratio). Selected: ${width}x${height}px. ` +
          'Recommended: 1080x1080px.',
          'Image Ratio Mismatch'
        );
        imageInput.value = '';
        selectedImageUrl = '';
        selectedImageName = '';
        renderImagePreview('');
        return;
      }

      const reader = new FileReader();
      reader.onload = (loadEvent) => {
        selectedImageUrl = text(loadEvent?.target?.result);
        selectedImageName = file.name || 'Category image';
        renderImagePreview(selectedImageUrl, selectedImageName);
      };
      reader.onerror = () => {
        selectedImageUrl = '';
        selectedImageName = '';
        showCategoryValidationModal('Unable to read selected category image.', 'Image Read Failed');
        renderImagePreview('');
      };
      reader.readAsDataURL(file);
    } catch {
      selectedImageUrl = '';
      selectedImageName = '';
      showCategoryValidationModal('Unable to validate selected category image.', 'Image Validation Failed');
      renderImagePreview('');
    }
  });

  addButton.addEventListener('click', async () => {
    const categoryName = text(nameInput.value);
    const categorySlug = text(slugInput.value) || slugify(categoryName);
    const categoryVisibility = statusToApi(statusInput.value);
    const categoryParent = toInt(parentInput.value, 0);
    const categoryDescription = text(descriptionInput.value);
    const categoryImage = text(selectedImageUrl);

    if (!categoryName) {
      showError('Category name is required.');
      nameInput.focus();
      return;
    }

    const payload = {
      name: categoryName,
      slug: categorySlug || slugify(categoryName),
      visibility: categoryVisibility,
      parent_category: categoryParent > 0 ? categoryParent : null,
      short_description: categoryDescription || null,
    };
    if (categoryImage) {
      payload.cover_image = categoryImage;
    }

    const defaultButtonText = addButton.textContent;
    addButton.disabled = true;
    addButton.textContent = 'Adding...';

    try {
      const result = await window.API.Admin.Categories.create({
        apiBaseUrl: runtime.apiBase,
        refreshToken: runtime.refreshToken,
        payload,
        timeoutMs: 12000,
      });

      nameInput.value = '';
      slugInput.value = '';
      statusInput.value = 'Active';
      parentInput.value = '';
      descriptionInput.value = '';
      if (imageInput) imageInput.value = '';
      selectedImageUrl = '';
      selectedImageName = '';
      slugUserEdited = false;
      renderImagePreview('');

      showSuccess(text(result?.message) || 'Category created.');
      await runtime.loadCategories({showLoading: false});
    } catch (error) {
      showError(error?.message || 'Failed to create category.');
    } finally {
      addButton.disabled = false;
      addButton.textContent = defaultButtonText;
    }
  });

  renderImagePreview('');
  runtime.refreshTotal();
  runtime.setCreateParentOptions('');
  runtime.loadCategories({showLoading: true});
}

function initCategoryAiWriter() {
  const trigger = document.querySelector('[data-category-ai-generate]');
  const categoryNameInput = document.querySelector('[data-category-name-input]');
  const descriptionInput = document.querySelector('[data-category-description-input]');
  const addCategoryButton = document.querySelector('[data-category-add-button]');
  const statusNode = document.querySelector('[data-category-ai-status]');
  const tableBody = document.querySelector('[data-categories-table-body]');
  const runtime = window.__adminCategoriesRuntime;

  if (!trigger || !categoryNameInput || !descriptionInput || !tableBody) return;

  const text = (value) => String(value ?? '').trim();
  const normalizeApiBase = (value) => text(value).replace(/\/+$/, '');
  const defaultAddButtonText = addCategoryButton?.textContent?.trim() || '+ Add Category';
  let aiRequestInFlight = false;

  const getApiBase = () => {
    const runtimeBase = text(runtime?.apiBase);
    if (runtimeBase) return normalizeApiBase(runtimeBase);

    const datasetBase = text(tableBody.dataset.apiBaseUrl);
    if (datasetBase) return normalizeApiBase(datasetBase);

    return 'http://localhost:8082';
  };

  const getRefreshToken = () => {
    const runtimeToken = text(runtime?.refreshToken);
    if (runtimeToken) return runtimeToken;

    const datasetToken = text(tableBody.dataset.refreshToken);
    if (datasetToken) return datasetToken;

    return text(window.API?.getToken?.() || '');
  };

  const setStatus = (message, tone = '') => {
    if (!statusNode) return;
    statusNode.textContent = text(message);
    statusNode.className = `categories-ai-status${tone ? ` is-${tone}` : ''}`;
  };

  const setAiButtonText = (button, nextText) => {
    if (!button) return;
    const textNode = button.querySelector('.categories-ai-btn-text');
    if (!textNode) return;
    textNode.textContent = text(nextText) || 'AI Write';
  };

  const setAiButtonProcessing = (button, isProcessing, processingLabel = 'Writing...') => {
    if (!button) return;

    if (isProcessing) {
      const textNode = button.querySelector('.categories-ai-btn-text');
      const currentLabel = text(textNode?.textContent) || 'AI Write';
      button.dataset.aiLabelBeforeProcessing = currentLabel;
      button.classList.add('is-processing');
      button.setAttribute('aria-busy', 'true');
      setAiButtonText(button, processingLabel);
      return;
    }

    button.classList.remove('is-processing');
    button.removeAttribute('aria-busy');
    const previousLabel = text(button.dataset.aiLabelBeforeProcessing) || 'AI Write';
    setAiButtonText(button, previousLabel);
    delete button.dataset.aiLabelBeforeProcessing;
  };

  const flashAiButtonState = (button, state) => {
    if (!button) return;
    const safeState = state === 'success' ? 'success' : 'error';
    const className = safeState === 'success' ? 'is-success' : 'is-error';
    const timeoutMs = safeState === 'success' ? 1200 : 1600;

    button.classList.remove('is-success', 'is-error');
    button.classList.add(className);
    window.setTimeout(() => {
      button.classList.remove(className);
    }, timeoutMs);
  };

  const updateButtonState = () => {
    const hasCategoryName = text(categoryNameInput.value) !== '';
    trigger.disabled = !hasCategoryName || aiRequestInFlight;
    const hint = hasCategoryName ? 'Write description with AI' : 'Add Category Name first';
    trigger.title = hint;
    trigger.setAttribute('aria-label', hint);
  };

  categoryNameInput.addEventListener('input', updateButtonState);

  trigger.addEventListener('click', async () => {
    if (trigger.disabled || aiRequestInFlight) return;

    const categoryName = text(categoryNameInput.value);
    if (!categoryName) {
      setStatus('Please add category name to write this.', 'error');
      showWarning('Please add category name to write this.');
      updateButtonState();
      return;
    }

    if (!window.API?.Admin?.Products?.aiContent) {
      const errorMessage = 'AI service is not available right now.';
      setStatus(errorMessage, 'error');
      showError(errorMessage);
      return;
    }

    aiRequestInFlight = true;
    trigger.classList.remove('is-success', 'is-error');
    updateButtonState();
    setAiButtonProcessing(trigger, true, 'Writing...');

    if (addCategoryButton) {
      addCategoryButton.disabled = true;
      addCategoryButton.textContent = 'Processing...';
    }

    setStatus(`AI is generating description for "${categoryName}"...`, 'processing');

    try {
      const result = await window.API.Admin.Products.aiContent({
        apiBaseUrl: getApiBase(),
        refreshToken: getRefreshToken(),
        payload: {
          prompt_type: 'category_description',
          name: categoryName,
          description: text(descriptionInput.value),
        },
        timeoutMs: 0,
      });

      const generatedDescription = text(result?.category_description);
      if (!generatedDescription) {
        throw new Error('AI did not return a category description.');
      }

      descriptionInput.value = generatedDescription;
      descriptionInput.dispatchEvent(new Event('input', {bubbles: true}));

      setStatus(`Description generated for "${categoryName}".`, 'success');
      flashAiButtonState(trigger, 'success');
      showSuccess('Category description written with AI.');
    } catch (error) {
      const message = text(error?.message) || 'AI request failed. Please try again.';
      setStatus(message, 'error');
      flashAiButtonState(trigger, 'error');
      showError(message);
    } finally {
      aiRequestInFlight = false;
      setAiButtonProcessing(trigger, false);

      if (addCategoryButton) {
        addCategoryButton.disabled = false;
        addCategoryButton.textContent = defaultAddButtonText;
      }

      updateButtonState();
    }
  });

  updateButtonState();
}

// ══════════════════════════════════════════
// CATEGORIES: EDIT PANEL (DEMO UI)
// ══════════════════════════════════════════
function initCategoryEditor() {
  const runtime = window.__adminCategoriesRuntime;
  const createPanel = document.querySelector('[data-category-create-panel]');
  const editPanel = document.querySelector('[data-category-edit-panel]');
  const tableBody = document.querySelector('[data-categories-table-body]');

  if (!runtime || !createPanel || !editPanel || !tableBody) return;
  if (editPanel.dataset.categoryEditorReady === 'true') return;
  editPanel.dataset.categoryEditorReady = 'true';

  const titleNode = editPanel.querySelector('[data-category-edit-title]');
  const nameInput = editPanel.querySelector('[data-category-edit-name]');
  const slugInput = editPanel.querySelector('[data-category-edit-slug]');
  const statusInput = editPanel.querySelector('[data-category-edit-status]');
  const parentInput = editPanel.querySelector('[data-category-edit-parent]');
  const editImageInput = editPanel.querySelector('[data-category-edit-image]');
  const editImagePreview = editPanel.querySelector('[data-category-edit-image-preview]');
  const descriptionInput = editPanel.querySelector('[data-category-edit-description]');
  const productsNode = editPanel.querySelector('[data-category-edit-products]');
  const shareNode = editPanel.querySelector('[data-category-edit-share]');
  const updatedNode = editPanel.querySelector('[data-category-edit-updated]');
  const cancelButton = editPanel.querySelector('[data-category-edit-cancel]');
  const saveButton = editPanel.querySelector('[data-category-edit-save]');
  const categoryValidationModal = document.getElementById('categoriesValidationModal');
  const categoryValidationTitleNode = categoryValidationModal?.querySelector('[data-category-validation-title]');
  const categoryValidationMessageNode = categoryValidationModal?.querySelector('[data-category-validation-message]');
  const categoryValidationCloseButton = categoryValidationModal?.querySelector('[data-category-validation-close]');

  let activeRow = null;
  let activeCategoryId = 0;
  let currentImageUrl = '';
  let pendingEditImageUrl = '';
  let pendingEditImageName = '';

  const text = (value) => String(value ?? '').trim();
  const toInt = (value, fallback = 0) => {
    const parsed = Number.parseInt(String(value ?? ''), 10);
    return Number.isFinite(parsed) ? parsed : fallback;
  };
  const slugify = (value) => String(value || '')
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '');
  const escapeHtml = (value) => text(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
  const statusToApi = (statusLabel) => (text(statusLabel).toLowerCase() === 'active' ? 'visible' : 'hidden');
  const maxCategoryImageBytes = 2 * 1024 * 1024;
  const categoryImageMinWidth = 600;
  const categoryImageMinHeight = 600;
  const categoryImageExpectedRatio = 1; // 1:1 square
  const categoryImageRatioTolerance = 0.03;

  const showCategoryValidationModal = (message, title = 'Category Image Error') => {
    const dialogMessage = text(message) || 'Please check your image and try again.';
    const dialogTitle = text(title) || 'Category Image Error';

    if (!categoryValidationModal) {
      showError(dialogMessage);
      return;
    }

    if (categoryValidationTitleNode) {
      categoryValidationTitleNode.textContent = dialogTitle;
    }

    if (categoryValidationMessageNode) {
      categoryValidationMessageNode.textContent = dialogMessage;
    }

    openModal('categoriesValidationModal');
    window.setTimeout(() => {
      categoryValidationCloseButton?.focus();
    }, 20);
  };

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

  const updateParentOptions = (selectedValue = '', excludeCategoryId = 0) => {
    if (!parentInput) return;

    const categories = Array.isArray(runtime.categories) ? runtime.categories : [];
    const selected = text(selectedValue);
    const currentId = toInt(excludeCategoryId, 0);

    const options = ['<option value="">None (Top level)</option>'];
    categories
      .filter((category) => {
        const id = toInt(category?.id, 0);
        return id > 0 && id !== currentId;
      })
      .sort((a, b) => text(a?.name).localeCompare(text(b?.name)))
      .forEach((category) => {
        const id = String(toInt(category.id, 0));
        const label = text(category.name) || `Category #${id}`;
        options.push(`<option value="${escapeHtml(id)}">${escapeHtml(label)}</option>`);
      });

    parentInput.innerHTML = options.join('');
    if (selected && Array.from(parentInput.options).some((opt) => opt.value === selected)) {
      parentInput.value = selected;
      return;
    }
    parentInput.value = '';
  };

  const renderEditImagePreview = (src, alt = 'Category image') => {
    if (!editImagePreview) return;

    const imageSrc = text(src);
    if (!imageSrc) {
      editImagePreview.innerHTML = '<span class="categories-image-upload-placeholder">No image selected</span>';
      return;
    }

    editImagePreview.innerHTML = `<img src="${escapeHtml(imageSrc)}" alt="${escapeHtml(alt)}" loading="lazy">`;
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

  const readRowData = (row) => ({
    id: toInt(row.dataset.categoryId, 0),
    name: row.dataset.categoryName || '',
    slug: row.dataset.categorySlug || '',
    status: row.dataset.categoryStatus || 'Draft',
    products: Number.parseInt(row.dataset.categoryProducts || '0', 10) || 0,
    share: Number.parseInt(row.dataset.categoryShare || '0', 10) || 0,
    parentId: row.dataset.categoryParentId || '',
    parent: row.dataset.categoryParent || '',
    updatedAt: row.dataset.categoryUpdated || '-',
    description: row.dataset.categoryDescription || '',
    image: row.dataset.categoryImage || '',
  });

  const populateEditor = (row) => {
    const category = readRowData(row);
    activeCategoryId = category.id;
    currentImageUrl = text(category.image);
    pendingEditImageUrl = '';
    pendingEditImageName = '';
    if (editImageInput) editImageInput.value = '';
    renderEditImagePreview(currentImageUrl, `${category.name || 'Category'} image`);

    if (titleNode) {
      titleNode.textContent = `Edit Category: ${category.name}`;
    }

    if (nameInput) nameInput.value = category.name;
    if (slugInput) slugInput.value = category.slug;
    if (descriptionInput) descriptionInput.value = category.description;
    setSelectValue(statusInput, category.status, 'Draft');
    updateParentOptions(category.parentId, category.id);

    if (productsNode) productsNode.textContent = `Products: ${category.products}`;
    if (shareNode) shareNode.textContent = `Share: ${category.share}%`;
    if (updatedNode) updatedNode.textContent = `Updated: ${category.updatedAt}`;
  };

  const closeEditor = () => {
    activeRow = null;
    activeCategoryId = 0;
    currentImageUrl = '';
    pendingEditImageUrl = '';
    pendingEditImageName = '';
    if (editImageInput) editImageInput.value = '';
    renderEditImagePreview('');
    setEditMode(false);
  };

  editImageInput?.addEventListener('change', async (event) => {
    const file = event.target?.files?.[0];
    if (!file) {
      pendingEditImageUrl = '';
      pendingEditImageName = '';
      renderEditImagePreview(currentImageUrl, 'Current image');
      return;
    }

    if (!file.type.startsWith('image/')) {
      showCategoryValidationModal('Please select an image file for category image.', 'Invalid File Type');
      editImageInput.value = '';
      pendingEditImageUrl = '';
      pendingEditImageName = '';
      renderEditImagePreview(currentImageUrl, 'Current image');
      return;
    }

    if (file.size >= maxCategoryImageBytes) {
      const sizeInMb = (file.size / (1024 * 1024)).toFixed(2);
      showCategoryValidationModal(
        `Category image must be smaller than 2MB. Selected file size: ${sizeInMb}MB.`,
        'File Too Large'
      );
      editImageInput.value = '';
      pendingEditImageUrl = '';
      pendingEditImageName = '';
      renderEditImagePreview(currentImageUrl, 'Current image');
      return;
    }

    try {
      const {width, height} = await readImageDimensions(file);
      const ratio = width / Math.max(1, height);
      const ratioDiff = Math.abs(ratio - categoryImageExpectedRatio);

      if (width < categoryImageMinWidth || height < categoryImageMinHeight) {
        showCategoryValidationModal(
          `Category image is too small. Minimum ${categoryImageMinWidth}x${categoryImageMinHeight}px required. ` +
          `Selected: ${width}x${height}px.`,
          'Image Dimensions Too Small'
        );
        editImageInput.value = '';
        pendingEditImageUrl = '';
        pendingEditImageName = '';
        renderEditImagePreview(currentImageUrl, 'Current image');
        return;
      }

      if (ratioDiff > categoryImageRatioTolerance) {
        showCategoryValidationModal(
          `Category image must be square (1:1 ratio). Selected: ${width}x${height}px. ` +
          'Recommended: 1080x1080px.',
          'Image Ratio Mismatch'
        );
        editImageInput.value = '';
        pendingEditImageUrl = '';
        pendingEditImageName = '';
        renderEditImagePreview(currentImageUrl, 'Current image');
        return;
      }

      const reader = new FileReader();
      reader.onload = (loadEvent) => {
        pendingEditImageUrl = text(loadEvent?.target?.result);
        pendingEditImageName = file.name || 'Category image';
        renderEditImagePreview(pendingEditImageUrl, pendingEditImageName);
      };
      reader.onerror = () => {
        pendingEditImageUrl = '';
        pendingEditImageName = '';
        showCategoryValidationModal('Unable to read selected category image.', 'Image Read Failed');
        renderEditImagePreview(currentImageUrl, 'Current image');
      };
      reader.readAsDataURL(file);
    } catch {
      pendingEditImageUrl = '';
      pendingEditImageName = '';
      showCategoryValidationModal('Unable to validate selected category image.', 'Image Validation Failed');
      renderEditImagePreview(currentImageUrl, 'Current image');
    }
  });

  tableBody.addEventListener('click', (event) => {
    const button = event.target.closest('[data-category-edit]');
    if (!button || !tableBody.contains(button)) return;

    const row = button.closest('[data-category-row]');
    if (!row) return;

    activeRow = row;
    populateEditor(row);
    setEditMode(true);
    editPanel.scrollIntoView({behavior: 'smooth', block: 'start'});
  });

  cancelButton?.addEventListener('click', () => {
    closeEditor();
    showInfo('Edit cancelled.');
  });

  saveButton?.addEventListener('click', async () => {
    if (!activeRow || activeCategoryId <= 0) return;

    const nextName = text(nameInput?.value);
    const nextSlug = text(slugInput?.value) || slugify(nextName);
    const nextStatus = statusInput?.value || 'Draft';
    const nextParentId = toInt(parentInput?.value, 0);
    const nextDescription = text(descriptionInput?.value);

    if (!nextName) {
      showError('Category name is required.');
      nameInput?.focus();
      return;
    }

    if (nextParentId > 0 && nextParentId === activeCategoryId) {
      showError('A category cannot be parent of itself.');
      return;
    }

    const payload = {
      name: nextName,
      slug: nextSlug,
      visibility: statusToApi(nextStatus),
      parent_category: nextParentId > 0 ? nextParentId : null,
      short_description: nextDescription || null,
    };
    if (pendingEditImageUrl) {
      payload.cover_image = pendingEditImageUrl;
    }

    const defaultSaveText = saveButton.textContent;
    saveButton.disabled = true;
    saveButton.textContent = 'Updating...';

    try {
      const result = await window.API.Admin.Categories.update({
        apiBaseUrl: runtime.apiBase,
        refreshToken: runtime.refreshToken,
        categoryId: activeCategoryId,
        payload,
        timeoutMs: 12000,
      });

      closeEditor();
      showSuccess(text(result?.message) || `"${nextName}" updated.`);
      await runtime.loadCategories({showLoading: false});
    } catch (error) {
      showError(error?.message || 'Failed to update category.');
    } finally {
      saveButton.disabled = false;
      saveButton.textContent = defaultSaveText;
    }
  });
}

// ══════════════════════════════════════════
// CATEGORIES: DELETE GUARD (DEMO UI)
// ══════════════════════════════════════════
function initCategoryDeleteGuards() {
  const runtime = window.__adminCategoriesRuntime;
  const tableBody = document.querySelector('[data-categories-table-body]');
  const feedback = document.querySelector('[data-categories-delete-feedback]');

  if (!runtime || !tableBody) return;
  if (tableBody.dataset.categoryDeleteGuardReady === 'true') return;
  tableBody.dataset.categoryDeleteGuardReady = 'true';

  const text = (value) => String(value ?? '').trim();
  const toInt = (value, fallback = 0) => {
    const parsed = Number.parseInt(String(value ?? ''), 10);
    return Number.isFinite(parsed) ? parsed : fallback;
  };

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

  tableBody.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-category-delete]');
    if (!button || !tableBody.contains(button)) return;

    const row = button.closest('[data-category-row]');
    if (!row) return;

    const categoryId = toInt(row.dataset.categoryId, 0);
    const categoryName = text(row.dataset.categoryName) || 'This category';

    if (categoryId <= 0) {
      showFeedback('Invalid category id. Please refresh and try again.', 'error');
      return;
    }

    if (!window.confirm(`Delete "${categoryName}"? This action cannot be undone.`)) {
      return;
    }

    const defaultButtonText = button.textContent;
    button.disabled = true;
    button.textContent = 'Deleting...';

    try {
      const result = await window.API.Admin.Categories.remove({
        apiBaseUrl: runtime.apiBase,
        refreshToken: runtime.refreshToken,
        categoryId,
        timeoutMs: 12000,
      });

      showFeedback(text(result?.message) || `"${categoryName}" deleted.`, 'success');
      await runtime.loadCategories({showLoading: false});
    } catch (error) {
      showFeedback(error?.message || `Failed to delete "${categoryName}".`, 'error');
    } finally {
      button.disabled = false;
      button.textContent = defaultButtonText;
    }
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
// PRODUCTS: DEV AUTO-FILL (local env only)
// ══════════════════════════════════════════
function initProductDemoAutoFill() {
  const form = document.getElementById('createProductForm');
  if (!form || form.dataset.devAutofill !== '1') return;

  const set = (selector, value) => {
    const el = form.querySelector(selector);
    if (!el) return;
    el.value = value;
    el.dispatchEvent(new Event('input', { bubbles: true }));
    el.dispatchEvent(new Event('change', { bubbles: true }));
  };

  const checkRadio = (name, value) => {
    const radio = form.querySelector(`[name="${name}"][value="${value}"]`);
    if (!radio) return;
    radio.checked = true;
    radio.dispatchEvent(new Event('change', { bubbles: true }));
    radio.closest('[data-variant-toggle-card], [data-product-type-card]')?.click();
  };

  // ── Basic info ──
  set('#productName', 'Classic T-Shirt');
  set('#productShortDescription', '100% cotton tee — premium quality, available in multiple sizes.');
  // Set the hidden textarea so Quill reads it on init
  set('#productDescription', '<p>Premium quality cotton t-shirt available in multiple sizes and colors. Made from 100% natural cotton for all-day comfort and breathability.</p>');

  // ── Variants: keep "No Variants" (default) ──
  // no change needed, "no" is already checked

  // ── Pricing & inventory (no variants) ──
  set('#simplePrice', '1150');
  set('#simpleBargainingPrice', '1050');
  set('#simpleQuantity', '120');
  set('#simpleAlertQty', '10');
  set('#simpleWeight', '0.40');

  // ── Shipping ──
  set('#productShippingProfile', 'Standard');

  // ── Discount: none (default) ──
  set('[data-discount-offer-duration]', 'none');

  // ── Publish: active (default) ──
  // already checked

  // ── Tags ──
  set('#productCatalogTags', 'tshirt, cotton, apparel');

  // ── SEO ──
  set('#productSlug', 'classic-tshirt');
  set('#productMetaTitle', 'Classic T-Shirt | Shop');
  set('#productMetaDescription', 'Buy our premium cotton t-shirt in multiple sizes and colors.');
  set('#productTags', 'buy tshirt online, cotton tee shop');
}

function initOrderCallPage() {
  const section = document.querySelector('[data-order-call-page]');
  if (!section) return;

  const apiBaseUrl = String(section.dataset.apiBaseUrl || '').replace(/\/+$/, '');
  const settingsFieldset = section.querySelector('[data-order-call-settings-fields]');
  const pageNameInput = section.querySelector('[data-order-call-page-name-input]');
  const languageInput = section.querySelector('[data-order-call-language-input]');
  const enabledInput = section.querySelector('[data-order-call-enabled-input]');
  const scopeInputs = Array.from(section.querySelectorAll('[data-order-call-scope-input]'));
  const scopeCards = Array.from(section.querySelectorAll('[data-order-call-scope-card]'));
  const submitButton = section.querySelector('[data-order-call-submit]');
  const saveStatusNode = section.querySelector('[data-order-call-save-status]');
  const configBadgeNode = section.querySelector('[data-order-call-config-badge]');
  const enabledLabelNode = section.querySelector('[data-order-call-enabled-label]');
  const sideStatusNode = section.querySelector('[data-order-call-side-status]');
  const sidePageNode = section.querySelector('[data-order-call-side-page]');
  const sideLanguageNode = section.querySelector('[data-order-call-side-language]');
  const sideScopeNode = section.querySelector('[data-order-call-side-scope]');
  const liveBadgeNode = section.querySelector('[data-order-call-live-badge]');
  const availableCountNode = section.querySelector('[data-order-call-available-count]');
  const availableCopyNode = section.querySelector('[data-order-call-available-copy]');
  const usageRingNode = section.querySelector('[data-order-call-usage-ring]');
  const usageRingValueNode = section.querySelector('[data-order-call-usage-ring-value]');
  const usageRingLabelNode = section.querySelector('[data-order-call-usage-ring-label]');
  const usageProgressFillNode = section.querySelector('[data-order-call-usage-progress-fill]');
  const availableMetaNode = section.querySelector('[data-order-call-available-meta]');
  const languageMetaNode = section.querySelector('[data-order-call-language-meta]');
  const scopeMetaNode = section.querySelector('[data-order-call-scope-meta]');
  if (
    !(pageNameInput instanceof HTMLInputElement) ||
    !(languageInput instanceof HTMLSelectElement) ||
    !(enabledInput instanceof HTMLInputElement) ||
    !scopeInputs.length
  ) return;

  const text = (value) => String(value ?? '').trim();
  const titleCase = (value) => text(value)
    .toLowerCase()
    .split(/[\s_-]+/g)
    .filter(Boolean)
    .map((chunk) => chunk.charAt(0).toUpperCase() + chunk.slice(1))
    .join(' ');
  const defaultPageName = text(section.dataset.defaultPageName || 'A Metafy');
  const languageOptions = Array.from(languageInput.options).map((option) => ({
    value: text(option.value).toLowerCase(),
    label: text(option.textContent) || titleCase(option.value),
  }));
  const defaultLanguage = languageOptions.some((option) => option.value === text(section.dataset.defaultLanguage).toLowerCase())
    ? text(section.dataset.defaultLanguage).toLowerCase()
    : (languageOptions[0]?.value || 'english');
  const normalizeCallScope = (value) => {
    const normalized = text(value).toLowerCase();
    if (normalized === 'all' || normalized === 'all_buyers') return 'all';
    return 'cod';
  };
  const defaultCallScope = normalizeCallScope(section.dataset.defaultCallScope || 'cod');
  const callScopeLabel = (value) => normalizeCallScope(value) === 'all'
    ? 'All Buyers'
    : 'Cash on Delivery Buyers';
  const normalizeLanguage = (value) => {
    const normalized = text(value).toLowerCase();
    if (languageOptions.some((option) => option.value === normalized)) {
      return normalized;
    }
    return defaultLanguage;
  };
  const languageLabel = (value) => {
    const normalized = normalizeLanguage(value);
    const matchedOption = languageOptions.find((option) => option.value === normalized);
    return matchedOption?.label || titleCase(normalized) || 'Unknown';
  };
  const normalizeAvailableCalling = (value) => {
    const parsed = Number(value);
    if (!Number.isFinite(parsed) || parsed < 0) return 0;
    return Math.floor(parsed);
  };
  const sanitizeState = (state = {}) => ({
    pageName: text(state.pageName) || defaultPageName,
    language: normalizeLanguage(state.language || defaultLanguage),
    enabled: Boolean(state.enabled),
    scope: normalizeCallScope(state.scope || defaultCallScope),
    availableCalling: normalizeAvailableCalling(state.availableCalling),
  });
  const stateFromApi = (payload = {}) => sanitizeState({
    pageName: payload?.recording_page_name,
    language: payload?.recording_language,
    enabled: payload?.is_calling,
    scope: payload?.calling_scope,
    availableCalling: payload?.available_calling,
  });
  const statesEqual = (left, right) => {
    const resolvedLeft = sanitizeState(left);
    const resolvedRight = sanitizeState(right);

    return resolvedLeft.pageName === resolvedRight.pageName
      && resolvedLeft.language === resolvedRight.language
      && resolvedLeft.enabled === resolvedRight.enabled
      && resolvedLeft.scope === resolvedRight.scope
      && resolvedLeft.availableCalling === resolvedRight.availableCalling;
  };
  const writeStoredState = (state) => {
    const resolvedState = sanitizeState(state);
    try {
      window.localStorage.setItem(ORDER_CALL_PAGE_NAME_STORAGE_KEY, resolvedState.pageName);
      window.localStorage.setItem(ORDER_CALL_LANGUAGE_STORAGE_KEY, resolvedState.language);
      window.localStorage.setItem(ORDER_CALL_ENABLED_STORAGE_KEY, resolvedState.enabled ? '1' : '0');
      window.localStorage.setItem(ORDER_CALL_SCOPE_STORAGE_KEY, resolvedState.scope);
    } catch {
      // Ignore storage failures and keep UI responsive.
    }
  };
  const setSaveStatus = (message, tone = 'info') => {
    if (!(saveStatusNode instanceof HTMLElement)) return;
    saveStatusNode.textContent = text(message);
    saveStatusNode.classList.remove('is-success', 'is-warning', 'is-danger');
    if (tone === 'success' || tone === 'warning' || tone === 'danger') {
      saveStatusNode.classList.add(`is-${tone}`);
    }
  };
  const setConfigBadge = (message, tone = 'info') => {
    if (!(configBadgeNode instanceof HTMLElement)) return;
    configBadgeNode.textContent = text(message);
    configBadgeNode.classList.remove('is-info', 'is-warning', 'is-success', 'is-danger');
    configBadgeNode.classList.add(`is-${tone}`);
  };
  const setLiveBadge = (label, tone = 'info') => {
    if (!(liveBadgeNode instanceof HTMLElement)) return;
    liveBadgeNode.textContent = text(label);
    liveBadgeNode.classList.remove('badge-success', 'badge-warning', 'badge-info', 'badge-danger');
    liveBadgeNode.classList.add(
      tone === 'success'
        ? 'badge-success'
        : tone === 'warning'
          ? 'badge-warning'
          : tone === 'danger'
            ? 'badge-danger'
            : 'badge-info'
    );
  };
  const setControlsDisabled = (disabled) => {
    if (settingsFieldset instanceof HTMLElement) {
      settingsFieldset.classList.toggle('is-disabled', Boolean(disabled));
    }
    pageNameInput.disabled = Boolean(disabled);
    languageInput.disabled = Boolean(disabled);
    enabledInput.disabled = Boolean(disabled);
    scopeInputs.forEach((input) => {
      if (input instanceof HTMLInputElement) input.disabled = Boolean(disabled);
    });
  };
  const setSubmitState = (label, disabled = false, mode = 'save') => {
    if (!(submitButton instanceof HTMLButtonElement)) return;
    submitButton.textContent = text(label) || 'Save Settings';
    submitButton.disabled = disabled;
    submitButton.dataset.mode = mode;
  };
  const syncScopeCards = () => {
    scopeCards.forEach((card) => {
      const input = card.querySelector('[data-order-call-scope-input]');
      const isActive = input instanceof HTMLInputElement && input.checked;
      card.classList.toggle('is-active', isActive);
    });
  };
  const render = (state, options = {}) => {
    const resolvedState = sanitizeState(state);
    const syncFormControls = options.syncFormControls !== false;
    const progressValue = Math.max(0, Math.min(100, resolvedState.availableCalling));
    const resolvedLanguageLabel = languageLabel(resolvedState.language);
    const resolvedScopeLabel = callScopeLabel(resolvedState.scope);

    section.dataset.callEnabled = resolvedState.enabled ? '1' : '0';

    if (syncFormControls) {
      pageNameInput.value = resolvedState.pageName;
      if (languageOptions.some((option) => option.value === resolvedState.language)) {
        languageInput.value = resolvedState.language;
      }
      enabledInput.checked = resolvedState.enabled;
      scopeInputs.forEach((input) => {
        if (!(input instanceof HTMLInputElement)) return;
        input.checked = normalizeCallScope(input.value) === resolvedState.scope;
      });
    }

    syncScopeCards();

    if (enabledLabelNode) enabledLabelNode.textContent = resolvedState.enabled ? 'On' : 'Off';
    if (sideStatusNode) sideStatusNode.textContent = resolvedState.enabled ? 'On' : 'Off';
    if (sidePageNode) sidePageNode.textContent = resolvedState.pageName;
    if (sideLanguageNode) sideLanguageNode.textContent = resolvedLanguageLabel;
    if (sideScopeNode) sideScopeNode.textContent = resolvedScopeLabel;
    if (availableCountNode) availableCountNode.textContent = String(resolvedState.availableCalling);
    if (availableCopyNode) {
      availableCopyNode.textContent = resolvedState.availableCalling === 1
        ? '1 call available for automated confirmation.'
        : `${resolvedState.availableCalling} calls available for automated confirmation.`;
    }
    if (usageRingNode instanceof HTMLElement) {
      usageRingNode.style.setProperty('--usage-progress', String(progressValue));
    }
    if (usageRingValueNode) usageRingValueNode.textContent = String(resolvedState.availableCalling);
    if (usageRingLabelNode) {
      usageRingLabelNode.textContent = resolvedState.availableCalling === 1 ? 'Call Left' : 'Calls Left';
    }
    if (usageProgressFillNode instanceof HTMLElement) {
      usageProgressFillNode.style.width = `${progressValue}%`;
    }
    if (availableMetaNode) {
      availableMetaNode.textContent = `${resolvedState.availableCalling} ${resolvedState.availableCalling === 1 ? 'Call' : 'Calls'}`;
    }
    if (languageMetaNode) languageMetaNode.textContent = resolvedLanguageLabel;
    if (scopeMetaNode) scopeMetaNode.textContent = resolvedScopeLabel;

    setLiveBadge(
      resolvedState.enabled ? 'Calling On' : 'Calling Off',
      resolvedState.enabled ? 'success' : 'warning'
    );
  };
  const syncDraftFromInputs = () => {
    const selectedScopeInput = scopeInputs.find((input) => input instanceof HTMLInputElement && input.checked);
    draftState = sanitizeState({
      ...draftState,
      pageName: text(pageNameInput.value) || defaultPageName,
      language: text(languageInput.value) || defaultLanguage,
      enabled: Boolean(enabledInput.checked),
      scope: selectedScopeInput?.value || defaultCallScope,
    });
  };
  const refreshDirtyState = (cleanMessage = 'Live settings loaded from API.', cleanTone = 'info') => {
    if (statesEqual(draftState, savedState)) {
      setConfigBadge('Ready to Edit', 'success');
      setSaveStatus(cleanMessage, cleanTone);
      return;
    }

    setConfigBadge('Unsaved Changes', 'warning');
    setSaveStatus('Preview updated. Click Save Settings to send changes to the API.', 'warning');
  };
  const resolveRefreshToken = () => text(
    window.API?.getToken?.()
    || (typeof window.getToken === 'function' ? window.getToken() : '')
  );
  const resolveRequestError = (error, fallbackMessage) => {
    if (error?.isTimeout) return 'Request timed out. Please try again.';
    if (error?.status === 401) {
      return 'Unauthorized (401). Server-provided refresh token is invalid or expired. Please re-login.';
    }
    return text(error?.message) || fallbackMessage;
  };
  const buildSavePayload = (state) => {
    const resolvedState = sanitizeState(state);
    return {
      is_calling: resolvedState.enabled,
      calling_scope: resolvedState.scope,
      recording_page_name: resolvedState.pageName,
      recording_language: resolvedState.language,
    };
  };
  const setLoadFailureState = (message) => {
    hasLoaded = false;
    render(savedState);
    setControlsDisabled(true);
    setSubmitState('Retry Load', false, 'retry');
    setConfigBadge('Load Failed', 'danger');
    setSaveStatus(message, 'danger');
    setLiveBadge('Load Failed', 'danger');
  };

  let savedState = sanitizeState({
    pageName: defaultPageName,
    language: defaultLanguage,
    enabled: false,
    scope: defaultCallScope,
    availableCalling: 0,
  });
  let draftState = {...savedState};
  let hasLoaded = false;

  const loadConfig = async () => {
    if (!apiBaseUrl) {
      const message = 'Missing backend API URL.';
      setLoadFailureState(message);
      if (typeof window.showError === 'function') window.showError(message);
      return;
    }

    if (!window.API?.Admin?.OrderCall?.getConfig) {
      const message = 'Order call API client is unavailable.';
      setLoadFailureState(message);
      if (typeof window.showError === 'function') window.showError(message);
      return;
    }

    const refreshToken = resolveRefreshToken();
    if (!refreshToken) {
      const message = 'Missing refresh token. Please login again.';
      setLoadFailureState(message);
      if (typeof window.showError === 'function') window.showError(message);
      return;
    }

    setControlsDisabled(true);
    setSubmitState('Loading...', true, 'loading');
    setConfigBadge('Loading', 'info');
    setSaveStatus('Loading call settings...', 'info');
    setLiveBadge('Loading', 'info');

    try {
      const payload = await window.API.Admin.OrderCall.getConfig({
        apiBaseUrl,
        refreshToken,
        timeoutMs: 12000,
      });

      savedState = stateFromApi(payload);
      draftState = {...savedState};
      hasLoaded = true;

      render(savedState);
      writeStoredState(savedState);
      setControlsDisabled(false);
      setSubmitState('Save Settings', false, 'save');
      setConfigBadge('Ready to Edit', 'success');
      setSaveStatus('Live settings loaded from API.', 'success');
    } catch (error) {
      hasLoaded = false;
      const message = resolveRequestError(error, 'Unable to load order call settings.');
      setLoadFailureState(message);
      if (typeof window.showError === 'function') window.showError(message);
    }
  };

  const handleDraftChange = () => {
    if (!hasLoaded) return;
    syncDraftFromInputs();
    render(draftState, {syncFormControls: false});
    refreshDirtyState();
  };

  pageNameInput.addEventListener('input', handleDraftChange);
  pageNameInput.addEventListener('change', handleDraftChange);
  languageInput.addEventListener('change', handleDraftChange);
  enabledInput.addEventListener('change', handleDraftChange);
  scopeInputs.forEach((input) => {
    if (!(input instanceof HTMLInputElement)) return;
    input.addEventListener('change', handleDraftChange);
  });

  if (submitButton instanceof HTMLButtonElement) {
    submitButton.addEventListener('click', async () => {
      if (submitButton.dataset.mode === 'retry' || !hasLoaded) {
        await loadConfig();
        return;
      }

      syncDraftFromInputs();

      if (statesEqual(draftState, savedState)) {
        setSaveStatus('No changes to save.', 'info');
        if (typeof window.showInfo === 'function') window.showInfo('No changes to save.');
        return;
      }

      const refreshToken = resolveRefreshToken();
      if (!refreshToken) {
        const message = 'Missing refresh token. Please login again.';
        setSaveStatus(message, 'danger');
        setConfigBadge('Save Failed', 'danger');
        if (typeof window.showError === 'function') window.showError(message);
        return;
      }

      setControlsDisabled(true);
      setSubmitState('Saving...', true, 'save');
      setConfigBadge('Saving', 'info');
      setSaveStatus('Sending changes to calling API...', 'info');

      try {
        const payload = await window.API.Admin.OrderCall.saveConfig({
          apiBaseUrl,
          refreshToken,
          payload: buildSavePayload(draftState),
          timeoutMs: 12000,
        });

        if (text(payload?.message).toLowerCase() !== 'updated') {
          throw new Error(text(payload?.message) || 'Update response was not recognized.');
        }

        savedState = sanitizeState(draftState);
        draftState = {...savedState};

        render(savedState);
        writeStoredState(savedState);
        setControlsDisabled(false);
        setSubmitState('Save Settings', false, 'save');
        setConfigBadge('Ready to Edit', 'success');
        setSaveStatus('Settings saved to live calling config.', 'success');
        if (typeof window.showSuccess === 'function') {
          window.showSuccess('Order call settings updated.');
        }
      } catch (error) {
        const message = resolveRequestError(error, 'Unable to save order call settings.');
        setControlsDisabled(false);
        setSubmitState('Save Settings', false, 'save');
        render(draftState);
        setConfigBadge('Save Failed', 'danger');
        setSaveStatus(message, 'danger');
        if (typeof window.showError === 'function') window.showError(message);
      }
    });
  }

  render(savedState);
  setControlsDisabled(true);
  setSubmitState('Loading...', true, 'loading');
  setConfigBadge('Loading', 'info');
  setSaveStatus('Loading call settings...', 'info');
  setLiveBadge('Loading', 'info');
  void loadConfig();
}

function initProductCallVoicePreview() {
  const card = document.querySelector('[data-product-call-voice]');
  if (!card) return;

  const productNameInput = document.getElementById('productName');
  if (!(productNameInput instanceof HTMLInputElement)) return;

  const titleNode = card.querySelector('[data-product-call-voice-title]');
  const statusNode = card.querySelector('[data-product-call-voice-status]');
  const durationNode = card.querySelector('[data-product-call-voice-duration]');
  const pageNode = card.querySelector('[data-product-call-voice-page]');
  const banglaNode = card.querySelector('[data-product-call-script-bn]');
  const englishNode = card.querySelector('[data-product-call-script-en]');

  const text = (value) => String(value ?? '').trim();
  const defaultPageName = text(card.dataset.pageName || 'A Metafy');
  const defaultProductTitle = text(card.dataset.defaultProductTitle || 'Premium Cotton T-Shirt');
  const defaultDuration = text(card.dataset.defaultDuration || '00:18');
  const featureActive = text(card.dataset.featureActive) === '1';

  let renderTimer = 0;

  const readStoredPageName = () => {
    try {
      return text(window.localStorage.getItem(ORDER_CALL_PAGE_NAME_STORAGE_KEY)) || defaultPageName;
    } catch {
      return defaultPageName;
    }
  };

  const buildBanglaScript = (pageName, productTitle) => (
    `Assalamu alaikum. ${pageName}-e apnake shagotom. ` +
    `Apni amader ${pageName} theke ${productTitle} order korechen. ` +
    'Apnar order ti confirm korte 1 chapun, cancel korte 2 chapun.'
  );

  const buildEnglishScript = (pageName, productTitle) => (
    `Assalamu alaikum. Welcome to ${pageName}. ` +
    `You placed an order for ${productTitle} from ${pageName}. ` +
    'Press 1 to confirm your order, or press 2 to cancel it.'
  );

  const resolveDuration = (productTitle) => {
    const words = text(productTitle).split(/\s+/).filter(Boolean).length;
    if (!words) return defaultDuration;
    const seconds = Math.max(16, Math.min(26, 15 + words));
    return `00:${String(seconds).padStart(2, '0')}`;
  };

  const renderPreview = (statusText) => {
    const productTitle = text(productNameInput.value) || defaultProductTitle;
    const pageName = readStoredPageName();

    if (titleNode) titleNode.textContent = productTitle;
    if (pageNode) pageNode.textContent = pageName;
    if (statusNode) {
      statusNode.textContent = statusText || (featureActive ? 'Auto voice ready' : 'Demo voice preview ready');
    }
    if (durationNode) durationNode.textContent = resolveDuration(productTitle);
    if (banglaNode) banglaNode.textContent = buildBanglaScript(pageName, productTitle);
    if (englishNode) englishNode.textContent = buildEnglishScript(pageName, productTitle);
  };

  const queueRefresh = () => {
    if (statusNode) {
      statusNode.textContent = featureActive ? 'Regenerating voice preview...' : 'Updating demo voice preview...';
    }

    window.clearTimeout(renderTimer);
    renderTimer = window.setTimeout(() => {
      renderPreview(featureActive ? 'Auto voice ready' : 'Demo voice preview ready');
    }, 260);
  };

  productNameInput.addEventListener('input', queueRefresh);
  productNameInput.addEventListener('change', queueRefresh);
  window.addEventListener('storage', (event) => {
    if (event.key !== ORDER_CALL_PAGE_NAME_STORAGE_KEY) return;
    renderPreview(featureActive ? 'Page name updated from Call Voice Setup.' : 'Page name updated for demo preview.');
  });

  renderPreview();
}

// ══════════════════════════════════════════
// PRODUCTS: CREATE SUBMIT
// ══════════════════════════════════════════
// ============================================
// PRODUCTS: EDIT PREFILL
// ============================================
function initProductEditPrefill() {
  const form = document.getElementById('createProductForm');
  if (!form || form.dataset.formMode !== 'edit') return;

  const productId = Number.parseInt(String(form.dataset.productId || ''), 10);
  if (!Number.isFinite(productId) || productId <= 0) return;

  const apiBase = String(form.dataset.apiBaseUrl || 'http://localhost:8082').replace(/\/+$/, '');
  const token =
    String(form.dataset.refreshToken || '').trim() ||
    localStorage.getItem('refresh_token') ||
    '';

  const setInput = (selector, value) => {
    const field = form.querySelector(selector);
    if (!field) return;
    field.value = String(value ?? '');
    field.dispatchEvent(new Event('input', { bubbles: true }));
    field.dispatchEvent(new Event('change', { bubbles: true }));
  };

  const setSelectSmart = (selector, rawValue) => {
    const field = form.querySelector(selector);
    if (!field) return;

    if (!(field instanceof HTMLSelectElement)) {
      const raw = String(rawValue ?? '').trim();
      if (selector === '#productCategory' && window._productCategoryPicker) {
        window._productCategoryPicker.setValue(raw);
      } else {
        field.value = raw;
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('change', { bubbles: true }));
      }
      return;
    }

    const value = String(rawValue ?? '').trim();
    if (!value) {
      field.value = '';
      field.dispatchEvent(new Event('change', { bubbles: true }));
      return;
    }

    const optionByValue = Array.from(field.options).find(
      (opt) => String(opt.value || '').trim().toLowerCase() === value.toLowerCase()
    );
    const optionByLabel = optionByValue || Array.from(field.options).find(
      (opt) => String(opt.textContent || '').trim().toLowerCase() === value.toLowerCase()
    );

    if (optionByLabel) {
      field.value = optionByLabel.value;
    } else {
      const option = document.createElement('option');
      option.value = value;
      option.textContent = value;
      field.appendChild(option);
      field.value = value;
    }

    field.dispatchEvent(new Event('change', { bubbles: true }));
  };

  const setRadio = (name, value) => {
    const radio = form.querySelector(`[name="${name}"][value="${value}"]`);
    if (!radio) return;
    radio.checked = true;
    radio.dispatchEvent(new Event('change', { bubbles: true }));
    radio.closest('[data-product-type-card], [data-variant-toggle-card], [data-drive-link-type-card]')?.click();
  };

  const toDatetimeLocal = (value) => {
    if (!value) return '';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '';
    const pad = (n) => String(n).padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
  };

  const parseList = (value) => Array.isArray(value) ? value : [];

  const applyProductToForm = (product) => {
    const type = String(product?.type || 'physical').toLowerCase();
    setRadio('product_type', type);

    setInput('#productName', product?.name || '');
    setSelectSmart('#productCategory', product?.category_id ?? product?.category ?? '');
    setInput('#productShortDescription', product?.short_description || '');

    if (window._productCreateEditor && typeof window._productCreateEditor.setFullDescription === 'function') {
      window._productCreateEditor.setFullDescription(product?.description || '');
    } else {
      setInput('#productDescription', product?.description || '');
    }

    setInput('#productCatalogTags', parseList(product?.tags).join(', '));
    setInput('#productSlug', product?.slug || '');
    setInput('#productMetaTitle', product?.meta_title || '');
    setInput('#productMetaDescription', product?.meta_description || '');
    setInput('#productTags', parseList(product?.seo_tags).join(', '));

    const publishType = String(product?.publish_type || 'active').toLowerCase();
    if (publishType === 'scheduled') {
      setRadio('publish_state', 'scheduled');
      setInput('#productScheduleAt', toDatetimeLocal(product?.publish_at));
    } else if (publishType === 'draft') {
      setRadio('publish_state', 'draft');
      setInput('#productScheduleAt', '');
    } else {
      setRadio('publish_state', 'active');
      setInput('#productScheduleAt', '');
    }

    const discountOffer = String(product?.is_discount_offer || 'inactive').toLowerCase();
    let discountDuration = 'none';
    if (discountOffer === 'lifetime') {
      discountDuration = 'lifetime';
    } else if (discountOffer === 'limited') {
      discountDuration = (product?.discount_start_at || product?.discount_end_at) ? 'date_time' : 'lifetime';
    }
    setInput('[data-discount-offer-duration]', discountDuration);
    setInput('#discountOfferType', product?.is_discount_type || 'fixed');
    setInput('#discountOffer', product?.discount_value ?? '');
    setInput('#discountOfferStartAt', toDatetimeLocal(product?.discount_start_at));
    setInput('#discountOfferEndAt', toDatetimeLocal(product?.discount_end_at));

    if (window._productCreateMedia) {
      if (typeof window._productCreateMedia.setCoverFromUrl === 'function') {
        window._productCreateMedia.setCoverFromUrl(product?.cover || product?.cover_image || '');
      }
      if (typeof window._productCreateMedia.setSliderEnabled === 'function') {
        window._productCreateMedia.setSliderEnabled(Boolean(product?.is_slider));
      }
      if (typeof window._productCreateMedia.setSliderItemsFromApi === 'function') {
        window._productCreateMedia.setSliderItemsFromApi(parseList(product?.media_items));
      }
    }

    if (type === 'physical') {
      setSelectSmart('#productShippingProfile', product?.shipping_profile || '');
      const hasVariants = Boolean(product?.is_variants);
      if (window._productCreateVariants && typeof window._productCreateVariants.setMode === 'function') {
        window._productCreateVariants.setMode(hasVariants);
      } else {
        setRadio('has_variants', hasVariants ? 'yes' : 'no');
      }

      if (hasVariants) {
        if (window._productCreateVariants && typeof window._productCreateVariants.setVariants === 'function') {
          window._productCreateVariants.setVariants(parseList(product?.variants));
        }
      } else {
        setInput('#simplePrice', product?.product_price ?? '');
        setInput('#simpleBargainingPrice', product?.bargaining_price ?? '');
        setInput('#simpleQuantity', product?.available_qty ?? '');
        setInput('#simpleAlertQty', product?.stock_alert ?? '');
        setInput('#simpleWeight', product?.weight ?? '');
      }
    } else {
      setInput('#productPrice', product?.product_price ?? '');
      setInput('#bargainingPrice', product?.bargaining_price ?? '');
    }

    if (type === 'downloadable') {
      const downloadableItems = parseList(product?.downloadables);
      const firstDownloadable = downloadableItems[0] || {};
      const accessType = String(firstDownloadable.access_type || '').toLowerCase();
      setRadio('drive_link_type', accessType === 'direct' ? 'public' : 'private');
      setInput('#productDriveLink', firstDownloadable.drive_link || '');
      setInput('#productDriveNotes', firstDownloadable.access_instruction || '');
      if (downloadableItems.length > 1) {
        showInfo('Multiple downloadable entries found. First entry loaded into edit fields.');
      }
    }

    if (type === 'subscription') {
      if (window._productCreateSubscriptions && typeof window._productCreateSubscriptions.setEntries === 'function') {
        window._productCreateSubscriptions.setEntries(parseList(product?.subscriptions));
      }
    }
  };

  const saveBtn = document.querySelector('[type="submit"][form="createProductForm"]');
  const originalSaveBtnText = saveBtn?.textContent || 'Update Product';

  const fetchAndFill = async () => {
    if (!token) {
      showWarning('Authentication token not found. Please log in again.');
      return;
    }

    if (saveBtn) {
      saveBtn.disabled = true;
      saveBtn.textContent = 'Loading...';
    }

    try {
      const payload = await window.API.Admin.Products.getById({
        apiBaseUrl: apiBase,
        refreshToken: token,
        productId,
        timeoutMs: 12000,
      });

      applyProductToForm(payload || {});
      showSuccess('Product loaded for editing.');
    } catch (error) {
      if (error?.isTimeout) {
        showError('Request timed out while loading product.');
      } else if (error?.status === 404) {
        showError(error?.message || 'Product not found.');
      } else if (error?.status === 401) {
        showError('Unauthorized (401). Please log in again.');
      } else {
        showError(error?.message || 'Network error. Could not load product data.');
      }
    } finally {
      if (saveBtn) {
        saveBtn.disabled = false;
        saveBtn.textContent = originalSaveBtnText;
      }
    }
  };

  fetchAndFill();
}

function initProductCreateSubmit() {
  const form = document.getElementById('createProductForm');
  if (!form) return;

  const getToken = () =>
    form.dataset.refreshToken ||
    localStorage.getItem('refresh_token') ||
    (window.API && typeof window.API.getToken === 'function' ? window.API.getToken() : '') ||
    '';

  const parseTagInput = (value) =>
    String(value || '').split(',').map((t) => t.trim()).filter(Boolean);

  const toIsoOrNull = (value) => {
    if (!value) return null;
    try { return new Date(value).toISOString(); } catch { return null; }
  };

  const collectPayload = () => {
    const type = form.querySelector('[name="product_type"]:checked')?.value || 'physical';
    const isPhysical = type === 'physical';
    const hasVariants = form.querySelector('[name="has_variants"]:checked')?.value === 'yes';

    const name = form.querySelector('#productName')?.value?.trim() || '';
    const categoryRaw = form.querySelector('#productCategory')?.value || '';
    const category = /^\d+$/.test(String(categoryRaw).trim())
      ? Number.parseInt(String(categoryRaw).trim(), 10)
      : String(categoryRaw).trim();
    const short_description = form.querySelector('#productShortDescription')?.value?.trim() || '';
    const description = form.querySelector('#productDescription')?.value?.trim() || '';

    let product_price = null;
    let bargaining_price = null;
    let available_qty = null;
    let stock_alert = null;
    let weight = null;

    if (isPhysical && !hasVariants) {
      product_price = parseFloat(form.querySelector('#simplePrice')?.value) || null;
      bargaining_price = parseFloat(form.querySelector('#simpleBargainingPrice')?.value) || null;
      available_qty = parseInt(form.querySelector('#simpleQuantity')?.value, 10) || null;
      stock_alert = parseInt(form.querySelector('#simpleAlertQty')?.value, 10) || null;
      weight = parseFloat(form.querySelector('#simpleWeight')?.value) || null;
    } else if (!isPhysical) {
      product_price = parseFloat(form.querySelector('#productPrice')?.value) || null;
      bargaining_price = parseFloat(form.querySelector('#bargainingPrice')?.value) || null;
    }

    const shipping_profile = isPhysical
      ? (form.querySelector('#productShippingProfile')?.value || null)
      : null;

    const discountDuration = form.querySelector('[data-discount-offer-duration]')?.value || 'none';
    let is_discount_offer = 'inactive';
    if (discountDuration === 'lifetime') is_discount_offer = 'lifetime';
    else if (discountDuration === 'date_time') is_discount_offer = 'limited';

    const hasDiscount = is_discount_offer !== 'inactive';
    const is_discount_type = hasDiscount
      ? (form.querySelector('#discountOfferType')?.value || 'fixed')
      : 'inactive';
    const discount_value = hasDiscount
      ? (parseFloat(form.querySelector('#discountOffer')?.value) || null)
      : null;
    const discount_start_at = (hasDiscount && discountDuration === 'date_time')
      ? toIsoOrNull(form.querySelector('#discountOfferStartAt')?.value)
      : null;
    const discount_end_at = (hasDiscount && discountDuration === 'date_time')
      ? toIsoOrNull(form.querySelector('#discountOfferEndAt')?.value)
      : null;

    const publish_type = form.querySelector('[name="publish_state"]:checked')?.value || 'active';
    const publish_at = publish_type === 'scheduled'
      ? toIsoOrNull(form.querySelector('#productScheduleAt')?.value)
      : null;

    const media = window._productCreateMedia;
    const cover_image = media?.getCoverImage?.() ?? null;
    const is_slider = media?.isSliderEnabled() ?? false;
    const media_items = is_slider ? (media?.getSliderItems() ?? []) : [];

    const variants = [];
    if (isPhysical && hasVariants) {
      Array.from(form.querySelectorAll('[data-variant-card]')).forEach((card) => {
        const id = card.dataset.variantCard;
        const colorToggle = card.querySelector(`[data-variant-color-toggle="${id}"]`);
        const sizeToggle = card.querySelector(`[data-variant-size-toggle="${id}"]`);
        variants.push({
          have_color: colorToggle?.checked || false,
          color: colorToggle?.checked ? (card.querySelector(`[data-variant-color-field="${id}"]`)?.value || '') : '',
          have_size: sizeToggle?.checked || false,
          size: sizeToggle?.checked ? (card.querySelector(`[data-variant-size-field="${id}"]`)?.value || '') : '',
          qty: parseInt(card.querySelector(`[data-variant-qty="${id}"]`)?.value, 10) || 0,
          alert_qty: parseInt(card.querySelector(`[data-variant-alert="${id}"]`)?.value, 10) || 0,
          weight: parseFloat(card.querySelector(`[data-variant-weight="${id}"]`)?.value) || 0,
        });
      });
    }

    const downloadables = [];
    if (type === 'downloadable') {
      const driveLink = form.querySelector('#productDriveLink')?.value?.trim() || '';
      const accessInstruction = form.querySelector('#productDriveNotes')?.value?.trim() || '';
      const linkTypeValue = form.querySelector('[data-drive-link-type]:checked')?.value || 'public';
      downloadables.push({
        access_type: linkTypeValue === 'private' ? 'email' : 'direct',
        drive_link: driveLink,
        access_instruction: accessInstruction,
      });
    }

    const subscriptions = [];
    if (type === 'subscription') {
      Array.from(form.querySelectorAll('[data-subscription-entry]')).forEach((entry) => {
        const id = entry.dataset.subscriptionEntry;
        subscriptions.push({
          email: entry.querySelector(`[name="subscriptions[${id}][email]"]`)?.value || '',
          number: entry.querySelector(`[name="subscriptions[${id}][mobile]"]`)?.value || '',
          username: entry.querySelector(`[name="subscriptions[${id}][username]"]`)?.value || '',
          password: entry.querySelector(`[name="subscriptions[${id}][password]"]`)?.value || '',
        });
      });
    }

    const tags = parseTagInput(form.querySelector('#productCatalogTags')?.value);
    const slug = form.querySelector('#productSlug')?.value?.trim() || '';
    const meta_title = form.querySelector('#productMetaTitle')?.value?.trim() || '';
    const meta_description = form.querySelector('#productMetaDescription')?.value?.trim() || '';
    const seo_tags = parseTagInput(form.querySelector('#productTags')?.value);

    const payload = {
      type,
      name,
      category,
      short_description,
      description,
      is_variants: isPhysical ? hasVariants : false,
      product_price,
      bargaining_price,
      available_qty,
      stock_alert,
      weight,
      shipping_profile,
      is_discount_offer,
      is_discount_type,
      discount_value,
      discount_start_at,
      discount_end_at,
      publish_type,
      publish_at,
      cover_image,
      is_slider,
      media_items,
      variants,
      tags,
      slug,
      meta_title,
      meta_description,
      seo_tags,
    };

    if (!String(cover_image || '').startsWith('data:image/')) {
      delete payload.cover_image;
    }

    payload.downloadables = downloadables;
    payload.subscriptions = subscriptions;

    return payload;
  };

  form.addEventListener('submit', async (event) => {
    event.preventDefault();

    if (form.dataset.productTypeMismatch === '1') {
      const shopSettingsUrl = String(form.dataset.shopSettingsUrl || '/admin/shop-settings').trim();
      showInfo('Change the store product type from Shop Settings before saving this product.');
      window.setTimeout(() => {
        window.location.href = shopSettingsUrl;
      }, 200);
      return;
    }

    const token = getToken();
    if (!token) {
      showWarning('Authentication token not found. Please log in again.');
      return;
    }

    const saveBtn = document.querySelector('[type="submit"][form="createProductForm"]');
    const isEditMode = form.dataset.formMode === 'edit';
    const editProductId = Number.parseInt(String(form.dataset.productId || ''), 10);
    const apiBaseUrl = String(form.dataset.apiBaseUrl || 'http://localhost:8082').replace(/\/+$/, '');
    const submitProductId = (isEditMode && Number.isFinite(editProductId) && editProductId > 0)
      ? editProductId
      : null;

    const originalText = saveBtn?.textContent || (isEditMode ? 'Update Product' : 'Save Product');
    if (saveBtn) {
      saveBtn.disabled = true;
      saveBtn.textContent = 'Saving…';
    }

    try {
      const payload = collectPayload();
      if (!String(payload.category || '').trim()) {
        window._productCategoryPicker?.markInvalid?.();
        window._productCategoryPicker?.focus?.();
        showWarning('Please select a category before saving the product.');
        return;
      }

      const data = await window.API.Admin.Products.save({
        apiBaseUrl,
        refreshToken: token,
        payload,
        productId: submitProductId,
        timeoutMs: 12000,
      });
      showSuccess(data.message || (isEditMode ? 'Product updated successfully.' : 'Product created successfully.'));
      sessionStorage.removeItem('product_create_type');
      sessionStorage.removeItem('product_drive_link_type');
      setTimeout(() => { window.location.href = '/admin/products'; }, 1500);
    } catch (error) {
      showError(error?.message || 'Network error. Could not reach the server.');
    } finally {
      if (saveBtn) {
        saveBtn.disabled = false;
        saveBtn.textContent = originalText;
      }
    }
  });
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
