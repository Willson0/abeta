<script>
import config from "@/components/config.json";

export default {
    name: "mailComponent",
    data () {
        return {
            name: "",
            email: "",
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
            let div = document.createElement("div");
            div.classList.add("services_popup");
            div.innerHTML = `<div style="padding-bottom:24px; background-color: #191919; color:white; overflow:hidden; overflow-y:auto" class="services_popup_main mailPopup">
                                    <div class="form_title">Экспертная рассылка<br>от ABETA Capital</div>
                                    <img id="closeSubscribe" style="top:16px;right:16px;position:absolute;width:40px;height:40px;filter: brightness(1.5);" src="/img/light_close.svg" alt="">
                                    <div class="webinar_registration_form">
                                        <div class="form_input">
                                            <label>Имя</label>
                                            <input id="input_name" style="color:black" v-model="name" type="text">
                                        </div>
                                        <div class="form_input">
                                            <label>Почта</label>
                                            <input id="input_email" style="color:black" v-model="email" type="text">
                                        </div>
                                        <button id="subscribeButton">Подписаться</button>
                                    </div>
                                    <div class="form_policy">Нажимая на кнопку, вы соглашаетесь <a style="color:#FF734C;">с политикой конфиденциальности</a></div>
                                </div>`
            document.body.appendChild(div);
            document.querySelector("#input_name").value = this.user.fullname;
            document.querySelector("#subscribeButton").onclick = this.subscribe;
            document.querySelector("#closeSubscribe").onclick = this.closePopup;

            requestAnimationFrame(() => {
                document.body.style.overflow="hidden";

                div.style.display = "flex";
                requestAnimationFrame(() => div.classList.add("active"));

                div.addEventListener("click", (ev) => {
                    if (ev.target.classList.contains("services_popup")) this.closePopup();
                });
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
            setTimeout(() => popup.remove(), 200);
        },
        async subscribe () {
            let name = document.querySelector("#input_name").value;
            let email = document.querySelector("#input_email").value;

            if (!name) return alert ("Заполните поле \"Имя\"!");
            if (!email) return alert ("Заполните поле \"Почта\"!");
            if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email)) return alert ("Неправильный формат почты!");

            await fetch (config.backend + "auth/subscribe", {
                method: "POST",
                body: JSON.stringify({
                    "initData": window.Telegram.WebApp.initData,
                    "name": name,
                    "email": email,
                }),
                headers: {
                    "Content-Type": "application/json"
                }
            }).then((response) => {
                if (response.ok) this.user.expert_mailing = !this.user.expert_mailing;
                this.closePopup();
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