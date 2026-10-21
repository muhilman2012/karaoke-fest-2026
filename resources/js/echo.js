import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const wsHost = (import.meta.env.VITE_REVERB_HOST && import.meta.env.VITE_REVERB_HOST !== 'localhost' && import.meta.env.VITE_REVERB_HOST !== '127.0.0.1')
    ? import.meta.env.VITE_REVERB_HOST
    : window.location.hostname;

const isHttps = (import.meta.env.VITE_REVERB_SCHEME ?? '') === 'https' || window.location.protocol === 'https:';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: wsHost,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? (isHttps ? 443 : 8080),
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: isHttps,
    enabledTransports: ['ws', 'wss'],
});

