<script setup>
import { onMounted, ref, watch, reactive, computed } from 'vue';
import { useToast } from "primevue/usetoast";
import io from '../../../../../../../../scripts/socket.io.js'
import SolicitacoesAssinaturaFilter from "@modules/Patrimonial/Protocolo/SolicitacoesAssinatura/Components/Assinaturas/SolicitacoesAssinaturaFilter.vue";
import Paginator from "primevue/paginator";
import Swal from 'sweetalert2';
import AssinaturasTable from "@modules/Patrimonial/Protocolo/SolicitacoesAssinatura/Components/Assinaturas/AssinaturasTable.vue";
import SolicitacoesActions from "@modules/Patrimonial/Protocolo/SolicitacoesAssinatura/Components/Assinaturas/SolicitacoesActions.vue";
import ModalInclusaoJustificativa from "@modules/Patrimonial/Protocolo/SolicitacoesAssinatura/Components/Assinaturas/ModalInclusaoJustificativa.vue";
import ModalProgressBar from "@modules/Patrimonial/Protocolo/Components/ModalProgressBar.vue";
import ModalLoading from "@modules/Components/ModalLoading.vue";

const props = defineProps({
    configuracao: Object,
    cpf_cnpj: String,
    filtro: Object,
    solicitacaoId: {
        type: String,
        default: null
    }
});

const documentos = ref([]);
const selectedDocumentos = ref([]);
const documentosAssinados = ref([]);
const assinaturasComErros = ref([]);
const documentosAssinando = ref([]);
const loadingDocumentos = ref(false);

const exibirProgresso = ref(false);

const assinadorAtivo = ref(false);
const certificates = ref([]);
const certificadoSelecionado = ref(null);

const sortOrder = ref(-1);
const sortField = ref('');
const rowsPerPage = ref(10);

const timeout = ref(null);

const showTable = ref(true);

const justificativa = ref('');

const solicitacaoId = ref(props.solicitacaoId || null);

const loadingRejeitandoAssinaturas = ref(false);

const modalInclusaoJustificativa = ref(false);

const toast = useToast();

const percentualProgresso = computed(() => {
    let percentual = (documentosAssinados.value.length ?? 0) / (selectedDocumentos.value.length ?? 0) * 100;
    return isNaN(percentual) ? 0 : percentual;
});

const filter = reactive({
    id: solicitacaoId.value,
    solicitante: null,
    cgm: null,
    data_inicio: null,
    data_fim: null,
    descricao: null,
    processo: null,
});

const paginate = reactive({
    rows: rowsPerPage.value,
    total: 0,
    to: 0
});

const getSolicitacoes = async (options = {}) => {
    selectedDocumentos.value = [];
    loadingDocumentos.value = true;
    showTable.value = true;

    let { page, rows = rowsPerPage.value } = options;

    const { id, solicitante, cgm, data_inicio, data_fim, descricao, processo } = filter;
    const search = { id, solicitante, cgm, data_inicio, data_fim, descricao, processo };

    const params = new URLSearchParams({
        page: page ? ++page : 1,
        perPage: rows,
        search: JSON.stringify(search)
    });

    try {
        documentos.value = [];
        const resp = await window.axios.get(
            `v4/api/patrimonial/protocolo/solicitacao-assinatura/cpf-cnpj/${props.cpf_cnpj}?` +
            params.toString() +
            `&status=${props.filtro.status}&sortField=${sortField.value}&sortOrder=${sortOrder.value}`
        );
        const data = resp.data.data;
        documentos.value = data.data || [];

        if (documentos.value.length === 0) {
            showTable.value = false;
        }

        handlePaginate(data);
    } catch (e) {
        alert("Não foi possível carregar os documentos.");
        console.error(e);
    }

    loadingDocumentos.value = false;
}

