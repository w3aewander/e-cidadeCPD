<script setup>
import {onUpdated, ref} from "vue";

const props = defineProps(['dadosVisualizar']);
const showDialod = ref(false);

onUpdated(() => {

});

const closeDialog = (e) => {
    showDialod.value = false;
}

const openDialog = (e) => {
    showDialod.value = true;
}

defineExpose({
    closeDialog,
    openDialog
});
</script>

<template>
    <Dialog
        :visible="showDialod"
        @update:visible="closeDialog"
        style="width: 30%;"
    >
        <div v-for="(dado, index) in dadosVisualizar">
            <label><b>{{dado.descricao}}</b></label>
            <p v-if="dado.value">{{dado.value}}</p>
            <p v-if="dado.valor">Valor: {{dado.valor}}</p>
            <p v-if="dado.aliquota">Alíquota: {{dado.aliquota}}</p>
        </div>
    </Dialog>
</template>

<style scoped>

</style>
