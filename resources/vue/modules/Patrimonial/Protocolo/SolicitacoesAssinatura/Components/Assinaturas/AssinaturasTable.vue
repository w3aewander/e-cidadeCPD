<script setup>
import { reactive, ref, watch } from "vue";
import { formatDateToBrazilian } from "../../../../../../utils/Strings.js";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Button from "primevue/button";
import ModalInfoColuna from "@modules/Patrimonial/Protocolo/Components/ModalInfoColuna.vue";

const props = defineProps({
    showTable: Boolean,
    documentos: Array,
    loadingDocumentos: Boolean,
    selectedDocumentos: Array,
    filtro: Object,
    exibirProgresso: Boolean
});

const emit = defineEmits(['onSort', 'update:selectedDocumentos']);

const urlEcidade = CurrentWindow.corpo.CurrentWindow.ECIDADE_REQUEST_PATH;

const modalInfo = ref(false);
const selectAll = ref(false);
const localSelectedDocumentos = ref(props.selectedDocumentos);

watch(localSelectedDocumentos, (newValue) => {
    if (newValue.length === props.documentos.filter(isSolicitacaoSelecionavel).length && newValue.length !== 0) {
        selectAll.value = true;
    }

    emit('update:selectedDocumentos', newValue);
}, { deep: true });

watch(() => props.selectedDocumentos, (newValue) => {
    if (props.selectedDocumentos.length === 0) {
        selectAll.value = false;
    }

    localSelectedDocumentos.value = newValue;
});

const isSolicitacaoDisabled = (row) => row.data_assinatura || row.data_rejeicao;
const isSolicitacaoSelecionavel = (row) => !row.data_assinatura && !row.data_rejeicao;

const dadosModalInfo = reactive({
    titulo: '',
    conteudo: ''
});

const sort = (event) => {
    emit('onSort', event);
}

const showModalInfo = (titulo = '', conteudo = '') => {
    dadosModalInfo.titulo = titulo;
    dadosModalInfo.conteudo = conteudo;
    modalInfo.value = true;
}

const openOrigem = (codigoProcesso) => {
    js_OpenJanelaIframe(
        'CurrentWindow.corpo',
        'db_iframe_origem',
        urlEcidade + `pro3_consultaprocesso002.php?codproc=${codigoProcesso}`,
        'Origem documento',
        true
    );
}

const visualizarArquivo = (storage_id) => {
    window.open(
        urlEcidade + `db_visualizar_estorage.php?id=${storage_id}`,
        null,
        'left=100,top=100,width=500,height=500;'
    );
}

const cortaTexto = (text, limit) => {
    if (!text) return '';
    return text.length > limit ? text.slice(0, limit) + '...' : text;
}

const rowClassHook = (row) => {
    return isSolicitacaoDisabled(row) ? 'p-disabled-checkbox' : '';
};

const rowUnselectHook = () => {
    selectAll.value = false;
};

const selectAllChangeHook = (event) => {
    selectAll.value = event.checked;

    if (event.checked) {
        localSelectedDocumentos.value = props.documentos.filter(isSolicitacaoSelecionavel);
    } else {
        localSelectedDocumentos.value = [];
    }
};
</script>

