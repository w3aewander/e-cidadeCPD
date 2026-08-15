<script setup>
import { onMounted, reactive, ref } from "vue";
import SolicitacoesTable from "@modules/Patrimonial/Protocolo/SolicitacoesAssinatura/Components/MinhasSolicitacoes/SolicitacoesTable.vue";
import SolicitacoesPorAssinanteTable from "@modules/Patrimonial/Protocolo/SolicitacoesAssinatura/Components/MinhasSolicitacoes/SolicitacoesPorAssinanteTable.vue";
import Dropdown from "primevue/dropdown";
import Paginator from "primevue/paginator";
import ModalLoading from "@modules/Components/ModalLoading.vue";
import ModalInfoColuna from "@modules/Patrimonial/Protocolo/Components/ModalInfoColuna.vue";
import DialogInclusaoObservacao from "@modules/Patrimonial/Protocolo/Components/DialogInclusaoObservacao.vue";
import DialogHistorico from "@modules/Patrimonial/Protocolo/Components/DialogProtocoloSolicitacao.vue";
import Swal from "sweetalert2";
import SolicitacoesPorProcessoTable from "@modules/Patrimonial/Protocolo/SolicitacoesAssinatura/Components/MinhasSolicitacoes/SolicitacoesPorProcessoTable.vue";
import SolicitacoesPorDocumentoTable from "@modules/Patrimonial/Protocolo/SolicitacoesAssinatura/Components/MinhasSolicitacoes/SolicitacoesPorDocumentoTable.vue";

const props = defineProps({
    cpf_cnpj: String,
});

const solicitacoes = ref([]);
const selectedSolicitacao = ref({});
const solicitacoesHistorico = ref({});
const opcaoSelecionada = ref("Solicitação");

const expandedRows = ref({});
const colunasTabela = ref(['aguardando', 'assinados', 'rejeitados']);

const sortOrder = ref(-1);
const sortField = ref('created_at');

const currentPage = ref(1);
const rowsPerPage = ref(10);
const solicitacoesRowsPerPage = ref(5);

const loadingSolicitacoes = ref(false);
const loading = ref(false);
const loadingMessage = ref('');

const modalInfo = ref(false);
const dialogObservacao = ref(false);
const dialogHistorico = ref(null);

const observacao = ref('');

const filtroAplicado = ref({});

const opcoesExibicao = ["Solicitação", "Assinante", "Processo", "Documento"];

const defaultStatus = ['solicitados', 'assinados', 'rejeitados'];

const filter = reactive({
    id: '',
    assinante: '',
    cgm: '',
    cpf_cnpj: '',
    processo: '',
    documento: '',
    data_solicitacao_inicio: '',
    data_solicitacao_fim: '',
    data_assinatura_inicio: '',
    data_assinatura_fim: '',
    data_rejeicao_inicio: '',
    data_rejeicao_fim: '',
    ano: '',
    requerente: '',
    status: ['solicitados', 'assinados', 'rejeitados']
});

const paginate = reactive({
    rows: rowsPerPage.value,
    total: 0,
    to: 0
});

const dadosModalInfo = reactive({
    titulo: '',
    conteudo: ''
});

const paginationSolicitacoes = reactive({});

const getSolicitacoes = async () => {
    loadingSolicitacoes.value = true;

    const params = new URLSearchParams({
        page: currentPage.value,
        per_page: rowsPerPage.value,
        pagination_solicitacoes: JSON.stringify(paginationSolicitacoes),
        filter: JSON.stringify(filter),
        sort_field: sortField.value,
        sort_order: sortOrder.value
    });

    try {
        solicitacoes.value = [];

        const resp = await window.axios.get(
            `v4/api/patrimonial/protocolo/solicitacao-assinatura/solicitante/${props.cpf_cnpj}?`
            + params.toString()
            + `&exibicao=${opcaoSelecionada.value.toString()}`
        );
        const data = resp.data.data;

        solicitacoes.value = data.data;

        colunasTabela.value = filter.status;

        filtroAplicado.value = JSON.parse(JSON.stringify(filter));

        handlePaginate(data);
    } catch (e) {
        loadingSolicitacoes.value = false;

        Swal.fire({ text: 'Não foi possível carregar as solicitações', icon: 'error', confirmButtonColor: '#4F7C9E'});
    }

    loadingSolicitacoes.value = false;
}

