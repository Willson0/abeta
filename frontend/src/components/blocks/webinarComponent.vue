<script>
import config from "@/components/config.json"
import {formatDate, generateSoftRandomColor, getRelativeDate} from "../../utils.js";
export default {
    name: "webinarComponent",
    methods: {
        generateSoftRandomColor,
        getRelativeDate, formatDate,
    },
    data () {
        return {
            config: config,
        }
    },
    props: {
        webinar: {
            type: Object,
            required: true,
        },
        size: {
            type: Number,
            default: 0, // 0 - стандартный размер; 1 - уменьшенный вариант
        }
    },
}
</script>

<template>
    <div @click="$router.push('/webinar/' + webinar.id + '?s=' + $route.query.s)" class="feed_webinar feed_web" v-if="size === 0">
        <div class="feed_webinar_image">
            <div class="feed_webinar_image_tags">
                <div class="feed_webinar_image_date" v-if="getRelativeDate(webinar.date)">{{getRelativeDate(webinar.date)}}</div>
            </div>
            <img :src="config.storage + webinar.image" alt="">
        </div>
        <div class="feed_webinar_date">Вебинар &middot; {{new Date() > new Date(webinar.date) ? 'В записи' : formatDate(webinar.date)}}</div>
        <div class="feed_webinar_title">{{webinar.title}}</div>
        <div class="feed_webinar_description" v-html="webinar.description"></div>
    </div>
    <div @click="$router.push('/webinar/' + webinar.id + '?s=' + $route.query.s)" :style="'background-color:' + generateSoftRandomColor()"
         v-if="size === 1" class="feed_others_events_slider_webinar feed_webinar feed_web">
        <div class="feed_others_future_events_slider_webinar_type" v-if="getRelativeDate(webinar.date)">{{ getRelativeDate(webinar.date) }}</div>
        <div class="feed_others_events_slider_webinar_title">{{webinar.title}}</div>
        <div class="feed_others_events_slider_webinar_description" v-html="webinar.description"></div>
        <hr>
        <div class="feed_others_events_slider_webinar_date">Вебинар &middot; {{ formatDate(webinar.date) }}</div>
    </div>
    <div v-if="size === 2" @click="this.$router.push('/webinar/' + webinar.id + '?s=' + $route.query.s)" class="feed_webinar_alt feed_web"
    :style="'background-color:' + generateSoftRandomColor()">
        <div class="feed_webinar_alt_date" style="color:white">Вебинар &middot; {{(new Date()).setHours(new Date().getHours() + 3) > new Date(webinar.date) ? 'В записи' : formatDate(webinar.date)}}</div>
        <hr style="background-color: white">
        <div class="feed_webinar_alt_title">{{ webinar.title }}</div>
        <div class="feed_webinar_alt_description" v-html="webinar.description"></div>
    </div>
    <div v-if="size === 3" @click="this.$router.push('/webinar/' + webinar.id + '?s=' + $route.query.s)" class="feed_webinar_alt feed_web"
    style="background-color:white; color:black;">
        <div class="feed_webinar_alt_date" style="color:#FF734C">Вебинар &middot; {{(new Date()).setHours(new Date().getHours() + 3) > new Date(webinar.date) ? 'В записи' : formatDate(webinar.date)}}</div>
        <hr style="background-color:#FF734C">
        <div class="feed_webinar_alt_title">{{ webinar.title }}</div>
        <div style="color:black;" class="feed_webinar_alt_description" v-html="webinar.description"></div>
    </div>
</template>

<style scoped>

</style>