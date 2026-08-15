<script setup>
import {onMounted, ref} from "vue";
import ModalLoading from "@modules/Components/ModalLoading.vue";
import Trumbowyg from "vue-trumbowyg";
import 'trumbowyg/dist/ui/trumbowyg.min.css';
import 'trumbowyg/dist/plugins/colors/trumbowyg.colors.min';
import 'trumbowyg/dist/plugins/history/trumbowyg.history.min';
import {useToast} from "primevue/usetoast";
import Transferencia from "@modules/Patrimonial/Protocolo/AndamentoProcesso/Components/Transferencia.vue";
import AcoesRedeSim from "@modules/Patrimonial/Protocolo/AndamentoProcesso/Components/RedeSim/AcoesRedeSim.vue";
import VisualizadorDados from "@modules/Patrimonial/Protocolo/AndamentoProcesso/Components/RedeSim/VisualizadorDados.vue";
import MensageriaProtocolo from "@modules/Patrimonial/Protocolo/AndamentoProcesso/Components/MensageriaProtocolo.vue";
import Swal from "sweetalert2";
import VerificaRecebimentoMassa from "@modules/Patrimonial/Protocolo/AndamentoProcesso/Components/VerificaRecebimentoMassa.vue";

$.trumbowyg.svgPath = window.ECIDADE_PATH + 'public/trumbowyg/ui/icons.svg';

const emit = defineEmits([
  'receberProcesso',
  'visualizarMensagens',
  'attProcessos',
  'onTransferirProcesso',
  'onArquivarProcesso',
  'onReceberProcesso',
  'onDepacharProcesso'
]);
const props = defineProps([
    'processo',
    'permitereceber',
    'permitearquivar',
    'permitedespacho',
    'permitetransferencia',
    'visualizaOutraJanela'
]);
const showDialog = ref(false);
const showDialogAssinatura = ref(false);
const urlIframeAssinatura = ref('');
const showDialogSolicitacaoAssinatura = ref(false);
const urlIframeSolicitacaoAssinatura = ref('');
const despachoPublicoTexto = ref();
const opcoes = ref([
    { name: 'Sim', code: true },
    { name: 'Não', code: false }
]);
const despachoPublico = ref(opcoes.value[0]);
const btnSelecionarArquivo = ref(null);
const btnRemoverArquivo = ref(null);
const anexos = ref([]);
const processoDados = ref();
const despachosAnteriores = ref(null);
const modalLoading = ref(false);
const textLoad = ref('');
const idUserLogado = ref([]);
const editor = ref({
    config: {
        btns: [
            [
                'foreColor',
                'backColor',
                'historyUndo',
                'historyRedo',
            ],
            ['strong', 'em', 'del'],
            ['formatting'],
            ['superscript', 'subscript'],
            ['link'],
            ['image'], // Our fresh created dropdown
            ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
            ['unorderedList', 'orderedList'],
            ['horizontalRule'],
            ['removeformat'],
        ]
    },
});
const toast = useToast();
const dialogTransferencia = ref(false);
const upload = ref(false);
const dialogAcoesRedeSim = ref(false);
const dialogViewDadosInscricao = ref(null);
const arquivaProcDialog = ref(false);
const motivoArquivamento = ref('');
const modalMensagem = ref(false);
//var para ajudar no iframe de consulta de processo que leva para este componente
//pro3_consultaprocesso002.php
const camposForm = ref([]);
const closable = ref(true);
const urlPdf = ref('');
const showPDF = ref(false);
const verificaRecebimentoMassa = ref(null);

const defaultPaginate = function () {
    this.perPage = 5;
    this.page = 0;
    this.total = 0;
    this.offset = 0;
}
const paginate = ref(defaultPaginate);

onMounted(() => {
    getProcessos(props.processo);
});

