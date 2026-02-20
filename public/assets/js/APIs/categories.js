(function (window, document) {
  'use strict';

  function isCategoriesPage() {
    if (window.__ADMIN_ROUTE_NAME === 'admin.categories') {
      return true;
    }

    return window.location.pathname.includes('/admin/categories');
  }

  function escapeHtml(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function toPositiveInteger(value) {
    const numeric = Number.parseInt(String(value), 10);
    return Number.isFinite(numeric) && numeric > 0 ? numeric : null;
  }

  function formatInteger(value) {
    const numeric = Number.parseInt(String(value || 0), 10) || 0;
    return new Intl.NumberFormat('en-US').format(numeric);
  }

  function clampPercent(value) {
    const numeric = Number.parseInt(String(value || 0), 10) || 0;
    if (numeric < 0) return 0;
    if (numeric > 100) return 100;
    return numeric;
  }

  function metricToneClass(index) {
    if (index === 0) return 'is-primary';
    if (index === 1) return 'is-info';
    if (index === 2) return 'is-success';
    return 'is-warning';
  }

  function categoryStatusBadgeClass(status) {
    return status === 'Active' ? 'badge-success' : 'badge-warning';
  }

  function notify(type, message) {
    const trimmed = String(message || '').trim();
    if (!trimmed) return;

    if (type === 'success' && typeof window.showSuccess === 'function') {
      window.showSuccess(trimmed);
      return;
    }

    if (type === 'error' && typeof window.showError === 'function') {
      window.showError(trimmed);
      return;
    }

    if (type === 'warning' && typeof window.showWarning === 'function') {
      window.showWarning(trimmed);
      return;
    }

    if (typeof window.showInfo === 'function') {
      window.showInfo(trimmed);
      return;
    }

    if (type === 'error') {
      console.error(trimmed);
      return;
    }

    console.log(trimmed);
  }

  function errorMessageFrom(error, fallback) {
    if (error && error.payload && typeof error.payload.message === 'string' && error.payload.message.trim()) {
      return error.payload.message.trim();
    }

    if (error && typeof error.message === 'string' && error.message.trim()) {
      return error.message.trim();
    }

    return fallback;
  }

  function collectRefs() {
    return {
      metricsGrid: document.querySelector('[data-categories-metrics]'),
      saveSetupButton: document.querySelector('[data-category-save-setup]'),

      createPanel: document.querySelector('[data-category-create-panel]'),
      createNameInput: document.querySelector('[data-category-name-input]'),
      createSlugInput: document.querySelector('[data-category-slug-input]'),
      createStatusInput: document.querySelector('[data-category-status-input]'),
      createParentInput: document.querySelector('[data-category-parent-input]'),
      createDescriptionInput: document.querySelector('[data-category-description-input]'),
      createAddButton: document.querySelector('[data-category-add-button]'),
      aiGenerateButton: document.querySelector('[data-category-ai-generate]'),
      aiStatus: document.querySelector('[data-category-ai-status]'),

      editPanel: document.querySelector('[data-category-edit-panel]'),
      editTitle: document.querySelector('[data-category-edit-title]'),
      editNameInput: document.querySelector('[data-category-edit-name]'),
      editSlugInput: document.querySelector('[data-category-edit-slug]'),
      editStatusInput: document.querySelector('[data-category-edit-status]'),
      editParentInput: document.querySelector('[data-category-edit-parent]'),
      editDescriptionInput: document.querySelector('[data-category-edit-description]'),
      editProducts: document.querySelector('[data-category-edit-products]'),
      editShare: document.querySelector('[data-category-edit-share]'),
      editUpdated: document.querySelector('[data-category-edit-updated]'),
      editSaveButton: document.querySelector('[data-category-edit-save]'),
      editCancelButton: document.querySelector('[data-category-edit-cancel]'),

      categoriesTotal: document.querySelector('[data-categories-total]'),
      categoriesTableBody: document.querySelector('[data-categories-table-body]'),
      deleteFeedback: document.querySelector('[data-categories-delete-feedback]'),

      suggestionsCount: document.querySelector('[data-suggestions-count]'),
      suggestionsReset: document.querySelector('[data-suggestions-next-reset]'),
      suggestionsList: document.querySelector('[data-suggestions-list]')
    };
  }

  function createState(refs) {
    return {
      metrics: [],
      categories: [],
      suggestionSchedule: {
        next_reset_in: '-',
        next_reset_at: '-'
      },
      suggestions: [],
      editingCategoryId: null,
      createSlugTouched: false,
      createButtonDefaultText: refs.createAddButton ? refs.createAddButton.textContent.trim() : '+ Add Category',
      editButtonDefaultText: refs.editSaveButton ? refs.editSaveButton.textContent.trim() : 'Update Category',
      saveSetupDefaultText: refs.saveSetupButton ? refs.saveSetupButton.textContent.trim() : 'Save Category Setup'
    };
  }

  function normalizeCategory(raw) {
    const id = toPositiveInteger(raw.id);
    const parentId = toPositiveInteger(raw.parent_id);

    return {
      id,
      name: String(raw.name || '').trim(),
      slug: String(raw.slug || '').trim(),
      parent_id: parentId,
      parent_name: raw.parent_name ? String(raw.parent_name) : null,
      description: String(raw.description || '').trim(),
      products: Number.parseInt(String(raw.products || 0), 10) || 0,
      share: Number.parseInt(String(raw.share || 0), 10) || 0,
      status: String(raw.status || 'Draft') === 'Active' ? 'Active' : 'Draft',
      updated_at: String(raw.updated_at || '-')
    };
  }

  function patchStateFromPayload(state, payload) {
    const data = payload && typeof payload === 'object' ? payload : {};

    state.metrics = Array.isArray(data.metrics) ? data.metrics.slice() : [];
    state.categories = Array.isArray(data.categories)
      ? data.categories.map((category) => normalizeCategory(category)).filter((category) => category.id)
      : [];
    state.suggestionSchedule = data.suggestionSchedule && typeof data.suggestionSchedule === 'object'
      ? Object.assign({ next_reset_in: '-', next_reset_at: '-' }, data.suggestionSchedule)
      : { next_reset_in: '-', next_reset_at: '-' };
    state.suggestions = Array.isArray(data.suggestions) ? data.suggestions.slice() : [];
  }

  function setButtonLoading(button, isLoading, loadingText, defaultText) {
    if (!button) return;
    button.disabled = !!isLoading;
    button.textContent = isLoading ? loadingText : defaultText;
  }

  function setAiStatus(refs, message, tone) {
    if (!refs.aiStatus) return;

    const safeTone = tone ? ` is-${tone}` : '';
    refs.aiStatus.className = `categories-ai-status${safeTone}`;
    refs.aiStatus.textContent = String(message || '');
  }

  function showDeleteFeedback(refs, message, type) {
    if (!refs.deleteFeedback) {
      notify(type === 'success' ? 'success' : 'error', message);
      return;
    }

    refs.deleteFeedback.textContent = message;
    refs.deleteFeedback.className = `categories-delete-feedback is-visible ${type === 'success' ? 'is-success' : 'is-error'}`;
  }

  function clearDeleteFeedback(refs) {
    if (!refs.deleteFeedback) return;

    refs.deleteFeedback.textContent = '';
    refs.deleteFeedback.className = 'categories-delete-feedback';
  }

  function renderMetrics(refs, state) {
    if (!refs.metricsGrid) return;

    if (!state.metrics.length) {
      refs.metricsGrid.innerHTML = `
        <article class="settings-stat-card is-warning">
          <span>No metrics</span>
          <strong>--</strong>
          <small>Demo data unavailable</small>
        </article>
      `;
      return;
    }

    refs.metricsGrid.innerHTML = state.metrics
      .map((metric, index) => {
        return `
          <article class="settings-stat-card ${metricToneClass(index)}">
            <span>${escapeHtml(metric.label || '-')}</span>
            <strong>${escapeHtml(metric.value || '--')}</strong>
            <small>${escapeHtml(metric.meta || '-')}</small>
          </article>
        `;
      })
      .join('');
  }

  function renderCategoriesTotal(refs, state) {
    if (!refs.categoriesTotal) return;
    refs.categoriesTotal.textContent = `${state.categories.length} total`;
  }

  function buildParentOptions(categories, selectedId, excludedId) {
    const selected = toPositiveInteger(selectedId);
    const excluded = toPositiveInteger(excludedId);

    const options = ['<option value="">None (Top level)</option>'];

    categories.forEach((category) => {
      if (excluded && category.id === excluded) return;
      const isSelected = selected && category.id === selected;
      options.push(
        `<option value="${category.id}"${isSelected ? ' selected' : ''}>${escapeHtml(category.name)}</option>`
      );
    });

    return options.join('');
  }

  function renderCreateParentOptions(refs, state) {
    if (!refs.createParentInput) return;

    const selectedValue = toPositiveInteger(refs.createParentInput.value);
    refs.createParentInput.innerHTML = buildParentOptions(state.categories, selectedValue, null);
  }

  function renderEditParentOptions(refs, state, categoryId, selectedParentId) {
    if (!refs.editParentInput) return;

    refs.editParentInput.innerHTML = buildParentOptions(state.categories, selectedParentId, categoryId);
  }

  function renderCategoriesTable(refs, state) {
    if (!refs.categoriesTableBody) return;

    if (!state.categories.length) {
      refs.categoriesTableBody.innerHTML = `
        <tr>
          <td colspan="7">No categories found.</td>
        </tr>
      `;
      return;
    }

    refs.categoriesTableBody.innerHTML = state.categories
      .map((category) => {
        const share = clampPercent(category.share);

        return `
          <tr data-category-row data-category-id="${category.id}">
            <td>
              <strong class="settings-cell-strong">${escapeHtml(category.name)}</strong>
              ${category.parent_name ? `<small class="categories-parent-note">Parent: ${escapeHtml(category.parent_name)}</small>` : ''}
            </td>
            <td>${escapeHtml(category.slug)}</td>
            <td>${formatInteger(category.products)}</td>
            <td class="categories-share-cell">
              <span class="categories-share-value">${share}%</span>
              <div class="settings-category-track categories-share-track">
                <span style="width: ${share}%"></span>
              </div>
            </td>
            <td><span class="badge ${categoryStatusBadgeClass(category.status)}">${escapeHtml(category.status)}</span></td>
            <td>${escapeHtml(category.updated_at)}</td>
            <td>
              <div class="products-table-actions">
                <button type="button" class="btn btn-secondary btn-sm" data-category-action="edit" data-category-id="${category.id}">Edit</button>
                <button type="button" class="btn btn-danger btn-sm" data-category-action="delete" data-category-id="${category.id}">Delete</button>
              </div>
            </td>
          </tr>
        `;
      })
      .join('');
  }

  function renderSuggestions(refs, state) {
    if (refs.suggestionsCount) {
      refs.suggestionsCount.textContent = String(state.suggestions.length);
    }

    if (refs.suggestionsReset) {
      refs.suggestionsReset.textContent = `Next reset ${state.suggestionSchedule.next_reset_in || '-'}`;
    }

    if (!refs.suggestionsList) return;

    if (!state.suggestions.length) {
      refs.suggestionsList.innerHTML = `
        <li>
          <strong>No suggestions</strong>
          <p>New AI suggestions will appear on the next reset.</p>
        </li>
      `;
      return;
    }

    refs.suggestionsList.innerHTML = state.suggestions
      .map((suggestion) => {
        return `
          <li>
            <strong>${escapeHtml(suggestion.title || 'Suggestion')}</strong>
            <p>${escapeHtml(suggestion.note || '-')}</p>
          </li>
        `;
      })
      .join('');
  }

  function renderAll(refs, state) {
    renderMetrics(refs, state);
    renderCategoriesTotal(refs, state);
    renderCategoriesTable(refs, state);
    renderCreateParentOptions(refs, state);
    renderSuggestions(refs, state);

    const editingId = toPositiveInteger(state.editingCategoryId);
    if (!editingId) return;

    const editingCategory = findCategoryById(state, editingId);
    if (!editingCategory) {
      closeEditor(refs, state);
      return;
    }

    if (refs.editTitle) refs.editTitle.textContent = `Edit Category: ${editingCategory.name}`;
    if (refs.editProducts) refs.editProducts.textContent = `Products: ${editingCategory.products}`;
    if (refs.editShare) refs.editShare.textContent = `Share: ${editingCategory.share}%`;
    if (refs.editUpdated) refs.editUpdated.textContent = `Updated: ${editingCategory.updated_at}`;
    renderEditParentOptions(refs, state, editingCategory.id, toPositiveInteger(refs.editParentInput?.value));
  }

  function openEditor(refs, state, category) {
    state.editingCategoryId = category.id;

    if (refs.createPanel) {
      refs.createPanel.classList.add('is-hidden');
    }

    if (refs.editPanel) {
      refs.editPanel.classList.add('is-visible');
      refs.editPanel.setAttribute('aria-hidden', 'false');
    }

    if (refs.editTitle) refs.editTitle.textContent = `Edit Category: ${category.name}`;
    if (refs.editNameInput) refs.editNameInput.value = category.name;
    if (refs.editSlugInput) refs.editSlugInput.value = category.slug;
    if (refs.editStatusInput) refs.editStatusInput.value = category.status;
    if (refs.editDescriptionInput) refs.editDescriptionInput.value = category.description;
    if (refs.editProducts) refs.editProducts.textContent = `Products: ${category.products}`;
    if (refs.editShare) refs.editShare.textContent = `Share: ${category.share}%`;
    if (refs.editUpdated) refs.editUpdated.textContent = `Updated: ${category.updated_at}`;

    renderEditParentOptions(refs, state, category.id, category.parent_id);

    refs.editPanel?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function closeEditor(refs, state) {
    state.editingCategoryId = null;

    if (refs.createPanel) {
      refs.createPanel.classList.remove('is-hidden');
    }

    if (refs.editPanel) {
      refs.editPanel.classList.remove('is-visible');
      refs.editPanel.setAttribute('aria-hidden', 'true');
    }
  }

  function resetCreateForm(refs, state) {
    if (refs.createNameInput) refs.createNameInput.value = '';
    if (refs.createSlugInput) refs.createSlugInput.value = '';
    if (refs.createStatusInput) refs.createStatusInput.value = 'Active';
    if (refs.createParentInput) refs.createParentInput.value = '';
    if (refs.createDescriptionInput) refs.createDescriptionInput.value = '';
    state.createSlugTouched = false;

    setAiStatus(refs, 'Click AI Write to auto-generate a demo short description.', '');
  }

  function wireCreateSlugAutomation(refs, state, api) {
    if (!refs.createNameInput || !refs.createSlugInput) return;

    refs.createNameInput.addEventListener('input', () => {
      if (state.createSlugTouched) return;

      refs.createSlugInput.value = api.utils.slugify(refs.createNameInput.value);
    });

    refs.createSlugInput.addEventListener('input', () => {
      const hasValue = refs.createSlugInput.value.trim().length > 0;
      state.createSlugTouched = hasValue;
    });
  }

  async function loadPage(refs, state, api) {
    if (refs.categoriesTableBody) {
      refs.categoriesTableBody.innerHTML = `
        <tr>
          <td colspan="7">Loading categories...</td>
        </tr>
      `;
    }

    const response = await api.categories.bootstrap();
    patchStateFromPayload(state, response.data);
    renderAll(refs, state);
  }

  function readCreatePayload(refs) {
    return {
      name: refs.createNameInput ? refs.createNameInput.value.trim() : '',
      slug: refs.createSlugInput ? refs.createSlugInput.value.trim() : '',
      status: refs.createStatusInput ? refs.createStatusInput.value : 'Draft',
      parent_id: refs.createParentInput ? toPositiveInteger(refs.createParentInput.value) : null,
      description: refs.createDescriptionInput ? refs.createDescriptionInput.value.trim() : ''
    };
  }

  function readEditPayload(refs) {
    return {
      name: refs.editNameInput ? refs.editNameInput.value.trim() : '',
      slug: refs.editSlugInput ? refs.editSlugInput.value.trim() : '',
      status: refs.editStatusInput ? refs.editStatusInput.value : 'Draft',
      parent_id: refs.editParentInput ? toPositiveInteger(refs.editParentInput.value) : null,
      description: refs.editDescriptionInput ? refs.editDescriptionInput.value.trim() : ''
    };
  }

  function findCategoryById(state, categoryId) {
    return state.categories.find((category) => category.id === categoryId) || null;
  }

  async function onCreateCategory(refs, state, api) {
    clearDeleteFeedback(refs);

    const payload = readCreatePayload(refs);
    if (!payload.name) {
      notify('warning', 'Category name is required.');
      refs.createNameInput?.focus();
      return;
    }

    setButtonLoading(refs.createAddButton, true, 'Creating...', state.createButtonDefaultText);

    try {
      const response = await api.categories.createCategory(payload);
      patchStateFromPayload(state, response.data);
      renderAll(refs, state);
      resetCreateForm(refs, state);
      notify('success', response.message || `Category "${payload.name}" created.`);
    } catch (error) {
      const message = errorMessageFrom(error, 'Unable to create category.');
      notify('error', message);
    } finally {
      setButtonLoading(refs.createAddButton, false, 'Creating...', state.createButtonDefaultText);
    }
  }

  async function onSaveEditCategory(refs, state, api) {
    clearDeleteFeedback(refs);

    const categoryId = toPositiveInteger(state.editingCategoryId);
    if (!categoryId) return;

    const payload = readEditPayload(refs);
    if (!payload.name) {
      notify('warning', 'Category name is required.');
      refs.editNameInput?.focus();
      return;
    }

    setButtonLoading(refs.editSaveButton, true, 'Updating...', state.editButtonDefaultText);

    try {
      const response = await api.categories.updateCategory(categoryId, payload);
      patchStateFromPayload(state, response.data);
      renderAll(refs, state);
      closeEditor(refs, state);
      notify('success', response.message || `Category "${payload.name}" updated.`);
    } catch (error) {
      const message = errorMessageFrom(error, 'Unable to update category.');
      notify('error', message);
    } finally {
      setButtonLoading(refs.editSaveButton, false, 'Updating...', state.editButtonDefaultText);
    }
  }

  async function onDeleteCategory(refs, state, api, categoryId, triggerButton) {
    clearDeleteFeedback(refs);

    const category = findCategoryById(state, categoryId);
    if (!category) {
      notify('error', 'Category not found.');
      return;
    }

    if (triggerButton) {
      triggerButton.disabled = true;
    }

    try {
      const response = await api.categories.deleteCategory(category.id);
      patchStateFromPayload(state, response.data);
      renderAll(refs, state);

      if (state.editingCategoryId === category.id) {
        closeEditor(refs, state);
      }

      const successMessage = response.message || `"${category.name}" removed.`;
      showDeleteFeedback(refs, successMessage, 'success');
      notify('success', successMessage);
    } catch (error) {
      const message = errorMessageFrom(error, 'Unable to delete this category.');
      showDeleteFeedback(refs, message, 'error');
      notify('error', message);
    } finally {
      if (triggerButton) {
        triggerButton.disabled = false;
      }
    }
  }

  async function onGenerateDescription(refs, state, api) {
    const categoryName = refs.createNameInput ? refs.createNameInput.value.trim() : '';

    if (!categoryName) {
      const message = 'Please add category name to write this.';
      setAiStatus(refs, message, 'error');
      notify('warning', message);
      refs.createNameInput?.focus();
      return;
    }

    if (refs.aiGenerateButton) {
      refs.aiGenerateButton.disabled = true;
      refs.aiGenerateButton.classList.add('is-processing');
    }

    setButtonLoading(refs.createAddButton, true, 'Processing...', state.createButtonDefaultText);
    setAiStatus(refs, `AI is generating a short description for "${categoryName}"...`, 'processing');

    try {
      const response = await api.categories.generateDescription({
        category_name: categoryName
      });

      if (refs.createDescriptionInput) {
        refs.createDescriptionInput.value = response.data && response.data.description
          ? response.data.description
          : '';
      }

      setAiStatus(refs, response.message || `Description generated for "${categoryName}".`, 'success');
      notify('success', 'AI description generated (demo).');
    } catch (error) {
      const message = errorMessageFrom(error, 'Unable to generate description.');
      setAiStatus(refs, message, 'error');
      notify('error', message);
    } finally {
      if (refs.aiGenerateButton) {
        refs.aiGenerateButton.disabled = false;
        refs.aiGenerateButton.classList.remove('is-processing');
      }

      setButtonLoading(refs.createAddButton, false, 'Processing...', state.createButtonDefaultText);
    }
  }

  async function onSaveSetup(refs, state, api) {
    if (!refs.saveSetupButton) return;

    setButtonLoading(refs.saveSetupButton, true, 'Saving...', state.saveSetupDefaultText);

    try {
      const response = await api.categories.saveSetup({
        staged_changes: state.categories.length
      });
      notify('success', response.message || 'Category setup saved.');
    } catch (error) {
      const message = errorMessageFrom(error, 'Unable to save category setup.');
      notify('error', message);
    } finally {
      setButtonLoading(refs.saveSetupButton, false, 'Saving...', state.saveSetupDefaultText);
    }
  }

  function wireRowActions(refs, state, api) {
    if (!refs.categoriesTableBody) return;

    refs.categoriesTableBody.addEventListener('click', (event) => {
      const actionButton = event.target.closest('[data-category-action]');
      if (!actionButton) return;

      const action = actionButton.getAttribute('data-category-action');
      const categoryId = toPositiveInteger(actionButton.getAttribute('data-category-id'));
      if (!categoryId) return;

      if (action === 'edit') {
        const category = findCategoryById(state, categoryId);
        if (!category) {
          notify('error', 'Category not found.');
          return;
        }

        clearDeleteFeedback(refs);
        openEditor(refs, state, category);
        return;
      }

      if (action === 'delete') {
        onDeleteCategory(refs, state, api, categoryId, actionButton);
      }
    });
  }

  function wireActions(refs, state, api) {
    wireCreateSlugAutomation(refs, state, api);
    wireRowActions(refs, state, api);

    refs.createAddButton?.addEventListener('click', () => {
      onCreateCategory(refs, state, api);
    });

    refs.aiGenerateButton?.addEventListener('click', () => {
      onGenerateDescription(refs, state, api);
    });

    refs.editSaveButton?.addEventListener('click', () => {
      onSaveEditCategory(refs, state, api);
    });

    refs.editCancelButton?.addEventListener('click', () => {
      closeEditor(refs, state);
      notify('info', 'Edit cancelled.');
    });

    refs.saveSetupButton?.addEventListener('click', () => {
      onSaveSetup(refs, state, api);
    });
  }

  async function bootstrap() {
    if (!isCategoriesPage()) return;

    const api = window.AdminAPI;
    if (!api || !api.categories) {
      console.error('AdminAPI categories controller is missing.');
      return;
    }

    const refs = collectRefs();
    if (!refs.categoriesTableBody) return;

    const state = createState(refs);
    wireActions(refs, state, api);

    try {
      await loadPage(refs, state, api);
    } catch (error) {
      const message = errorMessageFrom(error, 'Unable to load categories data.');
      notify('error', message);

      if (refs.categoriesTableBody) {
        refs.categoriesTableBody.innerHTML = `
          <tr>
            <td colspan="7">Failed to load categories.</td>
          </tr>
        `;
      }
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    bootstrap();
  });
})(window, document);
