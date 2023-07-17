<script setup>

import * as Vue from "vue";
import * as http from "@/http.js";

const name = Vue.ref("");
const months = Vue.ref([]);
const pagination = Vue.ref(null);

fetchMonths(1);

async function fetchMonths(page) {
    const res = await http.month.fetchAll(page);
    if (res.error) {
        console.log(res.error);
        return;
    }

    months.value = res.data.items;
    pagination.value = {
        index: res.data.page_index,
        total: res.data.page_total
    };
}

async function createMonth() {
    const n = name.value.trim();
    if (!n) return;

    const res = await http.month.create(n);
    if (res.error) {
        console.log(res.error);
        return;
    }

    await fetchMonths(1);
    name.value = "";
}

async function deleteMonth(id) {
    if (!confirm("Are you sure?")) return;

    const res = await http.month.remove(id);
    if (res.error) {
        console.log(res.error);
        return;
    }

    await fetchMonths(pagination.value.index);
}

async function updateDayValue(day, key, val) {
    if (!Object.hasOwn(day, key)) {
        console.error("day", day, "does not have key", key);
        return;
    }

    const res = await http.day.update(day.id, { [key]: val });

    if (res.error) {
        console.log(res.error);
        return;
    }

    day[key] = res.data[key];
}

async function updateWeightValue(event, day) {
    let val = event.target.value;
    let match = false;

    if (val === "") {
        match = true;
    } else {
        match = /^[1-9](\d(\.\d)?)?$/.test(val);
    }

    if (!match) {
        event.target.value = (day.weight / 1000) || "";
        return;
    }

    val = Number(val);
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

<ul v-if="pagination?.total > 1" class="pagination">
    <li v-for="page in pagination.total" :key="page">
        <button class="btn box"
                :class="{'pagination--current': page === pagination.index}"
                @click="pagination.index != page && fetchMonths(page)"
        >
            {{page}}
        </button>
    </li>
</ul>

<div v-if="months.length" class="items">
    <div v-for="it in months" class="items__item" :key="it.id">

        <button class="item__delete-btn btn btn--danger box" @click="deleteMonth(it.id)">
            &#215;
        </button>

        <div class="table">
            <h3>{{it.name}}</h3>

            <div class="table__item-container">
                <div v-for="t in it.days" class="table__item" :key="t.id">
                    <input class="box"
                           title="weight"
                           maxlength="4"
                           :class="{'weight--active': t.weight > 0}"
                           :value="t.weight > 0 ? t.weight / 1000 : ''"
                           @change="updateWeightValue($event, t)"
                    />

                    <button class="btn box"
                            title="supplement"
                            :class="{'supplement--active': !!t.supplement}"
                            @click="updateDayValue(t, 'supplement', 1 - t.supplement)"
                    >
                    </button>

                    <button class="btn box"
                            title="workout"
                            :class="{'workout--active': !!t.workout}"
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

.box:hover,
.box:focus-visible {
    transform: scale(1.2);
}

.btn {
    background: white;
    cursor: pointer;
}

.btn--danger:hover,
.btn--danger:focus {
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

.pagination {
    display: flex;
    list-style: none;
    margin-bottom: 2rem;
}

.pagination--current {
    background: lightsteelblue;
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
