<script setup>

import {onMounted, ref} from "vue";
import ModalLoading from "@modules/Components/ModalLoading.vue";
import MensageriaProtocolo from "@modules/Patrimonial/Protocolo/AndamentoProcesso/Components/MensageriaProtocolo.vue";
import Acoes from "@modules/Patrimonial/Protocolo/AndamentoProcesso/Components/Acoes.vue";
import {useToast} from "primevue/usetoast";
import DialogFiltros from "@modules/Patrimonial/Protocolo/AndamentoProcesso/Components/DialogFiltros.vue";
import {useConfirm} from "primevue/useconfirm";
import ConfirmDialog from 'primevue/confirmdialog';
import DespachoMassa from "@modules/Patrimonial/Protocolo/AndamentoProcesso/Components/DespachoMassa.vue";
import Transferencia from "@modules/Patrimonial/Protocolo/AndamentoProcesso/Components/Transferencia.vue";
import {useShepherd} from "vue-shepherd";
import BackToTop from "@modules/Patrimonial/Protocolo/AndamentoProcesso/Components/BackToTop.vue";
import Arquivar from "@modules/Patrimonial/Protocolo/AndamentoProcesso/Components/Arquivar.vue";
import Swal from "sweetalert2";
import VerificaRecebimentoMassa from "@modules/Patrimonial/Protocolo/AndamentoProcesso/Components/VerificaRecebimentoMassa.vue";

$.trumbowyg.svgPath = window.ECIDADE_PATH + 'public/trumbowyg/ui/icons.svg';

const props = defineProps(['orgao', 'permissoes', 'visualizaEmOutraJanela']);
const modalLoading = ref(false);
const textLoad = ref('');
const modalMensagem = ref(false);
const modalDespacho = ref(false);
const modalArquivar = ref(false);
const processoMensagem = ref();
const attAposFecharMensagem = ref(false);
const dialogAcoes = ref(false);
const toast = useToast();
const filtrosTotal = ref(0);
const dialogFiltros = ref(null);
const avisoMensagem = ref('');
const processoCodigo = ref(null);
const modalAcoes = ref(false);
const checkboxProcessos = ref();
const checkboxProcessosSend = ref();
const confirm = useConfirm();
const dialogTransferencia = ref(false);
const tourAcoesMassa = ref(null);
const tourCheckBox = ref(null);
const tourAcoesMassaCheckBox = ref(null);
const permissaoReceber = ref(false);
const permissaoArquivar = ref(false);
const permissaoDespacho = ref(false);
const permissaoTransferencia = ref(false);
const verificaRecebimentoMassa = ref(null);

onMounted(async () => {
    setPermissoes();
    await getProcessos();
    //tourProcessosEmMassa();
});

const processos = ref(null);
const camposForm = ref([]);

const defaultPaginate = function () {
    this.perPage = 5;
    this.page = 0;
    this.total = 0;
    this.offset = 0;
}
const paginate = ref(defaultPaginate);


const setPermissoes = () => {
    if (props.permissoes === 'administrador') {
        permissaoReceber.value = true;
        permissaoArquivar.value = true;
        permissaoDespacho.value = true;
        permissaoTransferencia.value = true;
        return;
    }

    if (Array.isArray(props.permissoes)) {
        permissaoReceber.value = props.permissoes.filter(item => item.id_item == 229286);
        permissaoReceber.value = permissaoReceber.value.length > 0;

        permissaoArquivar.value = props.permissoes.filter(item => item.id_item == 229287);
        permissaoArquivar.value = permissaoArquivar.value.length > 0;

        permissaoDespacho.value = props.permissoes.filter(item => item.id_item == 229288);
        permissaoDespacho.value = permissaoDespacho.value.length > 0;

        permissaoTransferencia.value = props.permissoes.filter(item => item.id_item == 229289);
        permissaoTransferencia.value = permissaoTransferencia.value.length > 0;
    }
}

