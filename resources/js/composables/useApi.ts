/**
 * API Helper for authenticated requests
 * Uses session-based authentication WITH CSRF tokens for POST/PATCH/DELETE
 */

interface ApiOptions extends RequestInit {
    params?: Record<string, string | number | boolean | undefined | null>;
}

/**
 * Get CSRF token from meta tag or XSRF-TOKEN cookie
 */
function getCsrfToken(): string | null {
    // Try to get from meta tag first
    const metaTag = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement;
    if (metaTag?.content) {
        return metaTag.content;
    }

    // Try to get from cookie
    const cookies = document.cookie.split(';');
    for (const cookie of cookies) {
        const [name, value] = cookie.trim().split('=');
        if (name === 'XSRF-TOKEN') {
            return decodeURIComponent(value);
        }
    }

    return null;
}

/**
 * Ensure CSRF token is available and valid
 */
async function ensureCsrfToken(): Promise<string | null> {
    let csrfToken = getCsrfToken();

    // If no token, try to get one from Laravel
    if (!csrfToken) {
        try {
            await fetch('/sanctum/csrf-cookie', {
                credentials: 'include'
            });
            csrfToken = getCsrfToken();
        } catch (error) {
            console.warn('Failed to get CSRF token:', error);
        }
    }

    return csrfToken;
}

/**
 * Make an authenticated API request using session cookies + CSRF
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

    // Add CSRF token for state-changing requests
    const method = (fetchOptions.method || 'GET').toUpperCase();
    if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
        const csrfToken = await ensureCsrfToken();
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }
    }

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

/**
 * Initialize CSRF token on page load
 */
export async function initializeCsrfToken(): Promise<void> {
    await ensureCsrfToken();
}

export default { api, apiGet, apiPost, apiPut, apiPatch, apiDelete, apiUpload, initializeCsrfToken };
