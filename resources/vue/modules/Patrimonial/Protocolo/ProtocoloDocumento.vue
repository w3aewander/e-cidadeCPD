<template>
    <ModalLoading
        :is-loading="modalLoading"
        message="salvando..."
    />

    <Dialog
        header="Preview Pdf"
        :maximizable="true"
        :modal="true"
        :style="{ width: '1000px' }"
        :visible="showPDF"
        :closable="true"
        @update:visible="closeDialogPreviewCapa"
    >
        <div
            class="mt-6"
            style="height:100%;margin:1px"
        >
            <iframe
                width="100%"
                height="100%"
                v-if="showPDF"
                :src="urlPdf"
                id="framepdf"
            ></iframe>
        </div>
    </Dialog>

    <Dialog
        header="Consulta Processo"
        :maximizable="true"
        :modal="true"
        :style="{ width: '1000px' }"
        :visible="showProcesso"
        :closable="true"
        @update:visible="closeDialogProcesso"
    >
        <div
            class="mt-6"
            style="height:100%;margin:1px"
        >
            <iframe
                width="100%"
                height="100%"
                v-if="showProcesso"
                :src="urlConsultaProcesso"
            ></iframe>
        </div>
    </Dialog>
    <Dialog header="Solicitação Assinatura"
            :maximizable="true"
            :modal="true"
            :style="{ width: '1000px' }"
            :visible="showSolicitaAssinatura"
            :closable="true"
            @update:visible="closeDialogSolicitaAssinatura"
    >
        <SolicitacaoAssinatura
          :codigo-despacho="0"
          :codigo-processo="processo.processo.p58_codproc"
        />
    </Dialog>

    <DialogCGM
        ref="dialogCGM"
        @select="selecaoDoCgmPeloDialogCGM"
    />

    <DialogInstituicao
        ref="dialogInstituicao"
        @select="selecaoDaInstituicaoPeloDialogInstituicao"
    />

    <DialogInstituicaoDepartamentos
        ref="dialogInstituicaoDepartamentos"
        :instituicao="form.instituicao ? form.instituicao.codigo : null"
        @select="selecaoDepartamentoPeloDialogInstituicaoDepartamentos"
    />

    <DialogInstituicaoDepartamentoUsuarios
        ref="dialogInstituicaoDepartamentoUsuarios"
        :instituicao="form.instituicao ? form.instituicao.codigo : null"
        :departamento="form.departamento ? form.departamento.coddepto : null"
        @select="selecaoDepartamentoPeloDialogInstituicaoDepartamentoUsuarios"
    />

    <DialogAssunto
        ref="dialogAssunto"
        :codigodocumento="form.documento ? form.documento : null"
        @select="selecaoDoAssuntoPeloDialog"
    />

    <Dialog
        header="Emitir Recibo"
        class="p-dialog p-component p-dialog-maximized"
        :visible="showDialogEmissaoRecibo"
        @update:visible="closeDialogEmissaoRecibo"
    >
        <iframe v-if="showDialogEmissaoRecibo" :src="urlEmissaoRecibo" width="100%" height="100%" frameborder="0"></iframe>
    </Dialog>


    <DialogConsultaProcesso
        ref="dialogConsultaProcesso"
        :carregar-dados-automatico="true"
        :ocultar-pai="false"
        :ocultar-volume="true"
        :ocultar-arquivado="true"
        titulo="Consulta de Documentos"
        @select="selectProcesso"
    />
    <div v-if="processo" class="processo-button-container">
        <Button @click="consultaProcesso"
                class="processo-button"
            ref="tourConsultaProcesso"
            :label="`${processo.processo.p58_numero} / ${processo.processo.p58_ano}`"
        ></Button>
        <hr style="width: 100%; margin-top: 10px"/>
    </div>

    <div
        class="container"
        ref="containerCadastro"
    >
        <div class="grid">
            <div class="col-12">
                <Fieldset
                    legend="Volume"
                    :collapsed="volumeClosed"
                >
                    <template #legend>
                        <div class="flex align-items-center pl-2">
                            <span class="font-bold" style="color:white">Volume</span>
                            <span @click="toogleVolume" class="pi pi-plus font-bold ml-2" ref="tourVolume"></span>
                        </div>
                    </template>
                    <div>
                        <div class="flex flex-warp gap-4 flex-row justify-content-evenly align-content-center" >
                            <div class="flex flex-column flex-grow-1 justify-content-center">
                                <div>
                                    <b>Código :</b>  {{processoSelecionado.processo_codigo ? processoSelecionado.processo_codigo : ''}}
                                </div>
                                <div>
                                    <b>Número :</b>  {{processoSelecionado.processo_numero ? processoSelecionado.processo_numero : ''}}
                                </div>
                                <div>
                                    <b>Tipo :</b> {{processoSelecionado.processo_tipo ? processoSelecionado.processo_tipo : ''}}
                                </div>
                                <div>
                                    <b>Data :</b> {{processoSelecionado.processo_data ? formateDate(processoSelecionado.processo_data) : ''}}
                                </div>
                            </div>
                            <div class="flex flex-column flex-grow-1 justify-content-center">
                                <div>
                                    <b>Instituição :</b>{{processoSelecionado.instituicao_nome  ? processoSelecionado.instituicao_nome : ''}}
                                </div>
                                <div style="width: 200px">
                                    <b>Descrição :</b>{{processoSelecionado.processo_descricao  ? processoSelecionado.processo_descricao : ''}}
                                </div>
                                <div>
                                    <b>Titular CGM :</b> {{processoSelecionado.titular_cgm  ? processoSelecionado.titular_cgm : ''}}
                                </div>
                                <div style="width: 200px">
                                    <b>Titular :</b> {{processoSelecionado.titular_nome  ? processoSelecionado.titular_nome : ''}}
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-content-end gap-2 mt-2">

                            <Button label="Cancela"
                                    @click="cancelaSelecaoProcesso"
                                    severity="danger"
                                    icon="pi pi-eraser"
                                    iconPos="right"
                                    ref="tourProcessoPaiCancelar"
                            />

                            <Button
                                label="Processo Principal"
                                icon="pi pi-search"
                                iconPos="right"
                                ref="tourProcessoPaiPesquisar"
                                @click="openDialogConsultaProcesso"
                            />

                        </div>

                    </div>

                </Fieldset>
            </div>
        </div>
        <div class="grid">
            <div class="col-4">
                <Fieldset
                    legend="Dados do"
                    :toggleable="false"
                    style="height: 350px;"
                >
                    <div
                        class="col-12"
                        ref="containerTitular"
                    >
                        <b>Titular:</b><span style="color: red;">*</span><br>

                        <div class="p-inputgroup">
                            <span
                                ref="titularButtonPesquisa"
                                class="p-inputgroup-addon"
                                @click="openDialogCGM"
                                v-show="!processo"
                                style="cursor: pointer;"
                            >
                                <i class="pi pi-search"></i>
                            </span>

                            <AutoComplete
                                v-model="form.titular"
                                placeholder="Titular"
                                :suggestions="cgms"
                                :dropdown="true"
                                @complete="pesquisarCgms($event)"
                                forceSelection
                                optionLabel="z01_nome"
                                @item-select="changeRequerente"
                                :disabled="!!processo"
                            />
                        </div>
                    </div>

                    <div
                        class="col-12"
                        ref="containerRequerente"
                    >
                        <b>Requerente:</b><span style="color: red;">*</span><br>

                        <InputText
                            placeholder="Requerente"
                            v-model="form.requerente"
                            class="col-12"
                            :value="form.requerente"
                            @change="changeRequerente"
                            :disabled="!!processo"
                        />
                    </div>

                    <div
                        class="col-12"
                        ref="containerDocumento"
                    >
                        <b>Documento:</b><span style="color: red;">*</span><br>

                        <AutoComplete
                            class="w-full"
                            v-model="form.documento"
                            placeholder="Documento"
                            :suggestions="documentos"
                            :dropdown="true"
                            @complete="pesquisarDocumentos($event)"
                            optionLabel="descricao_completa"
                            forceSelection
                            :disabled="!!processo"
                        />
                    </div>

                    <div
                        class="col-12"
                        ref="containerAssunto"
                    >
                        <b>Assunto:</b><span style="color: red;">*</span><br>

                        <div class="p-inputgroup">
                            <span
                                ref="assuntoButtonPesquisa"
                                class="p-inputgroup-addon"
                                @click="openDialogAssunto"
                                :disabled="!form.documento || !!processo"
                                style="cursor: pointer;"
                            >
                                <i class="pi pi-search"></i>
                            </span>

                            <AutoComplete
                                class="w-full"
                                v-model="form.assunto"
                                placeholder="Assunto"
                                optionLabel="p51_descr"
                                :suggestions="assuntos"
                                :dropdown="true"
                                @complete="pesquisarAssuntos($event)"
                                forceSelection
                                :disabled="!form.documento || !!processo"
                            />
                        </div>
                    </div>
                </Fieldset>
            </div>

            <div
                class="col-8"
                ref="containerObservacao"
            >
                <Fieldset
                    legend="Observação"
                    :toggleable="false"
                    style="height: 350px;"
                >
                    <trumbowyg
                        v-model="form.observacao"
                        :config="editor.config"
                        name="content"
                        :disabled="!!processo">
                    </trumbowyg>
                </Fieldset>
            </div>
        </div>

        <div class="grid">
            <div
                class="col-4"
                style="height: 358px"
                ref="containerEnviar"
            >
                <Fieldset
                    legend="Enviar para"
                    :toggleable="false"
                    class="mt-4"
                    style="height:100%"
                >
                    <div class="col-12" ref="containerSelecaoInstitucao">
                        <b>Instituição:</b><span style="color: red;">*</span><br>

                        <div class="p-inputgroup">
                            <span
                                class="p-inputgroup-addon"
                                @click="openDialogInstituicao"
                                v-show="!processo"
                                style="cursor: pointer;"
                            >
                                <i class="pi pi-search"></i>
                            </span>

                            <AutoComplete
                                v-model="form.instituicao"
                                placeholder="Instituição"
                                optionLabel="nomeinst"
                                :suggestions="instituicoes"
                                :dropdown="true"
                                forceSelection
                                @complete="pesquisarInstituicoes($event)"
                                :disabled="!!processo"
                            />
                        </div>
                    </div>

                    <div class="col-12" ref="containerSelecaoDepartamento">
                        <b>Departamento:</b><span style="color: red;">*</span><br>

                        <div class="p-inputgroup">
                            <span
                                class="p-inputgroup-addon"
                                @click="openDialogInstituicaoDepartamentos"
                                v-show="!processo"
                                style="cursor: pointer;"
                            >
                                <i class="pi pi-search"></i>
                            </span>

                            <AutoComplete
                                v-model="form.departamento"
                                placeholder="Departamento"
                                optionLabel="descrdepto"
                                :disabled="!form.instituicao || !!processo"
                                :suggestions="departamentos"
                                :dropdown="true"
                                forceSelection
                                @complete="pesquisarDepartamentosDaInstituicao($event)"
                            />
                        </div>
                    </div>

                    <div class="col-12">
                        <b>Usuário:</b><br>

                        <div
                            class="p-inputgroup"
                            ref="containerSelecaoUsuario"
                        >
                            <span
                                class="p-inputgroup-addon"
                                @click="openDialogInstituicaoDepartamentoUsuarios"
                                v-show="!processo"
                                style="cursor: pointer;"
                            >
                                <i class="pi pi-search"></i>
                            </span>

                            <AutoComplete
                                v-model="form.usuario"
                                placeholder="Usuário"
                                optionLabel="nome"
                                :suggestions="usuariosDepartamento"
                                :disabled="!form.departamento || !!processo"
                                :dropdown="true"
                                forceSelection
                                @complete="pesquisarUsuariosDoDepartamentosDaInstituicao($event)"
                            />
                        </div>
                    </div>
                </Fieldset>
            </div>

            <div class="col-8 mt-4">
                <Fieldset
                    legend="Anexos"
                    :toggleable="false"
                >
                    <FileUpload
                        name="anexos[]"
                        ref="upload"
                        style="height:400px"
                        url=""
                        @upload="()=>{}"
                        @select="atualizarListaArquivos"
                        @remove="atualizarListaArquivos"
                        :multiple="true"
                        accept="application/pdf,image/png,image/jpeg,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                        @clear="atualizarListaArquivos"
                    >
                        <template #header="{ chooseCallback, uploadCallback, clearCallback, files }">
                            <div class="flex flex-wrap justify-content-between align-items-center flex-1 gap-2">
                                <div class="flex gap-2">
                                    <Button
                                        @click="chooseCallback()"
                                        icon="pi pi-file-pdf"
                                        class="p-button-rounded"
                                        label="Selecione"
                                        ref="btnSelecionarArquivo"
                                        :disabled="!!processo"
                                    ></Button>

                                    <Button @click="clearCallback()"
                                            icon="pi pi-trash"
                                            class="p-button-rounded p-button-danger"
                                            label="Limpar"
                                            :disabled="!files || files.length === 0 || !!processo"
                                            ref="btnRemoverArquivo"
                                    />
                                    <Button @click="assinaturaDigital"
                                            icon="pi pi-pencil"
                                            class="p-button-rounded p-button-warning scalein animation-duration-2000 animation-iteration-infinite"
                                            label="Assinar"
                                            v-if="processo"
                                            ref="tourAssinar"
                                    />
                                    <Button @click="solicitarAssinatura"
                                            icon="pi pi-user-edit"
                                            class="p-button-rounded p-button-success scalein animation-duration-2000 animation-iteration-infinite"
                                            label="Solicitar Assinatura"
                                            ref="tourSolicitarAssinatura"
                                            v-if="processo"
                                    />
                                </div>
                            </div>
                        </template>

                        <template #empty>
                            <p>Arraste seus arquivos ou selecione</p>
                        </template>
                    </FileUpload>
                </Fieldset>
            </div>
        </div>

        <div class="grid d-flex flex-row justify-content-center mt-2">
            <Button
                v-if="!processo"
                label="Salvar Documento"
                class="mr-2 borda-arredondada"
                icon="pi pi-save"
                iconPos="right"
                @click="salvar"
            ></Button>

            <Button
                v-if="!processo"
                label="Limpar"
                class="p-button-danger mr-2 borda-arredondada"
                icon="pi pi-trash"
                iconPos="right"
                @click="limparForm"
            ></Button>

            <Button
                v-if="processo"
                label="Novo documento"
                class="mr-2 borda-arredondada"
                ref="tourNovoDocumento"
                icon="pi pi-file"
                iconPos="right"
                @click="novo"
            ></Button>

            <Button
                label="Preview"
                class="mr-2 borda-arredondada"
                v-if="!processo"
                @click="previewCapa"
                icon="pi pi-eye"
                iconPos="right"
            ></Button>
        </div>
    </div>

    <Button
        icon=""
        label="Passo a Passo"
        class="p-button-raised p-button-rounded"
        style="position: fixed;
        bottom: 10px; right: 10px;"
        @click="fazerTour"
    ></Button>