const assinarEcidade = async () => {
    documentosAssinados.value = [];
    assinaturasComErros.value = [];

    if (selectedDocumentos.value.length < 1) {
        return alert("Selecione pelo menos um documento para assinar!");
    }

    exibirProgresso.value = true;

    for (const documento of selectedDocumentos.value) {
        const arquivoAssinado = await assinarDocumentoEcidade(documento);
        await salvarArquivoAssinado(arquivoAssinado);
    }

    exibirProgresso.value = false;
    getSolicitacoes();
}

const assinarDocumentoEcidade = async (documento) => {
    let parametrosAssinaturaECidade = { ...props.configuracao.value };
    parametrosAssinaturaECidade.fileID = documento.documento.documento_storage;
    // parametrosAssinaturaECidade.qrcode_link = documento.value;
    parametrosAssinaturaECidade.qrcode_hash = documento.documento.documento_hash;
    parametrosAssinaturaECidade.sequencial = documento.documento.p01_sequencial;
    parametrosAssinaturaECidade.id_estorage = documento.documento.documento_storage;
    parametrosAssinaturaECidade.solicitacao_id = documento.id;

    let arquivoAssinado;

    try {
        const resp = await window.axios.post(`v4/api/assinador/assinar-ecidade`, parametrosAssinaturaECidade);
        const data = await resp.data;
        arquivoAssinado = {...parametrosAssinaturaECidade};
        arquivoAssinado.base64 = data.data.base64_file;
    } catch (e) {
        exibirProgresso.value = false;

        Swal.fire("Erro", "Um erro ocorreu ao tentar assinar documentos.", "error");

        throw new Error(e);
    }

    return arquivoAssinado;
}

const salvarArquivoAssinado = async (arquivoAssinado) => {
    let solicitacaoId = arquivoAssinado.solicitacao_id;

    try {
        await window.axios.post(
            `v4/api/patrimonial/protocolo/documentos/atualizar-documento-assinado`,
            arquivoAssinado
        );
    } catch (e) {
        if (e.response.data.message) {
            return assinaturasComErros.value.push(`(ID ${solicitacaoId}) ` + e.response.data.message);
        }

        return assinaturasComErros.value.push(
            `(ID ${solicitacaoId}) Não foi possível assinar o documento. Tente novamente mais tarde.`
        );
    }

    documentosAssinados.value.push(arquivoAssinado);
}

const obterBase64DoArquivo = async (codigoEstorage) => {
    const resp = await window.axios.get(
        `v4/api/assinador/obter-arquivo-base64/${codigoEstorage}`
    );
    return resp.data.data;
}

const assinarA3 = async () => {
    documentosAssinados.value = [];
    assinaturasComErros.value = [];

    if (selectedDocumentos.value.length < 1) {
        alert("Selecione pelo menos um documento para assinar!");
        return;
    }

    if (empty(certificadoSelecionado.value)) {
        alert("Selecione o certificado");
        return;
    }

    exibirProgresso.value = true;

    for (const documento of selectedDocumentos.value) {
        await assinarDocumentoA3(documento);
    }
}

const assinarDocumentoA3 = async (documento) => {
    const codigoEstorage = documento.documento.documento_storage;
    let parametrosAssinatura = { ...props.configuracao.value };
    parametrosAssinatura.fileID = codigoEstorage;
    parametrosAssinatura.fileB64 = "";
    parametrosAssinatura.fileCertificate = certificadoSelecionado.value;
    parametrosAssinatura.isCertBase64 = false;
    //parametrosAssinatura.qrcode_link = inputQrcodeLink.value;
    const data = await obterBase64DoArquivo(codigoEstorage);
    parametrosAssinatura.fileB64 = data.content_estorage;
    parametrosAssinatura.id_estorage = codigoEstorage;
    parametrosAssinatura.sequencial = documento.documento.p01_sequencial;
    parametrosAssinatura.qrcode_hash = documento.documento.documento_hash;
    documentosAssinando.value.push(parametrosAssinatura);
    atualizaTimeout();
    socket.emit('sign', parametrosAssinatura);
}

