<script>
import config from "@/components/config.json"
import adminnav from "@/components/adminnav.vue";
import {addClickScroll, parseLog, removeLoading} from "@/assets/utils.js"

export default {
    name: "adminUserIndexView",
    data () {
        return {
            userid: -1,
            user: {},
            year: 2025,
        }
    },
    async mounted () {
        this.userid = this.$route.params.id;
        await this.fetchUser();
        document.querySelector(".adminnav_main_main").style.padding="0 40px";

        document.querySelectorAll(".admin_indexuser_otherinfo_orders_table_el_products_container")
            .forEach((el) => addClickScroll(el))

        this.checkBlur();
        window.addEventListener("resize", () => {
            this.checkBlur();
        });

    },
    components: {
        adminnav,
    },
    methods: {
        parseLog,
        checkBlur () {
            let scrolls = document.querySelectorAll(".admin_indexuser_otherinfo_orders_table_el_products_container");

            scrolls.forEach((scroll) => {
                scroll.style.padding = "";
                scroll.parentElement
                    .querySelectorAll(".admin_indexuser_otherinfo_orders_table_el_products_blur")
                    .forEach((blur) => blur.style.display = "");

                if (scroll.clientWidth < scroll.scrollWidth) {
                    // scroll.style.padding = "0 20px";
                    scrolls.forEach((el) => el.style.padding = "0 20px");
                    scroll.parentElement
                        .querySelectorAll(".admin_indexuser_otherinfo_orders_table_el_products_blur")
                        .forEach((blur) => blur.style.display = "block");
                }
            })
        },
        async fetchUser () {
            await fetch(config.backend + `user/${this.userid}`, {
                method: "GET",
                credentials: "include",
            }).then((response) => {
                if (response.status === 401) return this.$router.push({"name": 'adminlogin'});
                else if (response.status === 404) return this.$router.push({"name": 'admin'});
                else if (!response.ok) return alert ("Произошла непредвиденная ошибка. Обратитесь к разработчику");
                return response.json();
            }).then((response) => {
                this.user = response;

                document.title = `ExoBloom | ${this.user.name} ${this.user.surname}`;

                for (let log of this.user.logs) {
                    let logDate = new Date(log.created_at);

                    let el = document.querySelector(`div[month="${logDate.getMonth()}"][date="${logDate.getDate()}"]`);
                    el.setAttribute("count", Number(el.getAttribute("count"))+1);

                    document.querySelectorAll(".admin_indexuser_activity_cell").forEach((cell) => {
                        let count = cell.getAttribute("count");
                        if (count != 0) cell.style.backgroundColor = `rgba(22, 255, 212,${count/10}`;
                    })
                }
                removeLoading();
            });

        },
        formatDate(dateString) {
            const date = new Date(dateString);
            const months = [
                'янв', 'фев', 'мар', 'апр', 'мая', 'июн',
                'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'
            ];

            const day = date.getDate();
            const month = months[date.getMonth()];
            const year = date.getFullYear();
            const hour = String(date.getHours()).padStart(2, '0');
            const minute = String(date.getMinutes()).padStart(2, '0');

            return `${day} ${month} ${year} год, ${hour}:${minute}`
        },
        getWeeksInMonth(year, month) {
            const firstDay = new Date(year, month - 1, 1);
            const lastDay = new Date(year, month, 0);

            const firstDayOfWeek = firstDay.getDay();
            const lastDayOfWeek = lastDay.getDay();

            const daysInMonth = lastDay.getDate();

            const offset = (firstDayOfWeek === 0 ? 6 : firstDayOfWeek - 1);
            let weeks = Math.ceil((daysInMonth+offset) / 7);

            if (lastDayOfWeek === 0) weeks ++;
            return weeks;
        },
        getShortMonthName(month, year) {
            const date = new Date(year, month - 1); // month - 1, т.к. месяцы начинаются с 0
            return date.toLocaleString('en-US', { month: 'short' });
        },
        getDateFromWeekAndDay(year, week, day) {
            let firstDayOfYear = new Date(year, 0, 1);
            const firstMonday = new Date(firstDayOfYear.setDate(1 + (1 - firstDayOfYear.getDay() + 7) % 7));

            let date = new Date(firstMonday);
            date.setDate(firstMonday.getDate() + (week - 1) * 7 + (day-1));
            return date;
        },
        deletepopup () {
            let popup = document.querySelector(".admin_indexuser_popup_delete");
            if (popup.style.display === "") {
                popup.style.display = "block";
                document.body.style.overflow = "hidden";

                popup.addEventListener("click", (ev) => {
                    if (ev.target.className === "admin_indexuser_popup_delete") {
                        popup.style.display = "";
                        document.body.style.overflow = "";
                    }
                });
            } else {
                popup.style.display = "";
                document.body.style.overflow = "";
            }
        },
        async deleteuser() {
            await fetch (config.backend + "user/" + this.user.id, {
                method: "delete",
                credentials: "include",
            }).then((response) => {
                if (!response.ok) return alert("Непредвиденная ошибка");
                else this.$router.push({"name": "admin"});
            });
        }
    }
}
</script>

