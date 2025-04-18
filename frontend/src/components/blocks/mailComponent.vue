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
        let popup = document.querySelector (".services_popup");
        popup.addEventListener("click", (ev) => {
            if (ev.target.classList.contains("services_popup")) this.closePopup();
        });
    },
    methods: {
        async sendData () {
            let div = document.createElement("div");
            div.classList.add("services_popup");
            div.innerHTML = `<div class="services_popup_main mailPopup">
                                    <div class="form_title">Экспертная рассылка<br>от ABETA Capital</div>
                                    <form @submit.prevent="sendData" class="webinar_registration_form">
                                        <div class="form_input">
                                            <label>Имя</label>
                                            <input v-model="" type="text">
                                        </div>
                                        <div class="form_input">
                                            <label>Почта</label>
                                            <input v-model="" type="text">
                                        </div>
                                        <button>Подписаться</button>
                                    </form>
                                    <div class="form_policy">Нажимая на кнопку, вы соглашаетесь <a>с политикой конфиденциальности</a></div>
                                </div>`

            requestAnimationFrame(() => {
                    document.body.style.overflow="hidden";
                    this.selectedService = serv;
                    let popup = document.querySelector(".services_popup");
                    popup.style.display = "flex";
                    requestAnimationFrame(() => popup.classList.add("active"));
            })

            // await fetch (config.backend + "auth", {
            //     method: "POST",
            //     body: JSON.stringify({"initData": window.Telegram.WebApp.initData,
            //         "expert_mailing": !this.user.expert_mailing}),
            //     headers: {
            //         "Content-Type": "application/json"
            //     }
            // }).then((response) => {
            //     if (response.ok) this.user.expert_mailing = !this.user.expert_mailing;
            // }).then((response) => {
            //
            // })
        },
        async closePopup () {
            document.body.style.overflow="";
            let popup = document.querySelector(".services_popup");
            popup.classList.remove("active");
            setTimeout(() => popup.style.display = "", 200);
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