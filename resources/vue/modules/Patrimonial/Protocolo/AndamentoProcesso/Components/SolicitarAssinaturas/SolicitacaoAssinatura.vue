<template>
    <main style="padding: 20px; display: flex; flex-direction: column; gap: 10px;">
        <ModalLoading :is-loading="loading" :message="loadingMessage" />

        <DialogCGM
            ref="dialogCGM"
            :carregar-dados-automatico="false"
            :cgmSelected="cgmSelected"
            :removerCgmSelecionado="removerCgmSelecionado"
            :solicitarAssinaturas="solicitarAssinaturas"
            :observacao="observacao"
            @update:observacao="(newValue) => observacao = newValue"
            @select="selecaoDoCgmPeloDialogCGM"
        />

        <DialogInfoSolicitacao
            ref="dialogInfoAssinante"
            :solicitacao="solicitacaoModalShow"
            :cancelarSolicitacao="cancelarSolicitarAssinatura"
            :reenviarSolicitacao="reenviarSolicitacao"
            @update:observacao="(newValue) => observacao = newValue"
        />

        <section class="action-buttons-e-legenda">
            <div class="action-buttons">
                <Button
                    title="Recarregar"
                    icon="pi pi-refresh"
                    rounded
                    @click="getDocumentos"
                />

                <Button
                    :label="allDocumentsSelected ? 'Desmarcar Todos' : 'Selecionar Todos'"
                    rounded
                    @click="toggleSelectAllDocumentos"
                />

                <Button
                    v-if="documentSelected.length !== 0"
                    label="Solicitar Assinatura"
                    icon="pi pi-plus"
                    rounded
                    @click="openDialogCGM"
                />
            </div>

            <div class="legendas">
                <div class="legenda-documento">
                    <i class="pi pi-circle-fill ponto-assinado-maior ponto-verde"></i>
                    <p>Todas solicitações assinadas</p>
                </div>
                <div class="legenda-documento">
                    <i class="pi pi-circle-fill ponto-assinado-maior ponto-vermelho"></i>
                    <p>Faltando assinaturas</p>
                </div>
            </div>
        </section>

        <div
            v-if="loading === false && loadingDocumentos === false && documentos.length === 0"
            class="sem-despachos-message-container"
        >
            <p>Esse despacho não possui documentos</p>
        </div>

        <p
            v-if="loading === false && loadingDocumentos === false && documentos.length !== 0"
            class="mensagem-selecionar"
        >
            Selecione documentos para solicitar assinaturas:
        </p>

        <div class="section-card-documentos">
            <div v-if="loadingDocumentos" class="loading-documentos">
                <ProgressSpinner />
                <h3>Carregando documentos...</h3>
            </div>

            <CardDocumento
                v-for="documento of documentos"
                :key="documento.p01_sequencial"
                :documento="documento"
                :documentoComSolicitacoesVisiveis="documentoComSolicitacoesVisiveis"
                :documentSelected="documentSelected"
                :solicitacoesSelected="solicitacoesSelected"
                :solicitacoesVisiveisESelecionaveis="solicitacoesVisiveisESelecionaveis"
                :allSolicitacoesSelected="allSolicitacoesSelected"
                :codigoProcesso="codigoProcesso"
                :codigoDespacho="codigoDespacho"
                :loading="loading"
                @openAssinantesDocumento="openAssinantesDocumento"
                @cancelarSolicitacoes="cancelarSolicitacoes"
                @toggleSelectAllSolicitacoes="toggleSelectAllSolicitacoes"
                @openDialogInfoAssinante="openDialogInfoAssinante"
                @update:documentSelected="(newDocumentSelected) => documentSelected = newDocumentSelected"
                @update:solicitacoesSelected="(newSolicitacoesSelected) => solicitacoesSelected = newSolicitacoesSelected"
                @update:loading="(newLoadingValue) => loading = newLoadingValue"
            />
        </div>
    </main>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import { useToast } from 'primevue/usetoast';
