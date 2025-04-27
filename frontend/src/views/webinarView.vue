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
            googlelink: "",
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

        await fetch (config.backend + "google/link", {
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
            this.googlelink = response.link;
        });

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
            let loading = document.querySelector(".loading")
            loading.style.opacity = "0";
            setTimeout(() => {
                loading.style.display = "none"
            }, 200);
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
            for (let field in this.fields) {
                if (field.toLocaleLowerCase() === "телефон") this.fields[field] = this.user.phone;
                if (field.toLocaleLowerCase() === "имя") this.fields[field] = this.user.fullname;
            }
        });

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
            document.querySelector(".webinar_registration_error").classList.remove("active");
            for (let field in this.fields) {
                if (!this.fields[field] || this.fields[field] == null) {
                    return document.querySelector(".webinar_registration_error").classList.add("active");
                }
                if (field.toLocaleLowerCase() === "телефон" && (!/(?:\D*\d){10,15}/.test(this.fields[field]) || this.fields[field].length > 15)) return alert ("Неправильный формат номера телефона");
                // if ((field.toLocaleLowerCase() === "имя" || field.toLocaleLowerCase() === "фио")) {
                //     this.fields[field] = this.fields[field].trim();
                //     if (!/^[A-ZА-ЯЁ][a-zа-яё]+(?: [A-ZА-ЯЁ][a-zа-яё]+){0,2}$/u.test(this.fields[field])) return alert ("Неправильный формат ФИО");
                // }
            }

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
                window.scrollTo({ top: 0, behavior: 'smooth' });
            })
        },
        copyLink () {
            navigator.clipboard.writeText(this.webinar.link);
        },
        async calendar () {
            // await fetch (config.backend + "webinar/" + this.$route.params.id + "/calendar", {
            //     method: "POST",
            //     headers: {
            //         "Content-Type": "application/json"
            //     },
            //     body: JSON.stringify({
            //         "initData": window.Telegram.WebApp.initData,
            //     })
            // }).then((response) => {
            //     if (response.ok) {
            //         let el = document.querySelector(".webinar_links_calendar_button");
            //         el.classList.add("active");
            //         setTimeout(() => el.classList.remove("active"), 3000);
            //     } else alert ("У вас не привязан аккаунт Calendly! Используйте /calendly");
            // })
            await fetch (config.backend + "google/event", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    "initData": window.Telegram.WebApp.initData,
                    "id": this.webinar.id,
                })
            }).then((response) => {
                return response.json();
            }).then((response) => {
                alert ("Вебинар успешно добавлен в ваш Google-календарь!");
                this.webinar.added_calendar = 1;
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
    <div class="loading"></div>
    <div class="webinar">
        <div class="feed_webinar_image">
            <div class="feed_webinar_image_tags">
                <div v-if="webinar.registered && isActual" style="background-color: #FF734C">Вы участвуете</div>
                <div class="feed_webinar_image_date" v-if="getRelativeDate(webinar.date)">{{ getRelativeDate(webinar.date) }}</div>
            </div>
            <img :src="config.storage + webinar.image" alt="">
        </div>
        <a :href="webinar.record_link" target="_blank" class="webinar_record" v-if="webinar.record_link">
            <div>
                <img src="/img/play.svg" alt="">
                <div>Посмотреть запись</div>
            </div>
        </a>
        <div class="webinar_main">
            <div class="webinar_date">Вебинар &middot; {{ formatDate(webinar.date) }}</div>
            <div class="webinar_title">{{webinar.title}}</div>
            <div class="webinar_description" v-html="webinar.description"></div>
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
                    <div style="position:relative" v-if="field === 'Размер портфеля'">
                        <img style="position:absolute; top:22px; right:22px; width:20px; height:12px;" src="/img/arrow-down.svg" alt="">
                        <select v-model="fields[field]" name="" id="">
                            <option value="до $100 тыс.">до $100 тыс.</option>
                            <option value="от $101 тыс. до $500 тыс.">от $101 тыс. до $500 тыс.</option>
                            <option value="от $501 тыс. до $1 млн">от $501 тыс. до $1 млн</option>
                            <option value="$1 млн+">$1 млн+</option>
                        </select>
                    </div>
                    <input v-model="fields[field]" v-else type="text" :name="field">
                </div>
                <button>Зарегистрироваться</button>
            </form>
            <div class="form_policy">
                Нажимая на кнопку, вы соглашаетесь <a href="https://abeta.org/politics" target="_blank">с политикой конфиденциальности</a>
            </div>
        </div>
        <div v-if="webinar.registered && isActual" class="webinar_registration form active">
            <div class="form_title">Вы уже записаны на вебинар</div>
            <div class="webinar_registration_info">
                <div class="webinar_registration_info_description">
                    {{ webinar.title }}
                </div>
                <div class="webinar_registration_info_date">
                    <div>{{ formatDate(webinar.date).split("·")[0] }}</div>
                    <div>{{ formatDate(webinar.date).split("·")[1] }}</div>
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
                <a :href="webinar.link" target="_blank" class="webinar_links_zoom_button"><div>Присоединиться</div></a>
            </div>
            <div class="webinar_links_calendar" v-if="!webinar.added_calendar">
                <img src="/img/calendar.svg" alt="">
                <div class="webinar_links_calendar_title">Добавьте событие в календарь, чтобы не забыть</div>
                <div class="webinar_links_calendar_button" @click="calendar" v-if="user.google_access_token"><div>Добавить в календарь</div></div>
                <a class="webinar_links_calendar_button" target="_blank" :href="googlelink" v-else><div>Привязать Google</div></a>
            </div>
        </div>
        <future-events-component :webinars="feed.upcoming_events?.filter(event => event.id !== webinar.id)"/>
        <mail-component :user="user"/>
    </div>
</template>

<style scoped>

</style>