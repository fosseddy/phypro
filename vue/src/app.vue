<script setup>

import * as Vue from "vue";

const name = Vue.ref("");
const months = Vue.ref([]);
const error = Vue.ref("");
const loading = Vue.ref(false);

(async () => {
    try {
        let res = await fetch("/api/month");

        res = await res.json();

        if (res.error) {
            error.value = res.error.message;
        } else {
            months.value = res.data.items;
        }
    } catch (err) {
        console.error(err);
        error.value = "something went wrong, but it is not your fault";
    }
})();

async function createMonth() {
    error.value = "";
    const n = name.value.trim();

    if (!n) {
        error.value = "month name is required";
        return;
    }

    loading.value = true;

    try {
        let res = await fetch("/api/month", {
            method: "POST",
            headers: { "content-type": "application/json" },
            body: JSON.stringify({ name: n })
        });

        res = await res.json();

        if (res.error) {
            error.value = res.error.message;
            return;
        }

        months.value.unshift(res.data);

        name.value = "";
    } catch (err) {
        console.error(err);
        error.value = "something went wrong, but it is not your fault";
    } finally {
        loading.value = false;
    }
}

async function deleteMonth(m) {
    loading.value = true;

    try {
        let res = await fetch(`/api/month?id=${m.id}`, { method: "DELETE" });
        res = await res.json();

        if (res.error) {
            error.value = res.error.message;
            return;
        }

        months.value = months.value.filter(it => it.id !== m.id);
    } catch (err) {
        console.error(err);
        error.value = "something went wrong, but it is not your fault";
    } finally {
        loading.value = false;
    }
}

</script>



<template>

<form @submit.prevent="createMonth">
    <input v-model="name">
    <button type="submit" :disabled="loading">{{loading ? "Loading..." : "Create"}}</button>
</form>

<p v-if="error">{{error}}</p>

<div v-if="months.length" class="items">
    <div v-for="it in months" class="items__item" :key="it.id">
        <button class="item__delete-btn btn btn--danger box" @click="deleteMonth(it)">
            &#215;
        </button>
        <div class="table">
            <h3>{{ it.name }}</h3>
            <div class="table__item-container">
                <div v-for="t in it.days" class="table__item" :key="t.id">
                    <input class="box"
                           title="weight"
                           :class="{ 'weight--active': t.weight > 0 }"
                           :value="t.weight > 0 || ''"
                    />
                    <button class="btn box"
                            title="supplement"
                            :class="{ 'supplement--active': !!t.supplement }"
                    >
                    </button>
                    <button class="btn box"
                            title="workout"
                            :class="{ 'workout--active': !!t.workout }"
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
    width: 21px;
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