const getProcessos = async (codProcesso) => {
    textLoad.value = "Buscando Processo...";
    modalLoading.value = true;

    try {
        camposForm.value.codProcesso = codProcesso;
        const urlParams = new URLSearchParams({...camposForm.value});
        const resp = await window.axios.get(
            `v4/api/patrimonial/protocolo/processo/buscarProcessos?${urlParams.toString()}`
        );

        processoDados.value = resp.data.data.data[0];
        modalLoading.value = false;

        if (resp.data.data.data.length < 1) {
            toast.add({severity: 'error', summary: 'Erro', detail: 'Erro ao buscar processo.'});
        }

        await getDespachosAnteriores();
    } catch (e) {
        processoDados.value = [];
        modalLoading.value = false;
    }
}

const getDespachosAnteriores = async ({page, rows} = {page: 0, rows: 5}) => {
    textLoad.value = "Buscando Despachos Anteriores...";
    modalLoading.value = true;

    try{
        paginate.value.page = ++page;
        if (rows) {
            if (paginate.value.perPage !== parseInt(rows)) {
                paginate.value.page = 1;
            }
            paginate.value.perPage = parseInt(rows);
        }

        const urlParams = new URLSearchParams({...paginate.value});
        const resp = await window.axios.get(
            `v4/api/patrimonial/protocolo/processo/despachos-anteriores/${processoDados.value.codigo}?${urlParams.toString()}`
        );

        if (resp.error === true) {
            modalLoading.value = false;
            alert('Erro ao buscar informações do processo');
        } else {
            despachosAnteriores.value = resp.data.data.data.data;
            const {current_page, total, per_page} = resp.data.data.data;
            paginate.value = {
                page: current_page,
                offset: current_page * per_page - 1,
                total,
                perPage: parseInt(per_page)
            };
            idUserLogado.value = resp.data.data.userId;

            modalLoading.value = false;
        }
    } catch (e) {
        modalLoading.value = false;
        despachosAnteriores.value = [];
    }
}

const visualizadorDeDocumentos = (codigosEStorage, index) => {
    const url = `${CurrentWindow.corpo.CurrentWindow.ECIDADE_REQUEST_PATH}db_visualizador_documentos.php?ids=${codigosEStorage}&viewIndex=${index}`;
    if (props.visualizaOutraJanela) {
        window.open(url);
    } else {
        js_OpenJanelaIframe(
            'CurrentWindow.corpo',
            'db_visualizador_imagens',
            url,
            'Visualizador de documentos',
            true
        );
    }
}


const visualizarDocumentos = async (despacho) => {
    textLoad.value = "Buscando Documentos...";
    modalLoading.value = true;
    const parametros = {}
    parametros.codigoProcesso =processoDados.value.codigo;
    parametros.procandamint = despacho.codigo;

    try {
        const resp = await window.axios.post(
            `v4/api/patrimonial/protocolo/processo/processodocumento/documentosPorProcAndamInt`,
            parametros
        ).then(response => {
            if (response.data.error == true) {
                modalLoading.value = false;
                alert(response.data.message);
                return;
            }

            var codigosEStorage = [];
            var index = 0;

            response.data.data.forEach((documento) => {
                codigosEStorage.push(documento.id_estorage);
            });

            if (codigosEStorage.length == 0) {
                modalLoading.value = false;
                alert("Nenhum documento encontrado para o processo.");
                return false;
            }
            modalLoading.value = false;

            visualizadorDeDocumentos(codigosEStorage, index);
        });
    } catch (e) {
        modalLoading.value = false;
    }
}

const visualizarDocumentosPorProcesso = async () => {
    textLoad.value = "Buscando Documentos...";
    modalLoading.value = true;
    const parametros = {}
    parametros.codigoProcesso = processoDados.value.codigo;

    try {
        const resp = await window.axios.post(
            `v4/api/patrimonial/protocolo/processo/processodocumento/documentosPorProcesso`,
            parametros
        ).then(response => {
            if (response.data.error == true) {
                alert(response.data.message);
                modalLoading.value = false;
                return;
            }

            var codigosEStorage = [];
            var index = 0;

            response.data.data.forEach((documento) => {
                codigosEStorage.push(documento.id_estorage);
            });

            if (codigosEStorage.length == 0) {
                alert("Nenhum documento encontrado para o processo.");
                modalLoading.value = false;
                return false;
            }
            modalLoading.value = false;

            visualizadorDeDocumentos(codigosEStorage, index);
        });
    } catch (e) {
        modalLoading.value = false;
    }
}

