<script setup>
import {ref} from "vue";
import ModalLoading from "../../../Components/ModalLoading.vue";
import ButtonDialogInfo from "./Components/buttonDialogInfo.vue";
import Calendar from "primevue/calendar";
import {useToast} from "primevue/usetoast";
import Swal from "sweetalert2";

const emit = defineEmits(['getAtendimento']);
const props = defineProps(['json', 'aprovarAtendimentos', 'visualizaEmOutraJanela', 'emiteRecibo']);
const showDialod = ref(false);
const dadosVisualizar = ref([]);
const buttonDialogInfo = ref(null);
const showVisualizadorArquivos = ref(false);
const url = ref('');
const observacao = ref('');
const anexosList = ref([]);
const gerarCamposAddAlvara = ref(false);
const modalLoading = ref(false);
const textLoad = ref('');
const dados = ref([]);
const inscricoes = ref([]);
const selectedInscricao = ref(null);
const opcoes = ref([
    { name: 'PERMANENTE', code: 1 },
    { name: 'PROVISÓRIO', code: 2 }
]);
const opcoesGrauRisco = ref([
    { codigo: 'A', descricao: 'ALTO' },
    { codigo: 'M', descricao: 'MÉDIO' },
    { codigo: 'B', descricao: 'BAIXO' },
]);
const camposAdicionaisResposta = ref(null);
const inscricaoDialog = ref(false);
const aprovarSucessoDialog = ref(false);
const textoAprovadoDialog = ref('');
const toast = useToast();

const ECIDADE_REQUEST_PATH = CurrentWindow.corpo.CurrentWindow.ECIDADE_REQUEST_PATH;

const searchSecoes = function() {
    return props.json.secoes.filter((secao) => {
        if (secao.tipo === 'anexo') {
            anexosList.value = secao.resposta;
        }

        secao.campos.forEach(campo => {
            if (!campo.label) {
                campo.label = campo.nome;
            }
        });

        secao.campos.forEach(campo => {
            if (campo.tipo === 'dialog') {
                if (!Array.isArray(campo.resposta)) {
                    if (!campo.resposta) {
                        campo.tipo = 'texto';
                    }
                    if (campo.resposta && typeof campo.resposta === 'object' && campo.resposta.hasOwnProperty('descricao')) {
                        campo.tipo = 'texto';
                        campo.resposta = campo.resposta.descricao;
                    }
                }
            }

            if (campo.tipo === 'lista') {
                if (campo.resposta && typeof campo.resposta === 'object' && campo.resposta.hasOwnProperty('descricao')) {
                    campo.tipo = 'texto';
                    campo.resposta = campo.resposta.descricao;
                }
            }
        });

        if (secao.nome === 'atividades' && props.json.acao === 'gerarAlvara') {
            gerarCamposAddAlvara.value = true;
            secao.resposta.forEach((resposta) => {
                resposta.provisorio = opcoes.value[0];
                resposta.dataProvisorio = null;
            });
            secao.grauRisco = opcoesGrauRisco.value[0];
        }

        return secao.nome !== "termo";
    });
}

const criarJsonCamposAdicionais = function () {
    var camposProvisorioPermanente = [];

    props.json.secoes.filter((secao) => {
        if (secao.nome === 'atividades') {
            secao.resposta.forEach((resposta, index) => {
                if (resposta.provisorio.code === 2 && resposta.dataProvisorio !== null) {
                    var dataFormatada = resposta.dataProvisorio;
                    var mes = null;
                    if (dataFormatada.getMonth() + 1 < 10) {
                        mes = `0${dataFormatada.getMonth() + 1}`;
                    } else {
                        mes = dataFormatada.getMonth() + 1;
                    }
                    dataFormatada = `${dataFormatada.getFullYear()}-${mes}-${dataFormatada.getDate()}`;
                    camposProvisorioPermanente.push({
                        indice: index.toString(),
                        valor: dataFormatada
                    });
                }
            });

            var data = null;
            if (camposProvisorioPermanente.length > 0) {
                data = {
                    atividades: {
                        grauRisco: secao.grauRisco.codigo,
                        campoProvisorioPermanente: camposProvisorioPermanente
                    }
                };
            } else {
                data = {
                    atividades: {
                        grauRisco: secao.grauRisco.codigo
                    }
                };
            }
            camposAdicionaisResposta.value =  JSON.stringify(data);
        }
    });
}