</template>

<script setup>

import { onMounted, ref, watch, nextTick } from 'vue';

import { useToast } from 'primevue/usetoast';
import DialogCGM from "./Components/DialogProtocoloDocumentoCGM";
import DialogInstituicao from '../../Configuracao/Instituicao/Components/DialogInstituicao';
import DialogInstituicaoDepartamentos from '../../Configuracao/Instituicao/Components/DialogInstituicaoDepartamentos';
import DialogInstituicaoDepartamentoUsuarios from '../../Configuracao/Instituicao/Components/DialogInstituicaoDepartamentoUsuarios';
import {useShepherd} from 'vue-shepherd';
import ModalLoading from '../../Components/ModalLoading';
import Trumbowyg from 'vue-trumbowyg';
import Swal from 'sweetalert2';
import 'trumbowyg/dist/ui/trumbowyg.min.css';
import 'trumbowyg/dist/plugins/colors/trumbowyg.colors.min';
import 'trumbowyg/dist/plugins/history/trumbowyg.history.min';
import DialogAssunto from "./Components/DialogProtocoloDocumentoAssunto";
import SolicitacaoAssinatura from "@modules/Patrimonial/Protocolo/AndamentoProcesso/Components/SolicitarAssinaturas/SolicitacaoAssinatura.vue";
import DialogConsultaProcesso from "@modules/Patrimonial/Protocolo/Components/DialogConsultaProcesso.vue";
import {formateDate} from "../../../utils/Strings";

