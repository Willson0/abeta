<script>
    import AllComponent from "@/components/allComponent.vue";
    import ConsultationComponent from "@/components/consultationComponent.vue";
    import ProfileComponent from "@/components/profileComponent.vue";
    import ChatComponent from "@/components/chatComponent.vue";
    import config from "@/components/config.json";
    import EventComponent from "@/components/eventComponent.vue";
    import AnalyticFeedComponent from "@/components/analyticFeedComponent.vue";
    import ServiceComponent from "@/components/serviceComponent.vue";

    export default {
        components: {
            ServiceComponent,
            AnalyticFeedComponent,
            EventComponent, ChatComponent, ProfileComponent, ConsultationComponent, AllComponent},
        data () {
            return {
                tg: "",
                selectedCategory: "Все",
                user: {},
                feed: {},
                timer: false,
            }
        },
        provide() {
            return {
                user: this.provideuser,
            }
        },
        computed: {
            provideuser() {
                return this.user;
            }
        },
        async mounted () {
            document.body.style.backgroundColor = "#F3F4F6";
            this.tg = window.Telegram.WebApp;
            this.tg.expand();

            const nav = document.querySelector(".main>.nav");
            window.addEventListener("scroll", () => {
                if (this.timer) return;
                // alert (nav.getBoundingClientRect().top);
                const scrollTop = window.scrollY || document.documentElement.scrollTop;
                const clientHeight = document.documentElement.clientHeight;
                const scrollHeight = document.documentElement.scrollHeight;
                if (nav.getBoundingClientRect().top <= 10 || scrollTop + clientHeight >= scrollHeight-5) {
                    const secondRow = document.querySelector(".main>.nav>div:last-child");
                    const top = secondRow.clientHeight;
                    const left = document.querySelector(".main>.nav>div:first-child").scrollWidth;

                    const oldstatus = nav.classList.contains("active");
                    nav.classList.add("active");
                    if (!oldstatus)
                    requestAnimationFrame(() => {
                        this.timer = true;
                        secondRow.style.position = "fixed";
                        secondRow.style.top = top + 24 + "px";
                        secondRow.style.left = "16px"
                        secondRow.style.transition = "1s";

                        requestAnimationFrame(() => {
                            secondRow.style.top = "16px";
                            secondRow.style.left = (left+24) + "px";
                            setTimeout(() => {
                                secondRow.style.position = "";
                                secondRow.style.top = "";
                                secondRow.style.left = "";
                                secondRow.style.transition = "";
                                this.timer = false;
                            }, 1000);
                        })
                    })
                }
                else {
                    const secondRow = document.querySelector(".main>.nav>div:last-child");
                    const top = secondRow.clientHeight;
                    const left = document.querySelector(".main>.nav>div:first-child").clientWidth;
                    const firstRow = document.querySelector(".main>.nav>div:first-child");

                    const oldstatus = nav.classList.contains("active");
                    nav.classList.remove("active");

                    if (oldstatus)
                        requestAnimationFrame(() => {
                            this.timer = true;
                            // nav.style.height = (nav.getBoundingClientRect().height + 8 + secondRow.getBoundingClientRect().height) + "px";
                            secondRow.style.position = "absolute";
                            secondRow.style.top = "16px";
                            secondRow.style.left = (left) + "px";
                            secondRow.style.transition = "1s";

                            firstRow.style.height = (2*firstRow.scrollHeight+8) + "px"

                            requestAnimationFrame(() => {
                                secondRow.style.top = top + 24 + "px";
                                secondRow.style.left = "16px"
                                setTimeout(() => {
                                    firstRow.style.height = "";
                                    secondRow.style.position = "";
                                    secondRow.style.top = "";
                                    secondRow.style.left = "";
                                    secondRow.style.transition = "";
                                    this.timer = false;
                                    // nav.style.height = ""
                                }, 1000);
                            })
                    });
                }
            }, { passive: true })

            try {
                await fetch (config.backend + "profile", {
                    method: "POST",
                    body: JSON.stringify({"initData": window.Telegram.WebApp.initData}),
                    headers: {
                        "Content-Type": "application/json",
                    }
                }).then((response) => {
                    alert (response.status);
                    return response.json();
                }).then((response) => {
                    this.user = response;
                    alert (this.user);
                    if (!this.user) {
                        let el = document.querySelector(".forbiddenPopup");
                        alert (el.style.display);
                        el.style.display = "flex";
                        alert (el.style.display);
                    }
                });

                await fetch (config.backend + "feed/all", {
                    method: "GET",
                }).then((response) => {
                    return response.json();
                }).then((response) => {
                    this.feed = response;
                });
            } catch {

            }
        },
        methods: {

        }
    }
</script>

<template>
    <div class="forbiddenPopup" style="display:none"><div>Для продолжения пройдите регистрацию в боте (/start).</div></div>
    <div class="main">
        <div class="header">
            <div class="header_logo">
                <img src="/img/logo.svg" alt="" @click="selectedCategory = 'Все'">
            </div>
            <div class="header_profile">
                <img @click="selectedCategory = 'Профиль'" :src="tg.initDataUnsafe?.user.photo_url" alt="">
                <div class="header_profile_notification"></div>
            </div>
        </div>
        <div class="nav" style="position:sticky">
            <div>
                <div @click="selectedCategory = category"
                     :class="selectedCategory === category ? 'active' : ''"
                     v-for="category in ['Все', 'Ивенты', 'Аналитика', 'Услуги']">
                    <div v-if="category === 'Консультация'" class="nav_notification"></div>
                    <p>{{category}}</p>
                </div>
            </div>
            <div>
                <div @click="selectedCategory = category"
                     :class="selectedCategory === category ? 'active' : ''"
                     v-for="category in ['Консультация', 'Чат', 'Профиль']">
                    <div v-if="category === 'Консультация'" class="nav_notification"></div>
                    <p>{{category}}</p>
                </div>
            </div>
        </div>
        <all-component :user="user" :feed="feed" v-if="selectedCategory === 'Все'"/>
        <consultation-component :user="user" v-if="selectedCategory === 'Консультация'"/>
        <profile-component @updateUser="(newuser) => user = newuser" :user="user" v-if="selectedCategory === 'Профиль'"/>
        <chat-component :user="user" v-if="selectedCategory === 'Чат'"/>
        <event-component :user="user" :feed="feed" v-if="selectedCategory === 'Ивенты'"/>
        <analytic-feed-component :user="user" :feed="feed" v-if="selectedCategory === 'Аналитика'"/>
        <service-component :user="user" :feed="feed" v-if="selectedCategory === 'Услуги'"/>
    </div>
</template>

<style scoped>

</style>