const visualizarInformacoes = (e) => {
    dadosVisualizar.value = e;
    buttonDialogInfo.value.openDialog();
}

const dataInfo = (data) => {
    if (data && typeof data === 'object') {
        return data.descricao;
    }
    return data;
}

const closeDialog = (e) => {
    inscricoes.value = [];
    aprovarSucessoDialog.value = false;
    inscricaoDialog.value = false;
    showDialod.value = false;
}

const openDialog = (e) => {
    dados.value = e;

    if (dados.value.acao === 'gerarAlvara') {
        veriticaInscricoes();
    }

    showDialod.value = true;
}

const openFile = (codigo, arquivos, viewAll = false) => {
    var ids = [];

    if (viewAll === true) {
        if (anexosList.value) {
            anexosList.value.forEach(arquivo => {
                ids.push(arquivo.codigo);
            });
        } else {
            toast.add({severity: 'warn', summary: 'Atenção', detail: 'O Atendimento não possui nenhum anexo!', life: 3000});
            return;
        }
    } else {
        ids.push(codigo);
    }

    ids = ids.join(',');

    var urlTarget = `${CurrentWindow.corpo.CurrentWindow.ECIDADE_REQUEST_PATH}db_visualizador_documentos.php?ids=${ids}`;

    if (props.visualizaEmOutraJanela) {
        window.open(urlTarget);
    } else {
        url.value = urlTarget;
        showVisualizadorArquivos.value = true;
    }
}

const closeVisualizadorArquivos = () => {
    showVisualizadorArquivos.value = false;
}

const prepararAtendimento = () => {
    criarJsonCamposAdicionais();
    var parametros = null;
    if (dados.value.acao === 'gerarAlvara') {
        if (inscricoes.value.length > 0) {
            inscricaoDialog.value = true;
        } else {
            parametros = setParametros();
            aprovarAtendimento(parametros);
        }
    } else {
        parametros = setParametros();
        aprovarAtendimento(parametros);
    }
}

const alterarAlvara = () => {
    if (selectedInscricao.value === null) {
        toast.add({severity: 'warn', summary: 'campo obrigatório', detail: 'Selecione uma inscrição para efetuar a alteração!', life: 3000});
        return;
    }
    var parametros = setParametros(selectedInscricao.value);
    aprovarAtendimento(parametros);
}

const aprovarAtendimento = async (parametros) => {
    try {
        textLoad.value = "Aprovando Atendimento...";
        modalLoading.value = true;

        const resp = await window.axios.post(
            `v4/api/patrimonial/ouvidoria/atendimento/atendimento/aprovarProcessoOuvidoria`,
            parametros
        );

        if (resp.data.error === true) {
            modalLoading.value = false;
            return;
        }

        modalLoading.value = false;

        const processo = resp.data.data;

        Swal.fire({
            icon: "success",
            title: "Sucesso!",
            text: `${resp.data.message}`,
            confirmButtonColor: '#4f7c9e'
        }).then(() => {
            if (props.emiteRecibo) {
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
                        closeDialog();
                        js_OpenJanelaIframe(
                            'CurrentWindow.corpo',
                            'db_iframe_documento_inclusao',
                            `${ECIDADE_REQUEST_PATH}cai4_recibo001.php?` +
                            `p58_codproc=${processo.p58_codproc}` +
                            `&codtipo=${processo.p58_codigo}` +
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
                                closeDialog();
                                js_OpenJanelaIframe(
                                    'CurrentWindow.corpo',
                                    'db_iframe_processos_apensados',
                                    `${ECIDADE_REQUEST_PATH}pro4_aba2protprocesso001.php?` +
                                    `p58_codproc=${processo.p58_codproc}`,
                                    'Processos Apensados',
                                    true
                                );
                            } else if (result.isDenied) {
                                sucessoAprovarAtendimento();
                            }
                        });
                    }
                });
            } else {
                sucessoAprovarAtendimento();
            }
        });
    } catch (e) {
        modalLoading.value = false;

        const resposta = e.response.data.message ?
            "Erro: " + e.response.data.message :
            "Não foi possível aprovar atendimento."
        ;
        Swal.fire({
            icon: "error",
            title: "Algo inesperado aconteceu...",
            text: `${resposta}`,
        });
        console.log('erro ' + e);
    }
}

