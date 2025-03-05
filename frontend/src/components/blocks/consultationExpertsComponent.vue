<script>
import config from "@/components/config.json";

export default {
    name: "consultationExpertsComponent",
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
                    setTimeout(() => el.classList.remove("active"), 3000);
                }
            })
        }
    }
}
</script>

<template>
    <div class="feed_consultation">
        <div class="feed_consultation_photos">
            <img v-for="(img, key) in ['/img/avatar1.png', '/img/avatar2.png', '/img/avatar3.png']"
                 :src="img" alt="" :style="'right: ' + 8*key + 'px'">
        </div>
        <div class="feed_consultation_title">
            Бесплатная консультация с экспертами ABETA
        </div>
        <div class="feed_consultation_description">
            Расширим возможности, подберем инструменты и инвестиционную стратегию
        </div>
        <button @click="sendData" class="feed_consultation_button">Выбрать удобное время</button>
    </div>
</template>

<style scoped>

</style>