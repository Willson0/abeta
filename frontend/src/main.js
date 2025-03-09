import './assets/main.css'
import { createRouter, createWebHistory } from 'vue-router'

import { createApp } from 'vue'
import App from './App.vue'
import Home from "@/views/home.vue";
import About from "@/views/about.vue";
import WebinarView from "@/views/webinarView.vue";
import AnalyticsView from "@/views/analyticsView.vue";
import AdminLoginView from "@/views/admin/adminLoginView.vue";
import AdminView from "@/views/admin/adminView.vue";
import adminUsersView from "@/views/admin/adminUsersView.vue";
import adminUserIndexView from "@/views/admin/adminUserIndexView.vue";
import adminOrderView from "@/views/admin/adminOrderView.vue";
import adminLogView from "@/views/admin/adminLogView.vue";
import adminAdminsView from "@/views/admin/adminAdminsView.vue";
import adminAddProductView from "@/views/admin/adminAddProductView.vue";
import adminProductsView from "@/views/admin/adminProductsView.vue";
import adminProductIndexView from "@/views/admin/adminProductIndexView.vue";

const routes = [
    { path: '/', component: Home },
    { path: '/webinar/:id', component: WebinarView },
    { path: '/analytics/:id', component: AnalyticsView },
    {
        path: "/admin/login",
        component: AdminLoginView,
        meta: { title: 'ABETA | Admin\'s Authorization' },
        name: 'adminlogin'
    },
    {
        path: "/admin",
        component: AdminView,
        meta: { title: 'ABETA | Admin', h: 'Dashboard' },
        name: 'admin'
    },
    {
        path: "/admin/users",
        component: adminUsersView,
        meta: { title: 'ABETA | Users', h: 'Users' },
        name: 'usersAdmin'
    },
    {
        path: "/admin/users/:id",
        component: adminUserIndexView,
        meta: { title: 'ABETA | User', h: 'User' },
        name: 'userIndexAdmin'
    },
    {
        path: "/admin/orders",
        component: adminOrderView,
        meta: { title: 'ABETA | Orders', h: 'Orders' },
        name: 'ordersAdmin'
    },
    {
        path: "/admin/logs",
        component: adminLogView,
        meta: { title: 'ABETA | Logs', h: 'Logs' },
        name: 'logsAdmin'
    },
    {
        path: "/admin/admins",
        component: adminAdminsView,
        meta: { title: 'ABETA | Admins', h: 'Admins' },
        name: 'adminsAdmin'
    },
    {
        path: "/admin/products/add",
        component: adminAddProductView,
        meta: { title: 'ABETA | Add product', h: 'Add product' },
        name: 'addProductAdmin'
    },
    {
        path: "/admin/products",
        component: adminProductsView,
        meta: { title: 'ABETA | Products', h: 'Products' },
        name: 'productsAdmin'
    },
    {
        path: "/admin/products/:id",
        component: adminProductIndexView,
        meta: { title: 'ABETA | Product', h: 'Product' },
        name: 'indexProductAdmin'
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

function loadTelegramScript() {
    return new Promise((resolve, reject) => {
        if (window.Telegram) {
            // Скрипт уже загружен
            resolve();
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://telegram.org/js/telegram-web-app.js';
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Не удалось загрузить Telegram Web App'));
        document.head.appendChild(script);
    });
}

// Логика загрузки скрипта в зависимости от маршрута
router.beforeEach((to, from, next) => {
    // Список маршрутов, где НЕ нужен Telegram Web App
    const excludeRoutes = ['/admin'];

    if (!excludeRoutes.some(route => to.path.startsWith(route))) {
        loadTelegramScript()
            .then(() => {
                console.log('Telegram Web App загружен');
                next();
            })
            .catch(err => {
                console.error(err);
                next();
            });
    } else {
        // Если скрипт не нужен, просто продолжаем навигацию
        next();
    }
});

window.Telegram.WebApp.expand();
window.Telegram.WebApp.disableVerticalSwipes();

createApp(App)
    .use(router)
    .mount('#app')
