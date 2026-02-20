/* ═══════════════════════════════════════════════════════════
   A Metafy ADMIN PANEL — MAIN JAVASCRIPT
   All interactive functionality
═══════════════════════════════════════════════════════════ */

// ── Initialize on DOM load ──
document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  initSidebar();
  initDropdowns();
  initModals();
  initToasts();
  initTables();
  initCharts();
  initSearch();
  initBotSettings();
  initProductsAttentionPanel();
  initCategoryAiWriter();
  initCategoryEditor();
  initCategoryDeleteGuards();
  setActivePage();
});

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
  if (!document.querySelector('.toast-container')) {
    const container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
}

function showToast(message, type = 'info', duration = 4000) {
  const container = document.querySelector('.toast-container');

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

// ══════════════════════════════════════════
// BOT SETTINGS
// ══════════════════════════════════════════
function initBotSettings() {
  const form = document.querySelector('[data-bot-settings]');
  if (!form) return;

  const toggles = form.querySelectorAll('.bot-toggle-input');
  if (!toggles.length) return;

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
  let isDirty = false;

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

  saveButton?.addEventListener('click', () => {
    syncMessengerDependency();

    Array.from(toggles).forEach((toggle, index) => {
      initialState[index] = toggle.checked;
    });

    isDirty = false;
    updateUi();
    showSuccess('Bot settings preview updated. Frontend only.');
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

  syncMessengerDependency();
  Array.from(toggles).forEach((toggle, index) => {
    initialState[index] = toggle.checked;
  });
  updateUi();
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
  debounce
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
