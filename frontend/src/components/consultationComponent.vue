<script>
import SupportComponent from "@/components/blocks/supportComponent.vue";
import config from "@/components/config.json"

export default {
    name: "consultationComponent",
    components: {SupportComponent},
    data () {
        return {
            venture: false,
            name: "",
            phone: "",
        }
    },
    async mounted () {

        await fetch (config.backend + "venture/status", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                initData: window.Telegram.WebApp.initData,
            }),
        }).then((response) => {
            return response.json();
        }).then((response) => {
            this.venture = response;
        });
    },
    props: {
      user: {
          type: Object,
          required: true,
      }
    },
    methods: {
        async sendVenture () {
            await fetch (config.backend + "venture", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    initData: window.Telegram.WebApp.initData,
                }),
            }).then((response) => {
                this.venture = 0;
            })
        },
        async sendData () {
            await fetch (config.backend + "support", {
                method: "POST",
                body: JSON.stringify({"initData": window.Telegram.WebApp.initData, "text": "Консультация с экспертами ABETA"}),
                headers: {
                    "Content-Type": "application/json",
                }
            }).then((response) => {
              
                    let el = document.querySelector(".feed_consultation_button");
                    el.classList.add("active");
                    setTimeout(() => {el.classList.remove("active"); location.reload()}, 3000);
               
            })
        }
    }
}
</script>

<template>
    <div class="consultation">
        <div class="consultation_tell_about">
            <div class="consultation_tell_about_title">
                На консультации расскажем как:
            </div>
            <ul class="consultation_tell_about_list">
                <li>Не потерять деньги даже в условиях шторма;</li>
                <li>Выбрать инвестиционные инструменты под ваши цели, а не интересы продавцов лопат;</li>
                <li>Определиться с регионом и инфраструктурой для размещения активов</li>
            </ul>
        </div>

        <div class="form" v-if="user.support === 1">
            <div class="form_free">Бесплатно</div>
            <div class="form_title">Консультация c ведущим<br>аналитиком компании</div>
            <form @submit.prevent="sendData">
                <div class="form_input">
                    <label for="name">Имя</label>
                    <input type="text" name="name" :value="user.fullname">
                </div>
                <div class="form_input">
                    <label for="phone">Телефон</label>
                    <input type="text" name="phone" :value="user.phone">
                </div>
                <button class="feed_consultation_button">Записаться на консультацию</button>
            </form>
            <div class="form_policy">Нажимая на кнопку, вы соглашаетесь <a>с политикой конфиденциальности</a></div>
        </div>
        <div class="feed_consultation" v-else-if="user.support === 0" style="margin-top:16px; margin-left:0;width:100%;box-sizing: border-box">
            <div class="feed_consultation_title" style="margin-top:0;">
                Вы записаны на консультацию
            </div>
            <div class="feed_consultation_description" >
                В скором времени с вами свяжется эксперт
            </div>
        </div>

        <support-component />
        <div class="consultation_venture" v-if="venture !== -1" :class="venture === 0 ? 'active' : ''">
            <img class="consultation_venture_img" src="/img/growing.svg" alt="">
            <div class="consultation_venture_title" v-if="venture === 0">Ваш запрос получен<br>и обрабатывается</div>
            <div class="consultation_venture_title" v-else>Актуальные<br>венчурные сделки</div>
            <div class="consultation_venture_description" v-if="venture === 0">Консультант по венчурным сделкам скоро выйдет с вами на связь</div>
            <div class="consultation_venture_description" v-else>Проверенные инвестиционные возможности в перспективные стартапы и фонды</div>
            <button @click="sendVenture" v-if="venture === 1">Запросить</button>
        </div>
    </div>
</template>

<style scoped>

</style>