const assinarDocumentos = function (despacho) {
    var url = CurrentWindow.corpo.CurrentWindow.ECIDADE_REQUEST_PATH;
    url = url.replace('//w', '/w');

    urlIframeAssinatura.value = `${url}db_assinar_documentos.php?codigoProcesso=${processoDados.value.codigo}&procandamint=${despacho.codigo}`;
    showDialogAssinatura.value = true;
}

const atualizarListaArquivos = ({files} = {files: []}) => {
    anexos.value = files;
}

const consultaProcesso = function () {
    const url = CurrentWindow.corpo.CurrentWindow.ECIDADE_REQUEST_PATH;
    js_OpenJanelaIframe(
        'CurrentWindow.corpo',
        'db_consulta_processo',
        `${url}pro3_consultaprocesso002.php?codproc=${processoDados.value.codigo}`,
        'Consulta Processso',
        true
    );
}

const solicitarAssinatura = function (despacho) {
    var url = CurrentWindow.corpo.CurrentWindow.ECIDADE_REQUEST_PATH;
    url = url.replace('//w', '/w');

    urlIframeSolicitacaoAssinatura.value = `
        ${url}web/patrimonial/protocolo/solicitacao-assinatura/processo/${processoDados.value.codigo}/despacho/${despacho.codigo}
    `;
    showDialogSolicitacaoAssinatura.value = true;
}

const closeDialog = (e) => {
    if (processoDados.value.codigostatus !== 1) {
        upload.value.clear();
    }
    showDialog.value = false;
    despachoPublicoTexto.value = undefined;
    despachoPublico.value = opcoes.value[0];
    motivoArquivamento.value = '';
    arquivaProcDialog.value = false;
}

const closeDialogAssinatura = (e) => {
    showDialogAssinatura.value = false;
    getDespachosAnteriores();
}

const closeDialogSolicitacaoAssinatura = (e) => {
    showDialogSolicitacaoAssinatura.value = false;
    getDespachosAnteriores();
}

const openDialog = (e) => {
    showDialog.value = true;
}

const verificaAssinatura = (data) => {
    if (data.codigo_assinante !== null) {
        if ((parseInt(data.codigo_assinante) === parseInt(idUserLogado.value))) {
            return 'pi pi-file-edit pulsacao';
        } else {
            return 'pi pi-file-edit';
        }
    }
    return 'pi pi-file-edit';
}

const despachar = async () => {
    if (despachoPublico.value === undefined) {
        toast.add({severity: 'warn', summary: 'campo obrigatório', detail: 'Selecione se o daspacho é público ou não.', life: 3000});
        return false;
    }
    if (despachoPublicoTexto.value === undefined) {
        toast.add({severity: 'warn', summary: 'campo obrigatório', detail: 'Preencha o campo Despacho.', life: 3000});
        return false;
    }

    textLoad.value = "Despachando Processo...";
    modalLoading.value = true;

    const selectFiles = [];
    for (let anexo of anexos.value) {
        let fileBuffer = await anexo.arrayBuffer();
        let blob = await generateBlob(fileBuffer, anexo.type);
        let base64 = await covertBlobToBase64(blob);
        selectFiles.push({
            nome: anexo.name,
            type: anexo.type,
            conteudo: base64
        });
    }

    const parametros = {};
    parametros.despachoInterno = despachoPublicoTexto.value;
    parametros.codigoProcesso = processoDados.value.codigo;
    parametros.despachoPublico = despachoPublico.value.code;
    parametros.hash = processoDados.value.hash;
    parametros.origemMensagem = false;
    parametros.acao = 'despachar';
    parametros.despachoAnexos = selectFiles;
    parametros.id_item_menu = 229288;

    try {
        const resp = await window.axios.post(
            'v4/api/patrimonial/protocolo/processo/processar',
            parametros
        );

        if (resp.data.data.houveAlteracao) {
            modalLoading.value = false;
            toast.add({severity: 'error', summary: 'Erro', detail: resp.data.data.mensagem});
            return;
        }

        modalLoading.value = false;
        if (resp.data.data.error) {
            toast.add({severity: 'error', summary: 'Erro', detail: 'Erro ao despachar processo.', life: 3000});
        } else {
            emit('onDepacharProcesso');
            upload.value.clear();
            despachoPublicoTexto.value = "";
            despachoPublico.value = opcoes.value[0];
            await getProcessos(processoDados.value.codigo)
            await getDespachosAnteriores();
        }

    } catch (e) {
        console.log('erro' + e);
        if (e.response.data.error) {
            await Swal.fire(
                'Atenção',
                e.response.data.message,
                "warning"
            );
        }
        modalLoading.value = false;
    }
}

