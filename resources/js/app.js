import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

// Create a fallback route function
const fallbackRoute = (name, params = {}, absolute = false) => {
    const routes = {
        'dashboard': '/dashboard',
        'login': '/login',
        'register': '/register',
        'password.request': '/forgot-password',
        'password.reset': '/reset-password',
        'verification.notice': '/verify-email',
        'password.confirm': '/confirm-password',
        'verification.verify': '/verify-email/:id/:hash',
        'verification.send': '/email/verification-notification',
        'logout': '/logout',
        'profile.edit': '/profile',
        'profile.password.update': '/profile/password',
        'appointments.index': '/appointments',
        'appointments.create': '/appointments/create',
        'medical-records.index': '/medical-records',
        'patients.index': '/patients',
        'doctors.index': '/doctors',
        'reports.index': '/reports',
        'settings': '/settings',
        'billing.index': '/billing',
    };
    
    let route = routes[name] || '/dashboard';
    
    // Replace parameters
    Object.keys(params).forEach(key => {
        if (route.includes(`:${key}`)) {
            route = route.replace(`:${key}`, params[key]);
        }
    });
    
    // Remove any remaining parameters
    route = route.replace(/\/:[^/]+/g, '');
    
    if (absolute && window.location.origin) {
        route = window.location.origin + route;
    }
    
    return route;
};

// Make route available globally
if (typeof window !== 'undefined') {
    window.route = fallbackRoute;
}

const appName = import.meta.env.VITE_APP_NAME || 'MediCare';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});