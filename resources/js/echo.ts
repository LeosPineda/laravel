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

// ✅ RESTORED: Include CSRF token in broadcasting auth headers (backend excludes API routes from CSRF)
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY || 'd7844fc467464fad6f63',
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'ap1',
    forceTLS: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
    },
});

export default window.Echo;
