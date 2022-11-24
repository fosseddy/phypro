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
            console.log(this.items);
        } catch (err) {
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

<div v-if="items.length" class="col items">
    <div v-for="it in items" class="row item">
        <button class="btn-delete">x</button>
        <div class="col">
            <h3>{{ it.name }}</h3>
            <div class="row table" style="">
                <div v-for="t in it.table" class="col table-item">
                    <input class="box" />
                    <button class="box">s</button>
                    <button class="box">w</button>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<style scoped>
.row {
    display: flex;
    flex-direction: row;
}

.col {
    display: flex;
    flex-direction: column;
}

.items {
    gap: 1rem;
    align-items: center;
}

.item {
    gap: 1rem;
}

.btn-delete {
    align-self: start;
}

.table {
    gap: 1px;
}

.table-item {
    gap: 1px;
}

.box {
    width: 20px;
    height: 20px;
    text-align: center;
}

/* debug */
.bred   { border: 1px solid red; }
.bgreen { border: 1px solid green; }
.bblue  { border: 1px solid blue; }
.bblack { border: 1px solid black; }
</style>
