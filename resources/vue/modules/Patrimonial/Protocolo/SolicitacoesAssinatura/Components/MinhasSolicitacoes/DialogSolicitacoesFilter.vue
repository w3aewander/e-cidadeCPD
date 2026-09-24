<script setup>
import { reactive, ref, watch } from "vue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import Calendar from "primevue/calendar";
import InputText from "primevue/inputtext";

const props = defineProps({
    filter: Object,
    opcaoSelecionada: String,
    checkboxFilter: Array,
    totalFiltros: Number,
});

const emit = defineEmits(['onSearch', 'clearFilter', 'setTotalFiltros', 'update:filter']);

const dialogVisible = ref(false);

const localFilter = reactive({ ...props.filter });

const filtroStatusPadrao = ['solicitados', 'assinados', 'rejeitados'];

watch(props.filter, (newFilters) => {
    Object.assign(localFilter, newFilters);
}, { deep: true });

const emitSearch = () => {
    closeDialog();

    const totalFiltros = Object.entries(props.filter)
        .filter(([key, value]) => {
            if (key === 'status') {
                const temValoresPadrao = filtroStatusPadrao.every(status => value.includes(status));
                return !temValoresPadrao;
            }

            return value !== null && value !== '' && value !== false;
        }).length;

    emit('setTotalFiltros', totalFiltros);
    emit('onSearch', localFilter);
}

const clearFilter = () => {
    emit('setTotalFiltros', 0);
    emit('clearFilter');
}

const filterCpfCnpj = (event) => {
    const valorFiltrado = event.target.value.replace(/[./-]/g, '');

    event.target.value = valorFiltrado;
    props.filter.cpf_cnpj = valorFiltrado;
}

const openDialog = () => {
    dialogVisible.value = true;
}

const closeDialog = () => {
    dialogVisible.value = false;
}

defineExpose({ openDialog, closeDialog });
</script>

<template>
    <Dialog
        header="Filtros"
        position="bottom"
        :visible="dialogVisible"
        @update:visible="closeDialog"
    >
        <div class="filter">
            <div class="filter-inputs" @keyup.enter="emitSearch">
                <div>
                    <InputText placeholder="ID" class="m-2" v-model="filter.id" style="width: 100px;" />

                    <InputText placeholder="Assinante" class="m-2" v-model="filter.assinante" />

                    <InputText placeholder="CGM" class="m-2" v-model="filter.cgm" style="width: 100px;" />

                    <InputText
                        v-if="opcaoSelecionada === 'Assinante'"
                        placeholder="CPF/CNPJ"
                        class="m-2"
                        v-model="filter.cpf_cnpj"
                        @input="filterCpfCnpj"
                    />

                    <InputText placeholder="Documento" class="m-2" v-model="filter.documento" />

                    <InputText placeholder="N° Processo" class="m-2" v-model="filter.processo" style="width: 130px;" />

                    <InputText
                        v-if="opcaoSelecionada === 'Processo' || opcaoSelecionada === 'Documento'"
                        placeholder="Ano"
                        class="m-2"
                        v-model="filter.ano"
                        style="width: 80px;"
                    />

                    <InputText
                        v-if="opcaoSelecionada === 'Processo' || opcaoSelecionada === 'Documento'"
                        placeholder="Requerente"
                        class="m-2"
                        v-model="filter.requerente"
                    />
                </div>

                <div style="display: flex;">
                    <div class="data-input-container">
                        <label class="label-data-solicitacao">
                            Data Solicitação:
                        </label>

                        <div>
                            <Calendar
                                placeholder="De"
                                v-model="filter.data_solicitacao_inicio"
                                class="m-2"
                                style="width: 100px;"
                                dateFormat="dd/mm/yy"
                                showButtonBar
                            />

                            <Calendar
                                placeholder="Até"
                                v-model="filter.data_solicitacao_fim"
                                class="m-2"
                                style="width: 100px;"
                                dateFormat="dd/mm/yy"
                                showButtonBar
                            />
                        </div>
                    </div>

                    <div class="data-input-container">
                        <label class="label-data-solicitacao">
                            Data Assinatura:
                        </label>

                        <div>
                            <Calendar
                                placeholder="De"
                                v-model="filter.data_assinatura_inicio"
                                class="m-2"
                                style="width: 100px;"
                                dateFormat="dd/mm/yy"
                                showButtonBar
                            />

                            <Calendar
                                placeholder="Até"
                                v-model="filter.data_assinatura_fim"
                                class="m-2"
                                style="width: 100px;"
                                dateFormat="dd/mm/yy"
                                showButtonBar
                            />
                        </div>
                    </div>

                    <div class="data-input-container">
                        <label class="label-data-solicitacao">
                            Data Rejeição:
                        </label>

                        <div>
                            <Calendar
                                placeholder="De"
                                v-model="filter.data_rejeicao_inicio"
                                class="m-2"
                                style="width: 100px;"
                                dateFormat="dd/mm/yy"
                                showButtonBar
                            />

                            <Calendar
                                placeholder="Até"
                                v-model="filter.data_rejeicao_fim"
                                class="m-2"
                                style="width: 100px;"
                                dateFormat="dd/mm/yy"
                                showButtonBar
                            />
                        </div>
                    </div>
                </div>

                <div class="checkboxes-container">
                    <label style="color: #4D4D4D">Status:</label>

                    <div class="checkbox-container">
                        <Checkbox v-model="filter.status" input-id="solicitados" value="solicitados" />
                        <label for="solicitados">
                            <Tag value="Solicitados" severity="warning" rounded />
                        </label>
                    </div>

                    <div class="checkbox-container">
                        <Checkbox v-model="filter.status" input-id="assinados" value="assinados" />
                        <label for="assinados">
                            <Tag value="Assinados" severity="success" rounded />
                        </label>
                    </div>

                    <div class="checkbox-container">
                        <Checkbox v-model="filter.status" input-id="rejeitados" value="rejeitados" />
                        <label for="rejeitados">
                            <Tag value="Rejeitados" severity="danger" rounded />
                        </label>
                    </div>
                </div>
            </div>

            <div class="filter-buttons-container">
                <Button
                    rounded icon="pi pi-search"
                    label="Pesquisar"
                    style="margin: 0;"
                    @click="emitSearch"
                ></Button>

                <Button
                    v-if="totalFiltros !== 0"
                    rounded icon="pi pi-filter-slash"
                    label="Limpar Filtro"
                    style="margin: 0;"
                    @click="clearFilter"
                ></Button>
            </div>
        </div>
    </Dialog>
</template>

<style scoped>
.filter {
    margin-top: 20px;
    display: flex;
    flex-direction: column;
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
    flex-direction: column;
    flex-wrap: wrap;
    justify-content: center;
    align-items: flex-start;
    gap: 10px;
}

.filter-buttons-container {
    display: flex;
    align-items: flex-end;
    margin-top: 5px;
    padding: 8px;
    gap: 15px;
}

.data-input-container {
    display: flex;
    flex-direction: column;
}

.label-data-solicitacao {
    margin-left: 10px;
    color: #4D4D4D;
}

.checkboxes-container {
    padding: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.checkbox-container {
    display: flex;
    align-items: center;
    gap: 5px;
}

:deep(.p-tag) {
    padding: 0.25rem 0.5rem;
}

:deep(.p-inputtext) {
    height: 32px;
    padding: 0.5rem 0.7rem;
    border-radius: 2rem;
}
</style>
