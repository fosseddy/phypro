<script setup>

import * as Vue from "vue";
import * as http from "@/http.js";

const name = Vue.ref("");
const months = Vue.ref([]);
const error = Vue.ref(null);
const loading = Vue.ref(false);

(async () => {
    const data = await http.useApi(http.month.fetchAll, loading, error);
    if (!error.value) months.value = data.items;
})();

async function createMonth() {
    const n = name.value.trim();
    if (!n) return;

    const data = await http.useApi(() => http.month.create(n), loading, error);
    if (error.value) return;

    months.value.unshift(data);
    name.value = "";
}

async function deleteMonth(id) {
    await http.useApi(() => http.month.remove(id), loading, error);
    if (error.value) return;

    months.value = months.value.filter(it => it.id !== id);
}

async function updateDayValue(day, key, val) {
    if (!Object.hasOwn(day, key)) {
        console.error("day", day, "does not have key", key);
        return;
    }

    await http.useApi(
        () => http.day.update(day.id, { [key]: val }),
        loading,
        error
    );
    if (error.value) return;

    day[key] = val;
}

function validateWeightValue(event) {
    // TODO(art): validate value
}

async function updateWeightValue(event, day) {
    const val = Number(event.target.value);

    if (isNaN(val)) {
        console.error("weight input value is NaN, somehow validation failed");
        console.log(event);
        return;
    }

    await updateDayValue(day, "weight", val * 1000);
}

</script>



<template>

<form @submit.prevent="createMonth">
    <input v-model="name">
    <button type="submit">Create</button>
</form>

<div style="min-height: 2rem;">
    <p v-if="loading">Loading...</p>
    <p v-if="error">{{error.message}}</p>
</div>

<div v-if="months.length" class="items">
    <div v-for="it in months" class="items__item" :key="it.id">

        <button class="item__delete-btn btn btn--danger box" @click="deleteMonth(it.id)">
            &#215;
        </button>

        <div class="table">
            <h3>{{ it.name }}</h3>

            <div class="table__item-container">
                <div v-for="t in it.days" class="table__item" :key="t.id">
                    <input class="box"
                           title="weight"
                           :class="{ 'weight--active': t.weight > 0 }"
                           :value="t.weight > 0 ? t.weight / 1000 : ''"
                           @input="validateWeightValue"
                           @change="updateWeightValue($event, t)"
                    />

                    <button class="btn box"
                            title="supplement"
                            :class="{ 'supplement--active': !!t.supplement }"
                            @click="updateDayValue(t, 'supplement', 1 - t.supplement)"
                    >
                    </button>

                    <button class="btn box"
                            title="workout"
                            :class="{ 'workout--active': !!t.workout }"
                            @click="updateDayValue(t, 'workout', 1 - t.workout)"
                    >
                        {{ t.value }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

</template>



<style scoped>

form {
    margin-bottom: 3rem;
}

.items {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.items__item {
    display: flex;
    gap: 1rem;
}

.item__delete-btn {
    align-self: start;
}

.table {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.table__item-container {
    display: flex;
    gap: 1px;
}

.table__item {
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.box {
    border: 1px solid black;
    width: 31px;
    height: 21px;
    text-align: center;
    transition: all .2s ease-in-out;
}

.box:hover {
    transform: scale(1.2);
}

.btn {
    background: white;
    cursor: pointer;
}

.btn--danger:hover {
    background: lightcoral;
}

.weight--active {
    background: thistle;
}

.supplement--active {
    background: moccasin;
}

.workout--active {
    background: lightgreen;
}

</style>



<style>

* {
    padding: 0;
    margin: 0;
    box-sizing: border-box;
}

html, body, #app { height: 100%; }

#app {
    display: flex;
    flex-direction: column;
    align-items: center;
}

</style>
