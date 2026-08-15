<template>
    <div>
        <table>
            <thead>
            <th v-for="header in headers" :key="header">{{ header }}</th>
            </thead>
            <tbody>
            <tr v-for="(row, index) in data" :key="index">
                <td v-for="(value, key) in row" :key="key">{{ value }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
    props: {
        headers: Array,
        data: Array
    }
});
</script>

<style scoped>
/* Estilos específicos do componente */
</style>