$.trumbowyg.svgPath = window.ECIDADE_PATH + 'public/trumbowyg/ui/icons.svg';

const props = defineProps({
    emiteRecibo: Boolean
});

const ECIDADE_REQUEST_PATH = CurrentWindow.corpo.CurrentWindow.ECIDADE_REQUEST_PATH;

const documentos = ref([]);
const usuarios = ref([]);
const usuariosDepartamento = ref([]);
const assuntos = ref([]);
const toast = useToast();
const cgms = ref([]);
const instituicoes = ref([]);
const departamentos = ref([]);
const dialogCGM = ref(null);
const dialogAssunto = ref(null);
const dialogConsultaProcesso = ref(null);
const dialogInstituicao = ref(null);
const dialogInstituicaoDepartamentos = ref(null);
const dialogInstituicaoDepartamentoUsuarios = ref(null);
const containerCadastro = ref(null);
const titularButtonPesquisa = ref(null);
const assuntoButtonPesquisa = ref(null);
const containerRequerente = ref(null);
const containerTitular = ref(null);
const containerDocumento = ref(null);
const containerAssunto = ref(null);
const containerObservacao = ref(null);
const containerEnviar = ref(null);
const containerSelecaoInstitucao = ref(null);
const containerSelecaoDepartamento = ref(null);
const containerSelecaoUsuario = ref(null);
const btnSelecionarArquivo = ref(null);
const btnRemoverArquivo = ref(null);
const anexos = ref([]);
const modalLoading = ref(false);
const upload = ref(null);
const processo = ref(null);
const processoSelecionado = ref({});
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
            ['image'],
            ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
            ['unorderedList', 'orderedList'],
            ['horizontalRule'],
            ['removeformat']
        ]
    },
});
const tourConsultaProcesso = ref(null);
const tourAssinar = ref(null);
const tourSolicitarAssinatura = ref(null);
const tourNovoDocumento = ref(null);
const tourProcessoPaiPesquisar = ref(null);
const tourProcessoPaiCancelar = ref(null);
const tourVolume = ref(null);

