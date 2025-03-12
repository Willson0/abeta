<script>
import FutureEventsComponent from "@/components/blocks/futureEventsComponent.vue";
import MailComponent from "@/components/blocks/mailComponent.vue";
import config from "@/components/config.json";
import {formatDate, getRelativeDate} from "@/utils.js"

export default {
    name: "webinarView",
    components: {MailComponent, FutureEventsComponent},
    data () {
        return {
            tg: null,
            backbutton: null,
            webinar: {},
            user: {},
            config: config,
            feed: {},
            fields: {},
        }
    },
    async mounted () {
        document.body.style.backgroundColor = "#F3F4F6";
        alert (window.history);

        let backbutton = window.Telegram.WebApp.BackButton;
        this.backbutton = backbutton;

        backbutton.show();

        backbutton.onClick(function () {
            window.history.back();
            backbutton.hide();
        })

        await fetch (config.backend + `webinar/` + this.$route.params.id, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                "initData": window.Telegram.WebApp.initData,
            })
        }).then((response) => {
            return response.json();
        }).then((response) => {
            this.webinar = response;
            for (let field of JSON.parse(this.webinar.fields)) {
                const fixedEncoded = field.replace(/u([0-9A-Fa-f]{4})/g, '\\u$1');
                const decoded = JSON.parse('"' + fixedEncoded + '"');
                this.fields[decoded] = "";
            }
        })

        await fetch (config.backend + "profile", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                "initData": window.Telegram.WebApp.initData,
            })
        }).then((response) => {
            return response.json();
        }).then((response) => {
            this.user = response;
        })

        await fetch (config.backend + "feed/all").then((response) => {
            return response.json();
        }).then((response) => {
            this.feed = response;
        })
    },
    methods: {
        getRelativeDate,
        formatDate,

        async sendData() {
            for (let field in this.fields)
                if (!field) return document.querySelector(".webinar_registration_error").classList.add("active");
            document.querySelector(".webinar_registration_error").classList.remove("active");

            await fetch (config.backend + "webinar/" + this.$route.params.id + "/registration", {
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
                this.webinar = response;
            })
        },
        copyLink () {
            navigator.clipboard.writeText(this.webinar.link);
        },
        async calendar () {
            await fetch (config.backend + "webinar/" + this.$route.params.id + "/calendar", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    "initData": window.Telegram.WebApp.initData,
                })
            }).then((response) => {
                if (response.ok) {
                    let el = document.querySelector(".webinar_links_calendar_button");
                    el.classList.add("active");
                    setTimeout(() => el.classList.remove("active"), 3000);
                }
            })
        }
    },
    computed: {
        isActual () {
            return (new Date(this.webinar.date) > new Date());
        }
    }
}
</script>

<template>
    <div class="webinar">
        <div class="feed_webinar_image">
            <div class="feed_webinar_image_tags">
                <div v-if="webinar.registered && isActual" style="background-color: #FF734C">Вы участвуете</div>
                <div class="feed_webinar_image_date" v-if="getRelativeDate(webinar.date)">{{ getRelativeDate(webinar.date) }}</div>
            </div>
            <img :src="config.storage + webinar.image" alt="">
        </div>
        <div class="webinar_record" v-if="webinar.record_link">
            <div>
                <img src="/img/play.svg" alt="">
                <div>Посмотреть запись</div>
            </div>
        </div>
        <div class="webinar_main">
            <div class="webinar_date">Вебинар &middot; {{ formatDate(webinar.date) }}</div>
            <div class="webinar_title">{{webinar.title}}</div>
            <div class="webinar_description">{{webinar.description}}</div>
        </div>
        <div v-if="!webinar.registered && isActual" class="webinar_registration form">
            <div class="form_title">Регистрация<br>на вебинар</div>
            <div class="webinar_registration_info">
                <div class="webinar_registration_info_description">
                    {{ webinar.title }}
                </div>
                <hr>
                <div class="webinar_registration_info_date">{{formatDate(webinar.date)}}</div>
            </div>
            <div class="webinar_registration_error">
                <div>Для регистрации нужно заполнить все поля</div>
            </div>
            <form @submit.prevent="sendData" class="webinar_registration_form">
                <div class="form_input" v-for="(key, field) in fields">
                    <label :for="field">{{ field }}</label>
                    <input v-model="fields[field]" type="text" :name="field">
                </div>
                <button>Зарегистрироваться</button>
            </form>
            <div class="form_policy">
                Нажимая на кнопку, вы соглашаетесь <a>с политикой конфиденциальности</a>
            </div>
        </div>
        <div v-if="webinar.registered && isActual" class="webinar_registration form active">
            <div class="form_title">Вы уже записаны на вебинар</div>
            <div class="webinar_registration_info">
                <div class="webinar_registration_info_description">
                    {{ webinar.title }}
                </div>
                <div class="webinar_registration_info_date">
                    <div>7 февраля</div>
                    <div>18:00 мск</div>
                </div>
            </div>
            <div class="form_policy">
                Напомним об участии за день до вебинара и в день проведения
            </div>
        </div>
        <div v-if="webinar.registered && isActual" class="webinar_links">
            <div class="webinar_links_zoom">
                <img src="/img/zoom.svg" alt="">
                <div class="webinar_links_zoom_title">Зум-cсылка на вебинар:</div>
                <div class="webinar_links_zoom_link"><a>{{webinar.link}}</a></div>
                <button @click="copyLink" class="webinar_links_zoom_button">Скопировать</button>
            </div>
            <div class="webinar_links_calendar">
                <img src="/img/calendar.svg" alt="">
                <div class="webinar_links_calendar_title">Добавьте событие в календарь, чтобы не забыть</div>
                <button class="webinar_links_calendar_button" @click="calendar">Добавить в календарь</button>
            </div>
        </div>
        <future-events-component :webinars="feed.upcoming_events"/>
        <mail-component  user="user"/>
    </div>
</template>

<style scoped>

</style>