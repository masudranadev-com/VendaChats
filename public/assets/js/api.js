/* Centralized API module for admin frontend. */
(function initApiModule(global) {
  'use strict';

  function toText(value) {
    return String(value ?? '').trim();
  }

  function normalizeBaseUrl(value) {
    return toText(value).replace(/\/+$/, '');
  }

  function readMetaToken() {
    if (typeof document === 'undefined') return '';
    const node = document.querySelector('meta[name="x-refresh-token"]');
    return toText(node?.getAttribute('content'));
  }

  function getToken() {
    // Keep compatibility with older code using jQuery metadata lookup.
    const jqToken = (() => {
      try {
        if (typeof global.$ === 'function') {
          return toText(global.$('meta[name="x-refresh-token"]').attr('content'));
        }
      } catch {
        // Ignore and fallback below.
      }
      return '';
    })();

    const token = jqToken || readMetaToken() || toText(global.localStorage?.getItem?.('refresh_token'));
    if (token) return token;

    try {
      if (typeof global.$ === 'function') {
        const logoutForm = global.$('#autoLogoutForm');
        if (logoutForm.length) {
          logoutForm.submit();
        }
      }
    } catch {
      // Best-effort auto logout behavior.
    }

    return '';
  }

  class ApiError extends Error {
    constructor(message, details = {}) {
      super(message);
      this.name = 'ApiError';
      this.status = details.status ?? 0;
      this.payload = details.payload;
      this.method = details.method || '';
      this.url = details.url || '';
      this.isTimeout = Boolean(details.isTimeout);
    }
  }

  async function request(options = {}) {
    const method = toText(options.method || 'GET').toUpperCase() || 'GET';
    const baseUrl = normalizeBaseUrl(options.baseUrl);
    const path = toText(options.path || '');
    const timeoutMs = Number.isFinite(Number(options.timeoutMs)) ? Math.max(1000, Number(options.timeoutMs)) : 12000;
    const token = toText(options.token || getToken());
    const includeRefreshToken = options.includeRefreshToken !== false;
    const includeNgrokHeader = Boolean(options.includeNgrokHeader);

    if (!baseUrl) {
      throw new ApiError('Missing backend API URL.', {method, url: path});
    }

    if (includeRefreshToken && !token) {
      throw new ApiError('Missing refresh token. Please login again.', {method, url: `${baseUrl}${path}`});
    }

    const headers = {
      'Accept': 'application/json',
      ...(options.headers && typeof options.headers === 'object' ? options.headers : {}),
    };

    if (includeRefreshToken) {
      headers['x-refresh-token'] = token;
    }
    if (includeNgrokHeader) {
      headers['ngrok-skip-browser-warning'] = 'true';
    }

    const hasBody = options.body !== undefined && options.body !== null;
    let body = undefined;
    if (hasBody) {
      const isFormData = typeof FormData !== 'undefined' && options.body instanceof FormData;
      if (isFormData) {
        body = options.body;
      } else {
        headers['Content-Type'] = headers['Content-Type'] || 'application/json';
        body = headers['Content-Type'].includes('application/json') ? JSON.stringify(options.body) : options.body;
      }
    }

    const controller = new AbortController();
    const timeoutId = global.setTimeout(() => controller.abort(), timeoutMs);
    const url = `${baseUrl}${path}`;

    try {
      const response = await fetch(url, {
        method,
        headers,
        body,
        signal: controller.signal,
      });

      const contentType = toText(response.headers.get('content-type')).toLowerCase();
      const payload = contentType.includes('application/json')
        ? await response.json().catch(() => ({}))
        : await response.text().catch(() => '');

      if (!response.ok) {
        const serverMessage = typeof payload === 'string'
          ? payload
          : (payload?.error || payload?.message || '');

        throw new ApiError(
          toText(serverMessage) || `Request failed (${response.status}).`,
          {status: response.status, payload, method, url}
        );
      }

      return payload;
    } catch (error) {
      if (error?.name === 'AbortError') {
        throw new ApiError('Request timed out. Please try again.', {method, url, isTimeout: true});
      }
      if (error instanceof ApiError) {
        throw error;
      }
      throw new ApiError(error?.message || 'Network error. Could not reach the server.', {method, url});
    } finally {
      global.clearTimeout(timeoutId);
    }
  }

  const API = {
    getToken,
    request,
    Admin: {
      FacebookAuth: {
        getStatus({apiBaseUrl, refreshToken, timeoutMs = 12000} = {}) {
          return request({
            baseUrl: normalizeBaseUrl(apiBaseUrl),
            path: '/api/admin/facebook-auth',
            method: 'GET',
            token: refreshToken,
            timeoutMs,
            includeNgrokHeader: true,
          });
        },
        updateSettings({apiBaseUrl, refreshToken, payload, timeoutMs = 12000} = {}) {
          return request({
            baseUrl: normalizeBaseUrl(apiBaseUrl),
            path: '/api/admin/facebook-auth',
            method: 'PUT',
            token: refreshToken,
            body: payload,
            timeoutMs,
            includeNgrokHeader: true,
          });
        },
        disconnect({apiBaseUrl, refreshToken, timeoutMs = 12000} = {}) {
          return request({
            baseUrl: normalizeBaseUrl(apiBaseUrl),
            path: '/api/admin/facebook-auth',
            method: 'DELETE',
            token: refreshToken,
            timeoutMs,
            includeNgrokHeader: true,
          });
        },
      },
      Products: {
        list({apiBaseUrl, refreshToken, page = 1, perPage = 10, timeoutMs = 12000} = {}) {
          const query = `?page=${encodeURIComponent(page)}&per_page=${encodeURIComponent(perPage)}`;
          return request({
            baseUrl: normalizeBaseUrl(apiBaseUrl),
            path: `/api/admin/products${query}`,
            method: 'GET',
            token: refreshToken,
            timeoutMs,
          });
        },
        getById({apiBaseUrl, refreshToken, productId, timeoutMs = 12000} = {}) {
          return request({
            baseUrl: normalizeBaseUrl(apiBaseUrl),
            path: `/api/admin/products/${encodeURIComponent(productId)}`,
            method: 'GET',
            token: refreshToken,
            timeoutMs,
          });
        },
        remove({apiBaseUrl, refreshToken, productId, timeoutMs = 12000} = {}) {
          return request({
            baseUrl: normalizeBaseUrl(apiBaseUrl),
            path: `/api/admin/products/${encodeURIComponent(productId)}`,
            method: 'DELETE',
            token: refreshToken,
            timeoutMs,
          });
        },
        save({apiBaseUrl, refreshToken, payload, productId = null, timeoutMs = 12000} = {}) {
          const hasId = Number.isFinite(Number(productId)) && Number(productId) > 0;
          return request({
            baseUrl: normalizeBaseUrl(apiBaseUrl),
            path: hasId
              ? `/api/admin/products/${encodeURIComponent(productId)}`
              : '/api/admin/products',
            method: hasId ? 'PUT' : 'POST',
            token: refreshToken,
            body: payload,
            timeoutMs,
          });
        },
      },
      Categories: {
        list({apiBaseUrl, refreshToken, page = 1, perPage = 200, timeoutMs = 15000} = {}) {
          const query = `?page=${encodeURIComponent(page)}&per_page=${encodeURIComponent(perPage)}`;
          return request({
            baseUrl: normalizeBaseUrl(apiBaseUrl),
            path: `/api/admin/categories${query}`,
            method: 'GET',
            token: refreshToken,
            timeoutMs,
          });
        },
        getById({apiBaseUrl, refreshToken, categoryId, timeoutMs = 12000} = {}) {
          return request({
            baseUrl: normalizeBaseUrl(apiBaseUrl),
            path: `/api/admin/categories/${encodeURIComponent(categoryId)}`,
            method: 'GET',
            token: refreshToken,
            timeoutMs,
          });
        },
        create({apiBaseUrl, refreshToken, payload, timeoutMs = 12000} = {}) {
          return request({
            baseUrl: normalizeBaseUrl(apiBaseUrl),
            path: '/api/admin/categories',
            method: 'POST',
            token: refreshToken,
            body: payload,
            timeoutMs,
          });
        },
        update({apiBaseUrl, refreshToken, categoryId, payload, timeoutMs = 12000} = {}) {
          return request({
            baseUrl: normalizeBaseUrl(apiBaseUrl),
            path: `/api/admin/categories/${encodeURIComponent(categoryId)}`,
            method: 'PUT',
            token: refreshToken,
            body: payload,
            timeoutMs,
          });
        },
        remove({apiBaseUrl, refreshToken, categoryId, timeoutMs = 12000} = {}) {
          return request({
            baseUrl: normalizeBaseUrl(apiBaseUrl),
            path: `/api/admin/categories/${encodeURIComponent(categoryId)}`,
            method: 'DELETE',
            token: refreshToken,
            timeoutMs,
          });
        },
      },
    },
  };

  global.getToken = getToken;
  global.API = API;
})(window);