const showPDF = ref(false);
const urlPdf = ref('');
const loadingPDF = ref(false);
const showProcesso = ref(false);
const showSolicitaAssinatura = ref(false);
const urlConsultaProcesso = ref('');
const volumeClosed = ref(true);
const valuesForm = function () {
    this.documento = null;
    this.assunto = null;
    this.titular = null;
    this.instituicao = null;
    this.departamento = null;
    this.usuario = null;
    this.observacao = '';
};

const showDialogEmissaoRecibo = ref(false);
const urlEmissaoRecibo = ref('');

const form = ref(new valuesForm());

const onAdvancedUpload = () => {
    toast.add({severity: 'info', summary: 'Success', detail: 'File Uploaded', life: 3000});
}

const toogleVolume = (e) =>{
    volumeClosed.value = false;
}

const pesquisarDocumentos = async ({query} = {query: ''}) => {
    const params = {descricao: query, exceto: [6, 7]};
    const urlSearchParams = new URLSearchParams(params);
    try {
        const resp = await window.axios.get(
            `v4/api/patrimonial/protocolo/tipo-documento?${urlSearchParams.toString()}`
        );
        documentos.value = resp.data.data;
    } catch (e) {
        documentos.value = [];
    }
}

const pesquisarAssuntos = async ({query} = {query: ''}) => {
    try {
        if (!form.value.documento) {
            return;
        }
        const resp = await window.axios.get(
            `v4/api/patrimonial/protocolo/tipo-documento/${form.value.documento.p91_sequencial}/tipo-processo?descricao=${query}`
        );
        assuntos.value = resp.data.data;
    } catch (e) {
        assuntos.value = [];
    }
}

const pesquisarCgms = async ({query} = {query: ''}) => {
    try {
        const resp = await window.axios.get(
            `v4/api/patrimonial/protocolo/cgm/search?nome=${query}`
        );
        cgms.value = resp.data.data.data;
    } catch (e) {
        cgms.value = [];
    }
}

const pesquisarInstituicoes = async ({query} = {query: ''}) => {
    try {
        const resp = await window.axios.get(
            `v4/api/configuracao/instituicao/search?nome=${query}`
        );
        instituicoes.value = resp.data.data.data;
    } catch (e) {
        instituicoes.value = [];
    }
}

const pesquisarDepartamentosDaInstituicao = async ({query} = {query: ''}) => {
    try {
        const resp = await window.axios.get(
            `v4/api/configuracao/instituicao/${form.value.instituicao.codigo}/departamentos?nome=${query}`
        );
        departamentos.value = resp.data.data.data;
    } catch (e) {
        departamentos.value = [];
    }
}

const pesquisarUsuariosDoDepartamentosDaInstituicao = async ({query} = {query: ''}) => {
    try {
        const resp = await window.axios.get(
            `v4/api/configuracao/instituicao/${form.value.instituicao.codigo}/departamento/${form.value.departamento.coddepto}?nome=${query}`
        );
        usuariosDepartamento.value = resp.data.data.data;
    } catch (e) {
        usuariosDepartamento.value = [];
    }
}

const openDialogCGM = () => {
    dialogCGM.value.openDialog();
}

const openDialogConsultaProcesso = () => {
    dialogConsultaProcesso.value.openDialog();
    dialogConsultaProcesso.value.doMaximize();
}

const cancelaSelecaoProcesso = () => {
    processoSelecionado.value = {};
    volumeClosed.value = true;
}

const selectProcesso = (processo) =>{
    dialogConsultaProcesso.value.closeDialog();
    processoSelecionado.value = processo;
    form.value.titular =  {
        z01_numcgm: processo.titular_cgm,
        z01_nome: processo.titular_nome
    }
    form.value.requerente = processo.processo_requerente;
    form.value.documento = '';
    form.value.assunto = '';
}
const openDialogAssunto = () => {
    if (form.value.documento == null) {
        toast.add({severity: 'warn', summary: 'campo obrigatório', detail: 'Selecione o documento', life: 3000});
        return false;
    } else {
        dialogAssunto.value.openDialogAssunto();
    }
}

const openDialogInstituicao = () => {
    dialogInstituicao.value.openDialog();
}