const generateBlob = async (fileBuffer, type) => {
    return new Blob([fileBuffer], {type});
};

const covertBlobToBase64 = (blob) => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(blob);
        reader.onload = () => {
            resolve(reader.result);
        }
        reader.onerror = () => {
            reject()
        }
    });
}

const transferirDialog = function () {
    dialogTransferencia.value.setProcesso(processoDados.value)
    dialogTransferencia.value.openDialog();
}

const fecharDialogsAposTransferencia = function () {
    dialogTransferencia.value.closeDialog();
    closeDialog();
    emit('onTransferirProcesso');
    emit('attProcessos');
}

const escapeHtml = (texto) => {
    let htmlString = texto;
    let tempElement = document.createElement('div');
    tempElement.innerHTML = htmlString;
    return tempElement.textContent || tempElement.innerText;
}

const acoesRedeSimProcesso = function (processo, enviarRespostaRedeSim = false) {
    dialogAcoesRedeSim.value.setProcesso(processo);
    dialogAcoesRedeSim.value.openDialog(enviarRespostaRedeSim);
}

const visualizarDados = async (processo) => {
    textLoad.value = "Buscando dados da inclusão...";
    modalLoading.value = true;

    try {
        const resp = await window.axios.get(
            `v4/api/tributario/issqn/redesim/dados-estabelecimento/?processo=${processo}`
        );

        if (resp.data.error) {
            modalLoading.value = false;
            toast.add({severity: 'error', summary: 'Erro', detail: 'Erro ao visualizar dados da inscrição.'});
            return;
        }

        var establishmentData = resp.data.data.establishmentData;
        var jsonInfo = Object.assign(JSON.parse(establishmentData));
        modalLoading.value = false;
        dialogViewDadosInscricao.value.openDialog(jsonInfo, processoDados.value.isProcessoRedesimInclusaoInscricao);
    } catch (e) {
        console.log('erro' + e);
        modalLoading.value = false;
    }
}

const arquivar = async () => {
    if (motivoArquivamento.value === '') {
        toast.add({severity: 'warn', summary: 'campo obrigatório', detail: 'Preencha o campo motivo.', life: 3000});
        return false;
    }

    textLoad.value = "Arquivando Processo...";
    modalLoading.value = true;

    const parametros = {};
    parametros.codigo = processoDados.value.codigo;
    parametros.motivo = motivoArquivamento.value;

    try {
        const resp = await window.axios.post(
            `v4/api/patrimonial/protocolo/processo/arquivar`,
            parametros
        );

        if (resp.data.error) {
            toast.add({severity: 'error', summary: 'Erro', detail: 'Erro ao arquivar processo.'});
            modalLoading.value = false;
            return;
        }

        motivoArquivamento.value = '';
        modalLoading.value = false;
        emit('attProcessos');
        emit('onArquivarProcesso');
        closeDialog();
    } catch (e) {
        console.log('erro' + e);
        if (e.response.data.error) {
            await Swal.fire(
                'Atenção',
                e.response.data.message,
                "warning"
            );
        }
        modalLoading.value = false;
    }
}

const tituloDialog = function () {
    if (processoDados.value !== undefined) {
        return processoDados.value.processo.toString();
    }
    return '';
}

const retornaStatus = function () {
    if (processoDados.value !== undefined) {
        return processoDados.value.codigostatus;
    }
    return 1;
}

const isRedeSim = function () {
    if (processoDados.value !== undefined) {
        return processoDados.value.isProcessoRedesim;
    }
    return false;
}

