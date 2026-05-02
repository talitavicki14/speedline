import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/pages/admin/bookings/show.js',
                'resources/js/pages/admin/cashier/index.js',
                'resources/js/pages/admin/dashboard/filter.js',
                'resources/js/pages/admin/dashboard/index.js',
                'resources/js/pages/admin/heroes/index.js',
                'resources/js/pages/admin/inventory/index.js',
                'resources/js/pages/admin/reports/index.js',
                'resources/js/pages/admin/reports/finance.js',
                'resources/js/pages/admin/reports/sales.js',
                'resources/js/pages/admin/reports/purchases.js',
                'resources/js/pages/admin/transactions/create.js',
                'resources/js/pages/admin/transactions/form.js',
                'resources/js/pages/admin/transactions/show.js',
                'resources/js/pages/admin/users/form.js',
                'resources/js/pages/admin/profile.js',
                'resources/js/pages/customer/bookings/create.js',
                'resources/js/pages/customer/payments/flow.js',
                'resources/js/pages/customer/vehicles/index.js',
                'resources/js/pages/customer/profile.js',
                'resources/js/pages/auth/verify-email.js',
                'resources/js/pages/landing/index.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
