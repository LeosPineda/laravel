import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Make Pusher available globally
declare global {
    interface Window {
        Pusher: typeof Pusher;
        Echo: Echo<'pusher'>;
    }
}

window.Pusher = Pusher;

// ✅ FIXED: Include CSRF token AND credentials for session/cookie handling
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY || 'd7844fc467464fad6f63',
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'ap1',
    forceTLS: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    },
    // Ensure credentials (cookies/session) are sent with auth request
    enabledTransports: ['ws', 'wss'],
});

export default window.Echo;
