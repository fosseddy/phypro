<script setup>

import * as Vue from "vue";

const error = Vue.ref(null);

Vue.onErrorCaptured((err, vm, info) => {
    console.log("this is error captured");
    console.error(err);
    error.value = err;

    return false;
});

window.addEventListener("unhandledrejection", (event) => {
    console.log("this is unhandled rejection event");
    error.value = event.reason;
});

</script>



<template>

<template v-if="error">
    <h1>Something went wrong</h1>
    <p>Reload the page and try again</p>
</template>

<slot v-else></slot>

</template>



<style scoped>
</style>