const openDialogInstituicaoDepartamentos = () => {
    dialogInstituicaoDepartamentos.value.openDialog();
}

const openDialogInstituicaoDepartamentoUsuarios = () => {
    dialogInstituicaoDepartamentoUsuarios.value.openDialog();
}

const changeRequerente = () => {
    if (!form.value.requerente || form.value.requerente === '') {
        form.value.requerente = form.value.titular.z01_nome;
    }
}

const selecaoDoCgmPeloDialogCGM = (cgm) => {
    form.value.titular = cgm;
    form.value.requerente = cgm.z01_nome;
    dialogCGM.value.closeDialog()
}

const selecaoDoAssuntoPeloDialog = (assunto) => {
    form.value.assunto = assunto;
}

const selecaoDaInstituicaoPeloDialogInstituicao = (instituicao) => {
    form.value.instituicao = instituicao;
}

const selecaoDepartamentoPeloDialogInstituicaoDepartamentos = (departamento) => {
    form.value.departamento = departamento;
}

const selecaoDepartamentoPeloDialogInstituicaoDepartamentoUsuarios = (usuario) => {
    form.value.usuario = usuario;
}

const atualizarListaArquivos = ({files} = {files: []}) => {
    anexos.value = files;
}

const salvar = async () => {
    if (!formValido()) {
        return;
    }

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

    const sendForm = {};
    sendForm.anexos = selectFiles;
    sendForm.titular = form.value.titular.z01_numcgm;
    sendForm.requerente = form.value.requerente;
    sendForm.assunto = form.value.assunto.p51_codigo;
    sendForm.observacao = form.value.observacao;
    sendForm.departamento_transferencia = form.value.departamento.coddepto;
    sendForm.instituicao_transferencia = form.value.instituicao.codigo;
    sendForm.usuario_transferir = form.value.usuario ? form.value.usuario.id_usuario : null;
    sendForm.principal = processoSelecionado.value.processo_codigo ? processoSelecionado.value.processo_codigo : null ;
    modalLoading.value = true;

    try {
        const resp = await axios.post(
            "v4/api/patrimonial/protocolo/documento",
            sendForm
        );
        const data = await resp.data.data;
        processo.value = data;
        modalLoading.value = false;
    } catch (e) {
        modalLoading.value = false;
        alert('Ocorreu um erro ao incluir Documento/Processo.');
    }

    await tourAposAprovacao();

    if (props.emiteRecibo) {
        emitirRecibo();
    }
}

const formValido = () => {
    if (!form.value.titular) {
        toast.add({severity: 'warn', summary: 'Campo Obrigatório', detail: 'Selecione o titular', life: 3000});
        return false;
    }

    if (!form.value.requerente) {
        toast.add({severity: 'warn', summary: 'Campo Obrigatório', detail: 'Digite o nome do requerente', life: 3000});
        return false;
    }

    if (!form.value.assunto) {
        toast.add({severity: 'warn', summary: 'Campo Obrigatório', detail: 'Selecione o assunto', life: 3000});
        return false;
    }

    if (!form.value.instituicao) {
        toast.add({severity: 'warn', summary: 'Campo Obrigatório', detail: 'Selecione a instituição', life: 3000});
        return false;
    }

    if (!form.value.departamento) {
        toast.add({severity: 'warn', summary: 'Campo Obrigatório', detail: 'Selecione o departamento', life: 3000});
        return false;
    }

    if (!form.value.observacao) {
        toast.add({
            severity: 'warn',
            summary: 'campo obrigatório',
            detail: 'Preencha o campo de observação',
            life: 3000
        });
        return false;
    }

    return true;
}

const emitirRecibo = () => {
    Swal.fire({
        text: 'Deseja emitir recibo?',
        icon: 'question',
        showDenyButton: true,
        confirmButtonColor: '#4f7c9e',
        denyButtonColor: '#d33',
        confirmButtonText: 'Sim',
        denyButtonText: 'Não'
    }).then((result) => {
        if (result.isConfirmed) {
            js_OpenJanelaIframe(
                'CurrentWindow.corpo',
                'db_iframe_documento_inclusao',
                `${ECIDADE_REQUEST_PATH}cai4_recibo001.php?` +
                `p58_codproc=${processo.value.processo.p58_codproc}` +
                `&codtipo=${processo.value.processo.p58_codigo}` +
                '&incproc=true&mostramenu=true&sIframe=IFdb_iframe_documento_inclusao' +
                '&origemRotinaNova=true',
                'Emitir Recibo',
                true
            );
        } else if (result.isDenied) {
            Swal.fire({
                text: 'Tem processos a apensar?',
                icon: 'question',
                showDenyButton: true,
                confirmButtonColor: '#4f7c9e',
                denyButtonColor: '#d33',
                confirmButtonText: 'Sim',
                denyButtonText: 'Não'
            }).then((result) => {
                if (result.isConfirmed) {
                    js_OpenJanelaIframe(
                        'CurrentWindow.corpo',
                        'db_iframe_processos_apensados',
                        `${ECIDADE_REQUEST_PATH}pro4_aba2protprocesso001.php?` +
                        `p58_codproc=${processo.value.processo.p58_codproc}`,
                        'Processos Apensados',
                        true
                    );
                }
            });
        }
    });
}

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

const generateBlob = async (fileBuffer, type) => {
    return new Blob([fileBuffer], {type});
};

const novo = () => {
    processo.value = null;
    volumeClosed.value = true;
    limparForm();
}

const limparForm = () => {
    form.value = new valuesForm();
    processoSelecionado.value  = {};
    upload._value.clear();
}