<template>
    <ModalInfoColuna v-model:visible="modalInfo" :dadosModal="dadosModalInfo" />

    <DataTable
        v-if="showTable"
        dataKey="id"
        :value="documentos"
        :loading="loadingDocumentos"
        v-model:selection="localSelectedDocumentos"
        :row-class="rowClassHook"
        :select-all="selectAll"
        @select-all-change="selectAllChangeHook"
        @row-unselect="rowUnselectHook"
        @sort="sort"
    >
        <Column
            v-if="filtro.status === 'nao-assinados' || filtro.status === 'todos'"
            selectionMode="multiple"
            style="width: 3rem"
            :exportable="false"
            :disabledSelection="exibirProgresso"
        />

        <Column field="id" header="ID" sortable>
            <template #body="{ data }">
                <b style="font-size: 0.9rem;">{{ data.id }}</b>
            </template>
        </Column>

        <Column field="cgm_solicitante.z01_nome" header="Solicitante" sortable>
            <template #body="{ data }">
                {{ data.cgm_solicitante.z01_nome }}
            </template>
        </Column>

        <Column field="cgm_solicitante.z01_numcgm" header="CGM" sortable>
            <template #body="{ data }">
                {{ data.cgm_solicitante.z01_numcgm }}
            </template>
        </Column>

        <Column field="created_at" header="Data Solicitação" sortable>
            <template #body="{ data }">
                {{ formatDateToBrazilian(data.created_at) }}
            </template>
        </Column>

        <Column field="observacao" header="Observação" sortable>
            <template #body="slotProps">
                <div class="coluna-com-botao-container">
                    <Button
                        v-if="slotProps.data.observacao && slotProps.data.observacao.length > 100"
                        title="Ver observação"
                        icon="pi pi-eye"
                        rounded
                        @click="showModalInfo('Observação', slotProps.data.observacao)"
                    />

                    <p class="m-0">
                        {{ cortaTexto(slotProps.data.observacao, 100) }}
                    </p>
                </div>
            </template>
        </Column>

        <Column
            field="data_assinatura"
            header="Data Assinatura"
            v-if="filtro.status === 'assinados' || filtro.status === 'todos'"
            sortable
        >
            <template #body="{ data }">
                {{ formatDateToBrazilian(data.data_assinatura) }}
            </template>
        </Column>

        <Column
            field="data_rejeicao"
            header="Data Rejeição"
            v-if="filtro.status === 'rejeitados' || filtro.status === 'todos'"
            sortable
        >
            <template #body="{ data }">
                {{ formatDateToBrazilian(data.data_rejeicao) }}
            </template>
        </Column>

        <Column
            field="justificativa_rejeicao"
            header="Justificativa Rejeição"
            v-if="filtro.status === 'rejeitados' || filtro.status === 'todos'"
            sortable
        >
            <template #body="slotProps">
                <div class="coluna-com-botao-container">
                    <Button
                        v-if="slotProps.data.justificativa_rejeicao
                            && slotProps.data.justificativa_rejeicao.length > 100"
                        title="Ver justificativa da rejeição"
                        icon="pi pi-eye"
                        rounded
                        @click="showModalInfo('Justificativa Rejeição', slotProps.data.justificativa_rejeicao)"
                    />

                    <p class="m-0">
                        {{ cortaTexto(slotProps.data.justificativa_rejeicao, 100) }}
                    </p>
                </div>
            </template>
        </Column>

        <Column field="documento.descricao" header="Descrição" sortable />

        <Column field="documento_id" header="Cod. Documento" sortable />

        <Column field="documento.processo.numero" header="N° Processo/Ano" sortable>
            <template #body="{ data }">
                {{ data.documento.processo.numero }}/{{ data.documento.processo.ano }}
            </template>
        </Column>

        <Column
            header="Ações"
            headerStyle="width: 5rem; text-align: center"
            bodyStyle="text-align: center; overflow: visible"
        >
            <template #body="{ data }">
                <div style="display: flex; gap: 10px;">
                    <Button
                        title="Origem do documento"
                        icon="pi pi-bars"
                        type="button"
                        rounded
                        @click="openOrigem(data.documento.processo.p58_codproc)"
                    />

                    <Button
                        title="Visualizar arquivo"
                        icon="pi pi-file-pdf"
                        type="button"
                        severity="danger"
                        rounded
                        @click="visualizarArquivo(data.documento.documento_storage)"
                    />
                </div>
            </template>
        </Column>
    </DataTable>
</template>

<style scoped>
.coluna-com-botao-container {
    display: flex;
    align-items: center;
    gap: 5px;
}

:deep(.p-checkbox-box) {
    border: 1px solid #808080;
}

:deep(.p-disabled-checkbox .p-checkbox-box) {
    border: 1px solid #bebebe;
    background: #e2e2e2;
}

:deep(.p-disabled-checkbox .p-checkbox-input) {
    cursor: not-allowed !important;
    pointer-events: none !important;
    user-select: none !important;
}

:deep(.p-button .p-button-icon-only) {
    width: 2.357rem;
    padding: 0.5rem 0;
    min-width: 2.357rem;
}

:deep(.p-button) {
    min-width: 2.357rem;
}
</style>
