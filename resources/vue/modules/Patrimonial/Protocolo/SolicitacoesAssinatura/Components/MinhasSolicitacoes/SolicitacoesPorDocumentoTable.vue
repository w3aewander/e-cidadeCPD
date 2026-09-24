<script setup>
import { reactive, ref } from "vue";
import { formatDateToBrazilian } from "../../../../../../utils/Strings.js";
import Button from "primevue/button";
import DataTable from "primevue/datatable";
import Tag from "primevue/tag";
import Paginator from "primevue/paginator";
import Column from "primevue/column";
import ModalInfoColuna from "@modules/Patrimonial/Protocolo/Components/ModalInfoColuna.vue";
import DialogSolicitacoesFilter from "@modules/Patrimonial/Protocolo/SolicitacoesAssinatura/Components/MinhasSolicitacoes/DialogSolicitacoesFilter.vue";

const props = defineProps({
    solicitacoes: Array,
    opcaoSelecionada: String,
    getSolicitacoes: Function,
    cancelarSolicitacao: Function,
    filter: Object,
    loadingSolicitacoes: Boolean,
    handleNoResultsMessage: Function
});

const emit = defineEmits([
    'onSort',
    'clearFilter',
    'openDialogHistorico',
    'openObservacaoDialog',
    'onSolicitacoesPageChange'
]);

const expandedRows = ref({});
const dialogInfo = ref(false);
const dialogFiltro = ref(null);
const totalFiltros = ref(0);

const checkboxFilter = ref([]);

const defaultStatus = ['solicitados', 'assinados', 'rejeitados'];

const dadosDialogInfoColuna = reactive({
    titulo: '',
    conteudo: ''
});

const onSort = (event) => {
    emit('onSort', event);
}

const onSolicitacoesPageChange = (data) => {
    emit('onSolicitacoesPageChange', data);
}

const openDialogHistorico = (data) => {
    emit('openDialogHistorico', data);
}

const openObservacaoDialog = (data) => {
    emit('openObservacaoDialog', data);
}

const openDialogInfoColuna = (titulo = '', conteudo = '') => {
    dadosDialogInfoColuna.titulo = titulo;
    dadosDialogInfoColuna.conteudo = conteudo;
    dialogInfo.value = true;
}

const clearFilter = () => {
    totalFiltros.value = 0;
    emit('clearFilter');
    dialogFiltro.value.closeDialog();
    props.getSolicitacoes();
}

const isFilterEmpty = () => {
    return Object.entries(props.filter).every(([key, value]) => {
        if (key === 'status') {
            return (
                Array.isArray(value) &&
                value.length === defaultStatus.length &&
                defaultStatus.every((status) => value.includes(status))
            );
        }

        return value === '' || value === null;
    });
}

const cortaTexto = (text, limit) => {
    if (!text) return '';
    return text.length > limit ? text.slice(0, limit) + '...' : text;
}
</script>

