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

    const token = jqToken
      || readMetaToken()
      || toText(global.localStorage?.getItem?.('access_token'))
      || toText(global.localStorage?.getItem?.('refresh_token'));
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
    const timeoutValue = Number(options.timeoutMs);
    const timeoutMs = Number.isFinite(timeoutValue) ? timeoutValue : 12000;
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
    const timeoutId = timeoutMs > 0
      ? global.setTimeout(() => controller.abort(), timeoutMs)
      : null;
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
      if (timeoutId !== null) {
        global.clearTimeout(timeoutId);
      }
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
        list({
          apiBaseUrl,
          refreshToken,
          page = 1,
          perPage = 10,
          search = '',
          productType = '',
          category = '',
          status = '',
          sortBy = '',
          timeoutMs = 12000
        } = {}) {
          const params = new URLSearchParams();
          params.set('page', String(page));
          params.set('per_page', String(perPage));

          const searchText = toText(search);
          const productTypeText = toText(productType);
          const categoryText = toText(category);
          const statusText = toText(status);
          const sortByText = toText(sortBy);

          if (searchText) params.set('search', searchText);
          if (productTypeText) params.set('product_type', productTypeText);
          if (categoryText) params.set('category', categoryText);
          if (statusText) params.set('status', statusText);
          if (sortByText) params.set('sort_by', sortByText);

          const query = `?${params.toString()}`;
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
        aiContent({apiBaseUrl, refreshToken, payload, timeoutMs = 0} = {}) {
          return request({
            baseUrl: normalizeBaseUrl(apiBaseUrl),
            path: '/api/admin/products/ai-content',
            method: 'POST',
            token: refreshToken,
            body: payload,
            timeoutMs,
          });
        },
        categoriesTree({apiBaseUrl, refreshToken, timeoutMs = 12000} = {}) {
          return request({
            baseUrl: normalizeBaseUrl(apiBaseUrl),
            path: '/api/admin/products/categories',
            method: 'GET',
            token: refreshToken,
            timeoutMs,
          });
        },
      },
      OrderHistory: {
        list({
          apiBaseUrl,
          refreshToken,
          page = 1,
          perPage = 10,
          search = '',
          status = '',
          paymentType = '',
          channel = '',
          sortBy = 'newest_first',
          timeoutMs = 12000
        } = {}) {
          const params = new URLSearchParams();
          params.set('page', String(page));
          params.set('per_page', String(perPage));

          const searchText = toText(search);
          const statusText = toText(status);
          const paymentTypeText = toText(paymentType);
          const channelText = toText(channel);
          const sortByText = toText(sortBy);

          if (searchText) params.set('search', searchText);
          if (statusText && statusText.toLowerCase() !== 'all') params.set('status', statusText);
          if (paymentTypeText && paymentTypeText.toLowerCase() !== 'all') params.set('payment_type', paymentTypeText);
          if (channelText && channelText.toLowerCase() !== 'all') params.set('channel', channelText);
          if (sortByText && sortByText.toLowerCase() !== 'all') params.set('sort_by', sortByText);

          const query = `?${params.toString()}`;
          return request({
            baseUrl: normalizeBaseUrl(apiBaseUrl),
            path: `/api/admin/order-history${query}`,
            method: 'GET',
            token: refreshToken,
            timeoutMs,
          });
        },
        updateStatus({
          apiBaseUrl,
          refreshToken,
          orderId,
          status,
          timeoutMs = 12000
        } = {}) {
          const orderIdText = toText(orderId);
          const statusText = toText(status);

          if (!orderIdText) {
            throw new ApiError('order_id is required');
          }
          if (!statusText) {
            throw new ApiError('status is required');
          }

          return request({
            baseUrl: normalizeBaseUrl(apiBaseUrl),
            path: `/api/admin/order-history/${encodeURIComponent(orderIdText)}/status`,
            method: 'PUT',
            token: refreshToken,
            body: {status: statusText},
            timeoutMs,
          });
        },
      },
      Users: {
        list({
          apiBaseUrl,
          refreshToken,
          page = 1,
          perPage = 10,
          search = '',
          channel = '',
          emotion = '',
          userType = '',
          status = '',
          timeoutMs = 12000
        } = {}) {
          const params = new URLSearchParams();
          params.set('page', String(page));
          params.set('per_page', String(perPage));

          const searchText = toText(search);
          const channelText = toText(channel);
          const emotionText = toText(emotion);
          const userTypeText = toText(userType);
          const statusText = toText(status);

          if (searchText) params.set('search', searchText);
          if (channelText && channelText.toLowerCase() !== 'all') params.set('channel', channelText);
          if (emotionText && emotionText.toLowerCase() !== 'all') params.set('emotion', emotionText);
          if (userTypeText && userTypeText.toLowerCase() !== 'all') params.set('user_type', userTypeText);
          if (statusText && statusText.toLowerCase() !== 'all') params.set('status', statusText);

          const query = `?${params.toString()}`;
          return request({
            baseUrl: normalizeBaseUrl(apiBaseUrl),
            path: `/api/admin/users_info${query}`,
            method: 'GET',
            token: refreshToken,
            timeoutMs,
          });
        },
        ban({apiBaseUrl, refreshToken, clientId, timeoutMs = 12000} = {}) {
          const resolvedClientId = toText(clientId);
          if (!resolvedClientId) {
            throw new ApiError('client_id is required');
          }

          return request({
            baseUrl: normalizeBaseUrl(apiBaseUrl),
            path: `/api/admin/users/${encodeURIComponent(resolvedClientId)}/ban`,
            method: 'PUT',
            token: refreshToken,
            timeoutMs,
          });
        },
        unban({apiBaseUrl, refreshToken, clientId, timeoutMs = 12000} = {}) {
          const resolvedClientId = toText(clientId);
          if (!resolvedClientId) {
            throw new ApiError('client_id is required');
          }

          return request({
            baseUrl: normalizeBaseUrl(apiBaseUrl),
            path: `/api/admin/users/${encodeURIComponent(resolvedClientId)}/unban`,
            method: 'PUT',
            token: refreshToken,
            timeoutMs,
          });
        },
      },
      Categories: {
        list({apiBaseUrl, refreshToken, page = 1, perPage = 200, timeoutMs = 15000} = {}) {
          const query = `?page=${encodeURIComponent(page)}&per_page=${encodeURIComponent(perPage)}`;
          const token = toText(readMetaToken() || getToken());
          return request({
            baseUrl: normalizeBaseUrl(apiBaseUrl),
            path: `/api/admin/categories${query}`,
            method: 'GET',
            token,
            timeoutMs,
          });
        },
        getById({apiBaseUrl, refreshToken, categoryId, timeoutMs = 12000} = {}) {
          const token = toText(readMetaToken() || getToken());
          return request({
            baseUrl: normalizeBaseUrl(apiBaseUrl),
            path: `/api/admin/categories/${encodeURIComponent(categoryId)}`,
            method: 'GET',
            token,
            timeoutMs,
          });
        },
        create({apiBaseUrl, refreshToken, payload, timeoutMs = 12000} = {}) {
          const token = toText(readMetaToken() || getToken());
          return request({
            baseUrl: normalizeBaseUrl(apiBaseUrl),
            path: '/api/admin/categories',
            method: 'POST',
            token,
            body: payload,
            timeoutMs,
          });
        },
        update({apiBaseUrl, refreshToken, categoryId, payload, timeoutMs = 12000} = {}) {
          const token = toText(readMetaToken() || getToken());
          return request({
            baseUrl: normalizeBaseUrl(apiBaseUrl),
            path: `/api/admin/categories/${encodeURIComponent(categoryId)}`,
            method: 'PUT',
            token,
            body: payload,
            timeoutMs,
          });
        },
        remove({apiBaseUrl, refreshToken, categoryId, timeoutMs = 12000} = {}) {
          const token = toText(readMetaToken() || getToken());
          return request({
            baseUrl: normalizeBaseUrl(apiBaseUrl),
            path: `/api/admin/categories/${encodeURIComponent(categoryId)}`,
            method: 'DELETE',
            token,
            timeoutMs,
          });
        },
      },
    },
  };

  global.getToken = getToken;
  global.API = API;
})(window);