const rejeitarAssinaturas = async () => {
    loadingRejeitandoAssinaturas.value = true;

    const solicitacoes = {};
    solicitacoes.justificativa = justificativa.value;
    solicitacoes.ids = [];
    selectedDocumentos.value.forEach(doc => {
        solicitacoes.ids.push(doc.id);
    });

    let resp;

    try {
        resp = await window.axios.post(
            'v4/api/patrimonial/protocolo/documentos/rejeitar-assinaturas-documentos',
            { solicitacoes }
        );
    } catch (e) {
        loadingRejeitandoAssinaturas.value = false;

        let erros = "";

        if (e.response.data.data.erros.length !== 0) {
            erros = handleErrors(e.response.data.data.erros);
        }

        const mensagem = e.response.data.message ? e.response.data.message : "Não foi possível rejeitar assinaturas";

        return Swal.fire({
            html: mensagem + ": <br><br>" + erros,
            icon: "error",
            confirmButtonColor: "#4a789c",
            width: "fit-content"
        });
    }

    loadingRejeitandoAssinaturas.value = false;
    justificativa.value = '';

    let mensagem = "Assinaturas rejeitadas com sucesso!";
    let icone = "success";

    if (resp.data.data.erros.length !== 0) {
        const erros = handleErrors(resp.data.data.erros);

        mensagem = "Não foi possível rejeitar as seguintes assinaturas: <br><br>" + erros;
        icone = "error";
    }

    Swal.fire({ html: mensagem, icon: icone, confirmButtonColor: "#4a789c", width: "fit-content" });

    getSolicitacoes();
}

const onSearch = (localFilter) => {
    Object.assign(filter, localFilter);
    getSolicitacoes();
};

const limpar = () => {
    exibirProgresso.value = false;
    certificadoSelecionado.value = null;
    timeout.value = null;
    getSolicitacoes();
}

const atualizaTimeout = () => {
    clearTimeout(timeout.value);
    let timeoutTime = (selectedDocumentos.value.length - documentosAssinados.value.length) * 20000;
    if (timeoutTime < 0) {
        timeout.value = setTimeout(() => {
            alert("Tempo de assinatura expirado!");
            limpar();
        }, timeoutTime);
    }
}

const sort = (event) => {
    sortField.value = event.sortField;
    sortOrder.value = event.sortOrder;

    getSolicitacoes();
}

const clearFilterAndReload = () => {
    clearFilter();
    clearSort()
    getSolicitacoes();
}

const clearFilter = () => {
    filter.id = null;
    filter.solicitante = null;
    filter.cgm = null;
    filter.data_inicio = null;
    filter.data_fim = null;
    filter.descricao = null;
    filter.processo = null;
    solicitacaoId.value = null;
}

const clearSort = () => {
    sortField.value = '';
    sortOrder.value = -1;
}

const onPageChange = (event) => {
    rowsPerPage.value = event.rows;
    getSolicitacoes({ page: event.page, rows: rowsPerPage.value });
}

const handlePaginate = (data) => {
    paginate.rows = rowsPerPage.value || 10;
    paginate.total = data.total || 0;
    paginate.current_page = data.current_page || 1;
    paginate.to = data.to || 0;
}

const handleNoResultsMessage = () => {
    if (props.filtro.status === 'assinados') {
        return "Você não possui nenhum documento assinado";
    } else if (props.filtro.status === 'rejeitados') {
        return "Você não possui nenhuma solicitação rejeitada";
    }

    return "Você não possui nenhuma solicitação de assinatura"
}

const handleMensagemProgresso = () => {
    return "Assinando documentos... " +
        `${documentosAssinados.value.length ?? 0}/${selectedDocumentos.value.length ?? 0} ` +
        `(${percentualProgresso.value}%)`;
}

const handleErrors = (data) => {
    return Object.entries(data).map(
        ([id, msg]) => `(ID ${id}) ${msg}`
    ).join("<br>");
}

const socket = io('http://localhost:9000');

socket.on('connect', () => {
    socket.emit('getCertificates', {});
    assinadorAtivo.value = true;
});

