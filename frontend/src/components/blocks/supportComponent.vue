<script>
import config from "@/components/config.json";

export default {
    name: "supportComponent",
    methods: {
        async sendData () {
            await fetch (config.backend + "support", {
                method: "POST",
                body: JSON.stringify({"initData": window.Telegram.WebApp.initData}),
                headers: {
                    "Content-Type": "application/json"
                }
            }).then((response) => {
                if (response.ok) {
                    let el = document.querySelector(".consultation_support_button");
                    el.classList.add("active");
                    setTimeout(() => el.classList.remove("active"), 3000);
                }
            })
        }
    }
}
</script>

<template>
    <div class="consultation_support">
        <img class="consultation_support_tg" src="/img/Telegram.svg" alt="">
        <div class="consultation_support_title">Чат поддержки АВЕТА</div>
        <div class="consultation_support_description">Экспертная помощь, разбор стратегий и рекомендации по вашим запросам</div>
<!--        <button class="consultation_support_button" @click="sendData">Связаться</button>-->
        <div class="consultation_support_button">
            <a href="https://t.me/abeta_clients">Связаться</a>
        </div>
    </div>
</template>

<style scoped>

</style>