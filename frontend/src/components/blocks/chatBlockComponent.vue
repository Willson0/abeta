<script>
import config from "@/components/config.json"
export default {
    name: "chatBlockComponent",
    data () {
      return {
          ok: false,
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
    <div class="feed_chat">
        <div class="feed_chat_image">
            <img src="/img/closed_chat.svg" alt="">
        </div>
        <div class="feed_chat_title">
            Закрытый чат ABETA
        </div>
        <div class="feed_chat_description">
            Задавай вопросы, обменивайся мнениями, получай ответы от экспертов
        </div>
        <button :style="ok ? 'active' : ''" @click="sendData" class="feed_chat_button">
            <p>Запросить доступ</p>
            <p>Приглашение отправлено</p>
        </button>
    </div>
</template>

<style scoped>

</style>