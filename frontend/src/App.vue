<script setup>
export default {
    mounted () {
        window.Telegram.WebApp.expand();
        window.Telegram.WebApp.disableVerticalSwipes();
    }
}
</script>

<template>
  <router-view/>
</template>

<style scoped>
</style>
