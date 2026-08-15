<script setup>
import {onMounted, ref, watch} from "vue";
import DialogCGM from "@modules/Patrimonial/Protocolo/Components/DialogProtocoloDocumentoCGM.vue";

const emit = defineEmits(['setTotalFiltros', 'getProcessos', 'setCamposForm', 'getAtendimentos']);
const props = defineProps(['telaProcesso', 'aprovarAtendimento']);

const showDialod = ref(false);
const filtrosAvancados = ref(false);
const filtrosAvancadosCount = ref(0);
const filtrosTotal = ref(0);
const checkboxFiltro = ref([]);
const checkboxFiltroTipoProcesso = ref([]);
const checkboxFiltroResponsavel = ref([]);
const filtrosStatus = ref(null);
const statusAtendimento = ref([
    { nome: 'Aguardando atendimento', code: 1 },
    { nome: 'Aprovado', code: 2 },
    { nome: 'Rejeitado', code: 3 }
]);
const camposForm = ref({
    processo: '',
    requerente: '',
    atendimento: '',
    descricao: '',
    cgm: '',
    filtrosCheckBox: checkboxFiltro,
    checkboxFiltroTipoProcesso: checkboxFiltroTipoProcesso,
    checkboxFiltroResponsavel: checkboxFiltroResponsavel
});
const dataDe = ref(null);
const dataAte = ref(null);
const dialogCGM = ref(null);
const requerente = ref([]);
const cgms = ref([]);

onMounted(() => {
    filtrosTotal.value = 0;
});

const closeDialog = (e) => {
    filtrosAvancados.value = false;
    showDialod.value = false;
}

const openDialog = (e) => {
    showDialod.value = true;
}

function totalFiltrosAplicados() {
    filtrosTotal.value = checkboxFiltroResponsavel.value.length + checkboxFiltroTipoProcesso.value.length + checkboxFiltro.value.length;

    if (Object.keys(camposForm.value).length > 0) {
        if ('atendimento' in camposForm.value && camposForm.value.atendimento !== '') {
            filtrosTotal.value++;
        }
        if ((camposForm.value.cgm !== '' && 'cgm' in camposForm.value) || (camposForm.value.requerente !== '' && 'requerente' in camposForm.value)) {
            filtrosTotal.value++;
        }

        if ('processo' in camposForm.value && camposForm.value.processo !== '') {
            filtrosTotal.value++;
        }
        if ('descricao' in camposForm.value && camposForm.value.descricao !== '') {
            filtrosTotal.value++;
        }
        if ((dataDe.value !== null) && (dataAte.value !== null)) {
            filtrosTotal.value++;
        }
    }

    return filtrosTotal.value;
}

watch(
    () => [dataDe.value, dataAte.value],
    ([dataDe, dataAte]) => {
        if (props.telaProcesso === true) {
            if (dataDe !== null && dataAte !== null) {
                aplicaFiltro();
            }

            if (dataDe === null && dataAte === null) {
                aplicaFiltro();
            }
        } else {
            if (dataDe !== null && dataAte !== null) {
                emit('setTotalFiltros', totalFiltrosAplicados());
            }

            if (dataDe === null && dataAte === null) {
                filtrosTotal.value--;
                emit('setTotalFiltros', totalFiltrosAplicados());
            }
        }
    }
);

const  aplicaFiltro = function (e) {
    if (dataDe.value && dataAte.value) {
        var data = dataDe.value;
        var dia = null;
        var mes = null;

        if (data.getDate() < 10) {
            dia = `0${data.getDate()}`;
        } else {
            dia = data.getDate();
        }

        if (data.getMonth() + 1 < 10) {
            mes = `0${data.getMonth() + 1}`;
        } else {
            mes = data.getMonth() + 1;
        }
        camposForm.value.dataDe = `${data.getFullYear()}-${mes}-${dia}`;

        var data2 = dataAte.value;
        var dia2 = null;
        var mes2 = null;

        if (data2.getDate() < 10) {
            dia2 = `0${data2.getDate()}`;
        } else {
            dia2 = data2.getDate();
        }

        if (data2.getMonth() + 1 < 10) {
            mes2 = `0${data2.getMonth() + 1}`;
        } else {
            mes2 = data2.getMonth() + 1;
        }
        camposForm.value.dataAte = `${data2.getFullYear()}-${mes2}-${dia2}`;
    }

    if (dataDe.value === null && dataAte.value === null) {
        camposForm.value.dataDe = '';
        camposForm.value.dataAte = '';
    }

    if (checkboxFiltroResponsavel.value.length > 0 || checkboxFiltroTipoProcesso.value.length > 0) {
        filtrosAvancadosCount.value = checkboxFiltroResponsavel.value.length + checkboxFiltroTipoProcesso.value.length;
    } else {
        filtrosAvancadosCount.value = 0;
    }

    camposForm.value.filtrosCheckBox = checkboxFiltro.value;
    camposForm.value.checkboxFiltroTipoProcesso = checkboxFiltroTipoProcesso.value;
    camposForm.value.checkboxFiltroResponsavel = checkboxFiltroResponsavel.value;

    emit('setTotalFiltros', totalFiltrosAplicados());
    emit('setCamposForm', camposForm.value);
    emit('getProcessos');
}

