import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, type DefineComponent, h } from 'vue';

import { initializeTheme } from './composables/useTheme';
import AppLayout from './Layouts/AppLayout.vue';

const appName = import.meta.env.VITE_APP_NAME || 'SIPTA IF';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: async (name) => {
        const page = await resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        );
        page.default.layout ??= name.startsWith('Auth/')
            ? undefined
            : AppLayout;
        return page;
    },
    setup({ el, App, props, plugin }) {
        initializeTheme();
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#2563eb', // Blue-600 Tailwind
        showSpinner: true,
    },
});