const receberProcessoDialog = async (processo, verificaTransferencia = true) => {
    textLoad.value = "Recebendo processo...";
    modalLoading.value = true;
    const parametros = {}
    parametros.codigoTransferencia = processo.transferencia;
    parametros.codigoProcesso = processo.codigo;
    parametros.hash = processo.hash;
    parametros.acao = 'receber';
    parametros.id_item_menu = 229286;
    parametros.processos = [];
    parametros.processos.push(processo);

    if (verificaTransferencia) {
        var processosComRecebimentoEmMassa = await verificaRecebimentoProcessoMultiplos(parametros.processos);

        if (processosComRecebimentoEmMassa.length > 0) {
            modalLoading.value = false;
            verificaRecebimentoMassa.value.openDialog(processosComRecebimentoEmMassa);
            return;
        }
    }

    try{
        const resp = await window.axios.post(
            `v4/api/patrimonial/protocolo/processo/processar`,
            parametros
        );

        if (resp.data.data.error === true) {
            toast.add({severity: 'error', summary: 'Erro', detail: 'Erro ao tentar receber processo', life: 3000});
            modalLoading.value = false;
            return false;
        }

        await getProcessos(props.processo);
        emit('onReceberProcesso', processoDados, true);

    } catch (e) {
        modalLoading.value = false;
        if (e.response.data.error) {
            await Swal.fire(
                'Atenção',
                e.response.data.message,
                "warning"
            );
        }
    }
}

const visualizarMensagens = function (processo) {
    attAposFecharMensagem.value = processo.mensagens_novas > 0;
    processoMensagem.value = processo.codigo;
    modalMensagem.value = true;
}

const closeDialogPreview = (e) => {
  showPDF.value = false;
}

const previewDespacho = async () => {
  modalLoading.value = true;
  const formPreview = {};
  formPreview.processo =  processoDados.value.codigo;
  if(!despachoPublicoTexto.value) {
    toast.add({severity: 'warn', summary: 'campo obrigatório', detail: 'Preencha o campo Despacho.', life: 3000});
    modalLoading.value = false;
    return;
  }
  formPreview.despacho = despachoPublicoTexto.value;

  try
  {
    const resp = await axios.post("/v4/api/patrimonial/protocolo/despacho-preview",
        formPreview,
        {
          responseType: 'blob'
        }
    );
    const data = await resp.data;
    const blob = new Blob([data], {type: 'application/pdf'});
    modalLoading.value = false;
    urlPdf.value = window.URL.createObjectURL(blob);
    showPDF.value = true;
  }catch (e) {
    toast.add({severity: 'error', summary: 'Erro', detail: 'Ocorreu um erro!.', life: 3000});
  }finally {
    modalLoading.value = false;
  }

}

const receberProcessoTransferenciaMultiplas = (receber = false, processos) => {
    if (Array.isArray(processos)) {
        if (processos.length === 1) {
            if (processos[0].transferencia) {
                receberProcessoDialog(processos[0], false);
            }
        }
    }
}

const verificaRecebimentoProcessoMultiplos = async (processos) => {
    var processosArray = [];

    processos.forEach((processo, index) => {
        processo.index = index;
    });

    const parametros = {};
    parametros.processos = processos;

    try {
        const resp = await window.axios.post(
            'v4/api/patrimonial/protocolo/processo/verifica-recebimento-processo-multiplos',
            parametros
        );

        const retorno = resp.data.data;
        processosArray = retorno.processos;

        return processosArray;
    } catch (e) {
        console.error('erro ' + e);
    }
}

defineExpose({
    closeDialog,
    openDialog
});
</script>

