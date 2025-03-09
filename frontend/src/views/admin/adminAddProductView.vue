<script>
import config from "@/components/config.json"
import adminnav from "@/components/adminnav.vue";
import {togglePopup, removeLoading, addTitle} from "@/assets/utils.js";
export default {
    name: "adminAddProductView.vue",
    data () {
        return {
            description: "",
            name: "",
            link: "",
            date: "",
            tags: [3,10,50],
            images: [],
            categories: [],
            selectCategories: [],
            chooseCategory: 0,
            selection: null,
            categorySearch: "",
            fields: [],
            selectedFields: [],
        }
    },
    async mounted () {
        this.updImgs();
        addTitle("admin_addproduct_popup_category_main tbody tr td:last-child", "Check");

        this.fetchCategories();
    },
    methods: {
        togglePopup,
        async fetchCategories () {
            let query = config.backend + "admin/fields";
            await fetch (query, {
                method: "GET",
                credentials: "include",
            }).then((response) => {
                if (!response.ok) return alert ("error");
                return response.json();
            }).then((response) => {
                this.fields = response;
                removeLoading();
            })
        },
        formatText () {
            const selected = window.getSelection();
            if (!selected.isCollapsed) {
                let range = selected.getRangeAt(0);

                let div = document.createElement("div");
                div.className = "formatmenu"
                div.innerHTML = `
                    <div class="bold"><i class="fa-solid fa-bold"></i></div>
                    <div class="italic"><i class="fa-solid fa-italic"></i></div>
                    <div class="underline"><i class="fa-solid fa-underline"></i></div>
                `

                const rect = range.getBoundingClientRect();
                div.style.top = window.scrollY + rect.top + "px";
                div.style.left = window.scrollX + rect.left + "px";

                document.body.appendChild(div);
                const walker = document.createTreeWalker(range.commonAncestorContainer, NodeFilter.SHOW_ELEMENT,null);

                let bold = document.querySelector(".formatmenu .bold");
                let italic =  document.querySelector(".formatmenu .italic");
                let underline =  document.querySelector(".formatmenu .underline");

                while (walker.nextNode()) {
                    console.log (walker.currentNode.tagName);
                    if (walker.currentNode.tagName === "B") {
                        bold.classList.add("active");
                    } else if (walker.currentNode.tagName === "I") {
                        italic.classList.add("active");
                    } else if (walker.currentNode.tagName === "U") {
                        underline.classList.add("active");
                    }
                }

                bold.addEventListener("click", (ev) => {
                    this.restoreSelection();
                    document.execCommand("bold", false, null);
                });
                italic.addEventListener("click", (ev) => {
                    this.restoreSelection();
                    document.execCommand("italic", false, null);
                })
                underline.addEventListener("click", (ev) => {
                    this.restoreSelection();
                    document.execCommand("underline", false, null);
                });

                let isdown = false;
                document.querySelector(".formatmenu").addEventListener("mousedown", () => {
                    isdown = true;
                })

                document.querySelector(".formatmenu").addEventListener("mouseup", () => {
                    isdown = false;
                })

                document.addEventListener("selectionchange", (ev) => {
                    if (!isdown) div.remove();
                })
            }
        },
        async sendData() {
            let formData = new FormData();

            if (!this.name) return alert ("Insert 'NAME' please");
            if (!this.link) return alert ("Insert 'Ссылка' please");
            if (!this.date) return alert ("Insert 'Дата' please");
            if (new Date(this.date) <= new Date()) return alert ("Дата должна быть новее сегодняшней");
            if (this.images.length === 0) return alert ("Count of images must be bigger 0!");
            if (this.selectedFields === 0) return alert ("Количество полей должно быть больше 0");

            formData.append("title", this.name);
            formData.append("description", document.querySelector(".admin_addproduct_main_textarea").innerHTML);
            formData.append("link", this.link);
            formData.append("date", this.date);
            formData.append("image", this.images[0]);
            this.selectedFields.forEach((el) => formData.append("fields[]", el));

            await fetch (config.backend + "webinar", {
                method: "POST",
                credentials: "include",
                body: formData,
            }).then((response) => {
                if (response.status === 401) return this.$router.push({name: "adminlogin"});
                else if (!response.ok) return alert ("Error");
                return response.json();
            }).then((response) => {
                this.$router.push("/admin/webinars/" + response.id);
            });
        },
        addimg (ev) {
            let file = ev.target.files[0];
            if (file && file.type.startsWith("image/")) {
                this.images.push(file);
                this.updImgs();
            }
        },
        updImgs() {
            const olddiv = document.querySelector("#img0");
            if (olddiv) olddiv.remove();

            this.images.forEach((el, index) => {
                let div = document.createElement("div");
                div.id = `img${index}`
                div.style.display = "flex";
                div.style.flexDirection = index % 2 === 0 ? "row" : "column";

                let half = "calc(50% - 3px)";

                div.style.width = index % 2 === 1 ? half : "100%";
                div.style.height = index % 2 === 0 ? half : "100%";

                if (index === 0) {
                    div.style.maxWidth = "66%";
                    div.style.width = "100%";
                    div.style.maxHeight = "100%";
                    div.style.height = "100%";
                }

                let img = document.createElement("img");

                let url = URL.createObjectURL(new Blob([el], {type: el.type}));
                img.src = url;

                img.style.width = index % 2 === 0 ? half : "100%";
                img.style.height = index % 2 === 1 ? half : "100%";

                if (index === this.images.length-1) {
                    img.style.height = "100%";
                    img.style.width = "100%";
                }

                // if (index === this.imgs.length-2) div.style.gap = "0";

                div.appendChild(img);

                let parent;
                if (index===0) parent = document.querySelector(".admin_addproduct_main_images");
                else parent = document.querySelector(`#img${index-1}`);

                parent.appendChild(div);
            });

            for (let img in this.images) {
                document.querySelector(`#img${img}>img`).addEventListener("click", () => {
                    this.images.splice(img, 1);
                    this.updImgs();
                })
            }
        },
        changeSelectCategory (id) {
            if (this.selectCategories.find(el => el.category === id)) return;

            if (this.chooseCategory === -1)
                return this.selectCategories.push({
                    category: id,
                    tag: -1,
                })

            this.selectCategories[this.chooseCategory].category = id;
            this.selectCategories[this.chooseCategory].tag = -1;
        },
        changeSelectTag (id) {
            if (!this.categories.categories
            .find(el => el.id === this.selectCategories[this.chooseCategory].category)
            .tags.find(el => el.id === id) && chooseCategory !== -1)
                    return alert("Invalid tag");


            this.selectCategories[this.chooseCategory].tag = id;
        },
        ondragover(ev) {
            ev.preventDefault();
        },
        drop (ev) {
            ev.preventDefault();
            const files = ev.dataTransfer.files;

            if (files.length > 0) {
                for (let file of files) {
                    if (file.type.startsWith("image/"))
                        this.images.push(file);
                    else console.log ("Push IMAGE file please")
                }
                this.updImgs();
            }
        },
        discard() {
            Object.assign(this.$data, this.$options.data.call(this));
            this.updImgs();
        },
        saveSelection () {
            const selection = window.getSelection();
            if (selection.rangeCount > 0) {
                this.selection = selection.getRangeAt(0);
            }
        },
        restoreSelection () {
            const selection = window.getSelection();
            if (this.selection) {
                selection.removeAllRanges();
                selection.addRange(this.selection);
            }
        }
    },
    components: {
        adminnav,
    }
}
</script>

