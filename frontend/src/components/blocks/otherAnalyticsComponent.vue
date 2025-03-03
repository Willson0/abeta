<script>
export default {
    name: "otherAnalyticsComponent",
    props: {
        analytics: {
            type: Array,
            required: true,
        }
    }
}
</script>

<template>
    <div class="feed_others_future_events">
        <div class="feed_others_events_title">Еще больше аналитических материалов</div>
        <div class="feed_others_events_slider">
            <div style="background-color:#191919" @click="$router.push('/analytics/' + analytic.id)"
                 v-for="analytic in analytics" class="feed_others_events_slider_analytics">
                <div class="feed_others_events_slider_analytics_header">Аналитика &middot; {{analytic.time}} минут чтения</div>
                <hr>
                <div class="feed_others_events_slider_analytics_title">{{analytic.title}}</div>
                <div class="feed_others_events_slider_analytics_description">{{analytic.description}}</div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>