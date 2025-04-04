<script>
import SupportComponent from "@/components/blocks/supportComponent.vue";
import config from "@/components/config.json"

export default {
    name: "profileComponent",
    components: {SupportComponent},
    props: {
        user: {
            required: true,
        }
    },
    data () {
        return {
            tg: {}
        }
    },
    async mounted () {
        this.tg = window.Telegram.WebApp;
    },
    methods: {
        async toggleNotifications(isEnable) {
            await fetch (config.backend + "auth", {
                method: "POST",
                body: JSON.stringify({"initData": window.Telegram.WebApp.initData,
                    "notifications": isEnable}),
                headers: {
                    "Content-Type": "application/json"
                }
            }).then((response) => {
                return response.json();
            }).then((response) => {
                this.$emit("updateUser", response);
            })
        },
        async sendData () {
            if (!/^[А-ЯЁ][а-яё]+(?: [А-ЯЁ][а-яё]+){0,2}$/.test(this.user.fullname))
                this.notify("Неправильный формат ФИО!", 1);
            if (this.user.phone && !/^\+?[1-9]\d{1,14}$/.test(this.user.phone))
                this.notify("Неправильный формат номера телефона!", 1);

            await fetch (config.backend + "auth", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    "initData": window.Telegram.WebApp.initData,
                    "fullname": this.user.fullname,
                    "phone": this.user.phone,
                    "bio": document.querySelector("#bio").innerHTML,
                })
            }).then((response) => {
                return response.json();
            }).then((response) => {
                let button = document.querySelector(".profile_main_form>button");

                this.notify("Настройки профиля успешно сохранены")
                let oldHTML = button.innerHTML;
                button.innerHTML = "Профиль обновлен";
                button.classList.add("active");

                setTimeout(() => {
                    button.innerHTML = oldHTML;
                    button.classList.remove("active");
                },5000);

                this.$emit("updateUser", response);
            })
        },
        notify (text, error) {
            let notifyContainer = document.querySelector(".notification_container");
            let div = document.createElement("div");

            if (error) {
                div.innerHTML = `<div class="notification error">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    <div>
                                        ${text}
                                    </div>
                                </div>`
            } else {
                div.innerHTML = `<div class="notification success">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <div>
                                        ${text}
                                    </div>
                                </div>`
            }
            notifyContainer.appendChild(div);

            let height = div.querySelector(".notification").getBoundingClientRect().height + 10;
            div.style.visibility = "visible";
            div.style.transform = `translateY(-${height}px)`;

            requestAnimationFrame(() => {
                div.style.transition = "0.2s";
                div.style.transform = "";
                div.style.height = height + "px";
            });

            setTimeout(() => {
                div.style.opacity = '0';
                setTimeout (() => {
                    div.remove();
                }, 200);
            }, 5000);
        },
    }
}
</script>

<template>
    <div class="notification_container">

    </div>
    <div class="profile">
        <div class="form">
            <div class="profile_main_header">
                <div class="profile_main_header_title">Профиль</div>
                <img :src="tg.initDataUnsafe?.user.photo_url" alt="">
            </div>
            <div class="profile_main_description">
                <div class="profile_main_description_text">
                    Информация используется для записи на вебинары, доступ к закрытой аналитике и чату.<br><br>Данные надежно защищены.<br>Спам не шлем
                </div>
                <img src="/img/lock.svg" alt="">
            </div>
            <form @submit.prevent="sendData" action="" class="profile_main_form">
                <div class="form_input">
                    <label for="name">Имя</label>
                    <input v-model="user.fullname" type="text" name="name">
                </div>
                <div class="form_input">
                    <label for="about">Обо мне</label>
                    <div class="form_input_textarea" id="bio" contenteditable v-html="user.bio"></div>
                </div>
                <div class="form_input">
                    <label for="phone">Телефон</label>
                    <input v-model="user.phone" type="text" name="phone">
                </div>
                <button style="background-color:#36B251">
                    Сохранить
                </button>
            </form>
        </div>
        <div :class="!user.notifications ? 'disabled' : ''" class="profile_notifications">
            <img class="profile_notifications_image" src="/img/notification.svg" alt="">
            <img class="profile_notifications_image_disabled" src="/img/notification_disabled.svg" alt="">
            <div v-if="user.notifications" class="profile_notifications_title">Уведомления включены</div>
            <div v-else class="profile_notifications_title">Уведомления отключены</div>
            <div v-if="user.notifications" class="profile_notifications_description">Напомним о вебинаре в Telegram:<br>за день и в день проведения</div>
            <div v-else class="profile_notifications_description">Не сможем напомнить о вебинаре<br>в Telegram: за день и в день проведения</div>
            <button @click="toggleNotifications(!user.notifications)" class="profile_notifications_button">{{user.notifications ? 'Отключить' : 'Включить'}}</button>
        </div>
        <support-component />
    </div>
</template>

<style scoped>

</style>
