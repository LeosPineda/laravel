/**
 * API Helper for authenticated requests
 * Uses session-based authentication without CSRF tokens
 */

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
        ...(fetchOptions.headers as Record<string, string> || {}),
    };

    // Don't set Content-Type for FormData (browser sets it with boundary)
    if (!(fetchOptions.body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
    }

    return fetch(url, {
        ...fetchOptions,
        headers,
        credentials: 'include', // Include session cookies
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