const getProcessos = async ({page, rows} = {page: 0, rows: 45}) => {
    textLoad.value = "Buscando Processos...";
    modalLoading.value = true;
    avisoMensagem.value = "Aguardando...";

    try {
        paginate.value.page = ++page;
        if (rows) {
            if (paginate.value.perPage !== parseInt(rows)) {
                paginate.value.page = 1;
            }
            paginate.value.perPage = parseInt(rows);
        }

        const urlParams = new URLSearchParams({...camposForm.value, ...paginate.value});
        const resp = await window.axios.get(
            `v4/api/patrimonial/protocolo/processo/buscarProcessos?${urlParams.toString()}`
        );
        const data = resp.data.data.data;
        const {current_page, total, per_page} = resp.data.data;
        paginate.value = {
            page: current_page,
            offset: current_page * per_page - 1,
            total,
            perPage: parseInt(per_page)
        };
        processos.value = data;
        modalLoading.value = false;

        if (processos.value.length < 1) {
            avisoMensagem.value = "Não foram encontrados resultados";
        }
        checkboxProcessos.value = null;
        checkboxProcessosSend.value = null;
    } catch (e) {
        checkboxProcessos.value = null;
        checkboxProcessosSend.value = null;
        avisoMensagem.value = "Não foram encontrados resultados";
        processos.value = [];
        modalLoading.value = false;
    }
}

const stringStatus = function (codigoStatus) {
    if (codigoStatus === 1) {
        return "A receber";
    }

    if (codigoStatus === 2) {
        return "Recebido";
    }

    if (codigoStatus === 3) {
        return "Despachado";
    }
}

const classTagStatus = function (codigoStatus) {
    if (codigoStatus === 1) {
        return "areceber";
    }

    if (codigoStatus === 2) {
        return "recebido";
    }

    if (codigoStatus === 3) {
        return "despachado";
    }
}

