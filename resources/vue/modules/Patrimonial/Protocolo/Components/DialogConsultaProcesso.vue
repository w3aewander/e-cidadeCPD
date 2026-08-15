<script setup>
import { ref ,toRaw} from 'vue';
import {formateDate} from "../../../../utils/Strings";
const paginator = ref(null);

const props = defineProps({
    carregarDadosAutomatico: {
        type: Boolean,
        required: false,
        default: true
    },
    ocultarPai: {
      type: Boolean,
      required: false,
      default: false
    },
    ocultarVolume: {
      type: Boolean,
      required: false,
      default: false
    },
    ocultarArquivado: {
        type: Boolean,
        required: false,
        default: false
    },
    titulo: {
        type: String,
        required: false,
        default: 'Consulta Processo'
    }
});

const emit = defineEmits(['select']);

const  dialog = ref(null);


const defaultFiltros = function () {
    this.filterField = 'processo_codigo';
    this.filterType = 'ilike';
    this.filterValue = '';
    this.ocultarPai = false;
    this.ocultarVolume = false;
    this.ocultarArquivado = false;
};

const defaultPaginate = function () {
    this.page = 0;
    this.total = 0;
    this.perpage = 10;
    this.offset = 0;
};



const processos = ref([]);
const showDialog = ref(false);
const paginate = ref(new defaultPaginate());
const filtros = ref(new defaultFiltros());
const loading = ref(false);
const tableSort = ref({
    sortField:'processo_codigo',
    sortDirection: 'desc'
});

const filterFields  = ref([
    {'field':'processo_codigo','description': "Código"},
    {'field':'processo_numero',  'description': "Número" },
    {'field':'processo_tipo',  'description': "Tipo" },
    {'field':'processo_data',  'description': "Data" },
    {'field':'processo_descricao',  'description': "Descrição" },
    {'field':'instituicao_nome',  'description': "Instituição" },
    {'field':'titular_cgm',  'description': "Titular" },
    {'field':'titular_nome',  'description': "Titular" },
    {'field':'processo_requerente',  'description': "Requerente" },
    {'field':'atendimento_numero',  'description': "Atendimento" },
    {'field':'processo_volume',  'description': "Volume" },
]);

const filterTypes = ref([
    {"type":"ilike","description" : "Contém"},
    {"type":"=","description" : "igual"},
    {"type":">","description" : "maior"},
    {"type":"<","description" : "menor"},
    {"type":"<=","description" : "menor ou igual"},
    {"type":">=","description" : "maior ou igual"},
]);

const closeDialog = (e) => {
    showDialog.value = false;
    processos.value = [];
    filtros.value = new defaultFiltros();
    paginate.value = new defaultPaginate();
}

const openDialog = (e) => {
    showDialog.value = true;
    processos.value = [];
    filtros.value = new defaultFiltros();
    paginate.value = new defaultPaginate();

    if (props.carregarDadosAutomatico) {
      console.log(props.carregarDadosAutomatico)
        pesquisarProcessos();
    }
}


const limparFiltros = () => {
    filtros.value = new defaultFiltros();
    pesquisarProcessos();
}


const selectItem = (e) => {
    emit("select", e.data);
}

const onSort = (e) => {
    processos.value = [];
    let sort = toRaw(e.multiSortMeta[0]);
    if(sort.field){
        tableSort.value = {
            sortField: sort.field,
            sortDirection: sort.order === 1 ? 'asc' : 'desc'
        };
    }
    pesquisarProcessos();
}


const pesquisarProcessos = async ({page, rows} = {page: 0, rows: 10}) => {

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

        filtros.value.ocultarVolume = props.ocultarVolume;
        filtros.value.ocultarArquivado = props.ocultarArquivado;
        filtros.value.ocultarPai = props.ocultarPai;

      const urlParams = new URLSearchParams({...filtros.value, ...paginate.value,...tableSort.value});

      const resp = await window.axios.get(
            `/v4/api/patrimonial/protocolo/processo/search?${urlParams.toString()}`
        );

        const data = resp.data.data.data;
        const {current_page, total, per_page} = resp.data.data;

        paginate.value = {
            page: current_page,
            offset: current_page * per_page - 1,
            total,
            perpage: parseInt(per_page)
        };

        processos.value = data;
    } catch (e) {
        processos.value = [];
    }
    loading.value = false;
}

const doMaximize = ()=>{
    console.log("clicou no maximazar");
    dialog.value.maximize();
}


defineExpose({
    closeDialog,
    openDialog,
    doMaximize
});

</script>

<template>
    <Dialog
        :header="props.titulo"
        :maximizable="true"
        :modal="true"
        :style="{ width: 'fit-content' }"
        :visible="showDialog"
        :closable="true"
        @update:visible="closeDialog"
        ref="dialog"
    >

        <div class="filtro-container" ref="containerFiltro">
            <div class="input-container">
                <div>
                    <Dropdown
                        v-model="filtros.filterField"
                        :options="filterFields"
                        optionLabel="description"
                        option-value="field"
                        placeholder="selecione campo"
                        class="w-full md:w-14rem"
                    />
                </div>
                <div>
                    <Dropdown
                        v-model="filtros.filterType"
                        :options="filterTypes"
                        optionLabel="description"
                        option-value="type"
                        placeholder="Tipo de Filtro"
                        class="w-full md:w-14rem"
                    />
                </div>
                <div>
                    <InputText v-model="filtros.filterValue" placeholder="Pesquisa" />
                </div>
            </div>

            <div class="flex justify-content-center flex-wrap">
                <Button
                    label="Pesquisar"
                    icon="pi pi-search"
                    class="filter-button"
                    @click="pesquisarProcessos({ page: 0 })"
                ></Button>
                <Button
                    label="Limpar Filtros"
                    icon="pi pi-filter-slash"
                    class="p-button-secondary filter-button"
                    @click="limparFiltros"
                ></Button>
            </div>
        </div>

        <div class="card" ref="containerTable">
            <DataTable
                showGridlines
                responsiveLayout="scroll"
                selectionMode="single"
                scrollHeight="flex"
                :value="processos"
                :loading="loading"
                :scrollable="true"
                @rowSelect="selectItem"
                @sort="onSort($event)"
                sortMode="multiple"
            >
                <Column field="processo_codigo" header="Código"  sortable />
                <Column field="processo_numero" header="Número"  sortable/>
                <Column field="processo_tipo" header="Tipo"  sortable/>
                <Column field="processo_data" header="Data"  sortable >
                    <template #body="{ data }">
                           {{formateDate(data.processo_data)}}
                    </template>
                </Column>
                <Column field="processo_descricao" header="Descrição" sortable/>
                <Column field="instituicao_nome" header="Instituição" sortable/>
                <Column field="titular_cgm" header="Titular CGM" sortable/>
                <Column field="titular_nome" header="Titular Nome" sortable/>
                <Column field="processo_requerente" header="Requerente" sortable/>
                <Column field="atendimento_numero" header="Atendimento" sortable/>
                <Column field="processo_volume" header="Volume" sortable/>
            </DataTable>
        </div>

        <Paginator
            ref="paginator"
            :rows="paginate.perpage"
            :totalRecords="paginate.total"
            v-model:first="paginate.offset"
            :rowsPerPageOptions="[10, 20, 30]"
            @page="pesquisarProcessos($event)"
        />

    </Dialog>
</template>

<style scoped>

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

.filter-button {
    margin: 5px;
    border-radius: 2rem;
}

</style>
