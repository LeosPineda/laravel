/**
 * API Helper for authenticated requests
 * Uses cookie-based auth (Sanctum sessions) instead of Bearer tokens
 */

// Get CSRF token from meta tag
function getCsrfToken(): string {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    return token || '';
}

// Get XSRF token from cookies
function getXsrfToken(): string {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    if (match) {
        return decodeURIComponent(match[1]);
    }
    return '';
}

interface ApiOptions extends RequestInit {
    params?: Record<string, string | number | boolean | undefined | null>;
}

/**
 * Make an authenticated API request using session cookies
 */
export async function api(endpoint: string, options: ApiOptions = {}): Promise<Response> {
    const { params, ...fetchOptions } = options;

    // Build URL with query params
    let url = endpoint.startsWith('/') ? endpoint : `/${endpoint}`;
    if (params) {
        const searchParams = new URLSearchParams();
        Object.entries(params).forEach(([key, value]) => {
            if (value !== undefined && value !== null) {
                searchParams.append(key, String(value));
            }
        });
        const queryString = searchParams.toString();
        if (queryString) {
            url += `?${queryString}`;
        }
    }

    // Default headers for session-based auth
    const headers: Record<string, string> = {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': getCsrfToken(),
        ...(fetchOptions.headers as Record<string, string> || {}),
    };

    // Add XSRF token for non-GET requests
    if (options.method && options.method !== 'GET') {
        const xsrfToken = getXsrfToken();
        if (xsrfToken) {
            headers['X-XSRF-TOKEN'] = xsrfToken;
        }
    }

    // Don't set Content-Type for FormData (browser sets it with boundary)
    if (!(fetchOptions.body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
    }

    return fetch(url, {
        ...fetchOptions,
        headers,
        credentials: 'same-origin', // Include cookies
    });
}

/**
 * Convenience methods
 */
export const apiGet = (endpoint: string, params?: Record<string, string | number | boolean | undefined | null>) =>
    api(endpoint, { method: 'GET', params });

export const apiPost = (endpoint: string, body?: unknown) =>
    api(endpoint, {
        method: 'POST',
        body: body instanceof FormData ? body : JSON.stringify(body)
    });

export const apiPut = (endpoint: string, body?: unknown) =>
    api(endpoint, {
        method: 'PUT',
        body: body instanceof FormData ? body : JSON.stringify(body)
    });

export const apiPatch = (endpoint: string, body?: unknown) =>
    api(endpoint, {
        method: 'PATCH',
        body: body instanceof FormData ? body : JSON.stringify(body)
    });

export const apiDelete = (endpoint: string) =>
    api(endpoint, { method: 'DELETE' });

/**
 * Upload file with FormData
 */
export const apiUpload = (endpoint: string, formData: FormData) =>
    api(endpoint, {
        method: 'POST',
        body: formData,
    });

export default { api, apiGet, apiPost, apiPut, apiPatch, apiDelete, apiUpload };
