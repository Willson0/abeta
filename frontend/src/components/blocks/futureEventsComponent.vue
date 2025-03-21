<script>
import {formatDate, generateSoftRandomColor} from "../../utils.js";

export default {
    name: "futureEventsComponent",
    methods: {
        formatDate, generateSoftRandomColor,
        redirect (id) {
            window.location.href = "/webinar/" + id + "?s=" + this.$route.query.s;
        }
    },
    data () {
        return {
            colors: [],
        }
    },
    props: {
        webinars: {
            type: Array,
            default: () => [],
        },
    },
    watch: {
        webinars() {
            this.colors = [];
            this.webinars.forEach(() => this.colors.push(this.generateSoftRandomColor()));
        }
    },
    mounted () {
        this.webinars.forEach(() => this.colors.push(this.generateSoftRandomColor()));
    },
}
</script>

<template>
    <div class="feed_others_future_events" v-if="webinars?.length">
        <div class="feed_others_events_title">Другие<br>предстоящие ивенты</div>
        <div class="feed_others_events_slider">
            <div v-for="(web, key) in webinars" class="feed_others_events_slider_webinar"
            :style="'background-color: ' + colors[key]" @click="redirect(web.id)">
                <div class="feed_others_future_events_slider_webinar_type" :style="'color:' + colors[key]">Вебинар</div>
                <div class="feed_others_events_slider_webinar_title">{{web.title}}</div>
                <hr>
                <div class="feed_others_events_slider_webinar_date">{{ formatDate(web.date) }}</div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>