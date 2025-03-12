<script>
import config from "@/components/config.json"
import {formatDate, generateSoftRandomColor} from "../../utils.js";

export default {
    data () {
        return {
            config: config,
        }
    },
    name: "analyticComponent",
    methods: {generateSoftRandomColor},
    props: {
        analytic: {
            type: Object,
            required: true,
        },
        size: {
            type: Number,
            default: 0, // 0 - обычный блок аналитики, 1 - уменьшенная версия блока аналитики
        }
    }
}
</script>

<template>
    <div v-if="size === 0" @click="this.$router.push('/analytics/' + analytic.id + '?s=' + $route.query.s)" class="feed_webinar feed_web">
        <div class="feed_webinar_image">
            <img :src="config.storage + analytic.image" alt="">
        </div>
        <div class="feed_webinar_date">Аналитика &middot; {{ analytic.time }} минут чтения</div>
        <div class="feed_webinar_title">{{analytic.title}}</div>
        <div class="feed_webinar_description">{{analytic.description}}</div>
    </div>
    <div v-if="size === 1" @click="this.$router.push('/analytics/' + analytic.id + '?s=' + $route.query.s)"
         class="feed_webinar_alt feed_web" :style="'background-color:' + generateSoftRandomColor()">
        <div class="feed_webinar_alt_date">Аналитика &middot; {{analytic.time}} минут чтения</div>
        <hr>
        <div class="feed_webinar_alt_title">{{ analytic.title }}</div>
        <div class="feed_webinar_alt_description">{{ analytic.description }}</div>
    </div>
    <div v-if="size === 2" @click="this.$router.push('/analytics/' + analytic.id + '?s=' + $route.query.s)"
         class="feed_webinar_alt feed_web" style="background-color:white; color:black;">
        <div class="feed_webinar_alt_date" style="color:#FF734C">Аналитика &middot; {{analytic.time}} минут чтения</div>
        <hr style="background-color:#FF734C">
        <div class="feed_webinar_alt_title">{{ analytic.title }}</div>
        <div class="feed_webinar_alt_description" style="color:black;">{{ analytic.description }}</div>
    </div>
</template>

<style scoped>

</style>