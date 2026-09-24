<script setup>
import { computed } from 'vue';
import InputSearch from '@modules/Components/InputSearch.vue';

const emit = defineEmits(['update:id', 'update:description', 'selected']);

const props = defineProps(['id', 'description', 'processo', 'instit']);

const paramsInputSearch = computed(() => {
    let processoFields = props.processo.split('/');

    if (processoFields[1] == undefined) {
        processoFields[1] = new Date().getFullYear();
    }

    return {
        p58_numero: processoFields[0],
        p58_ano: processoFields[1],
        p58_instit: props.instit 
    }
})

const idValue = computed({
    get: () => props.id,
    set: (value) => {
        emit('update:id', value);
    }
});

const descriptionValue = computed({
    get: () => props.description,
    set: (value) => {
        emit('update:description', value);
    }
});
</script>

<template>
    <InputSearch
        url="v4/api/patrimonial/protocolo/processo/getprocesso"
        id-field="processo_ano"
        description-field="p58_requer"
        :params="paramsInputSearch"
        v-model:id-value="idValue"
        v-model:description-value="descriptionValue"
        @selected="emit('selected', $event)"
    />
</template>
