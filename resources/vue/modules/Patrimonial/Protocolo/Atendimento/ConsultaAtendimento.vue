<script setup>

import {onMounted, ref} from "vue";
import ModalLoading from "../../../Components/ModalLoading";
import DialogAtendimentoDados from "./DialogAtendimentoDados.vue";
import DialogFiltros from "@modules/Patrimonial/Protocolo/AndamentoProcesso/Components/DialogFiltros.vue";
import {useToast} from "primevue/usetoast";

const props = defineProps(['aprovarAtendimentos', 'orgao','visualizaOutraJanela', 'emiteRecibo']);
const dialogAtendimentoDados = ref(null);
const modalLoading = ref(false);
const textLoad = ref('');
const atendimentos = ref(null);
const json = ref([]);
const dados = ref([]);
const cgms = ref([]);
const requerente = ref([]);
const instituicaoAtual = ref([]);
const filtrosTotal = ref(0);
const dialogFiltros = ref(null);
const toast = useToast();

const defaultPaginate = function () {
  this.page = 0;
  this.total = 0;
  this.perpage = 15;
  this.offset = 0;
};

const paginate = ref(defaultPaginate);
const camposForm = ref([]);

onMounted(() => {
    getAtendimentos();
})

const getAtendimentos = async ({page, rows} = {page: 0, rows: 15}) => {
  textLoad.value = "Buscando Atendimentos...";
  modalLoading.value = true;

    if (props.aprovarAtendimentos) {
        camposForm.value.filtroDepartamento = true
        camposForm.value.aprovarAtendimento = true
    }

    try {
        paginate.value.page = ++page;
        if (rows) {
          if (paginate.value.perpage !== parseInt(rows)) {
            paginate.value.page = 1;
            paginate.value.offset = 0;
          }
          paginate.value.perpage = parseInt(rows);
        }

        const urlParams = new URLSearchParams({...camposForm.value, ...paginate.value});
        const resp = await window.axios.post(
            `v4/api/patrimonial/ouvidoria/atendimento/atendimento/consultaAtendimentos?${urlParams.toString()}`
        );
        instituicaoAtual.value = resp.data.data.instituicao_atual;
        const data = resp.data.data.data.data;
        const {current_page, total, per_page} = resp.data.data.data;
        paginate.value = {
          page: current_page,
          offset: current_page * per_page - 1,
          total,
          perpage: parseInt(per_page)
        };
        atendimentos.value = data;
        modalLoading.value = false;
    } catch (e) {
        atendimentos.value = [];
        modalLoading.value = false;
    }
}

const formatCurrency = (rowData) => {
    const date = new Date(rowData + "T00:00:00-03:00");
    const day = date.getDate();
    const month = date.getMonth() + 1;
    const year = date.getFullYear();

    const formattedDay = (day < 10 ? '0' : '') + day;
    const formattedMonth = (month < 10 ? '0' : '') + month;

    return `${formattedDay}/${formattedMonth}/${year}`;
}


const selectItem = async(e) => {
    const dadosInformativos = new Object();

    if (parseInt(e.data.codigo_instituicao) === parseInt(instituicaoAtual.value)) {
        textLoad.value = "Buscando Dados do Atendimento...";
        modalLoading.value = true;

        try {
            dadosInformativos.atendimento = e.data.atendimento;
            dadosInformativos.solicitante = e.data.solicitante;
            dadosInformativos.tipo_processo_descricao = e.data.tipo_processo_descricao;
            dadosInformativos.data = formatCurrency(e.data.data);
            dadosInformativos.status = e.data.status;
            dadosInformativos.processo_numero_ano = e.data.processo_numero_ano;
            dadosInformativos.nomeinst = e.data.nomeinst;
            dadosInformativos.departamento_origem = `${e.data.codigo_departamento_origem} - ${e.data.descricao_departamento_origem}`;

            json.value = Object.assign(JSON.parse(e.data.metadados));

            if (Object.keys(json.value).length === 0) {
                modalLoading.value = false;
                toast.add({severity: 'error', summary: 'Erro', detail: 'Metadados indisponíveis do atendimento.', life: 3000});
                return;
            }
            dadosInformativos.acao = json.value.acao;
            dados.value = dadosInformativos;

            modalLoading.value = false;
            dialogAtendimentoDados.value.openDialog(dados.value);
        } catch (e) {
            json.value = [];
            modalLoading.value = false;
        }
    } else {
        alert("Esse atendimento não pertence ao departamento que está logado!");
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
</script>

<template>
    <DialogAtendimentoDados
        ref="dialogAtendimentoDados"
        :json="json"
        :aprovarAtendimentos="aprovarAtendimentos"
        :emiteRecibo="emiteRecibo"
        :visualizaEmOutraJanela="visualizaOutraJanela"
        @getAtendimento="getAtendimentos"
    />

    <ModalLoading
        :is-loading="modalLoading"
        :message="textLoad"
    />

    <DialogFiltros
        ref="dialogFiltros"
        :telaProcesso="false"
        @setTotalFiltros="setTotalFiltros"
        @setCamposForm="setCamposForm"
        @getAtendimentos="getAtendimentos"
        :aprovarAtendimento="aprovarAtendimentos"
    />

    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; padding: 10px;">
        <strong v-if="orgao" style="font-size: 1.3rem;">Órgão: {{orgao}}</strong>

        <div class="buttons-container">
            <Button
                v-if="filtrosTotal > 0"
                label="Filtros"
                class="btn-fitro action-btn"
                icon="pi pi-search"
                iconPos="left"
                :badge="filtrosTotal.toString()"
                @click="dialogFiltros.openDialog()"
            ></Button>
            <Button
                v-if="filtrosTotal <= 0"
                label="Pesquisa"
                class="btn-fitro action-btn"
                icon="pi pi-search"
                iconPos="left"
                @click="dialogFiltros.openDialog()"
            ></Button>
            <Button
                title="Recarregar Atendimentos"
                class="action-btn"
                icon="pi pi-refresh"
                @click="getAtendimentos"
            ></Button>
        </div>
    </div>

    <DataTable
        :value="atendimentos"
        showGridlines
        responsiveLayout="scroll"
        :scrollable="true"
        scrollHeight="flex"
        selectionMode="single"
        @rowSelect="selectItem"
        v-if="atendimentos !== null"
    >
        <Column field="atendimento" header="Atendimento">
            <template #body="{ data }">
                <Tag :value="data.atendimento"  class="atendimento"/>
            </template>
        </Column>
        <Column field="solicitante" header="Requerente" />
        <Column field="tipo_processo_descricao" header="Assunto" />
        <Column header="Data">
            <template #body="{ data }">
                {{ formatCurrency(data.data) }}
            </template>
        </Column>
        <Column field="status" header="Status" />
        <template #footer> Total: {{paginate.total}} </template>
    </DataTable>

    <Paginator
        ref="paginator"
        :rows="paginate.perpage"
        :totalRecords="paginate.total"
        v-model:first="paginate.offset"
        :rowsPerPageOptions="[15, 20, 30]"
        @page="getAtendimentos($event)"
        v-if="atendimentos !== null"
    />
</template>

<style scoped>
.buttons-container {
    height: 35px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 5px;
}

.action-btn {
    height: 100%;
    display: flex !important;
    justify-content: center;
    align-items: center;
    border-radius: 2rem !important;
}

.atendimento {
    background-color: #8787ff;
    color:black;
}

.btn-fitro {
    margin-right: 5px;
    border-radius: 10px;
}
</style>
