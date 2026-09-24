<template>
    <ModalLoading
        :is-loading="modalLoading"
    />

    <DialogProtocoloAddCgm
        ref="dialogAddCgm"
        @dialogClosed="pesquisarCgms"
    />

    <Dialog
        header="Incluir Solicitações de Assinatura"
        :maximizable="true"
        :modal="true"
        :style="{ width: 'fit-content' }"
        :visible="showDialog"
        :closable="true"
        @update:visible="closeDialog"
    >
        <div>
            <div v-if="cgmSelected.length !== 0" style="display: flex; flex-direction: column;">
                <div class="destinatarios-container">
                    <div class="destinatarios-list">
                        <span>Destinatários:</span>
                        <div v-for="cgm of cgmSelected" :key="cgm.cgm">
                            <Chip :label="cgm.nome" removable @remove="removeCgm(cgm)" />
                        </div>
                    </div>
                </div>

                <hr class="divisoria">

                <Textarea
                    v-model="localObservacao"
                    placeholder="Observação (opcional)"
                    auto-resize
                    :maxlength="maxCaracteres"
                    style="width: 100%; padding: 15px; border: none; border-radius: 10px;"
                />

                <small v-if="localObservacao.length !== 0" style="align-self: flex-end; margin: -10px 15px 5px 0;">
                    {{ localObservacao.length }} / {{ maxCaracteres }}
                </small>

                <hr class="divisoria">
            </div>

            <div class="filtro-container" ref="containerFiltro" @keyup.enter="pesquisarCgms({ page: 0 })">
                <div class="input-container">
                    <div>
                        <InputText v-model="filtro.cgm" placeholder="CGM" />
                    </div>
                    <div>
                        <InputText v-model="filtro.nome" placeholder="Nome"/>
                    </div>
                    <div>
                        <InputText v-model="filtro.cpf_cnpj" placeholder="CPF/CNPJ"/>
                    </div>
                </div>

                <div class="flex justify-content-center flex-wrap">
                    <Button
                        label="Pesquisar"
                        icon="pi pi-search"
                        class="filter-button"
                        @click="pesquisarCgms({ page: 0 })"
                    ></Button>
                    <Button
                        label="Limpar Filtros"
                        icon="pi pi-filter-slash"
                        class="p-button-secondary filter-button"
                        @click="limparFiltros"
                    ></Button>
                    <Button
                        label="Adicionar CGM"
                        icon="pi pi-user-plus"
                        class="filter-button"
                        style="margin:5px; background-color: #209f20; border: 1px solid #209f20;"
                        @click="openDialogAddCgm(null, null)"
                    ></Button>
                </div>
            </div>

            <div class="card" ref="containerTable">
                <DataTable
                    showGridlines
                    responsiveLayout="scroll"
                    selectionMode="single"
                    scrollHeight="flex"
                    :value="cgms"
                    :loading="loading"
                    :scrollable="true"
                    @rowSelect="selectItem"
                >
                    <Column field="z01_numcgm" header="CGM" />
                    <Column field="z01_nome" header="NOME" />
                    <Column field="z01_cgccpf" header="CPF/CNPJ" />
                    <Column header="AÇÃO">
                        <template #body="{ data }">
                            <i
                                id="caneta-editar"
                                class="pi pi-pencil"
                                @click="openDialogAddCgm($event, data.z01_cgccpf)"
                            ></i>
                        </template>
                    </Column>
                </DataTable>
            </div>

            <Paginator
                ref="paginator"
                :rows="paginate.perpage"
                :totalRecords="paginate.total"
                v-model:first="paginate.offset"
                :rowsPerPageOptions="[10, 20, 30]"
                @page="pesquisarCgms($event)"
            />

            <div
                v-if="cgmSelected.length !== 0"
                class="btn-enviar"
                @click="enviarSolicitacao"
            >
                Enviar
            </div>
        </div>
    </Dialog>
</template>

<script setup>

import { ref, reactive, watch } from 'vue';
import DialogProtocoloAddCgm from "./DialogProtocoloAddCgm.vue";
import ModalLoading from "../../../Components/ModalLoading.vue";
import Chip from 'primevue/chip';
import Textarea from "primevue/textarea";

const props = defineProps({
    carregarDadosAutomatico: {
        type: Boolean,
        required: false,
        default: true
    },
    cgmSelected: {
        type: Array,
        default: () => []
    },
    removerCgmSelecionado: Function,
    solicitarAssinaturas: Function,
    observacao: String
});

const cgms = ref([]);
const paginate = ref(defaultPaginate);
const filtro = ref(defaultFiltros);
const loading = ref(false);
const paginator = ref(null);
const showDialog = ref(false);
const containerFiltro = ref(null);
const containerTable = ref(null);
const dialogAddCgm = ref(null);
const modalLoading = ref(false);
const localCgmSelected = reactive([...props.cgmSelected]);
const localObservacao = ref(props.observacao);
const maxCaracteres = 500;

const defaultPaginate = function () {
    this.page = 0;
    this.total = 0;
    this.perpage = 10;
    this.offset = 0;
};

const defaultFiltros = function () {
    this.nome = '';
    this.cgm = '';
    this.cpf_cnpj = '';
};

const emit = defineEmits(['select', 'update:observacao']);

watch(() => props.cgmSelected, (newValue) => {
    localCgmSelected.length = 0;
    localCgmSelected.push(...newValue);
}, { immediate: true });

