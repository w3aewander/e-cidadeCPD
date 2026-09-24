<script setup>
import {ref} from 'vue';
const showDialod = ref(false);
const containerFiltro = ref(null);
const containerTable = ref(null);
const loading = ref(false);
const filtro = ref(defaultFiltros);
const assuntos = ref([]);
const paginate = ref(defaultPaginate);
const props = defineProps(['codigodocumento']);
const emit = defineEmits(['select']);

const defaultPaginate = function () {
    this.page = 0;
    this.total = 0;
    this.perpage = 15;
    this.offset = 0;
};
const defaultFiltros = function () {
    this.codigo = '';
    this.assunto = '';
};

const tableColumns = [
    {
        field: 'p51_codigo',
        header: 'CÓDIGO',
    },
    {
        field: 'p51_descr',
        header: 'ASSUNTO',
    }
];

const closeDialog = (e) => {
    showDialod.value = false;
    assuntos.value = [];
    filtro.value = new defaultFiltros();
    paginate.value = new defaultPaginate()
}

const openDialogAssunto = (e) => {
    showDialod.value = true;
    assuntos.value = [];
    filtro.value = new defaultFiltros();
    paginate.value = new defaultPaginate();
    pesquisarAssuntos();
}

const pesquisarAssuntos = async ({page, rows} = {page: 0, rows: 15}) => {

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
            `v4/api/patrimonial/protocolo/tipo-documento/${props.codigodocumento.p91_sequencial}?${urlParams.toString()}`
        );
        const data = resp.data.data.data;
        const {current_page, total, per_page} = resp.data.data;
        paginate.value = {
            page: current_page,
            offset: current_page * per_page - 1,
            total,
            perpage: parseInt(per_page)
        };

        assuntos.value = data;
    } catch (e) {
        assuntos.value = [];
    }
    loading.value = false;
}

const limparFiltros = () => {
    filtro.value = new defaultFiltros();
    pesquisarAssuntos();
}

const selectItem = (e) => {
    emit("select", e.data);
    closeDialog(false);
}

defineExpose({
    containerTable,
    containerFiltro,
    closeDialog,
    openDialogAssunto
});
</script>

<template>
    <Dialog
        header="Consulta de Assunto"
        :maximizable="true"
        :modal="true"
        :style="{ width: '1000px' }"
        :visible="showDialod"
        :closable="true"
        @update:visible="closeDialog"
    >
        <div class="mt-6">
            <div ref="containerFiltro">
                <Fieldset legend="Pesquisar Por">
                    <div class="flex justify-content-center flex-wrap">
                        <div style="margin-right:10px">
                            <b>CÓDIGO:</b> Ex:.. 30<br/>
                            <InputText v-model="filtro.codigo"/>
                        </div>
                        <div style="margin-right:10px">
                            <b>ASSUNTO:</b><br/>
                            <InputText v-model="filtro.assunto"/>
                        </div>
                    </div>

                    <div class="flex justify-content-center flex-wrap">
                        <Button
                            style="margin:5px"
                            label="Limpar Filtros"
                            icon="pi pi-trash"
                            class="p-button-secondary"
                            @click="limparFiltros"
                        />
                        <Button
                            label="Pesquisar"
                            style="margin:5px"
                            icon="pi pi-search"
                            @click="pesquisarAssuntos({ page: 0 })"
                        />
                    </div>
                </Fieldset>
            </div>
            <div class="card" ref="containerTable">
                <DataTable
                    :value="assuntos"
                    showGridlines
                    responsiveLayout="scroll"
                    :loading="loading"
                    @rowSelect="selectItem"
                    selectionMode="single"
                    :scrollable="true"
                    scrollHeight="flex"

                >
                    <Column v-for="col of tableColumns" :field="col.field" :header="col.header"
                            :key="col.field"></Column>
                </DataTable>
            </div>
            <Paginator
                ref="paginator"
                :rows="paginate.perpage"
                :totalRecords="paginate.total"
                v-model:first="paginate.offset"
                :rowsPerPageOptions="[10, 20, 30]"
                @page="pesquisarAssuntos($event)"
            />
        </div>
    </Dialog>
</template>

<style scoped>

</style>
