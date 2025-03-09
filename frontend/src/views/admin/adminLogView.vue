<script>
import config from "@/assets/config.json"
import adminnav from "@/components/adminnav.vue"
import {parseLog, removeLoading} from "@/assets/utils.js";
export default {
    name: "adminLogView",
    data () {
        return {
            logs: {},
            sorttext: {
                asc: "Start from new logs",
                desc: "Start from old logs",
            },
            sort: "asc",
            datefrom: "",
            dateto: "",
            user: "",
            ip: "",
            count: 100,
            page:1,
            countpage: 1,
            type: "user",
            typetext: {
                "user": "Show users' logs",
                "admin": "Show admins' logs",
            }
        }
    },
    async mounted () {
        await this.fetchlogs ();

        // document.querySelectorAll(".admin_logs_main_filters_date>input")
        // .forEach((el) => {
        //     el.addEventListener("focus", (ev) => {
        //         this.datefrom = "xx.xx.xxxx";
        //         requestAnimationFrame(() => {
        //             el.setSelectionRange(0,0);
        //         })
        //     })
        //     el.addEventListener("keydown", (ev) => {
        //         if (!/^\d$/.test(ev.key) && ev.key !== 'Backspace') ev.preventDefault();
        //
        //     })
        // })
    },
    methods: {
        parseLog,
        async fetchlogs () {
            let url = this.type === 'user' ? config.backend + "log?" : config.backend + "adminlog?";
            url += `sort=${this.sort}`;
            if (this.datefrom) url += `&datefrom=${this.datefrom}`;
            if (this.dateto) url += `&dateto=${this.dateto}`;
            if (this.user) url += `&user=${this.user}`;
            if (this.ip) url += `&ip=${this.ip}`;
            if (this.count) url += `&limit=${this.count}`;
            if (this.page) url += `&page=${this.page}`;
            
            await fetch (url, {
                method: "GET",
                credentials: "include",
            }).then((response) => {
                if (response.status === 401) return this.$router.push({name: "adminLogin"});
                else if (!response.ok) return alert ("Error");
                return response.json();
            }).then((response) => {
                this.logs = response.data;
                this.countpage = response.count;
                removeLoading();
            })
        },
    },
    components: {
        adminnav,
    }
}
</script>

<template>
    <adminnav>
        <div class="admin_logs_main">
            <div class="admin_logs_main_filters">
                <div title="Ascending/Descending sorting (Click to change)">
                    <p>Sorting</p>
                    <div @click="sort === 'asc' ? sort = 'desc' : sort = 'asc'" class="admin_logs_main_filters_select"><p>{{sorttext[sort]}}</p></div>
<!--                    <div class="admin_logs_main_filters_select_dropdown">-->
<!--                        <div v-for="text in sorttext"-->
<!--                         :class="text === sorttext[sort] ? 'active' : ''">-->
<!--                            {{text}}-->
<!--                        </div>-->
<!--                    </div>-->
                </div>
                <div title="Date from">
                    <p>Period from</p>
                    <div class="admin_logs_main_filters_date"><input v-model="datefrom" type="date"></div>
                </div>
                <div title="Date to">
                    <p>Period to</p>
                    <div class="admin_logs_main_filters_date"><input v-model="dateto" type="date"></div>
                </div>
                <div title="User's username/surname/name or ID">
                    <p>ID or name user's</p>
                    <div class="admin_logs_main_filters_input"><input v-model="user" type="text"></div>
                </div>
                <div title="User's last IP address">
                    <p>IP-address user's</p>
                    <div class="admin_logs_main_filters_input"><input v-model="ip" type="text"></div>
                </div>
                <div title="User's last IP address">
                    <p>Logs</p>
                    <div @click="type === 'user' ? type = 'admin' : type='user'" class="admin_logs_main_filters_select"><p>{{typetext[type]}}</p></div>
                </div>
            </div>
            <div class="admin_logs_main_apply_container">
                <button title="Fetch logs" @click="fetchlogs" class="admin_logs_main_apply"><i class="fa-solid fa-filter"></i>  Apply</button>
                <p>Show <input type="number" v-model="count"> logs</p>
            </div>
        </div>
        <div class="admin_logs_table">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Action</th>
                        <th>Ip address</th>
                    </tr>
                    <tr class="admin_logs_table_subtitle">
                        <th>Date and time</th>
                        <th>Description of action</th>
                        <th>Last ip</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="log in logs">
                        <th><div :title="log.created_at">{{new Date(log.created_at).toLocaleString()}}</div></th>
                        <th><div v-html="parseLog(log, false)"></div></th>
                        <th><div :title="log.ip" class="admin_logs_table_ip">{{log.ip}}</div></th>
                    </tr>
                </tbody>
            </table>
        </div>
    </adminnav>
</template>

<style scoped>

</style>