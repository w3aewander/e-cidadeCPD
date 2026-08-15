<script setup>
import { computed } from 'vue';
import InputSearch from '@modules/Components/InputSearch.vue';

const emit = defineEmits(['update:id', 'update:description', 'selected']);

const props = defineProps(['id', 'description']);

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
        url="v4/api/tributario/cadastro/buscar-logradouro"
        param-field="sequencial"
        id-field="sequencial"
        description-field="nome"
        method="GET"
        v-model:id-value="idValue"
        v-model:description-value="descriptionValue"
        @selected="emit('selected', $event)"
    />
</template>