const rejeitarAtendimento = async () => {
    if (empty(observacao.value)) {
        toast.add({severity: 'warn', summary: 'campo obrigatório', detail: 'O campo observação é obrigatório para rejeição!', life: 3000});
        return;
    }

    const parametros = {}
    var numeroAno = dados.value.atendimento.split("/");
    parametros.numeroProcesso = parseInt(numeroAno[0]);
    parametros.anoProcesso = parseInt(numeroAno[1]);
    parametros.motivo = observacao.value;

    try {
        textLoad.value = "Rejeitando Atendimento...";
        modalLoading.value = true;

        const resp = await window.axios.post(
            `v4/api/patrimonial/ouvidoria/atendimento/atendimento/rejeitarProcessoOuvidoria`,
            parametros
        );

        if (resp.data.error === true) {
            modalLoading.value = false;
            return;
        }

        modalLoading.value = false;
        textoAprovadoDialog.value = resp.data.message;
        aprovarSucessoDialog.value = true;
    } catch (e) {
        modalLoading.value = false;
        console.log('erro' + e);
    }
}

const sucessoAprovarAtendimento = () => {
    closeDialog();
    emit('getAtendimento');
}

const setParametros = (inscricao = false) => {
    const parametros = {}
    var numeroAno = dados.value.atendimento.split("/");
    parametros.numeroProcesso = parseInt(numeroAno[0]);
    parametros.anoProcesso = parseInt(numeroAno[1]);
    parametros.camposAdicionais = camposAdicionaisResposta.value;
    parametros.observacao = observacao.value;

    if (inscricao) {
        parametros.inscricao = inscricao;
    }
    return parametros;
}

const veriticaInscricoes = async () => {
    textLoad.value = "Verificando Inscrições...";
    modalLoading.value = true;

    var numeroAno = dados.value.atendimento.split("/");
    var numero = parseInt(numeroAno[0]);
    var ano = parseInt(numeroAno[1]);

    const parametros = {}
    parametros.numeroProcesso = numero;
    parametros.anoProcesso = ano;

    try {
        const resp = await window.axios.post(
            `v4/api/patrimonial/ouvidoria/atendimento/atendimento/existeInscricao`,
            parametros
        );

        if (resp.data.data.success) {
            modalLoading.value = false;
            inscricoes.value = resp.data.data.inscricoes;
            return true;
        }
        modalLoading.value = false;
        return false;
    } catch (e) {
        modalLoading.value = false;
        console.log('erro' + e);
        //retornar uma mensagem de erro na tela
        return false;
    }
}

const visualizarInscricao = function(inscricao) {
    const url = CurrentWindow.corpo.CurrentWindow.ECIDADE_REQUEST_PATH;
    js_OpenJanelaIframe(
        'CurrentWindow.corpo',
        'db_consulta_inscricao',
        `${url}iss3_consinscr003.php?numeroDaInscricao=${inscricao}`,
        'Consulta Inscrição',
        true
    );
}

const toggleSelection = (inscricao) => {
    if (selectedInscricao.value === inscricao) {
        selectedInscricao.value = null;
    }
};

const verificaMaskCpfCnpj = (data) => {
    if (data.length > 11) {
        return '##.###.###/####-##';
    }
    return '###.###.###-##';
}

defineExpose({
    closeDialog,
    openDialog
});
</script>