const assinaturaDigital = () => {
    const url = CurrentWindow.corpo.CurrentWindow.ECIDADE_REQUEST_PATH;
    js_OpenJanelaIframe(
        'CurrentWindow.corpo',
        'db_assinador_documentos',
        `${url}db_assinar_documentos.php?codigoProcesso=${processo.value.processo.p58_codproc}&procandamint=0`,
        'Visualizador de documentos',
        true
    );
}


const consultaProcessos = () => {

    const url = CurrentWindow.corpo.CurrentWindow.ECIDADE_REQUEST_PATH;
    js_OpenJanelaIframe(
        'CurrentWindow.corpo',
        'db_consulta_processos',
        `${url}func_protprocesso_protocolo.php?apenas_processopai=0&funcao_js=parent.js_mostra_processopai|0|1`,
        'Visualizador de documentos',
        true
    );

}

const solicitarAssinatura = () =>{
    showSolicitaAssinatura.value = true;
}

const previewCapa = () => {
    var url = CurrentWindow.ECIDADE_REQUEST_PATH;
    url = url.replace(/\/w/g, 'w');

    const formPreview = {};
    if (!formValido()) {
        return;
    }
    formPreview.titular = form.value.titular.z01_numcgm;
    formPreview.observacao = form.value.observacao;
    formPreview.requerente = form.value.requerente;
    formPreview.assunto = form.value.assunto.p51_codigo;
    formPreview.departamento_transferencia = form.value.departamento.coddepto;
    formPreview.instituicao_transferencia = form.value.instituicao.codigo;
    formPreview.usuario_transferir = form.value.usuario ? form.value.usuario.id_usuario : '';
    formPreview.principal = processoSelecionado.value.processo_codigo ?  processoSelecionado.value.processo_codigo : '';
    const urlParams = new URLSearchParams(formPreview);
    urlPdf.value = `${url}web/patrimonial/protocolo/capa-preview?` + urlParams.toString();
    loadingPDF.value = true;
    showPDF.value = true;
}

const closeDialogPreviewCapa = (e) => {
    showPDF.value = false;
}

const closeDialogEmissaoRecibo = (e) => {
    showDialogEmissaoRecibo.value = false;
}

const closeDialogProcesso = (e) => {
    showProcesso.value = false;
}

const consultaProcesso = () => {
    const url = CurrentWindow.ECIDADE_REQUEST_PATH;
    const processoParams = {};
    processoParams.p58_codproc = processo.value.processo.p58_codproc;
    const urlParams = new URLSearchParams(processoParams);
    urlConsultaProcesso.value = `${url}pro3_consultaprocesso002.php?` + urlParams.toString();
    showProcesso.value = true;
}

const closeDialogSolicitaAssinatura = (e) =>{
    showSolicitaAssinatura.value = false;
}

