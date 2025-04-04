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
                    setTimeout(() => {el.classList.remove("active"); location.reload()}, 3000);
                }
            })
        },

    },
    async mounted () {
        // await fetch (config.backend + "profile", {
        //     method: "POST",
        //     body: JSON.stringify({"initData": window.Telegram.WebApp.initData}),
        //     headers: {
        //         "Content-Type": "application/json",
        //     }
        // }).then((response) => {
        //     return response.json();
        // }).then((response) => {
        //     this.user = response;
        // });
    },
    props: {
        user: {
            type: Object,
            required: true,
        }
    },
}
</script>

<template>
    <div v-if="user.in_chat"></div>
    <div class="feed_chat" v-else-if="!user.chat_request">
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
    <div class="feed_chat" v-else style="background-color: #36B251">
        <div class="feed_chat_image">
            <img style="filter: invert(1);" src="/img/closed_chat.svg" alt="">
        </div>
        <div class="chat_title" style="color:white">
            Закрытый чат ABETA
        </div>
        <div class="feed_chat_description" style="color:white;">
            Ваш запрос получен. Доступ к чату откроется в течение 24 часов
        </div>
    </div>
</template>

<style scoped>

</style>