import DialogCGM from "../../../Components/DialogProtocoloDocumentoCGM.vue";
import DialogInfoSolicitacao from "../../../Components/DialogProtocoloSolicitacao.vue"
import ModalLoading from '../../../../../Components/ModalLoading.vue';
import CardDocumento from './CardDocumento.vue';
import Swal from "sweetalert2";

const props = defineProps(['codigoProcesso', 'codigoDespacho']);

const cgmSelected = ref([]);

const documentos = ref([]);
const documentoComSolicitacoesVisiveis = ref(null);

const dialogCGM = ref(null);
const dialogInfoAssinante = ref(null);

const solicitacoesVisiveisESelecionaveis = ref([]);
const solicitacaoModalShow = ref(null);
const solicitacoesSelected = ref([]);
const allSolicitacoesSelected = ref(false);

const documentSelected = ref([]);
const allDocumentsSelected = ref(false);

const loadingDocumentos = ref(false);
const loading = ref(false);
const loadingMessage = ref('');

const observacao = ref('');

const toast = useToast();

function CgmAssinatura() {
    this.cgm;
    this.nome;
    this.cpf_cnpj;
    this.assinado = false;
}

const getDocumentos = async () => {
    loadingDocumentos.value = true;

    documentos.value = [];
    documentSelected.value = [];

    try {
        let form = {
            codigoProcesso: props.codigoProcesso,
            codigoDespacho: props.codigoDespacho
        }

        let params = new URLSearchParams(form);

        const resp = await axios.get(
            `v4/api/patrimonial/protocolo/solicitacao-assinatura?` + params.toString()
        );

        documentos.value = resp.data.data.filter((documento) => {
            let re = /(?:\.([^.]+))?$/;
            let extensao = re.exec(documento.p01_nomedocumento)[1];
            return !extensao || extensao.includes('pdf', 'PDF');
        });

        loadingDocumentos.value = false;
    } catch (e) {
        loadingDocumentos.value = false;
        if (e.response) {
            alert(e.response.data.message);
            return;
        }
        alert("Ocorreu um erro ao carregar os documentos!");
    }
}

const solicitarAssinaturas = async () => {
    if (cgmSelected.value.length < 1 || documentSelected.value.length < 1) {
        alert("Para solicitar uma assinatura é necessário selecionar no mínimo um CGM e um Documento!");
        return;
    }

    let form = {};
    form.documentos = [];
    documentSelected.value.forEach(documento => {
        cgmSelected.value.forEach(cgm => {
            form.documentos.push({
                documento_id: documento.p01_sequencial,
                documento_nome: documento.p01_descricao,
                cgm_assinante: cgm.cgm
            })
        })
    })
    form.observacao = observacao.value;

    loading.value = true;
    loadingMessage.value = 'Solicitando assinaturas...';

    let resp;

    try {
        resp = await axios.post(
            "v4/api/patrimonial/protocolo/solicitacao-assinatura", form
        );

        let data = resp.data;
        if (data.data.error) {
            alert(data.data.message);
            return;
        }

        loading.value = false;
        limpar();
        getDocumentos();
    } catch (e) {
        loading.value = false;

        let erros = "";

        if (e.response.data.data && e.response.data.data.erros) {
            e.response.data.data.erros.forEach((erro) => {
                 erros += erro + '<br>';
            });
        }

        const mensagem = e.response.data.message ? e.response.data.message : "Não foi possível solicitar assinaturas";

        return Swal.fire({
            html: mensagem + ": <br><br>" + erros,
            icon: "error",
            confirmButtonColor: "#4a789c",
            width: "fit-content"
        });
    }

    let mensagem = "Assinaturas solicitadas com sucesso!";
    let icone = "success";

    if (resp.data.data.erros.length !== 0) {
        let erros = "";
        resp.data.data.erros.forEach((erro) => {
            erros += erro + '<br>';
        });

        mensagem = "Não foi possível solicitar as seguintes assinaturas: <br><br>" + erros;
        icone = "error";
    }

    Swal.fire({ html: mensagem, icon: icone, confirmButtonColor: "#4a789c", width: "fit-content" });

    observacao.value = '';

    getDocumentos();
}