const fazerTour = () => {
    if (processo.value == null) {

        const tour = useShepherd({
            useModalOverlay: true,
            defaultStepOptions: {
                classes: 'shadow-md bg-purple-dark',
                scrollTo: true
            }
        });


        tour.addStep({
            attachTo: {
                element: containerCadastro.value,
                on: 'top'
            },
            title: 'Faça um tour no cadastro de documentos!',
            buttons: [
                {
                    text: 'Cancelar',
                    action: tour.cancel
                },
                {
                    text: 'Próximo',
                    action: tour.next
                }
            ]
        }, 0);

        tour.addStep({
            attachTo: {
                element: tourVolume.value,
                on: 'top'
            },
            title: 'Para criar um volume clique aqui!',
            buttons: [
                {
                    text: 'Cancelar',
                    action: tour.cancel
                },
                {
                    text: 'Próximo',
                    action: () =>{
                        tourVolume.value.click();
                        setTimeout(function () {
                            tour.addStep({
                                attachTo: {
                                    element: tourProcessoPaiPesquisar.value.$el,
                                    on: 'top'
                                },
                                title: 'Veja os próximos passos!',
                                text: 'Pesquisa opcional para encontrar processo utilizar caso deseje criar um volume.',
                                buttons: [
                                    {
                                        text: 'Já conheço a rotina!',
                                        action: () => tour.cancel()
                                    },
                                    {
                                        text: 'Ver a próxima opção',
                                        action: () => tour.next()
                                    }
                                ]
                            }, 2);

                            tour.addStep({
                                attachTo: {
                                    element: tourProcessoPaiCancelar.value.$el,
                                    on: 'top'
                                },
                                title: 'Veja os próximos passos!',
                                text: 'Remover o processo pai quando não quiser criar um volume.',
                                buttons: [
                                    {
                                        text: 'Já conheço a rotina!',
                                        action: () =>{
                                            tourProcessoPaiCancelar.value.$el.click();
                                            tour.cancel()
                                        }
                                    },
                                    {
                                        text: 'Ver a próxima opção',
                                        action: () => {
                                            tourProcessoPaiCancelar.value.$el.click();
                                            tour.next()
                                        }
                                    }
                                ]
                            }, 3);
                            tour.next();
                        },500);
                    }
                }
            ]
        }, 1);

        tour.addStep({
            attachTo: {
                element: titularButtonPesquisa.value, on: 'top'
            },
            title: 'Pesquisando Titular',
            text: 'Clique aqui para pesquisar um titular no cadastro de CGM.',
            buttons: [
                {
                    text: 'Anterior',
                    action: () => {
                        tour.back();
                        dialogCGM.value.closeDialog();
                    }
                },
                {
                    text: 'Próximo',
                    action: () => {
                        titularButtonPesquisa.value.click();
                        setTimeout(function () {
                            tour.addStep({
                                attachTo: {element: dialogCGM.value.containerFiltro, on: 'bottom'},
                                title: 'Pesquisando Titular',
                                text: 'Digite em um dos filtros para pesquisar!',
                                buttons: [
                                    {
                                        text: 'Cancelar',
                                        action: () => tour.cancel()
                                    },
                                    {
                                        text: 'Anterior',
                                        action: () => {

                                            tour.back();
                                            dialogCGM.value.closeDialog();
                                        }
                                    },
                                    {
                                        text: 'Próximo',
                                        action: () => {
                                            tour.next()
                                        }
                                    }
                                ]
                            }, 5);
                            tour.addStep({
                                attachTo: {element: dialogCGM.value.containerTable, on: 'bottom'},
                                title: 'Pesquisando Titular',
                                text: 'Clique na linha que deseja selecionar!',
                                buttons: [
                                    {
                                        text: 'Cancelar',
                                        action: () => tour.cancel()
                                    },
                                    {
                                        text: 'Anterior',
                                        action: () => {
                                            tour.back();
                                        }
                                    },
                                    {
                                        text: 'Próximo',
                                        action: () => {
                                            tour.next();
                                            dialogCGM.value.closeDialog();
                                        }
                                    }
                                ]
                            }, 6);
                            tour.next();
                        }, 500)

                    }
                }
            ]
        }, 4);

        tour.addStep({
            attachTo: {
                element: containerTitular.value, on: 'top'
            },
            title: 'Pesquisando Titular',
            text: 'Você também pode pesquisar pelo nome do titular.',
            buttons: [
                {
                    text: 'Cancelar',
                    action: () => tour.cancel()
                },
                {
                    text: 'Anterior',
                    action: () => {
                        tour.back();
                    }
                },
                {
                    text: 'Próximo',
                    action: () => {
                        tour.next();
                    }
                }
            ]
        }, 7);

        tour.addStep({
            attachTo: {
                element: containerRequerente.value, on: 'top'
            },
            title: 'Altere o nome do requerente',
            text: 'Digite o nome do requerente caso seja diferente do titular.',
            buttons: [
                {
                    text: 'Cancelar',
                    action: () => tour.cancel()
                },
                {
                    text: 'Anterior',
                    action: () => {
                        tour.back();
                    }
                },
                {
                    text: 'Próximo',
                    action: () => {
                        tour.next();
                    }
                }
            ]
        }, 8);

        tour.addStep({
            attachTo: {
                element: containerDocumento.value, on: 'top'
            },
            title: 'Pesquise o documento desejado',
            text: 'Digite parte do nome do documento (exemplo: Processo) para autocompletar.',
            buttons: [
                {
                    text: 'Cancelar',
                    action: () => tour.cancel()
                },
                {
                    text: 'Anterior',
                    action: () => {
                        tour.back();
                    }
                },
                {
                    text: 'Próximo',
                    action: () => {
                        tour.next();
                    }
                }
            ]
        }, 9);

        tour.addStep({
            attachTo: {
                element: containerAssunto.value, on: 'top'
            },
            title: 'Pesquise o assunto desejado',
            text: 'Digite parte do assunto (exemplo: Alvará) para autocompletar.',
            buttons: [
                {
                    text: 'Cancelar',
                    action: () => tour.cancel()
                },
                {
                    text: 'Anterior',
                    action: () => {
                        tour.back();
                    }
                },
                {
                    text: 'Próximo',
                    action: () => {
                        tour.next();
                    }
                }
            ]
        }, 10)

        tour.addStep({
            attachTo: {
                element: containerObservacao.value, on: 'top'
            },
            title: 'Digite suas observações',
            text: 'Campo destinado às suas observações.',
            buttons: [
                {
                    text: 'Cancelar',
                    action: () => tour.cancel()
                },
                {
                    text: 'Anterior',
                    action: () => {
                        tour.back();
                    }
                },
                {
                    text: 'Próximo',
                    action: () => {
                        tour.next();
                    }
                }
            ]
        }, 11);

        tour.addStep({
            attachTo: {
                element: containerEnviar.value, on: 'right'
            },
            title: 'Direcionando o documento',
            text: 'Aqui você define para onde deve ser enviado o documento!',
            buttons: [
                {
                    text: 'Cancelar',
                    action: () => tour.cancel()
                },
                {
                    text: 'Anterior',
                    action: () => {
                        tour.back();
                    }
                },
                {
                    text: 'Próximo',
                    action: () => {
                        tour.next();
                    }
                }
            ]
        }, 12);
        tour.addStep({
            attachTo: {
                element: containerSelecaoInstitucao.value, on: 'right'
            },
            title: 'Direcioando o documento',
            text: 'Aqui você deve pesquisar a instituição para a qual deseja enviar o documento. ' +
                'A forma de pesquisa é similar ao de titular, podendo pesquisar ' +
                'ou autocompletar pelo nome da instituição.',
            buttons: [
                {
                    text: 'Cancelar',
                    action: () => tour.cancel()
                },
                {
                    text: 'Anterior',
                    action: () => {
                        tour.back();
                    }
                },
                {
                    text: 'Próximo',
                    action: () => {
                        tour.next();
                    }
                }
            ]
        }, 13);
        tour.addStep({
            attachTo: {
                element: containerSelecaoDepartamento.value, on: 'right'
            },
            title: 'Direcioando o documento',
            text: 'Após selecionar a instituição é necessario selecionar um departamento!',
            buttons: [
                {
                    text: 'Cancelar',
                    action: () => tour.cancel()
                },
                {
                    text: 'Anterior',
                    action: () => {
                        tour.back();
                    }
                },
                {
                    text: 'Próximo',
                    action: () => {
                        tour.next();
                    }
                }
            ]
        }, 14);
        tour.addStep({
            attachTo: {
                element: containerSelecaoUsuario.value, on: 'right'
            },
            title: 'Direcioando o documento',
            text: 'Caso queira direcionar para um usuário após selecionar o Departamento, selecione aqui!',
            buttons: [
                {
                    text: 'Cancelar',
                    action: () => tour.cancel()
                },
                {
                    text: 'Anterior',
                    action: () => {
                        tour.back();
                    }
                },
                {
                    text: 'Próximo',
                    action: () => {
                        tour.next();
                    }
                }
            ]
        }, 15);
        tour.addStep({
            attachTo: {
                element: btnSelecionarArquivo.value.$el, on: 'top'
            },
            title: 'Anexando arquivos',
            text: 'Você pode adicionar anexos ao seu documento clicando aqui!',
            buttons: [
                {
                    text: 'Anterior',
                    action: () => {
                        tour.back();
                    }
                },
                {
                    text: 'Próximo',
                    action: () => {
                        tour.next();
                    }
                }
            ]
        }, 16);
        tour.addStep({
            attachTo: {
                element: btnRemoverArquivo.value.$el, on: 'top'
            },
            title: 'Anexando arquivos',
            text: 'Você pode remover todos anexos clicando aqui!',
            buttons: [
                {
                    text: 'Anterior',
                    action: () => {
                        tour.back();
                    }
                },
                {
                    text: 'Concluir',
                    action: () => {
                        tour.next();
                    }
                }
            ]
        }, 17);
        tour.start();
    } else {
        tourAposAprovacao();
    }
}

