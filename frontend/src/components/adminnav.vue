<script>
import config from "@/components/config.json"
export default {
    data() {
        return {
            admin: {},
            config,
        }
    },
    async mounted() {
        // document.body.style.backgroundColor = "#14141e"
        // document.querySelectorAll(".adminnav_main_nav_main>div>p").forEach((el)=>{
        //     el.addEventListener("mouseenter", (ev) => ev.stopPropagation());
        // })

        let nav = document.querySelector(".adminnav_main_nav");
        nav.addEventListener("mouseenter", (ev) => {
            nav.classList.add("active");
        });

        nav.addEventListener("mouseleave", () => {
            nav.classList.remove("active");
        });

        nav.style.width = nav.clientWidth + 'px';

        let accountmenu = document.querySelector(".adminnav_buttons_account_menu");
        document.addEventListener('click', (event) => {
            if (!accountmenu.parentElement.contains(event.target) && accountmenu.classList.contains("active")) {
                accountmenu.classList.remove("active");
            }
        });
        document.body.style.backgroundColor = "#12121c";

        await fetch (config.backend + "admin/profile", {
            method: "GET",
            credentials: "include",
        }).then((response) => {
            if (response.status === 401) return this.$router.push({name: "adminlogin"});
            return response.json();
        }).then((response) => {
            this.admin = response;
            console.log(response);
        });
    },
    methods: {
        showaccount () {
            document.querySelector(".adminnav_buttons_account_menu").classList.toggle("active");
        },
        showmenu () {
            document.querySelector(".adminnav_main_nav").classList.toggle("active");
        },
        async logout () {
            await fetch (config.backend + "admin/logout", {
                method: "POST",
                credentials: "include",
            }).then((response) => {
                if (!response.ok) return alert ("Error");
                this.$router.push("/");
            })
        }
    }
}
</script>

<template>
    <div class="notifyContainer"></div>
    <div class="loadPopup">
        <p>Loading...</p>
    </div>
<div class="adminnav">
    <header class="adminnav_header">
        <div class="adminnav_slidebar">
            <i @click="showmenu()" class="fa-solid fa-list"></i>
            <div class="adminnav_title">
                {{ $route.meta.h }}
            </div>
        </div>
        <div class="adminnav_buttons">
<!--            <i class="fa-solid fa-magnifying-glass"></i>-->
<!--            <i class="fa-solid fa-envelopes-bulk"></i>-->
<!--            <div class="adminnav_buttons_account">-->
<!--                <img @click="showaccount()" :src="config.storage + admin.avatar" alt="">-->
<!--                <div class="adminnav_buttons_account_menu">-->
<!--                    <div class="adminnav_buttons_account_menu_main_triangle"></div>-->
<!--                    <div class="adminnav_buttons_account_menu_main">-->
<!--                        <div>-->
<!--                            <div class="adminnav_buttons_account_menu_main_button">-->
<!--                                <p>Profile</p>-->
<!--                            </div>-->
<!--                            <div class="adminnav_buttons_account_menu_main_button">-->
<!--                                <p>Settings</p>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                        <div class="adminnav_buttons_account_menu_main_line"></div>-->
<!--                        <div @click="logout()" class="adminnav_buttons_account_menu_main_button">-->
<!--                            <p>Logout</p>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
        </div>
    </header>
    <div class="adminnav_main">
        <nav class="adminnav_main_nav">
            <div class="adminnav_main_nav_background"></div>
            <div class="adminnav_main_nav_website">
                <div class="adminnav_main_nav_website_logo">
                    <img src="/img/logo.svg" alt="">
                </div>
                <p>Abeta</p>
            </div>
            <div class="adminnav_main_nav_line"></div>
            <div class="adminnav_main_nav_main">
                <div @click="$router.push({'name': 'admin'})">
                    <div v-if="$route.name === 'admin'" class="adminnav_main_nav_main_el_point">&middot;</div>
                    <i class="fa-solid fa-code-branch"></i>
                    <p>Dashboard</p>
                </div>
                <div @click="$router.push({'name': 'servicesAdmin'})">
                    <div v-if="$route.name === 'ordersAdmin'" class="adminnav_main_nav_main_el_point">&middot;</div>
                    <i class="fa-solid fa-cart-shopping"></i>
                    <p>Services</p>
                </div>
                <div @click="$router.push({'name': 'productsAdmin'})">
                    <div v-if="$route.name === 'productsAdmin'" class="adminnav_main_nav_main_el_point">&middot;</div>
                    <i class="fa-solid fa-seedling"></i>
                    <p>Webinars</p>
                </div>
                <div @click="$router.push({'name': 'analyticsAdmin'})">
                    <div v-if="$route.name === 'analyticsAdmin'" class="adminnav_main_nav_main_el_point">&middot;</div>
                    <i class="fa-solid fa-seedling"></i>
                    <p>Analytics</p>
                </div>
                <div @click="$router.push({'name': 'mailingAdmin'})">
                    <div v-if="$route.name === 'mailingAdmin'" class="adminnav_main_nav_main_el_point">&middot;</div>
                    <i class="fa-solid fa-cart-shopping"></i>
                    <p>Mailing</p>
                </div>
            </div>
        </nav>
        <div class="adminnav_main_main">
            <slot></slot>
        </div>
    </div>
</div>
</template>

<style scoped>

</style>