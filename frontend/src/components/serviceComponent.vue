<script>
import config from "@/components/config.json"
export default {
    name: "serviceComponent",
    props: {
        feed: {
            type: Object,
            required: true,
        }
    },
    data () {
        return {
            config: config,
            selectedService: {},
        }
    },
    async mounted () {
        let popup = document.querySelector (".services_popup");
        popup.addEventListener("click", (ev) => {
            if (ev.target.classList.contains("services_popup")) this.closePopup();
        });
    },
    methods: {
        popup (serv) {
            this.selectedService = serv;
            let popup = document.querySelector(".services_popup");
            popup.style.display = "flex";
            requestAnimationFrame(() => popup.classList.add("active"));
        },
        closePopup () {
            let popup = document.querySelector(".services_popup");
            popup.classList.remove("active");
            setTimeout(() => popup.style.display = "", 200);
        }
    }
}
</script>

<template>
    <div class="services_popup">
        <div class="services_popup_main">
            <div class="services_popup_header">
                <div class="services_popup_header_title">Услуга</div>
                <img @click="closePopup" src="/img/close.svg" alt="">
            </div>
            <div class="services_popup_title">{{selectedService.title}}</div>
            <div class="services_popup_description">{{selectedService.description}}</div>
            <button @click="closePopup" class="services_popup_button">{{selectedService.button}}</button>
        </div>
    </div>
    <div class="services">
        <div :style="'background-color: #' + service.color" v-for="service in feed.services" class="service">
            <img src="/img/example_service.svg" alt="">
            <div class="service_title">{{service.title}}</div>
            <div class="service_overview">{{service.overview}}</div>
            <button class="service_button" @click="popup(service)" :style="'color: #' + service.color">Подробнее</button>
        </div>
    </div>
</template>

<style scoped>

</style>