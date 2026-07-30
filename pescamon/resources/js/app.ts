import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import { createApp, createSSRApp } from 'vue';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import PescadexLayout from '@/layouts/PescadexLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Settings form pages keep the original shadcn settings shell.
const legacySettings = ['settings/Appearance'];

// @ts-expect-error
createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
            case name === 'auth/Login':
                // Branded, unwrapped PescaDex screens that build their own
                // full-page shell instead of the shared auth card.
                return null;
            case name == 'auth/Register':
                // Again, unwrapped PescaDex screen, not using the shared
                // auth card.
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case legacySettings.includes(name):
                return [AppLayout, SettingsLayout];
            default:
                // All PescaDex app screens render inside the studio shell.
                return PescadexLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
    setup({ el, App, props, plugin }) {
        // Use createSSRApp on the server (el is null there) and createApp in the browser.
        // eslint-disable-next-line @typescript-eslint/ban-ts-comment
        // @ts-expect-error
        const app = import.meta.env.SSR
            ? createSSRApp(App, props)
            : createApp(App, props);

        app.use(plugin).use(createPinia());

        if (el) {
            app.mount(el);
        }

        return app;
    },
});

// Browser-only initialization (skipped during SSR where `window` is undefined)...
if (typeof window !== 'undefined') {
    // This will set light / dark mode on page load...
    initializeTheme();

    // This will listen for flash toast data from the server...
    initializeFlashToast();
}
