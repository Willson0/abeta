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

// TODO: сделать функцию, которая будет писать "завтра", "послезавтра", "на этой неделе", "на следующей неделе" и т.д.
// TODO: а также добавить эту функцию во все нужные места
// TODO: ВЫПОЛНЕНО

// TODO: осуществить принцип "ассиметрии", применить разные цветы/размеры/дизайны в блоках "Все", "Ивенты", "Аналитика".

createApp(App)
    .use(router)
    .mount('#app')