<template>
    <div class="admin_indexuser_popup_delete">
        <div>
            <img src="../assets/img/admin_trashcan.svg" alt="">
            <h2>Are you sure you want to<br>delete this user</h2>
            <p>[{{user.id}}] {{user.name}} {{user.surname}} ({{user.telegram_id}})</p>
            <div>
                <button class="admin_indexuser_popup_delete_cancel" @click="deletepopup()">Cancel</button>
                <button class="admin_indexuser_popup_delete_delete" @click="deleteuser()">Delete</button>
            </div>
        </div>
    </div>
    <adminnav>
        <div class="admin_indexuser_main">
            <div class="admin_indexuser_profile">
                <div class="admin_indexuser_profile_avatar">
                    <img :src="user.avatar" alt="avatar">
                    <div class="admin_indexuser_profile_avatar_online" v-if="(new Date(user.entry_date+'Z')) >= (Date.now())"></div>
                </div>
                <div class="admin_indexuser_profile_text">
                    <div>
                        <h1>{{user.name}} {{user.surname}}</h1>
                        <div v-if="user.blocked_at">Blocked</div>
                    </div>
                    <h2 v-if="(new Date(user.entry_date+'Z')) >= (Date.now())">Online</h2>
                    <h2 v-else>Last seen at {{ formatDate(user.entry_date+'Z') }}</h2>
                </div>
            </div>
            <div class="admin_indexuser_buttons">
                <button @click="deletepopup()" title="Delete this user (Irreversible action)">Delete user</button>
            </div>
        </div>
        <hr class="admin_indexuser_line">
        <div class="admin_indexuser_info">
            <div>
                <div class="admin_indexuser_info_icons">
                    <i class="fa-solid fa-user"></i>
                    <i class="fa-brands fa-telegram"></i>
                    <i class="fa-solid fa-address-card"></i>
                    <i class="fa-solid fa-image"></i>
                </div>
                <div class="admin_indexuser_info_title">
                    <div>User ID</div>
                    <div>Telegram ID</div>
                    <div>Username</div>
                    <div>Avatar</div>
                </div>
                <div class="admin_indexuser_info_value">
                    <div>{{user.id}}</div>
                    <div>{{user.telegram_id}}</div>
                    <div>{{user.username}}</div>
                    <div><a :title="user.avatar" :href="user.avatar">Link</a></div>
                </div>
            </div>
            <div>
                <div class="admin_indexuser_info_icons">
                    <i style="font-weight:600" class="fa-solid fa-signature"></i>
                    <i style="font-weight:600" class="fa-solid fa-font"></i>
                    <i class="fa-solid fa-calendar-days"></i>
                    <i class="fa-solid fa-envelope"></i>
                </div>
                <div class="admin_indexuser_info_title">
                    <div>Name</div>
                    <div>Surname</div>
                    <div>User since</div>
                    <div>Email</div>
                </div>
                <div class="admin_indexuser_info_value">
                    <div>{{user.name}}</div>
                    <div>{{user.surname}}</div>
                    <div :title="user.created_at">{{formatDate(user.created_at)}}</div>
                    <div>{{ user.email }}</div>
                </div>
            </div>
        </div>
        <hr class="admin_indexuser_line">
        <div class="admin_indexuser_otherinfo_container">
            <div class="admin_indexuser_otherinfo">
                <div class="admin_indexuser_otherinfo_socialacc">
                    <div class="admin_indexuser_otherinfo_el_title">
                        <h1>Social accounts</h1>
