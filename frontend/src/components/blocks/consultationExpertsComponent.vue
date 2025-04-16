<script>
import config from "@/components/config.json";

export default {
    name: "consultationExpertsComponent",
    data () {
        return {
            user: {},
        }
    },
    methods: {
        async sendData () {
            await fetch (config.backend + "support", {
                method: "POST",
                body: JSON.stringify({"initData": window.Telegram.WebApp.initData, "text": "Консультация с экспертами ABETA"}),
                headers: {
                    "Content-Type": "application/json",
                }
            }).then((response) => {
                if (response.ok) {
                    let el = document.querySelector(".feed_consultation_button");
                    el.classList.add("active");
                    setTimeout(() => {el.classList.remove("active"); location.reload()}, 3000);
                }
            })
        }
    },
    async mounted () {
        await fetch (config.backend + "profile", {
            method: "POST",
            body: JSON.stringify({"initData": window.Telegram.WebApp.initData}),
            headers: {
                "Content-Type": "application/json",
            }
        }).then((response) => {
            return response.json();
        }).then((response) => {
            this.user = response;
        });
    }
}
</script>

<template>
<!--    // 1 - можно подавать заявку. 0 - не закрыта старая; -1 - идет кд-->
    {{user.support}}
    <div class="feed_consultation" v-if="user.support !== -1">
        <div class="feed_consultation_photos" v-if="user.support === 1">
            <img v-for="(img, key) in ['/img/avatar1.png', '/img/avatar2.png', '/img/avatar3.png']"
                 :src="img" alt="" :style="'right: ' + 8*key + 'px'">
        </div>
        <div class="feed_consultation_title"  v-if="user.support === 1">
            Бесплатная консультация с экспертами ABETA
        </div>
        <div class="feed_consultation_title" v-else>
            Вы записаны на консультацию
        </div>
        <div class="feed_consultation_description" v-if="user.support === 1">
            Расширим возможности, подберем инструменты и инвестиционную стратегию
        </div>
        <div class="feed_consultation_description" v-else>
            В скором времени с вами свяжется эксперт
        </div>
        <button @click="sendData" v-if="user.support === 1" class="feed_consultation_button">Записаться на консультацию</button>
    </div>
</template>

<style scoped>

</style>