<template>
    <ModalLoading
        :is-loading="modalLoading"
        :message="textLoad"
    />

    <Transferencia
        ref="dialogTransferencia"
        @fecharDialogsAposTransferencia="fecharDialogsAposTransferencia"
    />

    <Dialog
        header="Preview Pdf"
        class="p-dialog-maximized"
        :maximizable="true"
        :modal="true"
        :style="{ width: '1000px' }"
        :visible="showPDF"
        :closable="true"
        @update:visible="closeDialogPreview"
    >
        <div class="mt-6" style="height:100%;margin:1px">
            <iframe width="100%" height="100%" v-if="showPDF" :src="urlPdf" ref="framepreview" id="framepdf"></iframe>
        </div>
    </Dialog>

    <Dialog
        header="Assinar Documentos"
        class="p-dialog p-component p-dialog-maximized"
        :visible="showDialogAssinatura"
        @update:visible="closeDialogAssinatura"
    >
        <iframe :src="urlIframeAssinatura" width="100%" height="100%" frameborder="0"></iframe>
    </Dialog>

    <Dialog
        header="Solicitação de Assinatura"
        class="p-dialog p-component p-dialog-maximized"
        :visible="showDialogSolicitacaoAssinatura"
        @update:visible="closeDialogSolicitacaoAssinatura"
    >
        <iframe :src="urlIframeSolicitacaoAssinatura" width="100%" height="100%" frameborder="0"></iframe>
    </Dialog>

    <Dialog
        :header=tituloDialog()
        v-model:visible="arquivaProcDialog"
        modal
        style="width: 500px"
    >
        <ModalLoading
            :is-loading="modalLoading"
            :message="textLoad"
        />
        <div class="corpo">
            <div class="container" style="display: grid;">
                <label for="motivo">Motivo</label>
                <Textarea id="motivo" v-model="motivoArquivamento" variant="filled" rows="5" cols="30" style="width:100%"/>
            </div>
            <div class="container" style="text-align: center;">
                <Button
                    @click="motivoArquivamento='';arquivaProcDialog = false;"
                    label="Cancelar"
                    class="mr-4"
                    iconPos="right"
                >
                    <template #icon>
                        <i style="margin-right: 10px" class="pi pi-times"></i>
                    </template>
                </Button>
                <Button
                    severity="warning"
                    @click="arquivar()"
                    label="Arquivar"
                    iconPos="right"
                >
                    <template #icon>
                        <i style="margin-right: 10px" class="pi pi-inbox"></i>
                    </template>
                </Button>
            </div>
        </div>
    </Dialog>

    <AcoesRedeSim ref="dialogAcoesRedeSim" />

    <VisualizadorDados ref="dialogViewDadosInscricao" />

    <Dialog v-model:visible="modalMensagem" class="p-dialog-maximized" header=" ">
        <MensageriaProtocolo
            :processo="props.processo"
            :dialog="modalMensagem"
        />
    </Dialog>

    <VerificaRecebimentoMassa
        ref="verificaRecebimentoMassa"
        @closeDialog="receberProcessoTransferenciaMultiplas"
    />

    <div class="topo">
        <Toolbar style="background: none !important; border: none !important; width: 100% !important;">
            <template #start>
                <span :title="props.permitereceber ? 'Receber Processo' : 'Você não possui permissão para Receber.'">
                    <Button
                        v-if="retornaStatus() === 1"
                        @click="receberProcessoDialog(processoDados);"
                        class="btn-acoes-icon"
                        :icon="props.permitereceber ? 'pi pi-arrow-down-left' : 'pi pi-lock'"
                        severity="info"
                        text raised rounded aria-label="receber"
                        label="Receber"
                        :disabled="!props.permitereceber"
                    ></Button>
                </span>

                <span :title="props.permitedespacho ? 'Despachar Processo' : 'Você não possui permissão para Despachar.'">
                    <Button
                        v-if="retornaStatus() !== 1"
                        @click="despachar()"
                        class="btn-acoes-icon"
                        :icon="props.permitedespacho ? 'pi pi-file-import' : 'pi pi-lock'"
                        severity="warning" text raised rounded aria-label="despachar"
                        label="Despachar"
                        :disabled="!props.permitedespacho"
                    ></Button>
                </span>

                <span :title="props.permitetransferencia ? 'Transferir Processo' : 'Você não possui permissão para Transferir.'">
                    <Button
                        v-if="retornaStatus() !== 1"
                        @click="transferirDialog()"
                        class="btn-acoes-icon"
                        :icon="props.permitetransferencia ? 'pi pi-arrow-right-arrow-left' : 'pi pi-lock'"
                        severity="success" text raised rounded aria-label="transferir"
                        label="Transferir"
                        :disabled="!props.permitetransferencia"
                    ></Button>
                </span>

                <span :title="props.permitearquivar ? 'Arquivar Processo' : 'Você não possui permissão para Arquivar.'">
                    <Button
                        v-if="retornaStatus() !== 1"
                        @click="arquivaProcDialog = true"
                        style="color: #444242;"
                        class="btn-acoes-icon"
                        :icon="props.permitearquivar ? 'pi pi-inbox' : 'pi pi-lock'"
                        severity="contrast" text raised rounded aria-label="arquivar"
                        label="Arquivar"
                        :disabled="!props.permitearquivar"
                    ></Button>
                </span>
            </template>

            <template #center>
                <Button
                    style="font-size:15px !important;"
                    @click="consultaProcesso()"
                    class="filho-topo btn-acoes"
                    :label="tituloDialog()"
                    title="Visualizar Processo"
                    icon="pi pi-book"
                    iconPos="left"
                ></Button>
            </template>

            <template #end>
                <Button
                    v-if="isRedeSim()"
                    @click="visualizarDados(processoDados.codigo)"
                    class="filho-topo btn-acoes"
                    label="Dados"
                    title="Visualizar Dados"
                    icon="pi pi-list"
                    iconPos="left"
                ></Button>

                <Button
                    v-if="isRedeSim()"
                    @click="acoesRedeSimProcesso(processoDados)"
                    class="filho-topo btn-acoes"
                    label="Ação"
                    title="Executar Ação REDESIM"
                    iconPos="right"
                >
                    <template #icon>
                        <i style="margin-right: 10px" class="pi pi-list"></i>
                    </template>
                </Button>

                <Button
                    v-if="isRedeSim()"
                    @click="acoesRedeSimProcesso(processoDados, true)"
                    :class="retornaStatus() !== 1 ? 'filho-topo btn-acoes enviar-redesim' : 'filho-topo btn-acoes'"
                    label="REDESIM"
                    title="Enviar Resposta REDESIM"
                    icon="pi pi-send"
                    iconPos="left"
                ></Button>

                <Button
                    @click="visualizarDocumentosPorProcesso()"
                    class="filho-topo btn-acoes"
                    label="Documentos"
                    icon="pi pi-images"
                    iconPos="left"
                    title="Visualizar Documentos"
                ></Button>

                <Button
                    v-if="retornaStatus() !== 1"
                    @click="modalMensagem = true"
                    class="filho-topo btn-acoes"
                    label="Mensagens"
                    icon="pi pi-envelope"
                    iconPos="left"
                    title="Visualizar Mensagens"
                ></Button>
            </template>
        </Toolbar>
    </div>

    <Fieldset
        legend="Despacho"
        style="margin-top:10px;"
        v-if="retornaStatus() !== 1"
    >
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
            <div class="despacho-publico">
                <p>Despacho Público:</p>
                <Dropdown v-model="despachoPublico" :options="opcoes" optionLabel="name"/>
            </div>

            <Button icon="pi pi-eye" @click="previewDespacho" label="Despacho Preview" class="despacho-preview-btn"></Button>
        </div>

        <div>
            <trumbowyg
                v-model="despachoPublicoTexto"
                :config="editor.config"
                name="content">
                required
            </trumbowyg>

            <Fieldset
                legend="Anexar Documentos"
                style="margin-top:10px"
            >
                <FileUpload
                    name="anexos[]"
                    ref="upload"
                    url=""
                    class="fileupload-content"
                    accept="application/pdf,image/png,image/jpeg,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                    :multiple="true"
                    @upload="()=>{}"
                    @select="atualizarListaArquivos"
                    @remove="atualizarListaArquivos"
                    @clear="atualizarListaArquivos"
                >
                    <template #header="{ chooseCallback, uploadCallback, clearCallback, files }">
                        <div class="flex flex-wrap justify-content-between align-items-center flex-1 gap-2">
                            <div class="flex gap-2">
                                <Button
                                    icon="pi pi-file-pdf"
                                    class="p-button-rounded"
                                    label="Selecione"
                                    ref="btnSelecionarArquivo"
                                    @click="chooseCallback()"
                                ></Button>

                                <Button
                                    icon="pi pi-trash"
                                    class="p-button-rounded p-button-danger"
                                    label="Limpar"
                                    ref="btnRemoverArquivo"
                                    :disabled="!files || files.length === 0"
                                    @click="clearCallback()"
                                ></Button>
                            </div>
                        </div>
                    </template>

                    <template #empty>
                        <p>Arraste seus arquivos ou selecione</p>
                    </template>
                </FileUpload>
            </Fieldset>
        </div>
    </Fieldset>

    <Fieldset
        legend="Despachos Anteriores"
        style="margin-top:10px"
        :toggleable="true"
        :collapsed="false"
    >
        <DataTable
            showGridlines
            style="margin-top: 10px"
            responsiveLayout="scroll"
            scrollHeight="flex"
            selectionMode="single"
            :scrollable="true"
            :value="despachosAnteriores"
        >
            <template v-if="despachosAnteriores && despachosAnteriores.length > 0">
                <Column field="data" header="Data"/>
                <Column field="tipo" header="Tipo"/>
                <Column field="usuario" header="Usuário"/>
                <Column header="Despacho">
                    <template #body="{ data }">
                        <div class="ellipsis" v-html="data.despacho" v-tooltip="escapeHtml(data.despacho)"></div>
                    </template>
                </Column>
                <Column field="acoes" header="Ações" style="width: 100px">
                    <template #body="{ data }">
                        <div>
                            <i title="Visualizar Documentos" class="pi pi-eye" @click="visualizarDocumentos(data)"></i>
                            <i title="Assinar" :class="verificaAssinatura(data)" @click="assinarDocumentos(data)" style="margin-left:10px"></i>
                            <i title="Solicitar Assinatura" class="pi pi-user-edit" @click="solicitarAssinatura(data)" style="margin-left:10px"></i>
                        </div>
                    </template>
                </Column>
            </template>

            <template v-else>
                <h2 style="text-align:center">Não foram encontrados despachos anteriores</h2>
            </template>
        </DataTable>

        <Paginator
            ref="paginator"
            :rows="paginate.perPage"
            :totalRecords="paginate.total"
            v-model:first="paginate.offset"
            :rowsPerPageOptions="[15, 20, 30]"
            @page="getDespachosAnteriores($event)"
        />
    </Fieldset>