const filtrar = function () {
    if (dataDe.value && dataAte.value) {
        var data = dataDe.value;
        var dia = null;
        var mes = null;

        if (data.getDate() < 10) {
            dia = `0${data.getDate()}`;
        } else {
            dia = data.getDate();
        }

        if (data.getMonth() + 1 < 10) {
            mes = `0${data.getMonth() + 1}`;
        } else {
            mes = data.getMonth() + 1;
        }
        camposForm.value.dataDe = `${data.getFullYear()}-${mes}-${dia}`;

        var data2 = dataAte.value;
        var dia2 = null;
        var mes2 = null;

        if (data2.getDate() < 10) {
            dia2 = `0${data2.getDate()}`;
        } else {
            dia2 = data2.getDate();
        }

        if (data2.getMonth() + 1 < 10) {
            mes2 = `0${data2.getMonth() + 1}`;
        } else {
            mes2 = data2.getMonth() + 1;
        }
        camposForm.value.dataAte = `${data2.getFullYear()}-${mes2}-${dia2}`;
    }

    if (filtrosStatus.value) {
        camposForm.value.filtrosStatus = filtrosStatus.value.map(item => item.nome).join(', ');
    }

    emit('setCamposForm', camposForm.value);
    emit('getAtendimentos');
}

const openDialogCGM = () => {
    dialogCGM.value.openDialog();
}

