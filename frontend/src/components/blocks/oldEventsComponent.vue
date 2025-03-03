<script>
import {generateSoftRandomColor} from "@/utils.js";

export default {
    name: "oldEventsComponent",
    props: {
        webinars: {
            type: Array,
            required: true,
        }
    },
    methods: {
        generateSoftRandomColor,
        setColors () {
            document.querySelectorAll(".feed_others_events .feed_others_events_slider_webinar").forEach((el, index) => {
                const color = this.generateSoftRandomColor();
                if (index % 2 === 0) {
                    el.style.backgroundColor = color;
                    el.querySelector(".feed_others_future_events_slider_webinar_type").style.color = color;
                } else {
                    el.style.color = "black";
                    el.querySelector(".feed_others_future_events_slider_webinar_type").style.backgroundColor = color;
                    el.querySelector(".feed_others_future_events_slider_webinar_type").style.color = "white";
                }
            });
        }
    },
    async mounted () {
        this.setColors();
    },
    watch: {
        webinars () {
            requestAnimationFrame(() => {
                this.setColors();
            })
        }
    }
}
</script>

<template>
    <div class="feed_others_events" v-if="webinars?.length">
        <div class="feed_others_events_title">Смотреть сейчас</div>
        <div class="feed_others_events_slider">
            <div v-for="web in webinars" class="feed_others_events_slider_webinar">
                <div class="feed_others_future_events_slider_webinar_type">Вебинар</div>
                <div class="feed_others_events_slider_webinar_title">{{web.title}}</div>
                <div class="feed_others_events_slider_webinar_description">{{web.description}}</div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>