const cancelarSolicitacao = async (solicitacao_id) => {
    loading.value = true;
    loadingMessage.value = 'Cancelando solicitação...'

    try {
        await axios.delete(
            `v4/api/patrimonial/protocolo/solicitacao-assinatura/${solicitacao_id}`
        );
    } catch (e) {
        loading.value = false;

        if (e.response) {
            return Swal.fire({
                text: e.response.data.message,
                icon: 'error',
                confirmButtonColor: '#4F7C9E'
            });
        }

        return Swal.fire({
            text: 'Não foi possível cancelar a solicitação',
            icon: 'error',
            confirmButtonColor: '#4F7C9E'
        });
    }

    loading.value = false;

    Swal.fire({
        text: 'Solicitação cancelada com sucesso!',
        icon: 'success',
        confirmButtonColor: '#4F7C9E'
    });

    getSolicitacoes();
}

const reenviarSolicitacao = async (solicitacao) => {
    closeObservacaoDialog();

    loading.value = true;
    loadingMessage.value = 'Reenviando solicitação...';

    let form = {};
    form.documentos = [];
    form.observacao = observacao.value;

    if (Object.keys(selectedSolicitacao.value).length !== 0) {
        form.documentos.push({
            documento_id: selectedSolicitacao.value.documento_id,
            documento_nome: selectedSolicitacao.value.documento_nome,
            cgm_assinante: selectedSolicitacao.value.cgm_assinante
        })
    } else {
        form.documentos.push({
            documento_id: solicitacao.solicitacao.documento_id,
            documento_nome: solicitacao.solicitacao.documento.p01_descricao,
            cgm_assinante: solicitacao.solicitacao.cgm_assinante.z01_numcgm
        })
    }

    try {
        await axios.post(
            "v4/api/patrimonial/protocolo/solicitacao-assinatura", form
        );
    } catch (e) {
        loading.value = false;

        let erros = "";

        if (e.response.data.data.erros.length !== 0) {
            e.response.data.data.erros.forEach((erro) => {
                erros += erro + '<br>';
            });
        }

        const mensagem = e.response.data.message ? e.response.data.message : "Não foi possível reenviar solicitação";

        return Swal.fire({
            html: mensagem + ": <br><br>" + erros,
            icon: "error",
            confirmButtonColor: "#4a789c",
            width: "fit-content"
        });
    }

    loading.value = false;

    dialogHistorico.value.closeDialog();

    let mensagem = "Assinatura solicitada com sucesso!";
    let icone = "success";

    Swal.fire({ html: mensagem, icon: icone, confirmButtonColor: "#4a789c", width: "fit-content" });

    observacao.value = '';
    selectedSolicitacao.value = {};

    getSolicitacoes();
}

const openDialogHistorico = async (solicitacao) => {
    loading.value = true;
    loadingMessage.value = 'Buscando histórico...';

    const params = new URLSearchParams({
        cgm_assinante: solicitacao.assinante.z01_numcgm,
        documento_id: solicitacao.documento_id
    });

    try {
        const resp = await window.axios.get(
            `v4/api/patrimonial/protocolo/solicitacao-assinatura/historico/${props.cpf_cnpj}?`
            + params.toString()
        );
        const data = resp.data.data;

        const solicitacaoMaisRecente = data.reduce((latest, current) => {
            return new Date(current.created_at) > new Date(latest.created_at) ? current : latest;
        });

        solicitacoesHistorico.value = {
            solicitacao: solicitacaoMaisRecente,
            solicitacoesRelacionadas: data
        };
    } catch (e) {
        loading.value = false;

        return Swal.fire({ text: 'Não foi possível carregar histórico', icon: 'error', confirmButtonColor: "#4a789c"});
    }

    loading.value = false;

    dialogHistorico.value.openDialog();
}

