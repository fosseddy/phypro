<script setup>
//export default {
//    data() {
//        return {
//            tablename: "",
//            items: []
//        };
//    },

//    methods: {
//        updateStorage() {
//            localStorage.setItem("phypro-items", JSON.stringify(this.items));
//        },

//        createTable() {
//            this.tablename = this.tablename.trim();
//            if (!this.tablename.length) return;

//            const item = {
//                id: Math.random().toString(36).slice(2),
//                name: this.tablename,
//                table: []
//            };

//            for (let day = 1; day <= 31; day++) {
//                item.table.push({
//                    day,
//                    weightval: "",
//                    weight: false,
//                    workout: false,
//                    supplement: false
//                });
//            }

//            this.items = [item, ...this.items];
//            this.updateStorage();

//            this.tablename = "";
//        },

//        setItemValue(item, key, val) {
//            if (!Object.hasOwn(item, key)) {
//                return console.warn("item", item, "does not have key", key);
//            }

//            item[key] = val;
//            this.updateStorage();
//        },

//        deleteItem(item) {
//            if (!confirm("Are you sure?")) return;
//            this.items = this.items.filter(it => it.id !== item.id);
//            this.updateStorage();
//        },

//        validateWeightValue(event) {
//            const re = /^[1-9]{1}([0-9]{1})?$/;
//            const input = event.target;
//            if (!re.test(input.value)) {
//                input.value = input.value.slice(0, -1);
//            }
//        },

//        changeWeightValue(event, item) {
//            item.weightval = event.target.value;
//            this.setItemValue(item, "weight", item.weightval.length > 0);
//        }
//    },

//    created() {
//        const items = localStorage.getItem("phypro-items");
//        if (!items) return;

//        try {
//            this.items = JSON.parse(items);
//        } catch {
//            console.warn("invalid json in local storage");
//            console.log(items);
//        }
//    }
//};
import * as Vue from "vue";

const name = Vue.ref("world");
</script>

<template>
    <h1>hello, {{name}}</h1>
    <input v-model="name">
</template>

<!--template>
<form @submit.prevent="createTable">
    <input placeholder="name..." v-model="tablename" />
</form>

<div v-if="items.length" class="items">
    <div v-for="it in items" class="items__item" :key="it.id">
        <button class="item__delete-btn btn btn--danger box"
                @click="deleteItem(it)"
        >
            &#215;
        </button>
        <div class="table">
            <h3>{{ it.name }}</h3>
            <div class="table__item-container">
                <div v-for="t in it.table" class="table__item" :key="t.day">
                    <input class="box"
                           :class="{ 'weight--active': t.weight }"
                           :value="t.weightval"
                           @input="validateWeightValue"
                           @change="changeWeightValue($event, t)"
                    />
                    <button class="btn box"
                            :class="{ 'supplement--active': t.supplement }"
                            @click="setItemValue(t, 'supplement', !t.supplement)"
                    >
                    </button>
                    <button class="btn box"
                            :class="{ 'workout--active': t.workout }"
                            @click="setItemValue(t, 'workout', !t.workout)"
                    >
                        {{ t.day }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</template-->

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
