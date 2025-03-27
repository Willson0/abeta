<script>
import MailComponent from "@/components/blocks/mailComponent.vue";
import FutureEventsComponent from "@/components/blocks/futureEventsComponent.vue";
import ConsultationExpertsComponent from "@/components/blocks/consultationExpertsComponent.vue";
import OtherAnalyticsComponent from "@/components/blocks/otherAnalyticsComponent.vue";
import config from "@/components/config.json";
import {formatDate} from "../utils.js";

export default {
    name: "analyticsView",
    components: {OtherAnalyticsComponent, ConsultationExpertsComponent, MailComponent, FutureEventsComponent},
    data () {
        return {
            tg: null,
            backbutton: null,
            analytic: {},
            user: {},
            invest_port: "",
            config: config,
            feed: {},
            fields: {},
        }
    },
    async mounted () {
        document.body.style.backgroundColor = "#F3F4F6";

        let backbutton = window.Telegram.WebApp.BackButton;
        this.backbutton = backbutton;

        backbutton.show();

        backbutton.onClick(() => {
            this.$router.push('/?s=' + this.$route.query.s);
            backbutton.hide();
        })

        await fetch (config.backend + "analytic/" + this.$route.params.id, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                initData: window.Telegram.WebApp.initData,
            })
        }).then((response) => {
            return response.json();
        }).then((response) => {
            this.analytic = response;
            if (this.analytic.fields)
                for (let field of JSON.parse(this.analytic.fields)) {
                    const fixedEncoded = field.replace(/u([0-9A-Fa-f]{4})/g, '\\u$1');
                    const decoded = JSON.parse('"' + fixedEncoded + '"');
                    this.fields[decoded] = "";
                }
        })

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

        await fetch (config.backend + "feed/all", {
            method: "GET",
        }).then((response) => {
            return response.json();
        }).then((response) => {
            this.feed = response;
        });
    },
    methods: {
        formatDate,
        async sendData() {
            await fetch (config.backend + "analytic/" + this.$route.params.id +
                "/getaccess", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    "initData": window.Telegram.WebApp.initData,
                    "data": this.fields,
                })
            }).then((response) => {
                return response.json();
            }).then((response) => {
                this.analytic = response;
            })
        },
    }
}
</script>

<template>
    <div class="analytics">
        <div class="feed_webinar_image">
            <div class="feed_webinar_image_tags" v-if="analytic.link">
                <div style="background-color:#EB2026">Youtube</div>
<!--                <div style="background-color:#FF734C">1:15:24</div>-->
            </div>
            <img :src="config.storage + analytic.image" alt="">
        </div>
        <a :href="analytic.link" target="_blank" class="webinar_record" v-if="analytic.link && !analytic.locked">
            <div>
                <img src="/img/play.svg" alt="">
                <div>Посмотреть</div>
            </div>
        </a>
        <div class="analytics_main">
            <div class="analytics_date">Аналитика <span>&middot; {{formatDate(analytic.created_at)}}</span></div>
            <div class="webinar_title">{{ analytic.title }}</div>
            <div class="webinar_description">
                {{analytic.description}}
                <div class="analytics_description_blur" v-if="analytic.locked"></div>
                <div class="analytics_description_lock" v-if="analytic.locked">
                    <img src="/img/door_lock.svg" alt="">
                    <div class="webinar_description_lock_text">
                        Доступ по запросу
                    </div>
                </div>
            </div>
            <div class="analytics_addition">
                <div>
                    <div>Ссылка на инвестиционный инструмент:</div>
                    <a href="https://abeta.app/">https://abeta.app/</a>
                </div>
                <div v-if="!analytic.locked && analytic.pdf">
                    <a :href="config.storage + analytic.pdf">Скачать ПДФ-конспект встречи</a>
                </div>
            </div>
        </div>
        <div class="webinar_registration form" v-if="analytic.locked">
            <div class="form_free">Бесплатно</div>
            <div class="form_title">Доступ к закрытым материалам</div>
            <form @submit.prevent="sendData" class="webinar_registration_form">
                <div class="form_input" v-for="(key, field) in fields">
                    <label :for="field">{{ field }}</label>
                    <input v-model="fields[field]" type="text" :name="field">
                </div>
                <button>Получить доступ</button>
            </form>
            <div class="form_policy">
                Нажимая на кнопку, вы соглашаетесь <a href="https://abeta.org/politics" target="_blank">с политикой конфиденциальности</a>
            </div>
        </div>
        <other-analytics-component :analytics="feed?.analytics"/>
        <consultation-experts-component />
        <mail-component :user="user"/>
    </div>
</template>

<style scoped>

</style>