const receberProcesso = async (processo, telaAcoes = false, verificaTransferencia = true) => {
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

        if (resp.data.data === true) {
            await getProcessos();
        } else {
            modalLoading.value = false;
        }

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

const acoesProcesso = async (processo) => {
    textLoad.value = "Verificando Processo...";
    modalLoading.value = true;

    try {
        const resp = await window.axios.get(`v4/api/patrimonial/protocolo/processo/presenteDepartamento/${processo.codigo}`);
        if (resp.data.data.erro) {
            modalLoading.value = false;
            toast.add({severity: 'warn', summary: 'Atenção', detail: resp.data.data.mensagem});
            await getProcessos();
            return;
        }
        modalLoading.value = false;
        processoCodigo.value = processo.codigo;
        modalAcoes.value = true;
    } catch (e) {
        modalLoading.value = false;
    }
}

function setTotalFiltros(totalFiltros) {
    if (Number.isInteger(totalFiltros)) {
        filtrosTotal.value = totalFiltros;
    }
}

function setCamposForm(camposFormParametros) {
    camposForm.value = camposFormParametros;
}

function closeDialogMensagem() {
    if (attAposFecharMensagem.value) {
        getProcessos();
    }
}

const escapeHtml = (texto) => {
    let htmlString = texto;
    let tempElement = document.createElement('div');
    tempElement.innerHTML = htmlString;
    return tempElement.textContent || tempElement.innerText;
}

function makeObjetoProcesso (processo) {
    const processoObj = {};
    processoObj.codigoProcesso = processo.codigo;
    processoObj.hash = processo.hash;
    processoObj.codigoTransferencia = processo.transferencia;
    processoObj.numero = processo.processo;
    return processoObj;
}

function verificaProcessosMassa(tipoAcao) {
    let elementosModificados = [];

    checkboxProcessos.value.forEach((elemento) => {
        if (elemento.codigostatus === tipoAcao && tipoAcao === 1) {
            let elementoModificado = makeObjetoProcesso(elemento);
            elementosModificados.push(elementoModificado);
        }

        if (elemento.codigostatus !== 1 && tipoAcao === 2) {
            let elementoModificado = makeObjetoProcesso(elemento);
            elementosModificados.push(elementoModificado);
        }

        if (elemento.codigostatus !== 1 && tipoAcao === 3) {
            let elementoModificado = makeObjetoProcesso(elemento);
            elementosModificados.push(elementoModificado);
        }

        if (elemento.codigostatus !== 1 && tipoAcao === 4) {
            let elementoModificado = makeObjetoProcesso(elemento);
            elementosModificados.push(elementoModificado);
        }
    });

    checkboxProcessosSend.value = elementosModificados;

    if (checkboxProcessosSend.value.length < 1) {
        checkboxProcessosSend.value = null;
        checkboxProcessos.value = null;

        if (tipoAcao === 1) {
            toast.add({severity: 'warn', summary: 'Atenção', detail: `Selecione pelo menos um processo do tipo A receber!`, life: 3000});
            return false;
        } else if (tipoAcao === 3) {
            toast.add({severity: 'warn', summary: 'Atenção', detail: `Selecione pelo menos um processo que possa ser depachado!`, life: 3000});
            return false;
        } else if (tipoAcao === 2) {
            toast.add({severity: 'warn', summary: 'Atenção', detail: `Selecione pelo menos um processo que possa ser transferido!`, life: 3000});
            return false;
        } else if (tipoAcao === 4) {
            toast.add({severity: 'warn', summary: 'Atenção', detail: `Selecione pelo menos um processo que possa ser arquivado!`, life: 3000});
            return false;
        }

        return false;
    }

    return true;
}

const receberProcessoMassa = async (verificaTransferencia = true) => {
    textLoad.value = "Recebendo processos...";
    modalLoading.value = true;

    const parametros = {};
    parametros.processos = checkboxProcessosSend.value;
    parametros.acao = 'receberMassa';
    parametros.id_item_menu = 229286;

    if (verificaTransferencia) {
        var processosComRecebimentoEmMassa = await verificaRecebimentoProcessoMultiplos(parametros.processos);

        if (processosComRecebimentoEmMassa.length > 0) {
            modalLoading.value = false;
            verificaRecebimentoMassa.value.openDialog(processosComRecebimentoEmMassa);
            return;
        }
    }

    try {
        const resp = await window.axios.post(
            `v4/api/patrimonial/protocolo/processo/processar`,
            parametros
        );

        var processos = resp.data.data;
        if (processos.aprovados.length > 0) {
            toast.add({severity: 'success', summary: 'Processos Recebidos', detail: `${processos.aprovados.length} processos recebidos!`});
        }

        if (processos.erros.length > 0) {
            processos.erros.forEach((elemento) => {
                toast.add({severity: 'error', summary: 'Falha ao receber', detail: `Processo ${elemento.processo.numero} ---> ${elemento.mensagem}`});
            })
        }

        modalLoading.value = false;
        checkboxProcessos.value = null;
        checkboxProcessosSend.value = null;
        await getProcessos();
    } catch (e) {
        if (e.response.data.error) {
            await Swal.fire(
                'Atenção',
                e.response.data.message,
                "warning"
            );
        }
        checkboxProcessos.value = null;
        checkboxProcessosSend.value = null;
        modalLoading.value = false;
        console.log('error: ' + e);
    }
}

const confirm1 = (tipoAcao) => {
    if (!verificaProcessosMassa(tipoAcao)) {
        confirm.hide();
        return;
    }

    if (tipoAcao === 1) {
        const mensagem = criarMensagemRecebimento(checkboxProcessosSend.value.length);

        confirm.require({
            group: 'templating',
            message: mensagem,
            header: 'Atenção',
            icon: 'pi pi-exclamation-triangle',
            acceptIcon: 'pi pi-check',
            rejectIcon: 'pi pi-times',
            acceptClass: 'borda-arredondada',
            rejectClass: 'p-button-secondary p-button-outlined borda-arredondada',
            rejectLabel: 'Cancelar',
            acceptLabel: 'Receber',
            accept: receberProcessoMassa,
            reject: () => {}
        });
    }

    if (tipoAcao === 2) {
        transferirDialog();
    }

    if (tipoAcao === 3) {
        modalDespacho.value = true;
    }

    if (tipoAcao === 4) {
        modalArquivar.value = true;
    }
};

const criarMensagemRecebimento = (quantidadeProcessos) => {
    if (quantidadeProcessos === 1) {
        return `Deseja receber ${quantidadeProcessos} processo?`;
    } else if (quantidadeProcessos > 1) {
        return `Deseja receber ${quantidadeProcessos} processos?`;
    }
    return '';
}

const transferirDialog = function () {
    dialogTransferencia.value.setProcesso(checkboxProcessosSend.value);
    dialogTransferencia.value.openDialog();
}

const tourProcessosEmMassa = () => {
    tourAcoesMassaCheckBox.value = true;
    const tourAprovacao = useShepherd({
        useModalOverlay: true,
        defaultStepOptions: {
            classes: 'shadow-md bg-purple-dark',
            scrollTo: true
        }
    });
    tourAprovacao.addStep({
        attachTo: {
            element: tourAcoesMassa.value,
            on: 'bottom'
        },
        title: 'Ações em massa!',
        text: 'Agora você pode receber, despachar e transferir múiltiplos processos por vez.',
        buttons: [
            {
                text: 'Já conheço a rotina!',
                action: () => {
                    tourAprovacao.cancel();
                    tourAcoesMassaCheckBox.value = false;
                }
            },
            {
                text: 'Ver a próxima opção',
                action: () => {
                    tourAprovacao.next();
                    tourAcoesMassaCheckBox.value = false;
                }
            }
        ]
    }, 0);
    tourAprovacao.addStep({
        attachTo: {
            element: tourCheckBox.value,
            on: 'right-start'
        },
        title: 'Ações em massa!',
        text: 'Para isso você deve selecionar os processos pelo checkbox a esquerda do número do processo, você também ' +
            'pode selecionar todos da lista marcando o checkbox do topo da tabela. Marcando os processos as opções de ações serão' +
            ' habilitadas.',
        buttons: [
            {
                text: 'Anterior',
                action: () => {
                    tourAprovacao.back();
                }
            },
            {
                text: 'Ver a próxima opção',
                action: () => {
                    tourAprovacao.next();
                    checkboxProcessos.value = processos.value;
                }
            }
        ]
    }, 1);
    tourAprovacao.start();
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

const receberProcessoTransferenciaMultiplas = (receber = false, processos) => {
    if (Array.isArray(processos)) {
        if (processos.length === 1) {
            if (processos[0].transferencia) {
                receberProcesso(processos[0], false, false);
            }

            if (processos[0].codigoTransferencia) {
                receberProcessoMassa(false);
            }
        } else {
            receberProcessoMassa(false);
        }
    }
}
</script>

<template>
    <ModalLoading
        :is-loading="modalLoading"
        :message="textLoad"
    />

    <Dialog
        class="p-dialog-maximized"
        contentClass="background-modal-acoes"
        v-model:visible="modalAcoes"
        header=" "
        :maximizable="true"
        @hide="getProcessos"
    >
        <Acoes
            ref="dialogAcoes"
            @onArquivarProcesso="modalAcoes = false"
            @onTransferirProcesso="modalAcoes = false"
            @receberProcesso="receberProcesso"
            @attProcessos="getProcessos"
            :processo="processoCodigo"
            :permitereceber="permissaoReceber"
            :permitearquivar="permissaoArquivar"
            :permitedespacho="permissaoDespacho"
            :permitetransferencia="permissaoTransferencia"
            :visualizaOutraJanela="visualizaEmOutraJanela"
        />
    </Dialog>

    <Dialog v-model:visible="modalMensagem" class="p-dialog-maximized" @hide="closeDialogMensagem" header=" ">
        <MensageriaProtocolo
            :processo="processoMensagem"
            :dialog="modalMensagem"
        />
    </Dialog>

    <Dialog v-model:visible="modalDespacho"  header="Despachos Múltiplos">
        <DespachoMassa
            :processos="checkboxProcessosSend"
            @buscarProcessos="getProcessos"
            @fecharDialog="modalDespacho = false"
        />
    </Dialog>

    <Dialog v-model:visible="modalArquivar"  header="Arquivar Múltiplos" style="width: 500px">
        <Arquivar
            :processos="checkboxProcessosSend"
            @closeDialogArquivar="modalArquivar = false"
            @attProcessos="getProcessos"
        />
    </Dialog>

    <Transferencia
        ref="dialogTransferencia"
        @attProcessos="getProcessos"
    />

    <DialogFiltros
        ref="dialogFiltros"
        @getProcessos="getProcessos"
        @setTotalFiltros="setTotalFiltros"
        @setCamposForm="setCamposForm"
        :telaProcesso="true"
    />

    <VerificaRecebimentoMassa
        ref="verificaRecebimentoMassa"
        @closeDialog="receberProcessoTransferenciaMultiplas"
    />

    <ConfirmDialog group="templating">
        <template #message="slotProps">
            <div class="flex flex-column align-items-center w-full gap-3 border-bottom-1 surface-border">
                <i :class="slotProps.message.icon" class="text-6xl text-primary-500"></i>
                <p>{{ slotProps.message.message }}</p>
            </div>
        </template>
    </ConfirmDialog>

    <div ref="tourCheckBox">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px;">
            <strong style="font-size: 1.3rem;">Órgão: {{orgao}}</strong>

            <ButtonGroup style="display: flex; gap: 10px; height: 35px;">
                <div ref="tourAcoesMassa" style="display: flex; justify-content: center; align-items: center; gap: 10px;">
                    <Button
                        v-if="((Array.isArray(checkboxProcessos) && checkboxProcessos.length > 0) || tourAcoesMassaCheckBox)"
                        class="btn-acoes"
                        icon="pi pi-arrow-down-left"
                        severity="info"
                        text raised rounded aria-label="receber"
                        :disabled="!permissaoReceber"
                        :title="permissaoReceber ? 'Receber Processos' : 'Você não possui permissão para Receber.'"
                        @click="confirm1(1)"
                    ></Button>
                    <Button
                        v-if="((Array.isArray(checkboxProcessos) && checkboxProcessos.length > 0) || tourAcoesMassaCheckBox)"
                        class="btn-acoes"
                        icon="pi pi-file-import"
                        severity="warning"
                        text raised rounded aria-label="despachar"
                        :disabled="!permissaoDespacho"
                        :title="permissaoDespacho ? 'Despachar Processos' : 'Você não possui permissão para Despachar.'"
                        @click="confirm1(3)"
                    ></Button>
                    <Button
                        v-if="((Array.isArray(checkboxProcessos) && checkboxProcessos.length > 0) || tourAcoesMassaCheckBox)"
                        class="btn-acoes"
                        icon="pi pi-arrow-right-arrow-left"
                        severity="success"
                        text raised rounded aria-label="transferir"
                        :disabled="!permissaoTransferencia"
                        :title="permissaoTransferencia ? 'Transferir Processos' : 'Você não possui permissão para Transferir.'"
                        @click="confirm1(2)"
                    ></Button>
                    <Button
                        v-if="((Array.isArray(checkboxProcessos) && checkboxProcessos.length > 0) || tourAcoesMassaCheckBox)"
                        style="color: #444242"
                        class="btn-acoes"
                        icon="pi pi-inbox"
                        severity="contrast"
                        text raised rounded aria-label="arquivar"
                        title="Arquivar Processos"
                        :disabled="!permissaoArquivar"
                        :title="permissaoArquivar ? 'Arquivar Processos' : 'Você não possui permissão para Arquivar.'"
                        @click="confirm1(4)"
                    ></Button>
                </div>
                <Button
                    label="Pesquisa"
                    icon="pi pi-search"
                    iconPos="left"
                    style="display: flex !important; justify-content: center; align-items: center"
                    :badge="filtrosTotal > 0 ? filtrosTotal.toString() : ''"
                    :class="filtrosTotal > 0 ? 'borda-left-arredondada' : 'arredondada-solo'"
                    @click="dialogFiltros.openDialog()"
                ></Button>
                <Button
                    v-if="filtrosTotal > 0"
                    label="Limpar Filtro"
                    style="border-radius: 0px 2rem 2rem 0px; margin-left: -10px;"
                    @click="dialogFiltros.limparFiltrosProcesso()"
                ></Button>
                <Button
                    icon="pi pi-refresh"
                    class="arredondada-solo refresh"
                    title="Recarregar Processos"
                    @click="getProcessos"
                ></Button>
            </ButtonGroup>
        </div>

        <DataTable
            showGridlines
            responsiveLayout="scroll"
            :scrollable="true"
            scrollHeight="flex"
            :value="processos"
            v-model:selection="checkboxProcessos"
            dataKey="codigo"
        >
            <template v-if="processos && processos.length > 0">
                <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
                <Column header="Processo">
                    <template #body="{data}">
                        <p :class="data.parausuariologado === 1 ? 'para-user-logado' : ''">{{data.processo}}</p>
                    </template>
                </Column>
                <Column header="Requerente">
                    <template #body="{data}">
                        <p :class="data.parausuariologado === 1 ? 'para-user-logado' : ''">{{data.requerente}}</p>
                    </template>
                </Column>
                <Column header="Assunto">
                    <template #body="{data}">
                        <p :class="data.parausuariologado === 1 ? 'para-user-logado' : ''">{{data.descricao}}</p>
                    </template>
                </Column>
                <Column header="Data">
                    <template #body="{data}">
                        <p :class="data.parausuariologado === 1 ? 'para-user-logado' : ''">{{data.data}}</p>
                    </template>
                </Column>
                <Column header="Observação">
                    <template #body="{ data }">
                        <div :class="data.parausuariologado === 1 ? 'para-user-logado ellipsis' : 'ellipsis'" v-html="data.observacao" v-tooltip="escapeHtml(data.observacao)"></div>
                    </template>
                </Column>
                <Column field="codigostatus" header="Status" style="width: 85px">
                    <template #body="{ data }">
                        <Tag :value="stringStatus(data.codigostatus)"  :class="classTagStatus(data.codigostatus)"/>
                    </template>
                </Column>
                <Column field="acoes" header="Ações" style="width: 100px">
                    <template #body="{ data }">
                        <i
                            v-if="data.codigostatus === 1 && permissaoReceber"
                            title="Receber Processo"
                            class="pi pi-check hover-btn-acoes"
                            style="margin-left:10px"
                            @click="receberProcesso(data)"
                        ></i>

                        <i
                            v-if="data.codigostatus !== 1 && permissaoReceber"
                            title="Visualizar Mensagens"
                            class="pi pi-envelope hover-btn-acoes"
                            style="margin-left:10px"
                            @click="visualizarMensagens(data)"
                        >
                            <span v-if="data.mensagens_novas > 0" v-badge="data.mensagens_novas"></span>
                        </i>

                        <i title="Abrir Ações" class="pi pi-bars hover-btn-acoes" @click="acoesProcesso(data)" style="margin-left:10px"></i>
                    </template>
                </Column>
            </template>
            <template v-else>
                <h2 style="text-align:center">{{avisoMensagem}}</h2>
            </template>
            <template #footer> <div v-if="checkboxProcessos && (checkboxProcessos.length > 0)">Marcados: {{checkboxProcessos.length}}</div> Total: {{paginate.total}} </template>
        </DataTable>
        <Paginator
            ref="paginator"
            :rows="paginate.perPage"
            :totalRecords="paginate.total"
            v-model:first="paginate.offset"
            :rowsPerPageOptions="[100, 200, 1000]"
            @page="getProcessos($event)"
        />
    </div>
    <BackToTop/>
</template>

<style scoped>
.hover-btn-acoes:hover {
    cursor: pointer;
}

.para-user-logado {
    font-weight: bold;
}

.arredondada-solo{
    border-radius: 2rem !important;
}

.borda-left-arredondada{
    border-radius: 2rem 0px 0px 2rem !important;
}

.filtros-area{
    margin-top:5px;
    display:flex;
}

.filtro-group{
    display:flex;
    align-items: baseline;
}

.areceber {
    background-color: #59cbff;
    color:black;
}

.recebido {
    background-color: #7BEF7BFF;
    color:black;
}

.despachado {
    background-color: #FFCA6DFF;
    color:black;
}

.refresh {
    display: flex !important;
    justify-content: center;
    align-items: center;
}

.refresh:hover {
    cursor: pointer;
    color: #fff;
}

.receber:hover {
    cursor: pointer;
    color: #4a789c;
}

.despachar:hover {
    cursor: pointer;
    color: #FFCA6DFF;
}

.transferir:hover {
    cursor: pointer;
    color: #4a789c;
}

.arquivar:hover {
    cursor: pointer;
    color: #4a789c;
}

.ellipsis {
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.btn-acoes {
    display: flex !important;
    justify-content: center;
    align-items: center;
    background-color: white !important;
}
</style>
