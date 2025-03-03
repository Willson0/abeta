<script>
export default {
    name: "consultationExpertsComponent"
}
</script>

<template>
    <div class="feed_consultation">
        <div class="feed_consultation_photos">
            <img v-for="(img, key) in ['/img/avatar1.png', '/img/avatar2.png', '/img/avatar3.png']"
                 :src="img" alt="" :style="'right: ' + 8*key + 'px'">
        </div>
        <div class="feed_consultation_title">
            Бесплатная консультация с экспертами ABETA
        </div>
        <div class="feed_consultation_description">
            Расширим возможности, подберем инструменты и инвестиционную стратегию
        </div>
        <button class="feed_consultation_button">Выбрать удобное время</button>
    </div>
</template>

<style scoped>

</style>