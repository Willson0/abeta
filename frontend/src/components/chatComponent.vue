<script>
import config from "@/components/config.json";

export default {
    name: "chatComponent",
    props: {
      user: {
          type: Object,
          required: true,
      }
    },
    methods: {
        async sendData () {
            await fetch (config.backend + "group", {
                method: "POST",
                body: JSON.stringify({"initData": window.Telegram.WebApp.initData}),
                headers: {
                    "Content-Type": "application/json",
                }
            }).then((response) => {
                if (response.ok) {
                    let el = document.querySelector(".feed_chat_button");
                    el.classList.add("active");
                    setTimeout(() => el.classList.remove("active"), 3000);
                }
            })

            await fetch (config.backend + "profile", {
                method: "POST",
                body: JSON.stringify({"initData": window.Telegram.WebApp.initData}),
                headers: {
                    "Content-Type": "application/json"
                }
            })
        }
    }
}
</script>

<template>
    <div class="chat" v-if="!user.chat_request">
        <div class="feed_chat_image">
            <img src="/img/closed_chat.svg" alt="">
        </div>
        <div class="chat_title">
            Закрытый чат ABETA
        </div>
        <ul class="chat_description">
            <li>Задавайте вопросы и получайте ответы от экспертов,</li>
            <li>Обсуждайте стратегии и рыночные тренды,</li>
            <li>Доступ к аналитике и эксклюзивным инвестиционным идеям</li>
        </ul>
        <button style="padding: 16px 0;" @click="sendData" class="feed_chat_button">
            <p>Запросить доступ</p>
            <p>Приглашение отправлено</p>
        </button>
    </div>
    <div class="chat" v-else style="background-color: #36B251">
        <div class="feed_chat_image">
            <img style="filter: invert(1);" src="/img/closed_chat.svg" alt="">
        </div>
        <ul class="chat_description" style="list-style: none">
            <li>Ваш запрос получен. Доступ к чату откроется в течение 24 часов</li>
        </ul>
    </div>
</template>

<style scoped>

</style>