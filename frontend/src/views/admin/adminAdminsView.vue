<script>
import adminnav from "@/components/adminnav.vue";
import config from "@/components/config.json"
import {removeLoading} from "@/assets/utils.js";
export default {
    name: "adminAdminsView.vue",
    data () {
        return {
            admins: [],
            config: config,
        }
    },
    async mounted () {
        await this.fetchadmins();
    },
    components: {
        adminnav,
    },
    methods: {
        async fetchadmins() {
            await fetch (config.backend + "admin?limit=10000", {
                method: "GET",
                credentials: "include"
            }).then((response) => {
                if (response.status === 401) return this.$router.push({name: "adminLogin"});
                else if (!response.ok) return alert ("error");
                return response.json();
            }).then((response) => {
                this.admins = response.data;
                removeLoading();
            })
        }
    }
}
</script>

<template>
    <adminnav>
        <div class="admin_admins_main">
            <div v-for="admin in admins">
                <div class="admin_admins_main_el_header">
                    <div class="admin_admins_main_el_header_profile">
                        <div class="admin_admins_main_el_header_profile_avatar">
                            <img :src="config.storage + admin.avatar" alt="">
                            <div v-if="new Date(admin.entry_date+'Z') >= Date.now()"></div>
                        </div>
                        <p>{{admin.login}}</p>
                        <div v-if="new Date(admin.entry_date+'Z') >= Date.now()" class="admin_admins_main_el_header_online">Online</div>
                        <div v-else class="admin_admins_main_el_header_online offline">Offline</div>
                    </div>
                    <div class="admin_admins_main_el_header_options">
                        <i title="Actions" class="fa-solid fa-ellipsis-vertical"></i>
                    </div>
                </div>
                <hr>
                <div class="admin_admins_main_el_main">
                    <div>
                        <div>
                            <div class="admin_admins_main_el_main_title">Avatar</div>
                            <p><a :title="config.storage+admin.avatar" :href="config.storage+admin.avatar">Link</a></p>
                        </div>
                        <div>
                            <div class="admin_admins_main_el_main_title">Last action</div>
                            <p :title="admin.lastaction">{{ new Date(admin.lastaction).toLocaleString() }}</p>
                        </div>
                        <div>
                            <div class="admin_admins_main_el_main_title">Admin ID</div>
                            <p>{{ admin.id }}</p>
                        </div>
                    </div>
                    <div>
                        <div title="Count of actions for this day">
                            <div class="admin_admins_main_el_main_title">Actions today</div>
                            <p>{{ admin.actionsperday }}</p>
                        </div>
                        <div>
                            <div class="admin_admins_main_el_main_title">Added at</div>
                            <p :title="admin.created_at">{{ new Date(admin.created_at).toLocaleString() }}</p>
                        </div>
                        <div>
                            <div class="admin_admins_main_el_main_title">Telegram ID</div>
                            <p>{{admin.telegram_id}}</p>
                        </div>
                    </div>
                    <div>
                        <div>
                            <div class="admin_admins_main_el_main_title">Chat with</div>
                            <p v-if="!admin.chat_with">None</p>
                            <p v-else><a :title="`[${admin.chat_with.id}] ${admin.chat_with.name} ${admin.chat_with.surname}`" :href="'/admin/users/' + admin.chat_with.id">{{ admin.chat_with.username }}</a></p>
                        </div>
                        <div>
                            <div class="admin_admins_main_el_main_title">Last seen at</div>
                            <p :title="admin.entry_date">{{new Date(admin.entry_date).toLocaleString()}}</p>
                        </div>
                        <div title="Count of actions for the all time">
                            <div class="admin_admins_main_el_main_title">Actions all time</div>
                            <p>{{admin.actions}}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </adminnav>
</template>

<style scoped>

</style>