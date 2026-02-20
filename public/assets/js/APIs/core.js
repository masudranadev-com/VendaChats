(function (window) {
  'use strict';

  const DEFAULT_CONFIG = {
    mode: 'demo',
    demo: {
      categoriesJsonUrl: '/assets/js/APIs/json/categories.json',
      latencyMs: 420
    }
  };

  class ApiError extends Error {
    constructor(message, status, payload) {
      super(message);
      this.name = 'ApiError';
      this.status = status;
      this.payload = payload || null;
    }
  }

  function isObject(value) {
    return value !== null && typeof value === 'object' && !Array.isArray(value);
  }

  function deepMerge(base, override) {
    const output = Array.isArray(base) ? base.slice() : Object.assign({}, base);

    if (!isObject(override) && !Array.isArray(override)) {
      return output;
    }

    Object.keys(override).forEach((key) => {
      const baseValue = output[key];
      const overrideValue = override[key];

      if (Array.isArray(baseValue) && Array.isArray(overrideValue)) {
        output[key] = overrideValue.slice();
        return;
      }

      if (isObject(baseValue) && isObject(overrideValue)) {
        output[key] = deepMerge(baseValue, overrideValue);
        return;
      }

      output[key] = overrideValue;
    });

    return output;
  }

  function deepClone(value) {
    return JSON.parse(JSON.stringify(value));
  }

  function sleep(ms) {
    return new Promise((resolve) => {
      window.setTimeout(resolve, ms);
    });
  }

  function nowIso() {
    return new Date().toISOString();
  }

  function normalizeEnvelope(payload, fallbackMessage) {
    if (!isObject(payload)) {
      return {
        success: true,
        message: fallbackMessage,
        data: payload,
        errors: null,
        meta: {}
      };
    }

    return {
      success: typeof payload.success === 'boolean' ? payload.success : true,
      message: typeof payload.message === 'string' ? payload.message : (fallbackMessage || ''),
      data: Object.prototype.hasOwnProperty.call(payload, 'data') ? payload.data : payload,
      errors: Object.prototype.hasOwnProperty.call(payload, 'errors') ? payload.errors : null,
      meta: isObject(payload.meta) ? payload.meta : {}
    };
  }

  function slugify(input) {
    return String(input || '')
      .trim()
      .toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
      .replace(/^-|-$/g, '');
  }

  function toPositiveInteger(value) {
    const numeric = Number.parseInt(String(value), 10);
    return Number.isFinite(numeric) && numeric > 0 ? numeric : null;
  }

  function createDemoCategoriesController(config) {
    const store = {
      loaded: false,
      metrics: [],
      categories: [],
      suggestions: [],
      suggestionSchedule: {
        next_reset_in: '-',
        next_reset_at: '-'
      },
      nextCategoryId: 1000
    };

    function responseMeta() {
      return {
        mode: 'demo',
        generated_at: nowIso()
      };
    }

    function buildSuccess(data, message) {
      return {
        success: true,
        message: message || 'Success.',
        data,
        errors: null,
        meta: responseMeta()
      };
    }

    function throwError(status, message, errors) {
      const payload = {
        success: false,
        message,
        data: null,
        errors: errors || null,
        meta: responseMeta()
      };
      throw new ApiError(message, status, payload);
    }

    function normalizeCategory(raw) {
      const id = toPositiveInteger(raw.id) || store.nextCategoryId++;
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
        updated_at: String(raw.updated_at || 'Just now')
      };
    }

    function refreshParentNames() {
      const namesById = new Map();
      store.categories.forEach((category) => {
        namesById.set(category.id, category.name);
      });

      store.categories.forEach((category) => {
        category.parent_name = category.parent_id ? (namesById.get(category.parent_id) || null) : null;
      });
    }

    function syncMetrics() {
      if (!store.metrics.length) return;

      const totalCategoryMetric = store.metrics.find((metric) => {
        return String(metric.label || '').trim().toLowerCase() === 'total categories';
      });

      if (totalCategoryMetric) {
        totalCategoryMetric.value = String(store.categories.length);
      }

      const topCategoryMetric = store.metrics.find((metric) => {
        return String(metric.label || '').trim().toLowerCase() === 'top category';
      });

      if (topCategoryMetric) {
        const topCategory = store.categories.reduce((winner, category) => {
          if (!winner) return category;
          return category.share > winner.share ? category : winner;
        }, null);

        topCategoryMetric.value = topCategory ? topCategory.name : '-';
      }
    }

    function snapshot() {
      refreshParentNames();
      syncMetrics();
      return {
        metrics: deepClone(store.metrics),
        categories: deepClone(store.categories),
        suggestionSchedule: deepClone(store.suggestionSchedule),
        suggestions: deepClone(store.suggestions)
      };
    }

    function canCreateParentReference(parentId) {
      if (!parentId) return true;
      return store.categories.some((category) => category.id === parentId);
    }

    function willCreateCycle(targetCategoryId, nextParentId) {
      if (!nextParentId) return false;

      let cursor = nextParentId;
      while (cursor) {
        if (cursor === targetCategoryId) {
          return true;
        }

        const parent = store.categories.find((category) => category.id === cursor);
        cursor = parent ? parent.parent_id : null;
      }

      return false;
    }

    function findCategoryById(categoryId) {
      return store.categories.find((category) => category.id === categoryId) || null;
    }

    async function ensureLoaded() {
      if (store.loaded) return;

      const response = await window.fetch(config.demo.categoriesJsonUrl, {
        headers: {
          Accept: 'application/json'
        }
      });

      if (!response.ok) {
        throwError(response.status, 'Unable to load demo category JSON.');
      }

      const payload = await response.json();
      const envelope = normalizeEnvelope(payload, 'Demo category payload loaded.');
      const data = isObject(envelope.data) ? envelope.data : {};

      store.metrics = Array.isArray(data.metrics) ? deepClone(data.metrics) : [];
      store.categories = Array.isArray(data.categories)
        ? data.categories.map((category) => normalizeCategory(category))
        : [];
      store.suggestions = Array.isArray(data.suggestions) ? deepClone(data.suggestions) : [];
      store.suggestionSchedule = isObject(data.suggestionSchedule)
        ? deepClone(data.suggestionSchedule)
        : { next_reset_in: '-', next_reset_at: '-' };

      const largestExistingId = store.categories.reduce((max, category) => {
        return category.id > max ? category.id : max;
      }, 1000);

      store.nextCategoryId = largestExistingId + 1;
      refreshParentNames();
      store.loaded = true;
    }

    async function withLatency(resolver) {
      const delayMs = Number.parseInt(String(config.demo.latencyMs || 420), 10) || 420;
      await sleep(Math.max(120, delayMs));
      return resolver();
    }

    async function bootstrap() {
      await ensureLoaded();
      return withLatency(() => buildSuccess(snapshot(), 'Category page data loaded.'));
    }

    async function createCategory(payload) {
      await ensureLoaded();
      const body = isObject(payload) ? payload : {};

      return withLatency(() => {
        const name = String(body.name || '').trim();
        const slugInput = String(body.slug || '').trim();
        const parentId = toPositiveInteger(body.parent_id);
        const status = String(body.status || 'Draft') === 'Active' ? 'Active' : 'Draft';
        const description = String(body.description || '').trim();

        if (!name) {
          throwError(422, 'Validation failed.', {
            name: ['Category name is required.']
          });
        }

        if (parentId && !canCreateParentReference(parentId)) {
          throwError(422, 'Validation failed.', {
            parent_id: ['Selected parent category does not exist.']
          });
        }

        const slug = slugInput || slugify(name);

        if (!slug) {
          throwError(422, 'Validation failed.', {
            slug: ['Slug could not be generated from this category name.']
          });
        }

        const slugExists = store.categories.some((category) => category.slug.toLowerCase() === slug.toLowerCase());
        if (slugExists) {
          throwError(422, 'Validation failed.', {
            slug: ['Slug already exists. Please choose a different slug.']
          });
        }

        const category = {
          id: store.nextCategoryId++,
          name,
          slug,
          parent_id: parentId,
          parent_name: null,
          description,
          products: 0,
          share: 0,
          status,
          updated_at: 'Just now'
        };

        store.categories.unshift(category);
        refreshParentNames();

        return buildSuccess(snapshot(), `Category "${name}" created.`);
      });
    }

    async function updateCategory(categoryId, payload) {
      await ensureLoaded();
      const id = toPositiveInteger(categoryId);

      return withLatency(() => {
        if (!id) {
          throwError(404, 'Category not found.');
        }

        const current = findCategoryById(id);
        if (!current) {
          throwError(404, 'Category not found.');
        }

        const body = isObject(payload) ? payload : {};
        const name = String(body.name || '').trim();
        const slugInput = String(body.slug || '').trim();
        const parentId = toPositiveInteger(body.parent_id);
        const status = String(body.status || 'Draft') === 'Active' ? 'Active' : 'Draft';
        const description = String(body.description || '').trim();

        if (!name) {
          throwError(422, 'Validation failed.', {
            name: ['Category name is required.']
          });
        }

        if (parentId && !canCreateParentReference(parentId)) {
          throwError(422, 'Validation failed.', {
            parent_id: ['Selected parent category does not exist.']
          });
        }

        if (parentId === id || willCreateCycle(id, parentId)) {
          throwError(422, 'Validation failed.', {
            parent_id: ['Parent category would create a loop.']
          });
        }

        const slug = slugInput || slugify(name);
        if (!slug) {
          throwError(422, 'Validation failed.', {
            slug: ['Slug could not be generated from this category name.']
          });
        }

        const slugExists = store.categories.some((category) => {
          return category.id !== id && category.slug.toLowerCase() === slug.toLowerCase();
        });

        if (slugExists) {
          throwError(422, 'Validation failed.', {
            slug: ['Slug already exists. Please choose a different slug.']
          });
        }

        current.name = name;
        current.slug = slug;
        current.parent_id = parentId;
        current.description = description;
        current.status = status;
        current.updated_at = 'Just now';

        refreshParentNames();

        return buildSuccess(snapshot(), `Category "${name}" updated.`);
      });
    }

    async function deleteCategory(categoryId) {
      await ensureLoaded();
      const id = toPositiveInteger(categoryId);

      return withLatency(() => {
        if (!id) {
          throwError(404, 'Category not found.');
        }

        const category = findCategoryById(id);
        if (!category) {
          throwError(404, 'Category not found.');
        }

        const childCount = store.categories.filter((item) => item.parent_id === id).length;
        if (childCount > 0) {
          throwError(
            403,
            `Cannot delete "${category.name}". It has ${childCount} child categories. Reassign child categories first.`
          );
        }

        if (category.products > 0) {
          throwError(
            403,
            `Cannot delete "${category.name}". It has ${category.products} products. Move products to another category first.`
          );
        }

        store.categories = store.categories.filter((item) => item.id !== id);
        refreshParentNames();

        return buildSuccess(snapshot(), `"${category.name}" removed.`);
      });
    }

    async function generateDescription(payload) {
      await ensureLoaded();
      const body = isObject(payload) ? payload : {};

      return withLatency(() => {
        const categoryName = String(body.category_name || '').trim();

        if (!categoryName) {
          throwError(422, 'Validation failed.', {
            category_name: ['Please add category name to write this.']
          });
        }

        const description = `${categoryName} includes curated, high-demand items with clear quality standards, reliable pricing, and quick fulfillment for repeat buyers.`;

        return buildSuccess(
          {
            category_name: categoryName,
            description
          },
          `Description generated for "${categoryName}".`
        );
      });
    }

    async function saveSetup(payload) {
      await ensureLoaded();
      const body = isObject(payload) ? payload : {};

      return withLatency(() => {
        return buildSuccess(
          {
            staged_changes: Number.parseInt(String(body.staged_changes || 0), 10) || 0,
            saved_at: 'Just now'
          },
          'Category setup saved (demo).'
        );
      });
    }

    return {
      bootstrap,
      createCategory,
      updateCategory,
      deleteCategory,
      generateDescription,
      saveSetup
    };
  }

  const runtimeConfig = deepMerge(DEFAULT_CONFIG, window.__APP_API_CONFIG || {});
  const categoriesController = createDemoCategoriesController(runtimeConfig);

  const api = {
    ApiError,
    config: runtimeConfig,
    getConfig: function () {
      return deepClone(runtimeConfig);
    },
    setMode: function () {
      runtimeConfig.mode = 'demo';
      return runtimeConfig.mode;
    },
    controllers: {
      categories: categoriesController
    },
    categories: categoriesController,
    utils: {
      slugify
    }
  };

  window.AdminAPI = api;
  window.AppAPIs = api;
})(window);