socket.on('connect_error', () => {
    assinadorAtivo.value = false;
});

socket.on('disconnect', () => {
    assinadorAtivo.value = false;
});

socket.on('certificates', (event) => {
    certificates.value = event.certList;
});

socket.on('signed', async (event) => {
    let fileB64 = event.fileB64;
    let arquivoAssinado = documentosAssinando.value.filter(file => {
        return parseInt(event.fileID) === parseInt(file.id_estorage);
    }).shift();
    arquivoAssinado.base64 = fileB64;
    await salvarArquivoAssinado(arquivoAssinado);
    documentosAssinados.value.push(arquivoAssinado);
    atualizaTimeout();
});

watch(() => ({
        totalAssinados: documentosAssinados.value.length,
        totalErros: assinaturasComErros.value.length,
    }),
    ({ totalAssinados, totalErros }) => {
        const totalProcessados = totalAssinados + totalErros;
        const totalSelecionados = selectedDocumentos.value.length;

        if (totalProcessados === totalSelecionados) {
            if (totalErros === 0) {
                Swal.fire({
                    text: `Documentos assinados com sucesso!`,
                    icon: "success",
                    confirmButtonColor: "#4a789c"
                });
            } else {
                const errosString = assinaturasComErros.value.join("<br>");
                Swal.fire({
                    html: "Não foi possível assinar os seguintes documentos: <br><br>" + errosString,
                    icon: "error",
                    confirmButtonColor: "#4a789c",
                    width: "fit-content"
                });
            }

            selectedDocumentos.value = [];
            limpar();
        }
    },
    { deep: true }
);

onMounted(() => {
    getSolicitacoes();
});
</script>

<template>
    <ModalProgressBar
        :is-loading="exibirProgresso"
        :message="handleMensagemProgresso()"
        :percentual-progresso="percentualProgresso"
    />

    <ModalLoading :is-loading="loadingRejeitandoAssinaturas" message="Rejeitando Assinaturas..." />

    <ModalInclusaoJustificativa
        v-model:visible="modalInclusaoJustificativa"
        :rejeitarAssinaturas="rejeitarAssinaturas"
        :justificativa="justificativa"
        @update:justificativa="(newValue) => justificativa = newValue"
    />

    <SolicitacoesActions
        :assinarEcidade="assinarEcidade"
        :assinarA3="assinarA3"
        :assinadorAtivo="assinadorAtivo"
        :exibirProgresso="exibirProgresso"
        :certificates="certificates"
        :certificadoSelecionado="certificadoSelecionado"
        :selectedDocumentos="selectedDocumentos"
        :modalInclusaoJustificativa="modalInclusaoJustificativa"
        @update:certificado-selecionado="(newValue) => certificadoSelecionado = newValue"
        @update:modalInclusaoJustificativa="(newValue) => modalInclusaoJustificativa = newValue"
    />

    <SolicitacoesAssinaturaFilter
        :filter="filter"
        :getSolicitacoes="getSolicitacoes"
        @onSearch="onSearch"
        @clearFilter="clearFilterAndReload"
    />

    <div>
        <AssinaturasTable
            v-model:selectedDocumentos="selectedDocumentos"
            :documentos="documentos"
            :showTable="showTable"
            :loadingDocumentos="loadingDocumentos"
            :exibirProgresso="exibirProgresso"
            :filtro="filtro"
            @update:selectedDocumentos="(newValue) => selectedDocumentos = newValue"
            @onSort="sort"
        />

        <p v-if="!loadingDocumentos && documentos.length === 0" class="no-results-message">
            {{ handleNoResultsMessage() }}
        </p>
    </div>

    <Paginator
        v-if="paginate.total > 0"
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
.no-results-message {
    margin-top: 40px;
    text-align: center;
    font-size: 1.3rem;
    color: var(--text-color);
}

:deep(.p-dropdown) {
    border: 1px solid #939393;
    border-radius: 2rem;
    padding: 0 5px;
}
</style>
