<script>
const MONDAY = 1;
const SUNDAY = 0;
const TABLE_ROWS_COUNT = 6;

export default {
    data() {
        return {
            table: []
        };
    },

    computed: {
    },

    methods: {
        createTable() {
            let row = [];

            let weekday = 0;
            let day = 1;

            const now = new Date();

            const lastday =
                new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate();

            const prevMonthLastday =
                new Date(now.getFullYear(), now.getMonth(), 0).getDate();

            let firstdayWeekday =
                new Date(now.getFullYear(), now.getMonth(), 1).getDay();

            if (firstdayWeekday === SUNDAY) firstdayWeekday = 7;

            for (let i = 0; firstdayWeekday !== MONDAY; i++) {
                row[weekday++] = prevMonthLastday - i;
                firstdayWeekday--;
            }
            row.reverse();

            for (let i = 0; i < TABLE_ROWS_COUNT; i++) {
                while (weekday < 7) {
                    row[weekday++] = day++;
                    if (day > lastday) day = 1;
                }

                this.table.push(row);
                row = [];
                weekday = 0;

                if (i === 4 && this.table[i].includes(1)) break;
            }

            console.log(this.table);
        }
    },

    created() {
        this.createTable();
    }
};
</script>

<template>
</template>

<style scoped>
</style>