<!--                        <h2>Manage social account</h2>-->
                    </div>
                    <div class="admin_indexuser_otherinfo_el_main">
                        <table class="admin_indexuser_otherinfo_socialacc_table">
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="admin_indexuser_otherinfo_socialacc_el_service">
                                            <img src="../assets/img/telegram.svg" alt="">
                                            <div>Telegram &middot; @{{user.username}}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div :title="user.updated_at">
                                            used {{ formatDate(user.updated_at) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div :title="user.created_at">
                                            added {{ formatDate(user.created_at) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="admin_indexuser_otherinfo_socialacc_table_button">
                                            <i class="fa-solid fa-ellipsis"></i>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="user.email">
                                    <td>
                                        <div class="admin_indexuser_otherinfo_socialacc_el_service">
                                            <img src="../assets/img/gmail.webp" alt="">
                                            <div>Email &middot; {{user.email}}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            used - days ago
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            added {{ formatDate(user.email_verified_at) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="admin_indexuser_otherinfo_socialacc_table_button">
                                            <i class="fa-solid fa-ellipsis"></i>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="admin_indexuser_otherinfo_contact">
                    <div class="admin_indexuser_otherinfo_el_title">
                        <h1>Contact</h1>
                        <!--                        <h2>Manage social account</h2>-->
                    </div>
                    <div class="admin_indexuser_otherinfo_el_main">
                        <div class="admin_indexuser_otherinfo_contact_bot">
                            <div class="admin_indexuser_otherinfo_contact_bot_acc" title="Bot account in Telegram">
                                <img src="../assets/img/exobloom_bot.webp" alt="photo">
                                <div>
                                    <h2>@ExoBloomCompanyBot</h2>
                                    <p>Official bot</p>
                                </div>
                            </div>
                            <div class="admin_indexuser_otherinfo_contact_bot_button">
                                <p>Write to user by company telegram bot</p>
                                <a target="_blank" :href="`https://t.me/exobloomcompanybot?start=${user.id}`"><button title="Write to bot"><i class="fa-brands fa-telegram"></i>Telegram</button></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="admin_indexuser_otherinfo_contact">
                    <div class="admin_indexuser_otherinfo_el_title">
                        <h1>Orders</h1>
                        <!--                        <h2>Manage social account</h2>-->
                    </div>
                    <div class="admin_indexuser_otherinfo_el_main">
                        <div class="admin_indexuser_otherinfo_orders_table">
                            <table>
                                <tbody>
                                    <tr v-for="order in user.orders">
                                        <td :title="order.id">#{{ order.id }}</td>
                                        <td :title="order.created_at">{{ formatDate(order.created_at) }}</td>
                                        <td class="admin_indexuser_otherinfo_orders_table_el_products_td">
                                            <div class="admin_indexuser_otherinfo_orders_table_el_products_blur blurleft"></div>
                                            <div class="admin_indexuser_otherinfo_orders_table_el_products_container">
<!--                                                    <div class="admin_indexuser_otherinfo_orders_table_el_products_stub"></div>-->
                                                    <a :href="'/admin/products/' + product.product.id" :title="`[${product.product.id}] ${product.product.name} (x${product.count})`" v-for="product in order.products">{{ product.product.name }}<p>x{{product.count}}</p></a>
<!--                                                    <div class="admin_indexuser_otherinfo_orders_table_el_products_stub"></div>-->
                                            </div>
                                            <div class="admin_indexuser_otherinfo_orders_table_el_products_blur blurright"></div>
                                        </td>
                                        <td>
                                            <div class="admin_indexuser_otherinfo_orders_table_el_paid"
                                                :class="order.paid_at !== null ? 'paid' : ''"
                                                :title="order.paid_at !== null ? formatDate(order.paid_at) : ''">
                                                {{ order.paid_at !== null ? 'paid' : 'unpaid' }}
                                            </div>
                                        </td>
                                        <td>${{ order.cost }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="admin_indexuser_otherinfo_cart">
                    <div class="admin_indexuser_otherinfo_el_title">
                        <h1>Cart</h1>
                        <!--                        <h2>Manage social account</h2>-->
                    </div>
                    <div class="admin_indexuser_otherinfo_el_main">

                    </div>
                </div>
                <div class="admin_indexuser_otherinfo_logs">
                    <div class="admin_indexuser_otherinfo_el_title">
                        <h1>Logs</h1>
                        <!--                        <h2>Manage social account</h2>-->
                    </div>
                    <div class="admin_indexuser_otherinfo_el_main">
                        <div v-for="log in user.logs" v-html="`<p>${parseLog(log)}</p>`">
                        </div>
                    </div>
                </div>
            </div>
            <div class="admin_indexuser_activity">
                <div class="admin_indexuser_activity_title">
                    <h2>User activity</h2>
                    <div>
                        <p>2024</p>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                </div>
                <hr>
                <table>
                    <thead>
                        <tr>
                            <th></th>
                            <th>M</th>
                            <th>T</th>
                            <th>W</th>
                            <th>S</th>
                            <th>F</th>
                            <th>S</th>
                            <th>S</th>
                        </tr>
                    </thead>
                    <tbody v-for="month in 12">
                            <tr>
                                <td style="padding:0 10px 0 0">{{ getShortMonthName(month, year) }}</td>
                                <td v-for="cell in 7"><div class="admin_indexuser_activity_cell" count="0"
                                :date="getDateFromWeekAndDay(year, (month-1)*(getWeeksInMonth(year, month)-1), cell).getDate()"
                                :month="getDateFromWeekAndDay(year, (month-1)*(getWeeksInMonth(year, month)-1), cell).getMonth()"
                                :title="formatDate(getDateFromWeekAndDay(year, (month-1)*(getWeeksInMonth(year, month)-1), cell))">
                                </div></td>
                            </tr>
                            <tr v-for="week in (getWeeksInMonth(year, month)-2)">
                                <td><div> </div></td>
                                <td v-for="cell in 7"><div class="admin_indexuser_activity_cell" count="0"
                                :date="getDateFromWeekAndDay(year, week+(month-1)*(getWeeksInMonth(year, month)-1), cell).getDate()"
                                :month="getDateFromWeekAndDay(year, week+(month-1)*(getWeeksInMonth(year, month)-1), cell).getMonth()"
                                :title="formatDate(getDateFromWeekAndDay(year, (month-1)*(getWeeksInMonth(year, month)-1), cell))"></div></td>
                            </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </adminnav>
</template>

<style scoped>

</style>