<script>
import config from "@/components/config.json"
import adminnav from "@/components/adminnav.vue";
import {formatDate, notify, removeLoading, togglePopup} from "@/assets/utils.js";
export default {
    name: "adminView",
    data () {
        return {
            taskselected: [],
            data: {},
            perf: "accounts",
            tasks: [],
            task: -1,
            updtask: {},
        };
    },
    components: {
        adminnav,
    },
    computed: {
    },
    methods: {
        notify,
        formatDate,
        togglePopup,
        canvinit() {
            let canv = document.querySelector(".admin_main_subscriptions canvas");

            canv.height = canv.parentElement.clientHeight;
            canv.width = canv.parentElement.clientWidth;

            let ctx = canv.getContext("2d");

            let months = ["JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC"];
            let values = this.data[this.perf];

            let minvalue = Math.floor(Math.min(...values) / 10) * 10;
            let maxvalue = Math.ceil(Math.max(...values) / 10) * 10;
            let countvalues = maxvalue/10 - minvalue/10 + 1;

            ctx.strokeStyle = "#4c4c71";
            ctx.lineWidth = 1;
            ctx.font = "10px Poppins";
            ctx.fillStyle="white";
            ctx.textAlign = "right";

            let valuesColumn = 30;
            let margin = 10;

            for (let i = 0; i < countvalues; i ++) {
                let y = margin + 14 + 5 + ((canv.height-(margin+14+5)*2 - 20)/(countvalues-1)*(i));
                ctx.fillText(maxvalue - i*10, valuesColumn, y);
            }

            ctx.textAlign = "center";
            ctx.fillStyle = "red";
            let pointarr = [];
            for (let i = 0; i < months.length; i++) {
                let month = months[i];
                let x = (canv.width/months.length)*(i) + valuesColumn*2;

                ctx.beginPath();
                ctx.moveTo(x, margin);
                ctx.lineTo(x, canv.height-40);
                ctx.closePath();
                ctx.stroke();

                ctx.fillStyle = "white";
                ctx.fillText(month, x, canv.height-20);

                ctx.fillStyle = "#389466";
                let pointy = margin + 14 + ((canv.height-(margin+14+5)*2-20)/(countvalues-1))*((maxvalue-values[i])/10);
                ctx.arc(x, pointy, 4, 0, 2*Math.PI)
                ctx.fill();

                pointarr.push({
                    x: x,
                    y: pointy
                });
            }

            ctx.strokeStyle = "#389466";
            ctx.lineWidth = 1;

            for (let i = 0; i < pointarr.length-1; i++) {
                ctx.beginPath();
                ctx.moveTo(pointarr[i].x, pointarr[i].y);
                ctx.lineTo(pointarr[i+1].x, pointarr[i+1].y);
                ctx.stroke();
            }

            canv.addEventListener("mousemove", (ev) => {
                let gapcolumn = pointarr[1].x - pointarr[0].x;
                for (let i = 0; i < pointarr.length; i++) {
                    if (Math.abs((ev.clientX-canv.getBoundingClientRect().x)-pointarr[i].x)<gapcolumn/2) {
                        let info = document.querySelector(".admin_main_subscription_info_container");
                        info.style.top = pointarr[i].y + 'px';
                        info.style.left = pointarr[i].x + 'px';
                        info.querySelector(".admin_main_subscription_info").innerHTML = values[i];

                        info.style.opacity = 1;
                    }
                }
            });

            canv.addEventListener("mouseleave", (ev) => {
                let info = document.querySelector(".admin_main_subscription_info_container");
                info.style.opacity = '';
            })
        },
        showinfo (cl, text) {
            document.querySelectorAll(`.${cl}`).forEach((el) => {
                let info;

                el.addEventListener("mouseenter", () => {
                    let child = document.body.appendChild(document.createElement("div"));
                    child.innerHTML = `
                    <div class="admin_main_tasks_el_edit_info">
                        <div class="admin_main_tasks_el_edit_info_triangle"></div>
                        <div class="admin_main_tasks_el_edit_info_main">
                            ${text}
                        </div>
                    </div>
                `;
                    let div = child.querySelector(".admin_main_tasks_el_edit_info")
                    div.style.top = `${el.getBoundingClientRect().top + window.scrollY-30}px`;
                    div.style.left = `${el.getBoundingClientRect().left+el.clientWidth/2}px`;

                    div.style.opacity = 1;
                    info = div;
                });

                el.addEventListener("mouseleave",() => {
                    info.style.opacity = 0;
                    setTimeout(() => {
                        info.remove();
                    }, 200)
                })
            })
        },
        async fetchstats() {
            await fetch (config.backend + "stats", {
                method: "GET",
                credentials: "include",
            }).then((response) => {
                if (response.status === 401) this.$router.push({name: "adminLogin"})
                if (!response.ok) return alert ("Error");
                return response.json();
            }).then((response) => {
                this.data = response;
                removeLoading();

                this.canvinit();
            });
        },
        async fetchtasks() {
            await fetch (config.backend + "task", {
                method: "GET",
                credentials: "include",
            }).then((response) => {
                if (!response.ok) return alert ("error");
                return response.json();
            }).then((response) => {
                this.tasks = response;
            })
        },
        async deletetask () {
            await fetch (config.backend + `task/${this.updtask.id}`, {
                method: "DELETE",
                credentials: "include",
            }).then((response) => {
                if (!response.ok) return alert ("error");

                this.fetchtasks();
                this.notify(`Task №${this.updtask.id} successfully deleted!`)
                this.togglePopup("admin_main_tasks_popup");
            })
        },
        async savetask() {
            this.updtask.deadline = new Date(this.updtask.deadline)
            await fetch (config.backend + `task/${this.updtask.id}`, {
                method: "POST",
                credentials: "include",
                body: JSON.stringify(this.updtask),
                headers: {
                    "Content-type": "application/json",
                }
            }).then((response) => {
                if (!response.ok) return alert ("error");

                this.fetchtasks();
                this.notify(`Task №${this.updtask.id} successfully updated!`)
                this.togglePopup("admin_main_tasks_popup");
            })
        },
        async completeTasks () {
            await fetch (config.backend + "task/complete", {
                method: "POST",
                credentials: "include",
                body: JSON.stringify({"tasks": this.taskselected}),
                headers: {
                    "Content-type": "application/json",
                }
            }).then((response) => {
                if (!response.ok) return alert ("error");

                this.fetchtasks();
                this.notify(`Tasks ${this.taskselected} successfully completed!`)
                this.taskselected = [];
            })
        },
        async createtask () {
            if (!this.updtask.title) return alert ("Insert 'TITLE' please");
            if (!this.updtask.task) return alert ("Insert 'DESCRIPTION' please");
            if (!this.updtask.deadline) return alert ("Insert 'DEADLINE' please");
            if (new Date(this.updtask.deadline) < new Date()) return alert ("Now date is bigger than deadline!");

            await fetch (config.backend + 'task', {
                method: "POST",
                credentials: "include",
                body: JSON.stringify(this.updtask),
                headers: {
                    "Content-type": "application/json",
                }
            }).then((response) => {
                if (!response.ok) return alert ("Error");

                this.fetchtasks();
                this.togglePopup("admin_main_tasks_popup");
                this.notify("New Task successfully created!")
            })
        },
        toOnlyText (html) {
            return html.replace(/\s*style\s*=\s*["'][^"']*["']/gi, '');
        }
    },
    async mounted() {
        window.addEventListener("resize", this.canvinit);

        this.showinfo("fa-rotate-right", "reload");
        this.showinfo("admin_main_tasks_el_edit", "Edit task");
        this.showinfo("fa-x", "Remove");

        document.body.style.backgroundColor = "#12121c";

        await this.fetchstats();
    }
}
</script>