<template>
    <ModalInfoColuna v-model:visible="dialogInfo" :dadosModal="dadosDialogInfoColuna" />

    <DialogSolicitacoesFilter
        ref="dialogFiltro"
        :filter="filter"
        :opcao-selecionada="opcaoSelecionada"
        :checkbox-filter="checkboxFilter"
        :total-filtros="totalFiltros"
        :is-filter-empty="isFilterEmpty"
        @update:filter="(value) => filter = value"
        @set-total-filtros="(value) => totalFiltros = value"
        @clear-filter="clearFilter"
        @on-search="getSolicitacoes()"
    />

    <div class="filter-buttons">
        <Button
            label="Pesquisar"
            icon="pi pi-search"
            rounded
            :badge="totalFiltros > 0 ? totalFiltros.toString() : ''"
            @click="dialogFiltro.openDialog()"
        />

        <Button
            v-if="totalFiltros !== 0"
            label="Limpar filtros"
            icon="pi pi-filter-slash"
            rounded
            @click="clearFilter"
        />

        <Button title="Recarregar" icon="pi pi-refresh" rounded @click="getSolicitacoes()" />
    </div>

    <DataTable
        v-if="solicitacoes.length !== 0 || loadingSolicitacoes"
        v-model:expandedRows="expandedRows"
        dataKey="p01_sequencial"
        :value="solicitacoes"
        :loading="loadingSolicitacoes"
        @sort="onSort"
    >
        <Column expander style="width: 3rem" />

        <Column field="dados_documento.p01_descricao" header="Documento" sortable />

        <Column field="dados_documento.processo.p58_numero" header="N° Processo" sortable />

        <Column field="dados_documento.processo.p58_ano" header="Ano Processo" sortable />

        <Column field="dados_documento.processo.p58_requer" header="Requerente Processo" sortable />

        <template #expansion="slotProps">
            <DataTable :value="slotProps.data.solicitacoes.data">
                <Column field="id" header="ID" />

                <Column field="assinante.z01_nome" header="Assinante" />

                <Column field="assinante.z01_numcgm" header="CGM" />

                <Column field="created_at" header="Data Solicitação">
                    <template #body="{ data }">
                        {{ formatDateToBrazilian(data.created_at.date) }}
                    </template>
                </Column>

                <Column field="observacao" header="Observação">
                    <template #body="{ data }">
                        <div class="coluna-com-botao-container">
                            <Button
                                v-if="data.observacao && data.observacao.length > 100"
                                title="Ver Observação"
                                icon="pi pi-eye"
                                @click="openDialogInfoColuna('Observação', data.observacao)"
                            />

                            <p class="m-0">
                                {{ cortaTexto(data.observacao, 100) }}
                            </p>
                        </div>
                    </template>
                </Column>

                <Column field="data_assinatura" header="Data Assinatura">
                    <template #body="{ data }">
                        {{ formatDateToBrazilian(data.data_assinatura) }}
                    </template>
                </Column>

                <Column field="data_rejeicao" header="Data Rejeição">
                    <template #body="{ data }">
                        {{ formatDateToBrazilian(data.data_rejeicao) }}
                    </template>
                </Column>

                <Column field="justificativa_rejeicao" header="Justificativa Rejeição">
                    <template #body="{ data }">
                        <div class="coluna-com-botao-container">
                            <Button
                                v-if="data.justificativa_rejeicao && data.justificativa_rejeicao.length > 100"
                                title="Ver Justificativa Rejeição"
                                icon="pi pi-eye"
                                @click="openDialogInfoColuna('Justificativa Rejeição', data.justificativa_rejeicao)"
                            />

                            <p class="m-0">
                                {{ cortaTexto(data.justificativa_rejeicao, 100) }}
                            </p>
                        </div>
                    </template>
                </Column>

                <Column field="rejeitado" header="Status">
                    <template #body="{ data }">
                        <Tag
                            rounded
                            :value="data.rejeitado ? 'Rejeitado' : data.data_assinatura ? 'Assinado' : 'Solicitado'"
                            :severity="data.rejeitado ? 'danger' : data.data_assinatura ? 'success' : 'warning'"
                        />
                    </template>
                </Column>

                <Column header="Ações">
                    <template #body="{ data }">
                        <div style="display: flex; gap: 5px;">
                            <Button
                                title="Ver histórico"
                                icon="pi pi-list"
                                rounded
                                @click="openDialogHistorico({
                                    documento_id: data.documento_id,
                                    assinante: data.assinante
                                })"
                            />

                            <Button
                                v-if="!data.rejeitado && !data.data_assinatura"
                                title="Cancelar solicitação"
                                icon="pi pi-times"
                                severity="danger"
                                rounded
                                @click="cancelarSolicitacao(data.id)"
                            />

                            <Button
                                v-if="data.rejeitado"
                                title="Reenviar solicitação"
                                icon="pi pi-undo"
                                severity="warning"
                                rounded
                                @click="openObservacaoDialog({
                                    documento_id: data.documento_id,
                                    documento_nome: data.documento.p01_descricao,
                                    cgm_assinante: data.assinante.z01_numcgm
                                })"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <Paginator
                v-if="slotProps.data.solicitacoes.total > 0"
                :rows="slotProps.data.solicitacoes.per_page"
                :totalRecords="slotProps.data.solicitacoes.total"
                :first="(slotProps.data.solicitacoes.current_page - 1) * slotProps.data.solicitacoes.per_page"
                :currentPage="slotProps.data.solicitacoes.current_page"
                :rowsPerPageOptions="[5, 10, 20, 30, 50]"
                @page="(event) => onSolicitacoesPageChange({
                    index: slotProps.data.p01_sequencial,
                    event: event
                })"
            />
        </template>
    </DataTable>

    <p
        v-if="!loadingSolicitacoes && solicitacoes.length === 0"
        v-html="handleNoResultsMessage()"
        class="no-results-message"
    ></p>
</template>

<style scoped>
.filter-buttons {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    padding: 10px;
    position: absolute;
    top: 45px;
    right: 0;
}

.coluna-com-botao-container {
    display: flex;
    align-items: center;
    gap: 5px;
}

.no-results-message {
    margin-top: 40px;
    text-align: center;
    font-size: 1.3rem;
    color: #4e4e4e;
}

:deep(.p-button .p-button-icon-only) {
    width: 2.357rem;
    padding: 0.5rem 0;
    min-width: 2.357rem;
}

:deep(.p-button) {
    min-width: 2.357rem;
}

:deep(.p-dropdown) {
    border: 1px solid #939393;
    border-radius: 2rem;
    padding: 0 5px;
}

:deep(.p-tag) {
    padding: 0.25rem 0.5rem;
}
</style>