<template>
    <div class="popup admin_addproduct_popup_category">
        <div>
            <div class="admin_addproduct_popup_category_input">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input v-model="categorySearch" @input="fetchCategories()" placeholder="Search..." type="text">
            </div>
            <div v-if="categories.popular && !categorySearch" class="admin_addproduct_popup_category_main">
                <div class="admin_addproduct_popup_category_main_recent">
                    <h3>Recent</h3>
                    <table class="admin_addproduct_popup_category_table">
                        <tbody>
                            <tr @click="changeSelectCategory(category.id); togglePopup('admin_addproduct_popup_category')"
                                v-show="!selectCategories.find(el => el.category === category.id)"
                                v-for="category in categories.recent.slice(0,4)">
                                <td>{{ category.name }}</td>
                                <td class="admin_addproduct_popup_category_tags"><div>{{ category.tags.length }} Tags</div></td>
                                <td><i class="fa-solid fa-ellipsis-vertical"></i></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="admin_addproduct_popup_category_main_recent">
                    <h3>Popular</h3>
                    <table class="admin_addproduct_popup_category_table">
                        <tbody>
                        <tr @click="changeSelectCategory(category.id); togglePopup('admin_addproduct_popup_category')"
                            v-for="category in categories.popular.slice(0,10)"
                            v-show="!selectCategories.find(el => el.category === category.id)">
                            <td>{{ category.name }}<span title="Usages"> x{{ category.usage_count }}</span></td>
                            <td class="admin_addproduct_popup_category_tags"><div>{{ category.tags.length }} Tags</div></td>
                            <td><i class="fa-solid fa-ellipsis-vertical"></i></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div v-else class="admin_addproduct_popup_category_main">
                <div class="admin_addproduct_popup_category_main_recent">
                    <h3>Search</h3>
                    <table class="admin_addproduct_popup_category_table">
                        <tbody>
                        <tr @click="changeSelectCategory(category.id); togglePopup('admin_addproduct_popup_category')"
                            v-show="!selectCategories.find(el => el.category === category.id)"
                            v-for="category in categories.categories">
                            <td>{{ category.name }}</td>
                            <td class="admin_addproduct_popup_category_tags"><div>{{ category.tags.length }} Tags</div></td>
                            <td><i class="fa-solid fa-ellipsis-vertical"></i></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div v-if="categories.categories" class="popup admin_addproduct_popup_tag">
        <div v-if="selectCategories[chooseCategory]">
            <div v-if="categories.categories.find(el => el.id === selectCategories[chooseCategory].category)">
                <div class="admin_addproduct_popup_tag_main">
                    <h3>Tags of {{categories.categories.find(el => el.id === selectCategories[chooseCategory].category).name}} ({{selectCategories[chooseCategory].category}})</h3>
                    <div>
                        <div @click="changeSelectTag(tag.id); togglePopup('admin_addproduct_popup_tag')"
                             v-for="tag in categories.categories.find(el => el.id === selectCategories[chooseCategory].category).tags">
                            {{tag.name}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <adminnav>
        <div class="admin_addproduct_main">
            <div>
                <div>
                    <h2>Description</h2>
                    <div>
                        <div>
                            <h3>Product Name</h3>
                            <input v-model="name" type="text">
                        </div>
                        <div>
                            <h3>Business Description</h3>
                            <div @mouseup="saveSelection(); formatText()" class="admin_addproduct_main_textarea"
                                 spellcheck="false" contenteditable="true" @keyup="saveSelection()">
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <h2>Дата</h2>
                    <div>
                        <div>
                            <h3>Дата</h3>
                            <input v-model="date" type="datetime-local">
                        </div>
                    </div>
                </div>
                <div>
                    <h2>Поля заполнения</h2>
                    <div class="admin_addproduct_fields">
                        <div @click="selectedFields.includes(field) ? selectedFields = selectedFields.filter((a) => a !== field) : selectedFields.push(field)"
                             :class="selectedFields.includes(field) ? 'active' : ''" v-for="field in fields">{{field}}</div>
                    </div>
                </div>
            </div>
            <div>
                <div>
                    <h2>Product Images</h2>
                    <div class="admin_addproduct_main_images">

                        <label v-if="images.length === 0" class="admin_addproduct_main_addimage" @drop="drop" @dragover="ondragover">
                            <input @change="addimg" type="file" accept="image/*" alt="">
                            <div>
                                <i class="fa-regular fa-image"></i>
                                <p>Click to upload or drag and drop</p>
                            </div>
                        </label>

                    </div>
                </div>
                <div>
                    <h2>Ссылка</h2>
                    <div>
                        <div>
                            <h3>Ссылка на вебинар</h3>
                            <input v-model="link">
                        </div>
                    </div>
                </div>
                <div class="admin_addproduct_main_buttons">
                    <button @click="discard">Discard</button>
                    <button @click="sendData" class="admin_addproduct_main_buttons_add">Add webinar</button>
                </div>
            </div>
        </div>
    </adminnav>
</template>

<style scoped>

</style>