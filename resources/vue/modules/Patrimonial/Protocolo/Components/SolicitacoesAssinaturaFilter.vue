<script setup>
import { reactive, watch } from 'vue';
import Calendar from 'primevue/calendar';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';

const props = defineProps({
    filter: Object,
    activeIndex: Number,
    getSolicitacoes: Function,
    retry: Function,
    destroy: Function
});

const emit = defineEmits(['onSearch', 'clearFilter']);

const localFilter = reactive({ ...props.filter });

watch(() => props.filter, (newFilters) => {
    Object.assign(localFilter, newFilters);
}, { immediate: true });

const emitSearch = () => {
    emit('onSearch', localFilter);
}

const emitClearFilter = () => {
    emit('clearFilter');
}
</script>

<template>
    <div class="filter">
        <div class="filter-inputs">
            <InputText placeholder="ID" class="m-2" v-model="localFilter.id" style="width: 100px;" />

            <InputText placeholder="Solicitante" class="m-2" v-model="localFilter.solicitante" />

            <InputText placeholder="CGM" class="m-2" v-model="localFilter.cgm" style="width: 100px;" />

            <div>
                <label class="label-data-solicitacao">
                    Data Solicitação:
                </label>

                <div>
                    <Calendar
                        placeholder="Data Início"
                        v-model="localFilter.data_inicio"
                        class="m-2"
                        style="width: 100px;"
                        dateFormat="dd/mm/yy"
                        showButtonBar
                    />

                    <Calendar
                        placeholder="Data Fim"
                        v-model="localFilter.data_fim"
                        class="m-2"
                        style="width: 100px;"
                        dateFormat="dd/mm/yy"
                        showButtonBar
                    />
                </div>
            </div>

            <InputText placeholder="Descrição" class="m-2" v-model="localFilter.descricao" />

            <InputText placeholder="N° Processo" class="m-2" v-model="localFilter.processo" style="width: 130px;" />
        </div>

        <div class="filter-buttons-container">
            <Button
                rounded icon="pi pi-search"
                label="Pesquisar"
                title="Pesquisar"
                style="margin: 0;"
                @click="emitSearch"
            ></Button>

            <Button
                rounded icon="pi pi-filter-slash"
                label="Limpar Filtro"
                title="Limpar"
                style="margin: 0;"
                @click="emitClearFilter"
            ></Button>

            <Button rounded icon="pi pi-refresh" style="margin: 0;" @click="getSolicitacoes"></Button>
        </div>
    </div>
</template>

<style scoped>
.filter {
    display: flex;
    justify-content: center;
    align-items: center;
}

@media (max-width: 1400px) {
    .filter {
        flex-direction: column;
    }
}

.filter-inputs {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: flex-end;
}

.filter-buttons-container {
    display: flex;
    align-items: flex-end;
    margin-top: auto;
    padding: 7.2px;
    gap: 14px;
}

.label-data-solicitacao {
    width: 100%;
    display: flex;
    justify-content: center;
    color: #4D4D4D;
    font-size: 1rem;
}

:deep(.p-inputtext) {
    height: 32px;
    padding: 0.5rem 0.7rem;
    border-radius: 2rem;
}
</style>