const selecaoDoCgmPeloDialogCGM = (cgm) => {
    camposForm.value.cgm = cgm.z01_numcgm;
    requerente.value = cgm.z01_nome;
    emit('setTotalFiltros', totalFiltrosAplicados());
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

const changeRequerente = () => {
    if (typeof requerente.value === 'object') {
        if ('z01_numcgm' in requerente.value && requerente.value.z01_numcgm !== null) {
            camposForm.value.cgm = requerente.value.z01_numcgm;
        }

        if ('z01_nome' in requerente.value && requerente.value.z01_nome !== null) {
            camposForm.value.requerente = requerente.value.z01_nome;
        }

        emit('setTotalFiltros', totalFiltrosAplicados());
    }
}

const limparFiltros = () => {
    camposForm.value = [];
    requerente.value = [];
    dataDe.value = null;
    dataAte.value = null;
    emit('setTotalFiltros', totalFiltrosAplicados());
}

const limparFiltrosProcesso = (attProcessos = true) => {
    camposForm.value = [];
    dataDe.value = null;
    dataAte.value = null;
    checkboxFiltro.value = [];
    checkboxFiltroTipoProcesso.value = [];
    checkboxFiltroResponsavel.value = [];
    filtrosAvancadosCount.value = 0;
    emit('setTotalFiltros', totalFiltrosAplicados());
    emit('setCamposForm', camposForm.value);
    if (attProcessos) {
        emit('getProcessos');
    }
}

defineExpose({
    limparFiltrosProcesso,
    closeDialog,
    openDialog
});
</script>

<template>
    <DialogCGM
        ref="dialogCGM"
        @select="selecaoDoCgmPeloDialogCGM"
    />
    <Dialog
        :visible="showDialod"
        @update:visible="closeDialog"
        position="bottom"
        :showHeader="false"
        :dismissableMask="true"
        :modal="true"
        :closable="true"
        contentStyle="border-radius:10px"
    >
        <div class="modal" v-if="telaProcesso">
            <i class="pi pi-times fechar-modal" @click="closeDialog"></i>
            <div class="filtros-input">
                <div class="filtro-campo">
                    <b style="margin-left: 5px;">Processo</b><br/>
                    <InputText @change="aplicaFiltro(camposForm.processo)" placeholder="Ex:.. 15/2024" v-model="camposForm.processo" style="width:160px"/>
                </div>
                <div class="filtro-campo">
                    <b style="margin-left: 5px;">Requerente</b><br/>
                    <InputText @change="aplicaFiltro(camposForm.requerente)" placeholder="Ex:.. Adereldo" v-model="camposForm.requerente" style="width:160px"/>
                </div>
                <div class="filtro-campo">
                    <b style="margin-left: 5px;">De:</b><br/>
                    <Calendar
                        id="dataDe"
                        v-model="dataDe"
                        dateFormat="dd/mm/yy"
                        showIcon
                        style="width:160px"
                        showButtonBar
                    />
                </div>
                <div class="filtro-campo">
                    <b style="margin-left: 5px;">Até:</b><br/>
                    <Calendar
                        id="dataAte"
                        v-model="dataAte"
                        dateFormat="dd/mm/yy"
                        showIcon
                        style="width:160px"
                        showButtonBar
                    />
                </div>
            </div>
            <div class="filtros-input" style="margin-top: 5px;">
                <div class="filtro-campo">
                    <b style="margin-left: 5px;">Assunto</b><br/>
                    <InputText @change="aplicaFiltro(camposForm.descricao)" placeholder="Ex:.. ITBI" v-model="camposForm.descricao" style="width:160px"/>
                </div>
            </div>
            <div class="filtros-input-checkbox">
                <div style="margin: 5px;">
                    <Checkbox v-model="checkboxFiltro" value="1" @change="aplicaFiltro(checkboxFiltro)"/>
                    <Tag class="ml-2" value="A receber"  style="color:black;background-color: #59cbff;border-radius: 5px;"/>
                </div>
                <div style="margin: 5px;">
                    <Checkbox v-model="checkboxFiltro" value="2" @change="aplicaFiltro(checkboxFiltro)"/>
                    <Tag class="ml-2" value="Recebidos"  style="color:black;background-color: #7bef7b;border-radius: 5px;"/>
                </div>
                <div style="margin: 5px;">
                    <Checkbox v-model="checkboxFiltro" value="3" @change="aplicaFiltro(checkboxFiltro)"/>
                    <Tag class="ml-2" value="Despachados"  style="color:black;background-color: #ffca6d;border-radius: 5px;"/>
                </div>
                <div style="margin: 5px;">
                    <Checkbox v-model="checkboxFiltro" value="4" @change="aplicaFiltro(checkboxFiltro)"/>
                    <Tag class="ml-2" value="Mensagens não lida"  style="color:black;border-radius: 5px;background-color: #f8e702;"/>
                </div>
            </div>
            <div class="filtros-avancados">
                <div class="filtro-avancado-label">
                    <Badge
                        v-if="filtrosAvancadosCount > 0"
                        :value="filtrosAvancadosCount"
                        title="Filtros avançados aplicados"
                        style="padding-top: 1px; cursor: pointer"
                        class="pulsacao"
                    />
                    <p>Filtros Avançados</p>
                    <i title="Abrir filtros avançados" class="pi pi-arrow-right icon-filtro-avancado" style="padding-top: 5px;" v-if="filtrosAvancados === false" @click="filtrosAvancados = true"></i>
                    <i title="Fechar filtros avançados" class="pi pi-times icon-filtro-avancado" style="padding-top: 5px; color:red" v-if="filtrosAvancados === true" @click="filtrosAvancados = false"></i>
                </div>
                <div class="campos-filtros-avancados" v-if="filtrosAvancados" style="display:flex">
                    <div class="grupo-esquerda">
                        <div class="container" style="max-height: 10px;">
                            <Checkbox v-model="checkboxFiltroResponsavel" value="meu" @change="aplicaFiltro(checkboxFiltroResponsavel)"/>
                            <label class="ml-2">Processos vinculados ao meu usuário</label>
                        </div>
                        <div class="container" style="max-height: 10px;">
                            <Checkbox v-model="checkboxFiltroResponsavel" value="outros" @change="aplicaFiltro(checkboxFiltroResponsavel)"/>
                            <label class="ml-2">Processos vinculados a outros usuários do departamento</label>
                        </div>
                        <div class="container" style="max-height: 10px;">
                            <Checkbox v-model="checkboxFiltroResponsavel" value="departamento" @change="aplicaFiltro(checkboxFiltroResponsavel)"/>
                            <label class="ml-2">Processos vinculados ao meu departamento</label>
                        </div>
                    </div>
                    <div class="grupo-direita">
                        <div class="container" style="max-height: 10px;">
                            <Checkbox v-model="checkboxFiltroTipoProcesso" value="interno"  @change="aplicaFiltro(checkboxFiltroTipoProcesso)"/>
                            <label class="ml-2"> Interno </label>
                        </div>
                        <div class="container" style="max-height: 10px;">
                            <Checkbox v-model="checkboxFiltroTipoProcesso" value="externo"  @change="aplicaFiltro(checkboxFiltroTipoProcesso)"/>
                            <label class="ml-2"> Externo </label>
                        </div>
                        <div class="container" style="max-height: 10px;">
                            <Checkbox v-model="checkboxFiltroTipoProcesso" value="manual" @change="aplicaFiltro(checkboxFiltroTipoProcesso)"/>
                            <label class="ml-2"> Manual </label>
                        </div>
                    </div>
                </div>
            </div>
            <div style="margin-top: 20px;text-align: center;">
                <Button
                    v-if="filtrosTotal > 0"
                    label="Limpar Tudo"
                    @click="limparFiltrosProcesso()"
                    style="border-radius: 10px"
                />
            </div>
        </div>
        <div class="modal" v-if="!telaProcesso">
            <i class="pi pi-times fechar-modal" @click="closeDialog"></i>
            <div class="filtros-input">
                <div>
                    <b style="margin-left: 5px;">CGM</b><br/>
                    <div class="p-inputgroup">
                        <span
                            class="p-inputgroup-addon"
                            @click="openDialogCGM"
                        >
                            <i class="pi pi-search"></i>
                        </span>
                        <AutoComplete
                            v-model="requerente"
                            placeholder="Titular"
                            :suggestions="cgms"
                            :dropdown="true"
                            @complete="pesquisarCgms($event)"
                            forceSelection
                            optionLabel="z01_nome"
                            @change="changeRequerente"
                            style="width: 240px"
                        />
                    </div>
                </div>
                <div>
                    <b style="margin-left: 5px;">Atendimento</b><br/>
                    <InputText
                        placeholder="Ex:.. 15/2024"
                        v-model="camposForm.atendimento"
                        style="width:190px"
                        @change="aplicaFiltro(camposForm.atendimento)"
                    />
                </div>
                <div>
                    <b style="margin-left: 5px;">De:</b><br/>
                    <Calendar
                        id="dataDe"
                        v-model="dataDe"
                        dateFormat="dd/mm/yy"
                        showIcon
                        style="width:160px"
                        showButtonBar
                    />
                </div>
                <div>
                    <b style="margin-left: 5px;">Até:</b><br/>
                    <Calendar
                        id="dataAte"
                        v-model="dataAte"
                        dateFormat="dd/mm/yy"
                        showIcon
                        style="width:160px"
                        showButtonBar
                    />
                </div>
                <div>
                    <b style="margin-left: 5px;">Assunto</b><br/>
                    <InputText @change="aplicaFiltro(camposForm.descricao)" placeholder="Ex:.. ITBI" v-model="camposForm.descricao" style="width:160px"/>
                </div>
                <div v-if="!aprovarAtendimento" class="filtro-campo" style="display:flex; flex-direction: column;">
                    <b>Status</b>
                    <MultiSelect
                        style="min-width: 183px"
                        v-model="filtrosStatus"
                        :options="statusAtendimento"
                        optionLabel="nome"
                        placeholder="Selecione um Status"
                    />
                </div>
            </div>
            <div class="flex justify-content-center flex-wrap" style="margin-top: 10px">
                <Button
                    label="Limpar"
                    style="margin:5px"
                    icon="pi pi-trash"
                    @click="limparFiltros"
                    class="p-button-secondary action-btn"
                />
                <Button
                    label="Filtrar"
                    style="margin:5px"
                    icon="pi pi-search"
                    @click="filtrar"
                    class="action-btn"
                />
            </div>
        </div>
    </Dialog>
</template>

<style scoped>
.filtros-input {
    display:flex;
    flex-wrap:wrap;
    gap: 10px;
}
.filtros-input-checkbox {
    display:flex;
    flex-wrap:wrap;
    margin-top:15px;
}
.filtro-avancado-label {
    display: flex;
    align-items: center;
    gap: 10px;
}
.fechar-modal{
    position: absolute;
    top: 10px;
    right: 10px;
}
.fechar-modal:hover {
    cursor:pointer;
    color:red;
}
.icon-filtro-avancado:hover {
    cursor:pointer;
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
}
.modal {
    padding: 15px;
}
@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.2);
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
}

.action-btn {
    border-radius: 2rem !important;
}
</style>