const handleNoResultsMessage = () => {
    const filterLabels = {
        id: 'ID',
        assinante: 'Assinante',
        cgm: 'CGM',
        cpf_cnpj: 'CPF/CNPJ',
        processo: 'Processo',
        documento: 'Documento',
        data_solicitacao_inicio: 'Data Solicitação (De)',
        data_solicitacao_fim: 'Data Solicitação (Até)',
        data_assinatura_inicio: 'Data Assinatura (De)',
        data_assinatura_fim: 'Data Assinatura (Até)',
        data_rejeicao_inicio: 'Data Rejeição (De)',
        data_rejeicao_fim: 'Data Rejeição (Até)',
        ano: 'Ano',
        requerente: 'Requerente',
        status: 'Status'
    };

    const appliedFilters = Object.entries(filtroAplicado.value)
        .filter(([key, value]) => {
            if (key === 'status') {
                const hasAllDefaultStatus = defaultStatus.every(status => value.includes(status));
                return !hasAllDefaultStatus;
            }
            return value !== '' && value !== null && value !== false;
        })
        .map(([key, value]) => {
            if (key.includes('data')) {
                let date = new Date(value);
                value = date.toLocaleString('pt-BR', { year: 'numeric', month: '2-digit', day: '2-digit' });
            }

            if (key === 'status') {
                return `${filterLabels[key]}: ${value.join(', ')}`;
            }
            return `${filterLabels[key]}: ${value}`;
        });

    if (appliedFilters.length > 0) {
        return `Nenhum resultado encontrado para:<br><br>- ${appliedFilters.join('<br>- ')}`;
    }

    return 'Você não solicitou nenhuma assinatura';
}

const onTabChange = () => {
    Object.keys(paginationSolicitacoes).forEach((key) => {
        delete paginationSolicitacoes[key];
    });

    clearFilter();
    clearSort();

    getSolicitacoes();
}

const onSort = (event) => {
    sortField.value = event.sortField;
    sortOrder.value = event.sortOrder;

    getSolicitacoes();
}

const onPageChange = (event) => {
    currentPage.value = event.page + 1;
    rowsPerPage.value = event.rows;

    getSolicitacoes({ page: currentPage.value, rows: rowsPerPage.value });
}

const onSolicitacoesPageChange = ({ index, event }) => {
    solicitacoesRowsPerPage.value = event.rows;

    paginationSolicitacoes[index] = {
        page_solicitacoes: event.page + 1,
        per_page_solicitacoes: solicitacoesRowsPerPage.value,
    };

    getSolicitacoes();
}

const clearFilter = () => {
    Object.keys(filter).forEach((key) => {
        if (key === 'status') {
            filter[key] = ['solicitados', 'assinados', 'rejeitados'];
        } else {
            filter[key] = '';
        }
    });
}

const clearSort = () => {
    sortField.value = 'created_at';
    sortOrder.value = -1;
}

const handlePaginate = (data) => {
    paginate.rows = rowsPerPage.value || 10;
    paginate.total = data.total || 0;
    paginate.current_page = currentPage.value || 1;
    paginate.to = data.to || 0;
}

const openObservacaoDialog = (data) => {
    selectedSolicitacao.value = data;
    dialogObservacao.value = true;
}

const closeObservacaoDialog = () => {
    dialogObservacao.value = false;
}

onMounted(() => {
    getSolicitacoes();
});
</script>