watch(localObservacao, (newValue) => {
    emit('update:observacao', newValue);
})

const pesquisarCgms = async ({page, rows} = {page: 0, rows: 10}) => {
    loading.value = true;

    try {
        paginate.value.page = ++page;

        if (rows) {
            if (paginate.value.perpage !== parseInt(rows)) {
                paginate.value.page = 1;
                paginate.value.offset = 0;
            }
            paginate.value.perpage = parseInt(rows);
        }

        const urlParams = new URLSearchParams({...filtro.value, ...paginate.value});
        const resp = await window.axios.get(
            `v4/api/patrimonial/protocolo/cgm/search?${urlParams.toString()}`
        );

        const data = resp.data.data.data;
        const {current_page, total, per_page} = resp.data.data;

        paginate.value = {
            page: current_page,
            offset: current_page * per_page - 1,
            total,
            perpage: parseInt(per_page)
        };

        cgms.value = data;
    } catch (e) {
        cgms.value = [];
    }

    loading.value = false;
}

const closeDialog = (e) => {
    showDialog.value = false;
    cgms.value = [];
    props.cgmSelected.value = [];
    localObservacao.value = '';
    filtro.value = new defaultFiltros();
    paginate.value = new defaultPaginate();
}

const openDialog = (e) => {
    showDialog.value = true;
    cgms.value = [];
    filtro.value = new defaultFiltros();
    paginate.value = new defaultPaginate();
    if (props.carregarDadosAutomatico) {
        pesquisarCgms();
    }
}

const limparFiltros = () => {
    filtro.value = new defaultFiltros();
    pesquisarCgms();
}

const selectItem = (e) => {
    emit("select", e.data);
    filtro.value = new defaultFiltros();
}

const removeCgm = (cgm) => {
    props.removerCgmSelecionado(cgm);
}

const enviarSolicitacao = () => {
    props.solicitarAssinaturas();
    closeDialog(false);
}

const openDialogAddCgm = async (event = null, cpfCnpj = null) => {
    if (event !== null) {
        event.stopPropagation();
    }

    try {
        modalLoading.value = true;
        const resp = await axios.post(
            "v4/api/patrimonial/protocolo/verifica-permissao-cgm"
        );

        if (resp.data.data.erro) {
            alert(resp.data.data.mensagem);
            modalLoading.value = false;
        } else {
            modalLoading.value = false;
            dialogAddCgm.value.openDialog();

            if (cpfCnpj !== null) {
                const cgm = {
                    cpfcnpj: cpfCnpj,
                    isFisica: false,
                    isJuridica: false
                };
                if (cpfCnpj.length <= 11) {
                    cgm.cpfcnpj = cpfCnpj;
                    cgm.isFisica = true;
                    cgm.isJuridica = false;
                } else {
                    cgm.cpfcnpj = cpfCnpj;
                    cgm.isFisica = false;
                    cgm.isJuridica = true;
                }
                await dialogAddCgm.value.pesquisaCpfCnpj(cgm);
            }
        }
    } catch (e) {
        modalLoading.value = false;
        alert('Erro ao verificar permissão do usuário!');
    }
}

defineExpose({
    containerTable,
    containerFiltro,
    closeDialog,
    openDialog
});
</script>

<style scoped>
.destinatarios-container {
    padding: 15px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: flex-start;
    gap: 5px;
}

.destinatarios-list {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    gap: 5px;
    flex-wrap: wrap;
}

.destinatarios-list span {
    font-size: 1.3rem;
}

.filtro-container {
    padding: 15px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 5px;
}

.input-container {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

#caneta-editar:hover {
    color: blue;
}

.filter-button {
    margin: 5px;
    border-radius: 2rem;
}

.p-dialog .p-dialog-header .p-dialog-header-icon {
    color: #e1dfdd !important;
}

.p-dialog .p-dialog-header .p-dialog-header-icon:focus {
    box-shadow: none !important;
}

.p-link:focus {
    box-shadow: none !important;
}

.p-chip {
    padding: 0 0.5rem !important;
    background-color: #f4f4f4 !important;
    border: 1px solid #c8c8c8 !important;
    border-radius: 16px !important;
}

.p-chip .p-chip-text {
    margin-top: 0.4rem !important;
    margin-bottom: 0.4rem !important;
    font-size: 1.1rem !important;
    line-height: normal !important;
}

.p-chip-text {
    line-height: normal !important;
}

.p-inputtext {
    border-radius: 2rem !important;
}

.btn-enviar {
    position: fixed;
    bottom: 10px;
    right: 20px;
    background: #246e24;
    padding: 10px;
    color: white;
    width: 90px;
    text-align: center;
    border-radius: 10px;
    cursor: pointer;
    transition: ease 0.3s;
}

.btn-enviar:hover {
    background-color: #287c28;
}

.divisoria {
    width: 97%;
    height: 1px;
    margin: 0 auto;
    background-color: #D1D1D1;
    border: none;
}

:deep(.p-inputtext:enabled:focus) {
    box-shadow: none;
}

:deep(.p-icon) {
    margin-bottom: 2px !important;
}

:deep(.p-dialog .p-dialog-content) {
    padding: 0 !important;
}

:deep(.p-button-secondary) {
    background-color: #d45c00;
}
</style>