const cancelarSolicitarAssinatura = async (solicitacao_id) => {
    loading.value = true;
    loadingMessage.value = 'Cancelando solicitação...'

    try {
        const resp = await axios.delete(
            `v4/api/patrimonial/protocolo/solicitacao-assinatura/${solicitacao_id}`
        );

        let data = resp.data;

        if (data.data.error) {
            loading.value = false;
            alert(data.data.message);
            return;
        }

        loading.value = false;

        alert("Solicitação cancelada com sucesso!");

        limpar();
        await getDocumentos();
    } catch (e) {
        loading.value = false;
        if (e.response) {
            alert(e.response.data.message);
            return;
        }
        alert("Ocorreu um erro!");
    }
}

const cancelarSolicitacoes = async () => {
    if (solicitacoesSelected.length === 0) {
        alert("Nenhuma solicitação selecionada para cancelar.");
        return;
    }

    loading.value = true;
    loadingMessage.value = 'Cancelando solicitações...'

    try {
        const resp = await axios.post(
            'v4/api/patrimonial/protocolo/solicitacao-assinatura/cancelar-assinaturas',
            {solicitacoes_ids: solicitacoesSelected.value.map(s => s.id)}
        );

        let data = resp.data;

        if (data.data.erros && data.data.erros.length > 0) {
            loading.value = false;
            alert(data.data.erros.join("\n"));
            return;
        }

        loading.value = false;

        alert("Solicitações canceladas com sucesso!");

        limpar();
        await getDocumentos();
    } catch (e) {
        loading.value = false;

        if (e.response) {
            alert(e.response.data.message);
            return;
        }
        alert("Ocorreu um erro!" + e);
    }
}

const reenviarSolicitacao = async ({ solicitacao, documento }) => {
    dialogInfoAssinante.value.closeDialog();

    loading.value = true;
    loadingMessage.value = 'Reenviando solicitação...';

    let form = {};
    form.documentos = [];
    form.documentos.push({
        documento_id: documento.p01_sequencial,
        documento_nome: documento.p01_descricao,
        cgm_assinante: solicitacao.cgm_assinante.z01_numcgm
    })
    form.observacao = observacao.value;

    try {
        await axios.post(
            "v4/api/patrimonial/protocolo/solicitacao-assinatura", form
        );

        loading.value = false;

        limpar();
        getDocumentos();
    } catch (e) {
        loading.value = false;

        let erros = "";

        if (e.response.data.data.erros.length !== 0) {
            e.response.data.data.erros.forEach((erro) => {
                erros += erro + '<br>';
            });
        }

        const mensagem = e.response.data.message ? e.response.data.message : "Não foi possível solicitar assinaturas";

        return Swal.fire({
            html: mensagem + ": <br><br>" + erros,
            icon: "error",
            confirmButtonColor: "#4a789c",
            width: "fit-content"
        });
    }

    let mensagem = "Assinatura solicitada com sucesso!";
    let icone = "success";

    Swal.fire({ html: mensagem, icon: icone, confirmButtonColor: "#4a789c", width: "fit-content" });

    observacao.value = '';

    getDocumentos();
}

const selecaoDoCgmPeloDialogCGM = (cgm) => {
    let cgmAssinatura = new CgmAssinatura();
    cgmAssinatura.cgm = cgm.z01_numcgm;
    cgmAssinatura.nome = cgm.z01_nome;
    cgmAssinatura.cpf_cnpj = cgm.z01_cgccpf;

    const cgmJaAdicionado = cgmSelected.value.some(el => el.cgm === cgmAssinatura.cgm);

    if (cgmJaAdicionado) {
        toast.add({severity: 'error', summary: 'Atenção', detail: 'Este destinatário já foi adicionado!', life: 3000});
    } else {
        cgmSelected.value.push(cgmAssinatura);
    }
}