const tourAposAprovacao = async () => {
    const verificarElementos = async () => {
        await nextTick();
        return (
            tourConsultaProcesso.value?.$el instanceof HTMLElement &&
            tourAssinar.value?.$el instanceof HTMLElement &&
            tourNovoDocumento.value?.$el instanceof HTMLElement
        );
    };

    while (!(await verificarElementos())) {
        await new Promise(resolve => setTimeout(resolve, 500));
    }

    return new Promise((resolve) => {
        const tourAprovacao = useShepherd({
            useModalOverlay: true,
            defaultStepOptions: {
                classes: 'shadow-md bg-purple-dark',
                scrollTo: true
            }
        });
        tourAprovacao.addStep({
            attachTo: {
                element: tourConsultaProcesso.value.$el,
                on: 'top'
            },
            title: 'Veja os próximos passos!',
            text: 'Você tem as seguintes opções: a primeira é clicar no código do processo para visualizá-lo na tela de consulta.',
            buttons: [
                {
                    text: 'Já conheço a rotina!',
                    action: () => tourAprovacao.cancel()
                },
                {
                    text: 'Ver a próxima opção',
                    action: () => tourAprovacao.next()
                }
            ]
        }, 0);
        tourAprovacao.addStep({
            attachTo: {
                element: tourAssinar.value.$el,
                on: 'top'
            },
            title: 'Assinatura de documentos',
            text: 'Ou você pode assinar o documento clicando nesse botão. Selecione os aquivos e clique em assinar.',
            buttons: [
                {
                    text: 'Já conheço a rotina!',
                    action: () => tourAprovacao.cancel()
                },
                {
                    text: 'Ver a opção anterior',
                    action: () => tourAprovacao.back()
                },
                {
                    text: 'Ver a próxima opção',
                    action: () => tourAprovacao.next()
                }
            ]
        }, 1);

        tourAprovacao.addStep({
            attachTo: {
                element: tourSolicitarAssinatura.value.$el,
                on: 'top'
            },
            title: 'Solicitação de Assinaturas',
            text: 'Para solicitar assinaturas de documentos deve clicar nesse botão.',
            buttons: [
                {
                    text: 'Já conheço a rotina!',
                    action: () => tourAprovacao.cancel()
                },
                {
                    text: 'Ver a opção anterior',
                    action: () => tourAprovacao.back()
                },
                {
                    text: 'Ver a próxima opção',
                    action: () => tourAprovacao.next()
                }
            ]
        }, 2);
        tourAprovacao.addStep({
            attachTo: {
                element: tourNovoDocumento.value.$el, on: 'top'
            },
            title: 'Novo processo',
            text: 'Lembre-se que seu documento foi concluído e para iniciar um novo clique aqui!',
            buttons: [
                {
                    text: 'Ver a opção anterior',
                    action: () => tourAprovacao.back(),
                },
                {
                    text: 'Concluir',
                    action: () => tourAprovacao.next(),
                }
            ]
        }, 3);
        tourAprovacao.on('complete', resolve);
        tourAprovacao.on('cancel', resolve);
        tourAprovacao.start();
    });
}

onMounted(() => {
    pesquisarDocumentos();
});

watch(() => form.value.instituicao, (newValue, oldValue) => {
    if (!newValue) {
        form.value.departamento = null;
        return;
    }

    if ((newValue && oldValue) && (newValue.codigo !== oldValue.codigo)) {
        form.value.departamento = null;
    }
});

watch(() => form.value.documento, (newValue, oldValue) => {
    if (!newValue) {
        form.value.assunto = null;
        return;
    }
    if ((newValue && oldValue) && (newValue.p91_sequencial !== oldValue.p91_sequencial)) {

        form.value.assunto = null;
    }
});

watch(() => form.value.departamento, (newValue, oldValue) => {
    if ((newValue && oldValue) && (newValue.coddepto !== oldValue.coddepto)) {
        form.value.usuario = null;
    }
});

</script>

<style scoped>
.trumbowyg-box {
    min-height: 250px !important;
    height: 250px !important;
}

.processo-button-container {
    width: 100%;
    display: flex !important;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding-top: 10px;
}

.processo-button {
    width: fit-content;
    border-radius: 2rem !important;
}

:deep(.p-button.borda-arredondada) {
    border-radius: 2rem !important;
}

:deep(.p-inputtext) {
    border: 1px solid #bcbcbc;
}

:deep(.p-inputgroup-addon) {
    border-top: none;
    border-left: none;
    border-bottom: none;
}
</style>
