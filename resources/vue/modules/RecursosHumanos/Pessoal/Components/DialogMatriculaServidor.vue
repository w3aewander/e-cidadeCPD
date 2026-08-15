<template>
    <Dialog :header="`Consulta de Matrículas`" :maximizable="true" :modal="true" :style="{ width: '1000px' }"
        :visible="showDialod" :closable="true" @update:visible="closeDialog">
        <div class="mt-6">
            <Fieldset legend="Pesquisar Por">
                <div class="flex justify-content-center flex-wrap">
                    <div style="margin-right:10px">
                        <b>Matrícula:</b><br />
                        <InputText v-model="inputsFiltros.matricula" />
                    </div>
                    <div style="margin-right:10px">
                        <b>Nome do Servidor:</b><br />
                        <InputText v-model="inputsFiltros.nome" />
                    </div>
                </div>

                <div class="flex justify-content-center flex-wrap">
                    <Button style="margin:5px" label="Limpar Filtros" icon="pi pi-trash" class="p-button-secondary"
                        @click="limparFiltro" />
                    <Button label="Pesquisar" style="margin:5px" icon="pi pi-search" @click="filtrar()" />
                </div>
            </Fieldset>
            <div class="card" style="height: calc(100vh - 143px)">
                <DataTable :value="matriculas" showGridlines responsiveLayout="scroll" :loading="loading"
                    @rowSelect="selectItem" selectionMode="single" :scrollable="true" scrollHeight="flex" :paginator="true"
                    :rowsPerPageOptions="[10, 20, 50]" :rows="10" v-model:filters="filters">
                    <Column v-for="col of tableColumns" :field="col.field" :header="col.header" :key="col.field" />
                </DataTable>
            </div>
        </div>
    </Dialog>
</template>
<script setup>
import { ref } from 'vue';
import { FilterMatchMode } from 'primevue/api';

//const props = defineProps(['instituicao']);

const emit = defineEmits(['select']);

const filtrosDefault = {
    rh01_regist: { value: null, matchMode: FilterMatchMode.CONTAINS },
    z01_nome: { value: null, matchMode: FilterMatchMode.CONTAINS },
};

const filters = ref(filtrosDefault);

const inputsFiltrosDefault = function () {
    this.matricula = '';
    this.nome = '';
};
const inputsFiltros = ref(new inputsFiltrosDefault());

const showDialod = ref(false);
const loading = ref(false);
const matriculas = ref([]);
const tableColumns = [
    {
        field: 'rh01_regist',
        header: 'Matrícula',
    },
    {
        field: 'z01_nome',
        header: 'Nome do Servidor',
    }
];

const getMatriculas = async () => {

    loading.value = true;
    try {
        const resp = await window.axios.get(
            `v4/api/recursos-humanos/pessoal.php/rhpessoal/cgm?perpage=1000`
        );
        const data = resp.data.data.data;
        console.log(data);
        matriculas.value = data;
    } catch (e) {
        matriculas.value = [];
        console.log(e);
    }
    loading.value = false;
    //${props.instituicao}
}

const filtrar = () => {
    filters.value.rh01_regist.value = inputsFiltros.value.matricula
    filters.value.z01_nome.value = inputsFiltros.value.nome
}

const limparFiltro = () => {
    Object.keys(filters.value).forEach(key => {
        filters.value[key].value = '';
    });
    inputsFiltros.value = new inputsFiltrosDefault();
}

const closeDialog = (e) => {
    showDialod.value = false;
}

const openDialog = (e) => {
    showDialod.value = true;
    limparFiltro();
    getMatriculas();
}

const selectItem = (e) => {
    emit("select", e.data);
    closeDialog(false);
}

defineExpose({
    closeDialog,
    openDialog
});

</script>
<style scoped></style>