<template>
    <ModalLoading :is-loading="loading" :message="loadingMessage" />

    <ModalInfoColuna v-model:visible="modalInfo" :dadosModal="dadosModalInfo" />

    <DialogHistorico
        ref="dialogHistorico"
        :solicitacao="solicitacoesHistorico"
        :cancelarSolicitacao="cancelarSolicitacao"
        :reenviarSolicitacao="reenviarSolicitacao"
        @update:loading="(newValue) => loading = newValue"
        @update:loadingMessage="(newValue) => loadingMessage = newValue"
        @update:observacao="(newValue) => observacao = newValue"
    />

    <DialogInclusaoObservacao
        v-model:visible="dialogObservacao"
        :modalShow="dialogObservacao"
        :observacao="observacao"
        :solicitacao="selectedSolicitacao"
        :reenviarSolicitacao="reenviarSolicitacao"
        @update:observacao="(newValue) => observacao = newValue"
        @clearSelectedSolicitacao="selectedSolicitacao = {}"
    />

    <section class="top-actions">
        <div class="select-container">
            <label>Exibir por:</label>
            <Dropdown v-model="opcaoSelecionada" :options="opcoesExibicao" @change="onTabChange"/>
        </div>
    </section>

    <SolicitacoesTable
        v-if="opcaoSelecionada === 'Solicitação'"
        :solicitacoes="solicitacoes"
        :opcao-selecionada="opcaoSelecionada"
        :get-solicitacoes="getSolicitacoes"
        :cancelar-solicitacao="cancelarSolicitacao"
        :filter="filter"
        :filtro-aplicado="filtroAplicado"
        :colunas-tabela="colunasTabela"
        :loading-solicitacoes="loadingSolicitacoes"
        :handle-no-results-message="handleNoResultsMessage"
        @on-sort="onSort"
        @clear-filter="clearFilter"
        @open-dialog-historico="(data) => openDialogHistorico(data)"
        @open-observacao-dialog="(data) => openObservacaoDialog(data)"
    />

    <SolicitacoesPorAssinanteTable
        v-if="opcaoSelecionada === 'Assinante'"
        :solicitacoes="solicitacoes"
        :opcao-selecionada="opcaoSelecionada"
        :get-solicitacoes="getSolicitacoes"
        :cancelar-solicitacao="cancelarSolicitacao"
        :filter="filter"
        :pagination-solicitacoes="paginationSolicitacoes"
        :loading-solicitacoes="loadingSolicitacoes"
        :handle-no-results-message="handleNoResultsMessage"
        @on-sort="onSort"
        @clear-filter="clearFilter"
        @on-solicitacoes-page-change="(data) => onSolicitacoesPageChange(data)"
        @open-dialog-historico="(data) => openDialogHistorico(data)"
        @open-observacao-dialog="(data) => openObservacaoDialog(data)"
    />

    <SolicitacoesPorProcessoTable
        v-if="opcaoSelecionada === 'Processo'"
        :solicitacoes="solicitacoes"
        :opcao-selecionada="opcaoSelecionada"
        :get-solicitacoes="getSolicitacoes"
        :cancelar-solicitacao="cancelarSolicitacao"
        :filter="filter"
        :pagination-solicitacoes="paginationSolicitacoes"
        :loading-solicitacoes="loadingSolicitacoes"
        :handle-no-results-message="handleNoResultsMessage"
        @on-sort="onSort"
        @clear-filter="clearFilter"
        @on-solicitacoes-page-change="(data) => onSolicitacoesPageChange(data)"
        @open-dialog-historico="(data) => openDialogHistorico(data)"
        @open-observacao-dialog="(data) => openObservacaoDialog(data)"
    />

    <SolicitacoesPorDocumentoTable
        v-if="opcaoSelecionada === 'Documento'"
        :solicitacoes="solicitacoes"
        :opcao-selecionada="opcaoSelecionada"
        :get-solicitacoes="getSolicitacoes"
        :cancelar-solicitacao="cancelarSolicitacao"
        :filter="filter"
        :pagination-solicitacoes="paginationSolicitacoes"
        :loading-solicitacoes="loadingSolicitacoes"
        :handle-no-results-message="handleNoResultsMessage"
        @on-sort="onSort"
        @clear-filter="clearFilter"
        @on-solicitacoes-page-change="(data) => onSolicitacoesPageChange(data)"
        @open-dialog-historico="(data) => openDialogHistorico(data)"
        @open-observacao-dialog="(data) => openObservacaoDialog(data)"
    />

    <Paginator
        v-if="paginate.total > 0"
        :page="currentPage"
        :rows="rowsPerPage"
        :totalRecords="paginate.total"
        :rowsPerPageOptions="[10, 20, 30, 50, 80, 100]"
        @page="onPageChange"
    >
        <template #start>
            {{ paginate.to }} de {{ paginate.total }}
        </template>
    </Paginator>
</template>

<style scoped>
.top-actions {
    padding: 10px;
    display: flex;
    justify-content: space-between;
}

.select-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
}

:deep(.p-dropdown) {
    border: 1px solid #939393;
    border-radius: 2rem;
    padding: 0 5px;
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