<template>
    <Dialog v-model:visible="inscricaoDialog" header="Opções">
        <div class="container" style="display:inline-flex">
            <div v-for="(inscricao, index) in inscricoes" :key="index" style="margin-left: 10px;">
                <input type="radio" :id="'inscricao_' + index" :value="inscricao" v-model="selectedInscricao" @click="toggleSelection(inscricao)"/>
                <label :for="'inscricao_' + index" @click="visualizarInscricao(inscricao)" class="ml-2">{{ inscricao }}</label>
            </div>
        </div>
        <template #footer>
            <Button @click="aprovarAtendimento(setParametros())" label="Nova Inscrição" icon="pi pi-file" iconPos="right"/>
            <Button @click="alterarAlvara" label="Alterar Inscrição" icon="pi pi-file-edit" iconPos="right"/>
        </template>
    </Dialog>
    <ModalLoading
        :is-loading="modalLoading"
        :message="textLoad"
    />
    <buttonDialogInfo
        :dadosVisualizar="dadosVisualizar"
        ref="buttonDialogInfo"
    />
    <Dialog
        :visible="showDialod"
        @update:visible="closeDialog"
        style="width: 95%; height: 80%"
        class="p-dialog-maximized"
        header="Dados do atendimento"
    >
        <Fieldset
            style="margin-top:10px"
            legend="Informações do Atendimento"
        >
            <div class="campos">
                <div class="campo-grupo">
                    <b>Atendimento:</b>
                    <input
                        v-model="dados.atendimento"
                        class="input"
                        :readonly="true"
                    />
                </div>
                <div class="campo-grupo">
                    <b>Requerente:</b>
                    <input
                        v-model="dados.solicitante"
                        class="input"
                        :readonly="true"
                    />
                </div>
                <div class="campo-grupo">
                    <b>Descrição:</b>
                    <input
                        v-model="dados.tipo_processo_descricao"
                        class="input"
                        :readonly="true"
                    />
                </div>
                <div class="campo-grupo">
                    <b>Data:</b>
                    <input
                        v-model="dados.data"
                        class="input"
                        :readonly="true"
                    />
                </div>
                <div class="campo-grupo">
                    <b>Status:</b>
                    <input
                        v-model="dados.status"
                        class="input"
                        :readonly="true"
                    />
                </div>
                <div class="campo-grupo">
                    <b>Processo:</b>
                    <input
                        v-model="dados.processo_numero_ano"
                        class="input"
                        :readonly="true"
                    />
                </div>
                <div class="campo-grupo">
                    <b>Instituição:</b>
                    <input
                        v-model="dados.nomeinst"
                        class="input"
                        :readonly="true"
                    />
                </div>
                <div class="campo-grupo">
                    <b>Depto de Origem:</b>
                    <input
                        v-model="dados.departamento_origem"
                        class="input"
                        :readonly="true"
                    />
                </div>
            </div>
        </Fieldset>
        <Fieldset
            legend="Inscrições"
            v-if="inscricoes.length > 0"
            style="margin-top:10px"
        >
            <div class="container" style="display:inline-flex">
                <div v-for="(inscricao, index) in inscricoes" :key="index" style="margin-left: 10px;">
                    <input type="radio" :id="'inscricao_' + index" :value="inscricao" v-model="selectedInscricao" @click="toggleSelection(inscricao)"/>
                    <label :for="'inscricao_' + index" @click="visualizarInscricao(inscricao)" class="ml-2">{{ inscricao }}</label>
                </div>
            </div>
        </Fieldset>
        <Fieldset
            v-for="(secao, index) in searchSecoes()"
            :key="index"
            :legend="secao.label"
            style="margin-top:10px"
        >
            <div>
                <div class="campos"
                     v-if="secao.tipo !== 'tabela' && secao.tipo !== 'anexo' ">
                    <div v-for="(campo, index) in secao.campos"
                         class="campo-grupo"
                         :key="index">
                        <b
                            v-if="campo.label !== ''"
                        >
                            {{ campo.label }}:
                        </b>
                        <input
                            v-model="campo.resposta"
                            class="input"
                            v-mask="campo.mascara.replaceAll('0','#')"
                            :readonly="true"
                            v-if="(campo.tipo === 'string'  || campo.tipo === 'inteiro') && campo.mascara"
                        />
                        <input
                            v-model="campo.resposta"
                            class="input"
                            v-if="campo.tipo === 'cep'"
                            :readonly="true"
                            v-mask="'#####-###'"
                        >
                        <input v-model="campo.resposta"
                               class="input"
                               v-if="(
                                       campo.tipo === 'string' ||
                                       campo.tipo === 'inteiro' ||
                                       campo.tipo === 'texto' ||
                                       campo.tipo === 'monetario' ||
                                       campo.tipo === 'numerico' ||
                                       campo.tipo === 'lista'
                                   ) && !campo.mascara"
                               :readonly="true"
                        />
                        <input
                            v-model="campo.resposta"
                            v-mask="verificaMaskCpfCnpj(campo.resposta)"
                            class="input"
                            v-if="campo.tipo === 'cpfCnpj'"
                            :readonly="true"
                        />
                        <input
                            v-model="campo.resposta"
                            class="input"
                            v-if="campo.tipo === 'email'"
                            :readonly="true"
                        />
                        <input
                            v-model="campo.resposta"
                            v-mask="campo.mascara.replaceAll('0','#')"
                            class="input"
                            v-if="campo.tipo === 'cpf'"
                            :readonly="true"
                        />
                        <input v-model="campo.resposta"
                               v-mask="campo.mascara.replaceAll('0','#')"
                               class="input"
                               v-if="campo.tipo === 'cnpj'"
                               :readonly="true"
                        />

                        <input v-model="campo.resposta"
                               v-mask="'##/##/####'"
                               class="input"
                               v-if="campo.tipo === 'data'"
                               :readonly="true"
                        />
                        <input
                            class="input"
                            v-if="campo.tipo === 'autocomplete' || campo.tipo === 'lista_dinamica' "
                            :value="campo.resposta ? campo.resposta.descricao :''"
                            :readonly="true"
                        >
                        <button
                            style="width:100px; align-self: center; padding:10px"
                            v-if="campo.tipo === 'button-dialog' || campo.tipo === 'dialog' "
                            @click="visualizarInformacoes(campo.resposta)"
                            class="button-dialog"
                        >
                            Visualziar
                        </button>
                    </div>
                </div>
                <div v-if="secao.tipo === 'tabela'" >
                    <div
                        v-if="secao.nome === 'transmitentes' ||
                             secao.nome === 'intermediadores' ||
                             secao.nome === 'adquirentes' ||
                             secao.nome === 'benfeitorias' ||
                             secao.nome === 'atividades' ||
                             secao.nome === 'socios'"
                    >
                        <DataTable :value="secao.resposta">
                            <div v-for="(campo,index) in secao.campos"
                                 :key="index">
                                <Column :header="campo.label">
                                    <template #body="{ data }">
                                        {{ dataInfo(data[campo.nome]) }}
                                    </template>
                                </Column>
                            </div>
                            <Column header="Permanente" v-if="gerarCamposAddAlvara">
                                <template #body="{ data }">
                                    <Dropdown
                                        v-model="data.provisorio"
                                        :options="opcoes"
                                        optionLabel="name"
                                    />
                                    <Calendar
                                        v-if="data.provisorio.code !== 1"
                                        id="data"
                                        v-model="data.dataProvisorio"
                                        dateFormat="dd/mm/yy"
                                        showIcon
                                        style="padding-left:15px;width:140px;"
                                        showButtonBar
                                    />
                                </template>
                            </Column>
                        </DataTable>
                        <div class="container" style="margin: inherit;">
                            <label>Grau de Risco:</label>
                            <Dropdown
                                style="margin-left: 10px;"
                                v-model="secao.grauRisco"
                                :options="opcoesGrauRisco"
                                optionLabel="descricao"
                            />
                        </div>
                    </div>
                    <div v-else v-for="(resposta,index) in secao.resposta"
                         class="campos"
                         :key="index">
                        <div v-for="(campo,index) in secao.campos"
                             class="campo-grupo"
                             :key="index">
                            <b>{{ campo.label }}:</b>
                            <input v-model="resposta[campo.nome]"
                                   class="input"
                                   v-mask="campo.mascara.replaceAll('0','#')"
                                   v-if="campo.tipo === 'string' && campo.mascara"
                                   :readonly="true"
                            >

                            <input class="input"
                                   :readonly="true"
                                   v-model="resposta[campo.nome]"
                                   v-if="(
                                       campo.tipo === 'string' ||
                                       campo.tipo === 'inteiro' ||
                                       campo.tipo === 'texto' ||
                                       campo.tipo === 'monetario' ||
                                       campo.tipo === 'numerico'
                                   ) && !campo.mascara"
                            >

                            <input v-model="resposta[campo.nome]"
                                   :readonly="true"
                                   v-mask="'###.###.###-##'"
                                   class="input"
                                   v-if="campo.tipo === 'cpfCnpj'"
                            >

                            <input v-model="resposta[campo.nome]"
                                   :readonly="true"
                                   v-mask="'###.###.###-##'"
                                   class="input"
                                   v-if="campo.tipo === 'cpf'"
                            >

                            <input v-model="resposta[campo.nome]"
                                   :readonly="true"
                                   v-mask="'##.###.###/####-##'"
                                   class="input"
                                   v-if="campo.tipo === 'cnpj'"
                            >

                            <input
                                v-model="resposta[campo.nome]"
                                class="input"
                                v-if="campo.tipo === 'email'"
                                :readonly="true"
                            >

                            <input
                                v-model="resposta[campo.nome]"
                                class="input"
                                v-if="campo.tipo === 'cep'"
                                :readonly="true"
                                v-mask="'#####-###'"
                            >

                            <input
                                :readonly="true"
                                class="input"
                                v-model="resposta[campo.nome]"
                                v-mask="'##/##/####'"
                                v-if="campo.tipo === 'data'"
                            >

                            <input
                                :readonly="true"
                                class="input"
                                v-if="campo.tipo === 'autocomplete' || campo.tipo === 'lista_dinamica' "
                                :value="resposta[campo.nome] ? resposta[campo.nome].descricao :''"
                            >

                            <select v-model="resposta[campo.nome]"
                                    class="input"
                                    v-if="campo.tipo === 'lista'"
                                    disabled
                            >
                                <option value="">--</option>
                                <option v-for="(opcao, index) in  campo.opcoes"
                                        :value="opcao"
                                        :key="index">
                                    {{ opcao.descricao }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="campos" v-if="secao.tipo === 'anexo'">
                    <div v-for="(arquivo, index) in secao.resposta"
                         class="campo-grupo"
                         :key="index">
                        <input
                            v-model="arquivo.descricao"
                            class="input arquivo-select"
                            :readonly="true"
                            @click="openFile(arquivo.codigo, secao.resposta)"
                        />
                    </div>
                </div>
            </div>
        </Fieldset>
        <div class="campos-acoes" v-if="aprovarAtendimentos">
            <label class="label-observacao" for="observacao">
                Observação
            </label>

            <Textarea
                id="observacao"
                v-model="observacao"
                rows="5"
                cols="30"
                style="width: 98.5%;"
            ></Textarea>

            <div class="action-buttons-container">
                <Button
                    label="Aprovar"
                    severity="success"
                    icon="pi pi-check"
                    iconPos="right"
                    class="action-button"
                    @click="prepararAtendimento()"
                ></Button>
                <Button
                    label="Rejeitar"
                    severity="danger"
                    icon="pi pi-times"
                    iconPos="right"
                    class="action-button"
                    @click="rejeitarAtendimento()"
                ></Button>
                <Button
                    label="Visualizar Documentos"
                    severity="info"
                    icon="pi pi-file"
                    iconPos="right"
                    class="action-button"
                    @click="openFile(null, null, true)"
                ></Button>
            </div>
        </div>
        <div id="visualizador-arquivos" v-if="showVisualizadorArquivos">
            <iframe :src="url"></iframe>
            <button @click="closeVisualizadorArquivos">Fechar</button>
        </div>
    </Dialog>
</template>

<style scoped>
    .button-dialog {
        border-radius: 30px;
    }

    .arquivo-select:hover {
        cursor: pointer;
    }

    .campos {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        margin: 5px;
        padding: 2px;
    }

    .campo-grupo {
        display: flex;
        flex-direction: column;
        padding: 5px;
        gap: 2px;
    }

    #visualizador-arquivos {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
        background-color: rgba(0, 0, 0, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
    }

    #visualizador-arquivos iframe {
        width: 100%;
        height: 100%;
    }

    #visualizador-arquivos button {
        position: absolute;
        top: 10px;
        right: 10px;
    }

    .campos-acoes {
        margin-top: 10px;
        text-align:center;
    }

    .label-observacao {
        font-size: 1.2rem;
        font-weight: bold;
        display: block;
        text-align: start;
        margin-left: 1%;
        margin-bottom: 5px;
    }

    .input {
        min-height: 35px;
        background-color: #dddddd;
        border: none;
        padding: 10px;
    }

    ::v-deep .espacamento-icone {
        margin-right: 5px;
    }

    .action-buttons-container {
        padding: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
    }

    .action-button {
        display: flex !important;
        flex-direction: row-reverse;
        justify-content: center;
        align-items: center;
        gap: 5px;
    }

    :deep(.p-button-icon.p-button-icon-right.pi) {
        margin: 0;
    }
</style>