</template>

<style scoped>
.btn-acoes-icon {
    margin-left: 10px;
    margin-right: 10px;
    background-color:white !important;
}

.btn-acoes {
    display: flex !important;
    align-items: center;
    border-radius: 2rem;
    min-width: 110px !important;
    min-height: 36px !important;
}

.enviar-redesim {
    width: 110px !important;
}

.topo {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top:10px;
}

.extrema-esquerda {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.extrema-direita {
    display: flex;
    justify-content: flex-start;
    gap: 10px;
}

.filho-topo {
    margin-right: 10px;
    height: 25px;
    border: 10px;
}

.despacho-publico {
    display: flex;
    align-items: center;
    gap: 10px;
}

.despacho-publico p {
    margin: 0;
}

.despacho-preview-btn {
    display: flex !important;
    align-items: center;
    border-radius: 2rem;
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.5);
    }
    100% {
        transform: scale(1);
    }
}

.pulsacao {
    animation-name: pulse;
    animation-duration: 1s;
    animation-iteration-count: infinite;
    animation-timing-function: ease-in-out;
    color: #0a8cf6
}

.trumbowyg-box {
    min-height: 200px !important;
    height: 200px !important;
}

.fileupload-content {
    height: 100px !important;
}

.ellipsis {
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

:deep(.p-dropdown) {
    border: 1px solid #a5a5a5;
    border-radius: 2rem;
}

:deep(.p-button-danger) {
    background: #d13438;
}

:deep(.p-fileupload-file-remove) {
    display: flex !important;
    background: white;
}

:deep(.p-button .p-button-danger .p-button-text) {
    color: #fff !important;
}
</style>
