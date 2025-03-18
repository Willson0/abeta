<script>
import config from "@/components/config.json";

export default {
    name: "mailComponent",
    data () {
        return {

        }
    },
    props: {
        user: {
            type: Object,
            required: true,
        }
    },
    async mounted () {

    },
    methods: {
        async sendData () {
            await fetch (config.backend + "auth", {
                method: "POST",
                body: JSON.stringify({"initData": window.Telegram.WebApp.initData,
                    "expert_mailing": !this.user.expert_mailing}),
                headers: {
                    "Content-Type": "application/json"
                }
            }).then((response) => {
                if (response.ok) this.user.expert_mailing = !this.user.expert_mailing;
            }).then((response) => {

            })
        }
    }
}
</script>

<template>
    <div v-if="!user.expert_mailing" class="feed_newsletter">
        <div class="feed_newsletter_image">
            <img src="/img/mail.svg" alt="">
        </div>
        <div class="feed_newsletter_title">
            Экспертная рассылка от ABETA Capital
        </div>
        <div class="feed_newsletter_description">
            Аналитика, инсайды и свежие идеи — в вашей почте. Только важное, без лишнего
        </div>
        <button @click="sendData" class="feed_newsletter_button">
            <p :class="!user.expert_mailing ? 'active' : ''">Подписаться</p>
            <p :class="user.expert_mailing ? 'active' : ''">Вы подписаны</p>
        </button>
    </div>
</template>

<style scoped>

</style>