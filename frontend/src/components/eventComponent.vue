<script>
import config from "@/components/config.json";
import WebinarComponent from "@/components/blocks/webinarComponent.vue";
import ChatBlockComponent from "@/components/blocks/chatBlockComponent.vue";
import FutureEventsComponent from "@/components/blocks/futureEventsComponent.vue";
import ConsultationExpertsComponent from "@/components/blocks/consultationExpertsComponent.vue";
import MailComponent from "@/components/blocks/mailComponent.vue";
import OldEventsComponent from "@/components/blocks/oldEventsComponent.vue";

export default {
    name: "eventComponent",
    components: {
        OldEventsComponent,
        MailComponent,
        ConsultationExpertsComponent, FutureEventsComponent, ChatBlockComponent, WebinarComponent},
    data () {
        return {
            selectedFilter: "Все",
            additionWebinars: [],
            isFull: false,
            isLoading: false,
        }
    },
    async mounted () {
        window.addEventListener("scroll", (ev) => {
            // Получаем целевой элемент (блок, который нужно отслеживать)
            const targetBlock = document.querySelector('.old_events_main .feed_web:nth-last-child(2)');
            const rect = targetBlock.getBoundingClientRect();

            if (rect.top <= window.innerHeight && !this.isLoading && !this.isFull
                && this.selectedFilter === "В записи") this.loadMoreWebinars();
        })
    },
    props: {
        feed: {
            type: Object,
            required: true,
        },
        user: {
            type: Object,
            required: true,
        }
    },
    methods: {
        async loadMoreWebinars () {
            this.isLoading = true;

            await fetch (config.backend + "feed/webinars?offset=" + (this.feed.old_events.length + this.additionWebinars.length))
                .then((response) => {
                    return response.json();
                }).then((response) => {
                    this.isLoading = false;
                    if (this.additionWebinars.length === 0) this.isFull = true;
                    this.additionWebinars = this.additionWebinars.concat(response);
                })
        }
    },
}
</script>

<template>
    <div class="nav old_events">
        <div>
            <div @click="selectedFilter = filter"
                 :class="selectedFilter === filter ? 'active' : ''"
                 v-for="filter in ['Все', 'Предстоящие', 'В записи']">
                <p>{{filter}}</p>
            </div>
        </div>
    </div>
    <div class="old_events_main" v-if="selectedFilter === 'Все'">
        <webinar-component v-for="web in feed.upcoming_events?.slice(0,1)" :webinar="web" />
        <webinar-component v-for="web in feed.upcoming_events?.slice(1,2)" :size="1" :webinar="web" />
        <webinar-component v-for="web in feed.upcoming_events?.slice(2,3)" :webinar="web" />
        <chat-block-component />
        <webinar-component v-for="web in feed.upcoming_events?.slice(3,5)" :webinar="web" />
        <future-events-component :webinars="feed.upcoming_events?.slice(5)"/>
        <webinar-component v-for="web in feed.old_events?.slice(0,1)" :webinar="web" />
        <webinar-component v-for="web in feed.old_events?.slice(1,2)" :size="2" :webinar="web" />
        <webinar-component v-for="web in feed.old_events?.slice(2,3)" :webinar="web" />
        <mail-component :user="user"/>
        <webinar-component v-for="web in feed.old_events?.slice(3,5)" :webinar="web" />
        <old-events-component :webinars="feed.old_events?.slice(5)" />
        <consultation-experts-component />
    </div>
    <div class="old_events_main" v-if="selectedFilter === 'Предстоящие'">
        <webinar-component v-for="web in feed.upcoming_events?.slice(0,1)" :webinar="web" />
        <webinar-component v-for="web in feed.upcoming_events?.slice(1,2)" :size="1" :webinar="web" />
        <webinar-component v-for="web in feed.upcoming_events?.slice(2,3)" :webinar="web" />
        <chat-block-component />
        <webinar-component v-for="web in feed.upcoming_events?.slice(3,5)" :webinar="web" />
        <webinar-component v-for="web in feed.upcoming_events?.slice(5,7)" :size="2" :webinar="web" />
        <webinar-component v-for="web in feed.upcoming_events?.slice(7,9)" :webinar="web" />
        <mail-component :user="user"/>
        <webinar-component v-for="web in feed.upcoming_events?.slice(9,11)" :webinar="web" />
        <consultation-experts-component />
        <webinar-component v-for="web in feed.upcoming_events?.slice(11)" :size="3" :webinar="web" />
    </div>
    <div class="old_events_main" v-if="selectedFilter === 'В записи'">
        <webinar-component v-for="web in feed.old_events?.slice(0,1)" :webinar="web" />
        <webinar-component v-for="web in feed.old_events?.slice(1,2)" :size="2" :webinar="web" />
        <webinar-component v-for="web in feed.old_events?.slice(2,3)" :webinar="web" />
        <chat-block-component />
        <webinar-component v-for="web in feed.old_events?.slice(3,5)" :webinar="web" />
        <webinar-component v-for="web in feed.old_events?.slice(5,7)" :size="2" :webinar="web" />
        <webinar-component v-for="web in feed.old_events?.slice(7,9)" :webinar="web" />
        <mail-component :user="user"/>
        <webinar-component v-for="web in feed.old_events?.slice(9,11)" :webinar="web" />
        <consultation-experts-component />
        <webinar-component v-for="web in feed.old_events?.slice(11)" :size="3" :webinar="web" />
        <webinar-component v-for="web in additionWebinars" :size="3" :webinar="web" />
    </div>
</template>

<style scoped>

</style>