const removerCgmSelecionado = (cgm) => {
    cgmSelected.value = cgmSelected.value.filter(
        (el) => el.cgm !== cgm.cgm
    );
}

const openDialogCGM = () => {
    dialogCGM.value.openDialog();
}

const openDialogInfoAssinante = ({ solicitacao, documento }) => {
    const solicitacoesRelacionadas = (documentos.value)
        .map((doc) => doc.solicitacao_assinatura)
        .reduce((acc, curr) => acc.concat(curr), [])
        .filter((item) =>
            item.cgm_assinante.z01_nome === solicitacao.cgm_assinante.z01_nome &&
            item.documento_id === solicitacao.documento_id
        )
        .sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

    solicitacaoModalShow.value = { solicitacao, solicitacoesRelacionadas, documento };
    dialogInfoAssinante.value.openDialog();
}

const openAssinantesDocumento = (documento) => {
    if (documentoComSolicitacoesVisiveis.value === documento) {
        documentoComSolicitacoesVisiveis.value = null;
        solicitacoesVisiveisESelecionaveis.value = [];
        solicitacoesSelected.value = [];
    } else {
        documentoComSolicitacoesVisiveis.value = documento;
        solicitacoesVisiveisESelecionaveis.value = documento.solicitacao_assinatura.filter(
            (solicitacao) => !solicitacao.data_assinatura && !solicitacao.data_rejeicao
        );
        allSolicitacoesSelected.value = false;
    }
}

const limpar = () => {
    documentSelected.value = [];
    allDocumentsSelected.value = false;
    cgmSelected.value = [];
    documentos.value = [];
    solicitacoesSelected.value = [];
    allSolicitacoesSelected.value = false;
}

const toggleSelectAllDocumentos = () => {
    if (allDocumentsSelected.value) {
        documentSelected.value = [];
    } else {
        documentSelected.value = [...documentos.value];
    }

    allDocumentsSelected.value = !allDocumentsSelected.value;
};

const toggleSelectAllSolicitacoes = () => {
    if (allSolicitacoesSelected.value) {
        solicitacoesSelected.value = [];
    } else {
        solicitacoesSelected.value = [...solicitacoesVisiveisESelecionaveis.value];
    }

    allSolicitacoesSelected.value = !allSolicitacoesSelected.value;
}

watch(documentSelected, (QtdDocumentosSelecionados) => {
    allDocumentsSelected.value =
        QtdDocumentosSelecionados.length === documentos.value.length && documentos.value.length !== 0
    ;
});

watch(solicitacoesSelected, (QtdSolicitacoesSelecionadss) => {
    allSolicitacoesSelected.value =
        QtdSolicitacoesSelecionadss.length === solicitacoesVisiveisESelecionaveis.value.length
    ;
});

onMounted(() => {
    getDocumentos();
})
</script>

<style scoped>
.action-buttons-e-legenda {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

.action-buttons {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
}

.legenda-documento {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 5px;
}

.legenda-documento p {
    font-size: 1rem;
    color: var(--text-color);
}

.ponto-assinado-maior {
    font-size: 0.7rem;
}

.legendas {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
}

.section-card-documentos {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    width: 100%;
}

.loading-documentos {
    margin-top: 50px;
    display: flex;
    justify-content: center;
    flex-direction: column;
    color: var(--text-color);
}

.ponto-verde {
    color: #287628;
}

.ponto-vermelho {
    color: #9b1313;
}

.sem-despachos-message-container {
    display: flex;
    justify-content: center;
}

.sem-despachos-message-container p {
    font-size: 1.5rem;
    color: var(--text-color);
}

.mensagem-selecionar {
    margin: 0 0 0 5px;
    font-size: 1.2rem;
    color: var(--text-color);
    font-style: italic;
}

@keyframes pulse {
    0% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(0, 0, 0, 0.7);
    }

    70% {
        transform: scale(1);
        box-shadow: 0 0 0 10px rgba(0, 0, 0, 0);
    }

    100% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(0, 0, 0, 0);
    }
}
</style>
