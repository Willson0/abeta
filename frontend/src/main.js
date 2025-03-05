import './assets/main.css'
import { createRouter, createWebHistory } from 'vue-router'

import { createApp } from 'vue'
import App from './App.vue'
import Home from "@/views/home.vue";
import About from "@/views/about.vue";
import WebinarView from "@/views/webinarView.vue";
import AnalyticsView from "@/views/analyticsView.vue";

const routes = [
    { path: '/', component: Home },
    { path: '/webinar/:id', component: WebinarView },
    { path: '/analytics/:id', component: AnalyticsView },
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

createApp(App)
    .use(router)
    .mount('#app')

window.Telegram.WebApp.expand(); // Растягиваем приложение на весь экран
window.Telegram.WebApp.disableVerticalSwipes();