<template>
    <div class="admin_main_tasks_popup popup">
        <div>
            <h2 v-if="task !== -1"><i class="fa-solid fa-pen"></i>&nbsp;Edit task #{{updtask.id}}</h2>
            <h2 v-else>New task</h2>
            <div>
                <label>
                    Title:
                    <input type="text" v-model="updtask.title">
                </label>
            </div>
            <div>
                <label>
                    Desciription:
                    <input type="text" v-model="updtask.task">
                </label>
            </div>
            <div>
                <label>
                    Deadline:
                    <input type="date" v-model="updtask.deadline">
                </label>
            </div>
            <div class="admin_main_tasks_popup_buttons">
                <button v-if="task !== -1" @click="deletetask()" style="background-color:transparent; border: 1px solid rgba(255,255,255,0.1)">Delete task</button>
                <button v-if="task !== -1" @click="savetask()">Save task</button>
                <button v-else @click="createtask()">Create task</button>
            </div>
        </div>
    </div>
    <adminnav>
        <div class="admin_main_subscriptions">
            <div class="admin_main_subscription_header">
                <div class="admin_main_subscription_title">
                    <h2>Пользователи</h2>
                    <h1>Эффективность</h1>
                </div>
            </div>
            <div class="admin_main_subscriptions_canvas">
                <canvas>Your browser is not supported canvas.</canvas>
                <div class="admin_main_subscription_info_container">
                    <div class="admin_main_subscription_info_triangle"></div>
                    <div class="admin_main_subscription_info"></div>
                </div>
            </div>
        </div>
        <div class="admin_main_statistics">
            <div>
                <div class="admin_main_statistics_el_main">
                    <div style="background:linear-gradient(45deg, #3abd2a, #0e880a);" class="admin_main_statistics_el_main_img">
                        <i class="fa-solid fa-money-bill"></i>
                    </div>
                    <div class="admin_main_statistics_el_main_title">
                        <h4>Вебинары</h4>
                        <h3>{{ data.money }} ивентов</h3>
                    </div>
                </div>
                <div class="admin_main_statistics_el_footer">
                    <div class="admin_main_statistics_el_footer_line"></div>
                    <div class="admin_main_statistics_el_footer_info">
                        <i class="fa-regular fa-calendar"></i>
                        За последние 30 дней
                    </div>
                </div>
            </div>
            <div>
                <div class="admin_main_statistics_el_main">
                    <div style="background:linear-gradient(45deg, #3abd2a, #0e880a);" class="admin_main_statistics_el_main_img">
                        <i class="fa-solid fa-cash-register"></i>
                    </div>
                    <div class="admin_main_statistics_el_main_title">
                        <h4>Аналитики</h4>
                        <h3>{{ data.money }} материалов</h3>
                    </div>
                </div>
                <div class="admin_main_statistics_el_footer">
                    <div class="admin_main_statistics_el_footer_line"></div>
                    <div class="admin_main_statistics_el_footer_info">
                        <i class="fa-regular fa-calendar"></i>
                        За последние 30 дней
                    </div>
                </div>
            </div>
            <div>
                <div class="admin_main_statistics_el_main">
                    <div class="admin_main_statistics_el_main_img">
                        <div></div>
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="admin_main_statistics_el_main_title">
                        <h4>Услуги</h4>
                        <h3>{{ data.usersPerDay }} пользователей</h3>
                    </div>
                </div>
                <div class="admin_main_statistics_el_footer">
                    <div class="admin_main_statistics_el_footer_line"></div>
                    <div class="admin_main_statistics_el_footer_info">
                        <i class="fa-solid fa-rotate-right"></i>
                        За последние 30 дней
                    </div>
                </div>
            </div>
            <div>
                <div class="admin_main_statistics_el_main">
                    <div class="admin_main_statistics_el_main_img">
                        <div></div>
                        <i class="fa-regular fa-message"></i>
                    </div>
                    <div class="admin_main_statistics_el_main_title">
                        <h4>Венчурные сделки</h4>
                        <h3>{{ data.logsPerDay }} пользователей</h3>
                    </div>
                </div>
                <div class="admin_main_statistics_el_footer">
                    <div class="admin_main_statistics_el_footer_line"></div>
                    <div class="admin_main_statistics_el_footer_info">
                        <i class="fa-solid fa-rotate-right"></i>
                        За последние 30 дней
                    </div>
                </div>
            </div>
        </div>
        <div class="admin_main_tasks_management">
            <div class="admin_main_tasks">
                <div class="admin_main_tasks_header">
                    <div class="admin_main_tasks_title">
                        <h1>Топ популярности</h1>
                        <h2>Материалы</h2>
                    </div>
                </div>
                <div class="admin_main_tasks_main">
                    <div @click="$router.push('/admin/analytics/' + el.id)" v-for="el in data.analytics">
                        <div class="admin_main_tasks_el_text">
                            <div class="admin_main_tasks_el_title">
                                <span>{{ el.title }}</span>
                            </div>
                            <div v-html="toOnlyText(el.description)" class="admin_main_tasks_el_description">
                            </div>
                        </div>
                        <div class="admin_main_tasks_el_edit">
                            <i class="fa-solid fa-pen"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="admin_main_tasks">
                <div class="admin_main_tasks_header">
                    <div class="admin_main_tasks_title">
                        <h1>Топ популярности</h1>
                        <h2>Ивенты</h2>
                    </div>
                </div>
                <div class="admin_main_tasks_main">
                    <div @click="$router.push('/admin/webinars/' + el.id)" v-for="el in data.webinars">
                        <div class="admin_main_tasks_el_text">
                            <div class="admin_main_tasks_el_title">
                                <span>{{ el.title }}</span>
                            </div>
                            <div v-html="toOnlyText(el.description)" class="admin_main_tasks_el_description">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </adminnav>
</template>

<style scoped>

</style>