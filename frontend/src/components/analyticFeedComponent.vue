<script>
import analyticComponent from "@/components/blocks/analyticComponent.vue";
import ChatBlockComponent from "@/components/blocks/chatBlockComponent.vue";
import MailComponent from "@/components/blocks/mailComponent.vue";
import ConsultationExpertsComponent from "@/components/blocks/consultationExpertsComponent.vue";
import config from "@/components/config.json";
export default {
    name: "analyticFeedComponent",
    components: {ConsultationExpertsComponent, MailComponent, ChatBlockComponent, analyticComponent},
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
    data () {
        return {
            isFull: false,
            isLoading: false,
            additionAnalytics: [],
        }
    },
    async mounted () {
        window.addEventListener("scroll", (ev) => {
            // Получаем целевой элемент (блок, который нужно отслеживать)
            const targetBlock = document.querySelector('.analytics .feed_web:nth-last-child(2)');
            const rect = targetBlock.getBoundingClientRect();

            if (rect.top <= window.innerHeight && !this.isLoading && !this.isFull) this.loadMoreAnalytics();
        })
    },
    methods: {
        async loadMoreAnalytics () {
            this.isLoading = true;

            await fetch (config.backend + "feed/analytics?offset=" + (this.feed.analytics.length + this.additionAnalytics.length))
                .then((response) => {
                    return response.json();
                }).then((response) => {
                    this.isLoading = false;
                    if (this.additionAnalytics.length === 0) this.isFull = true;
                    this.additionAnalytics = this.additionAnalytics.concat(response);
                })
        }
    }
}
</script>

<template>
    <div class="analytics">
        <analytic-component v-for="analytic in feed.analytics?.slice(0,1)" :analytic="analytic"/>
        <analytic-component v-for="analytic in feed.analytics?.slice(1,2)" :size="1" :analytic="analytic"/>
        <analytic-component v-for="analytic in feed.analytics?.slice(2,3)" :analytic="analytic"/>
        <chat-block-component :user="user"/>
        <analytic-component v-for="analytic in feed.analytics?.slice(3,5)" :analytic="analytic"/>
        <analytic-component v-for="analytic in feed.analytics?.slice(5,7)" :size="1" :analytic="analytic"/>
        <analytic-component v-for="analytic in feed.analytics?.slice(7,9)" :analytic="analytic"/>
        <mail-component :user="user"/>
        <analytic-component v-for="analytic in feed.analytics?.slice(9,11)" :analytic="analytic"/>
        <consultation-experts-component />
        <analytic-component v-for="analytic in feed.analytics?.slice(11)" :size="2" :analytic="analytic"/>
        <analytic-component v-for="analytic in additionAnalytics" :size="2" :analytic="analytic"/>
    </div>
</template>

<style scoped>

</style>