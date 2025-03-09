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
import adminOrderView from "@/views/admin/adminServiceView.vue";
import adminLogView from "@/views/admin/adminLogView.vue";
import adminAdminsView from "@/views/admin/adminAdminsView.vue";
import adminAddProductView from "@/views/admin/adminAddProductView.vue";
import adminProductsView from "@/views/admin/adminWebinarsView.vue";
import adminProductIndexView from "@/views/admin/adminProductIndexView.vue";
import adminAnalyticsView from "@/views/admin/adminAnalyticsView.vue";
import adminAddAnalyticView from "@/views/admin/adminAddAnalyticView.vue";
import adminAnalyticIndexView from "@/views/admin/adminAnalyticIndexView.vue";
import adminServiceView from "@/views/admin/adminServiceView.vue";
import adminAddServiceView from "@/views/admin/adminAddServiceView.vue";
import adminServiceIndexView from "@/views/admin/adminServiceIndexView.vue";
import mailingView from "@/views/admin/mailingView.vue";

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
        path: "/admin/services",
        component: adminServiceView,
        meta: { title: 'ABETA | Services', h: 'Services' },
        name: 'servicesAdmin'
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
        path: "/admin/webinars/add",
        component: adminAddProductView,
        meta: { title: 'ABETA | Add webinar', h: 'Add Webinar' },
        name: 'addProductAdmin'
    },
    {
        path: "/admin/webinars",
        component: adminProductsView,
        meta: { title: 'ABETA | Webinars', h: 'Webinars' },
        name: 'productsAdmin'
    },
    {
        path: "/admin/webinars/:id",
        component: adminProductIndexView,
        meta: { title: 'ABETA | Webinar', h: 'Webinar' },
        name: 'indexProductAdmin'
    },
    {
        path: "/admin/analytics/add",
        component: adminAddAnalyticView,
        meta: { title: 'ABETA | Add analytic', h: 'Add analytic' },
        name: 'addAnalyticAdmin'
    },
    {
        path: "/admin/analytics",
        component: adminAnalyticsView,
        meta: { title: 'ABETA | Analytics', h: 'Analytics' },
        name: 'analyticsAdmin'
    },
    {
        path: "/admin/analytics/:id",
        component: adminAnalyticIndexView,
        meta: { title: 'ABETA | analytic', h: 'analytic' },
        name: 'indexAnalyticAdmin'
    },
    {
        path: "/admin/services/add",
        component: adminAddServiceView,
        meta: { title: 'ABETA | Add service', h: 'Add service' },
        name: 'addServiceAdmin'
    },
    {
        path: "/admin/services/:id",
        component: adminServiceIndexView,
        meta: { title: 'ABETA | Service', h: 'Service' },
        name: 'indexServiceAdmin'
    },
    {
        path: "/admin/mailing",
        component: mailingView,
        meta: { title: 'ABETA | Mailing', h: 'Mailing' },
        name: 'mailingAdmin'
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

                window.Telegram.WebApp.expand();
                window.Telegram.WebApp.disableVerticalSwipes();
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

createApp(App)
    .use(router)
    .mount('#app')
