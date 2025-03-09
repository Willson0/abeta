<script>
import adminnav from "@/components/adminnav.vue";
import config from "@/assets/config.json"
import {removeLoading} from "@/assets/utils.js";
export default {
    name: "adminUsersView",
    data () {
        return {
            dateasc:true,
            nameasc:null,
            blocked:false,
            fromdate: '',
            todate: '',
            page: 1,
            countpage: 10,
            inputpage: '...',
            selectedusers: [],
            users: [],
            totaluser: 0,
            totalblocked: 0,
            totalonline: 0,
            search: '',
        }
    },
    components: {
        adminnav,
    },
    async mounted () {
        document.body.style.backgroundColor = "#12121c";

        document.addEventListener("click", (ev) => {
            let sort = document.querySelector(".admin_users_main_header_buttons_sort_main");
            let filter = document.querySelector(".admin_users_main_header_buttons_filter_main");

            if (!sort.parentElement.contains(ev.target) && sort.classList.contains("active"))
                this.showsort();
            if (!filter.parentElement.contains(ev.target) && filter.classList.contains("active"))
                this.showfilter();
        })

        let input = document.querySelector(".admin_users_main_main_foot_paginator input");

        input.addEventListener("focus", () => {
            input.disabled = false;
            input.value = '';
        });

        input.addEventListener("blur", () => {
            if (input.value == '') return input.value = '...';
            if (input.value > this.countpage) return input.value = '...';

            this.page = Number(input.value);
            if (this.countpage - Number(this.inputpage) < 3) return this.inputpage = '...';
            this.fetchusers();
        });

        input.addEventListener('keydown', function(event) {
            if ((event.key < '0' || event.key > '9') && event.key !== 'Backspace') {
                event.preventDefault();
            }
            if (event.key === 'Enter') {
                input.blur();
            }
        });

        await this.fetchusers();
        await fetch (config.backend + "user/stats", {
            "methods" : "GET",
            credentials: 'include',
        }).then((response) => {
            if (!response.ok) return alert ("Непредвиденная ошибка. Сообщите разработчику");
            return response.json();
        }).then((response) => {
            this.totaluser = response.users;
            this.totalblocked = response.blocked;
            this.totalonline = response.online;

            if (this.users) removeLoading();
        })
    },
    methods: {
        async fetchusers() {
            let url = config.backend + 'user?limit=10';
            url += `&page=${this.page}`
            if (this.dateasc !== null) url += `&datesort=${this.dateasc?'asc':'desc'}`;
            if (this.nameasc !== null) url += `&namesort=${this.nameasc?'asc':'desc'}`;
            if (this.fromdate !== '') url += `&datefrom=${this.fromdate}`;
            if (this.todate !== '') url += `&dateto=${this.todate}`;
            if (this.blocked) url += "&blocked=true";
            if (this.search) url += `&s=${this.search}`

            await fetch(url, {
                method: "GET",
                credentials: 'include',
            }).then((response) => {
                if (response.status === 401) return this.$route.push({"name": "adminLogin"})
                else if (!response.ok)
                    return alert ("Произошла непредвиденная ошибка! Обратитесь к разработчику.");
                return response.json();
            }).then((response) => {
                this.users = response.users;
                this.countpage = response.count;

                if (this.totalonline !== 0) removeLoading();
            })
        },
        showsort () {
            let el = document.querySelector(".admin_users_main_header_buttons_sort_main");

            if (el.style.display === 'block') {
                el.classList.remove("active");
                setTimeout(() => {
                    el.style.display=""
                }, 200);
            } else {
                el.style.display = 'block';
                requestAnimationFrame(() => {
                    el.classList.add("active");
                })
            }
        },
        showfilter() {
            let el = document.querySelector(".admin_users_main_header_buttons_filter_main");

            if (el.style.display === 'block') {
                el.classList.remove("active");
                setTimeout(() => {
                    el.style.display=""
                }, 200);
            } else {
                el.style.display = 'block';
                requestAnimationFrame(() => {
                    el.classList.add("active");
                })
            }
        },
        selectall() {
            let headercheckbox = document.querySelector('.admin_users_main_main_table_checkbox');
            let allcheckbox = document.querySelectorAll('.admin_users_main_main_table_checkbox');
            if (!headercheckbox.classList.contains("active")) {
                this.selectedusers = [];
                this.users.forEach((user) => this.selectedusers.push(user.id));
            }
            else
                this.selectedusers = [];

        //     TODO: with backend change all.
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
        async changepage (page) {
            if (page <= 0 || page > this.countpage) return;
            if (page > 3 && (this.countpage-2) > page) {
                this.inputpage = `${page}`;
                this.page = page;
            } else {
                this.page = page;
                this.inputpage = '...';
            }
            await this.fetchusers();
        },
        selectcheckbox (ev, id) {
            let el = ev.target.closest('.admin_users_main_main_table_checkbox');
            if (el.classList.contains("active")) {
                this.selectedusers = this.selectedusers.filter((el)=>el !== id);
            } else {
                this.selectedusers.push(id);
            }
        },
        async downloadexport() {
            await fetch(config.backend + "user/export", {
                    method: "GET",
                    credentials: "include",
                }).then ((response) => {
                    if (!response.ok) return alert("Непредвиденная ошибка. Сообщите разработчику");
                    return response.blob();
                }).then((response) => {
                    let url = window.URL.createObjectURL(response);
                    console.log(url);

                    let a = document.createElement('a');
                    a.href = url;
                    a.download = "users.xls";
                    document.body.appendChild(a);
                    a.click();
                    a.remove();

                    window.URL.revokeObjectURL(url);
            })
        }
    }
}
</script>

<template>
    <adminnav>
        <div class="admin_users_statistics">
            <div>
                <h1>{{ totaluser }}</h1>
                <p>Total users</p>
            </div>
            <div>
                <h1>{{ totalblocked }}</h1>
                <p>Total blocked</p>
            </div>
            <div>
                <h1>{{ totalonline }}</h1>
                <p>Online</p>
            </div>
        </div>
        <div class="admin_users_main">
            <div class="admin_users_main_header">
                <div class="admin_users_main_header_search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input @input="fetchusers()" v-model="search" placeholder="Search by name" type="text">
                </div>
                <div class="admin_users_main_header_buttons">
                    <div class="admin_users_main_header_buttons_sort">
                        <div @click="showsort()" class="admin_users_main_header_buttons_el_title">
                            <i class="fa-solid fa-sort"></i> <p>Sort by</p>
                        </div>
                        <div class="admin_users_main_header_buttons_sort_main">
                            <h4>Sort by</h4>
                            <hr>
                            <h5>Date</h5>
                            <div>
                                <div @click="dateasc = true; nameasc=null" :class="dateasc ? 'active' : ''">
                                    <div></div>
                                    <p>Ascending</p>
                                </div>
                                <div @click="dateasc = false; nameasc=null" :class="dateasc === false ? 'active' : ''">
                                    <div></div>
                                    <p>Descending</p>
                                </div>
                            </div>
                            <hr>
                            <h5>Name</h5>
                            <div>
                                <div @click="nameasc = true; dateasc=null" :class="nameasc ? 'active' : ''">
                                    <div></div>
                                    <p>A-Z</p>
                                </div>
                                <div @click="nameasc = false; dateasc=null" :class="nameasc === false ? 'active' : ''">
                                    <div></div>
                                    <p>Z-A</p>
                                </div>
                            </div>
                            <hr>
                            <div class="admin_users_main_header_buttons_sort_buttons">
                                <button @click="nameasc=null; dateasc=true">Reset</button>
                                <button @click="showsort(); fetchusers()">Apply now</button>
                            </div>
                        </div>
                    </div>
                    <div class="admin_users_main_header_buttons_filter">
                        <div @click="showfilter()" class="admin_users_main_header_buttons_el_title">
                            <i class="fa-solid fa-filter"></i> Filter
                        </div>
                        <div class="admin_users_main_header_buttons_filter_main">
                            <h4>Filter by</h4>
                            <hr>
                            <div class="admin_users_main_header_buttons_filter_main_title">
                                <h5>Date range</h5>
                                <p @click="fromdate = ''; todate = ''">Reset</p>
                            </div>
                            <div class="admin_users_main_header_buttons_filter_main_date">
                                <div>
                                    <p>From:</p>
                                    <input v-model="fromdate" type="date">
                                </div>
                                <div>
                                    <p>To:</p>
                                    <input v-model="todate" type="date">
                                </div>
                            </div>
                            <hr>
                            <div class="admin_users_main_header_buttons_filter_main_title">
                                <h5>Status</h5>
                                <p @click="blocked=false">Reset</p>
                            </div>
                            <div>
                                <div @click="blocked = false" :class="!blocked ? 'active' : ''">
                                    <div></div>
                                    <p>Normal</p>
                                </div>
                                <div @click="blocked = true" :class="blocked ? 'active' : ''">
                                    <div></div>
                                    <p>Blocked</p>
                                </div>
                            </div>
                            <hr>
                            <div class="admin_users_main_header_buttons_sort_buttons">
                                <button @click="nameasc=true; dateasc=true">Reset</button>
                                <button @click="showfilter(); fetchusers()">Apply now</button>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div @click="downloadexport()" class="admin_users_main_header_buttons_el_title">
                            <i class="fa-solid fa-file-excel"></i> Export
                        </div>
                    </div>
                </div>
            </div>
            <div class="admin_users_main_main">
                <table class="admin_users_main_main_table">
                    <thead>
                        <tr>
                            <th><div :class="selectedusers.length === users.length ? 'active' : ''" @click="selectall()" class="admin_users_main_main_table_checkbox"><i class="fa-solid fa-check"></i></div></th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Surname</th>
                            <th>Username</th>
                            <th>Avatar</th>
                            <th>Email</th>
                            <th>Email Verified</th>
                            <th>Blocked</th>
                            <th>Registered at</th>
                        </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(user, key) in users">
                        <th><div @click="selectcheckbox($event, user.id)" :class="selectedusers.includes(user.id) ? 'active' : ''" class="admin_users_main_main_table_checkbox"><i class="fa-solid fa-check"></i></div></th>
                        <th>{{ user.id }}</th>
                        <th><a target="_blank" :href="'/admin/users/' + user.id">{{ user.name ? user.name : '?' }}</a></th>
                        <th>{{ user.surname ? user.surname : '?'}}</th>
                        <th>{{ user.username ? user.username : '?' }}</th>
                        <th><a target="_blank" :href="user.avatar">link</a></th>
                        <th>{{ user.email ? user.email : '-' }}</th>
                        <th>{{ user.email_verified_at ? formatDate(user.email_verified_at) : '-' }}</th>
                        <th>
                            <div :class="user.blocked_at ? 'blocked' : ''" class="admin_users_main_main_table_blocked">
                                <div></div>
                                <p v-if="user.blocked_at">Blocked</p>
                                <p v-else>Unblocked</p>
                            </div>
                        </th>
                        <th>{{ user.created_at ? formatDate(user.created_at) : '-' }}</th>
                    </tr>
                    </tbody>
                </table>
                <div class="admin_users_main_main_foo">
                    <div @click="changepage(page-1)" class="admin_users_main_main_foot_button">
                        <i class="fa-solid fa-arrow-left"></i>
                        Previous
                    </div>
                    <div class="admin_users_main_main_foot_paginator" v-if="countpage > 6">
                        <div @click="changepage(i)" v-for="i in 3" :class="i === page ? 'active' : ''">{{i}}</div>
                        <input v-model="inputpage" :class="Number(inputpage) === page ? 'active' : ''" class="admin_users_main_main_foot_paginator_input">
                        <div @click="changepage(i)" v-for="i in [countpage-2, countpage-1, countpage]" :class="i === page ? 'active' : ''">{{i}}</div>
                    </div>
                    <div class="admin_users_main_main_foot_paginator" v-else>
                        <div @click="changepage(i)" v-for="i in countpage" :class="i === page ? 'active' : ''">{{i}}</div>
                    </div>
                    <div @click="changepage(page+1)" class="admin_users_main_main_foot_button">
                        Next
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </div>
            </div>
        </div>
    </adminnav>
</template>

<style scoped>

</style>