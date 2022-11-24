<script>
export default {
    data() {
        return {
            tablename: "",
            items: []
        };
    },

    computed: {
    },

    methods: {
        createTable() {
            this.tablename = this.tablename.trim();
            if (!this.tablename.length) return;

            const item = {
                name: this.tablename,
                table: []
            };

            for (let day = 1; day <= 31; day++) {
                item.table.push({
                    day,
                    weightval: "",
                    weight: false,
                    workout: false,
                    supplement: false
                });
            }

            this.items = [item, ...this.items];
            localStorage.setItem("phypro-items", JSON.stringify(this.items));

            this.tablename = "";
        },

        clear() {
            localStorage.clear();
            this.items = [];
        }
    },

    created() {
        const items = localStorage.getItem("phypro-items");
        if (!items) return;

        try {
            this.items = JSON.parse(items);
        } catch {
            console.warn("invalid json in local storage");
            console.log(items);
        }
    }
};
</script>

<template>
<button @click="clear">clear</button>

<form @submit.prevent="createTable">
    <input placeholder="name..." v-model="tablename" />
    <button type="submit">Create table</button>
</form>

<div v-if="items.length" class="items">
    <div v-for="it in items" class="items__item">
        <button class="item__delete-btn btn btn--danger box">&#215;</button>
        <div class="table">
            <h3>{{ it.name }}</h3>
            <div class="table__item-container">
                <div v-for="t in it.table" class="table__item">
                    <input class="box" />
                    <button class="btn box"></button>
                    <button class="btn box"></button>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<style scoped>
.items {
    display: flex;
    flex-direction: column;
    align-items: center;
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
    width: 20px;
    height: 20px;
    text-align: center;
}

.btn {
    background: white;
    cursor: pointer;
}

.btn:hover {
    background: whitesmoke;
}

.btn--danger:hover {
    background: lightcoral;
}

/* debug */
.bred   { border: 1px solid red; }
.bgreen { border: 1px solid green; }
.bblue  { border: 1px solid blue; }
.bblack { border: 1px solid black; }
</style>
