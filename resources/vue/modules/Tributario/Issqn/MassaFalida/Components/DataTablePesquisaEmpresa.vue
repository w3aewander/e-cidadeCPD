<script setup>
import { useToast } from 'primevue/usetoast';
import { ref } from 'vue';
import { toCnpj } from '@utils/Strings';
import EmpenhoService from '../Services/EmpresaService';

// service
const toast = useToast();

// emits
const emit = defineEmits(['select', 'close']);

// data
const isLoading = ref(false);
const empresas = ref([]);
const empresasTotal = ref(0);
const empresasPage = ref(1);
const empresasLimit = ref(10);
const selectedEmpresa = ref(null);
const filters = ref({numcgm: '', cgccpf: '', nome: ''});

// methods
const onRowSelect = () => {
    emit('select', selectedEmpresa.value);
}

const getEmpresas = async (pageSate = false) => {
    try {
        isLoading.value = true;
        const pageFilters = {
            page: 1,
            limit: 10
        };

        if (pageSate) {
            pageFilters.page = pageSate.page + 1;
            pageFilters.limit = pageSate.rows;
        }

        const params = { ...formattedFilters(), ...pageFilters };
        const response = await EmpenhoService.getEmpresas(params);
        empresas.value = response.data;
        empresasTotal.value = response.total;
        empresasPage.value = response.current_page;
        empresasLimit.value = response.per_page;
    } catch (e) {
        console.error(e);
        toast.add({ severity: 'error', summary: 'Erro', detail: 'Erro ao buscar empresas', life: 3000 });
    } finally {
        isLoading.value = false;
    }
}

const formattedFilters = () => {
    return {
        numcgm: filters.value.numcgm.trim().replace(/\D/g, ''),
        cgccpf: filters.value.cgccpf.trim().replace(/\D/g, ''),
        nome: filters.value.nome.trim()
    }
}

const undoFilters = () => {
    filters.value = {numcgm: '', cgccpf: '', nome: ''};
    getEmpresas();
}
</script>

<template>
    <DataTable
        :value="empresas"
        selection-mode="single"
        dataKey="numcgm"
        :loading="isLoading"
        v-model:selection="selectedEmpresa"
        class="mt-5"
        tableStyle="min-width: 50rem"
        @rowSelect="onRowSelect"
    >
        <template #header>
            <div class="flex justify-content-center gap-2" @keypress.enter="getEmpresas()">
                <div class="flex flex-column">
                    <label>CGM</label>
                    <InputText v-model="filters.numcgm" />
                </div>
                <div class="flex flex-column">
                    <label>CNPJ</label>
                    <InputText v-model="filters.cgccpf" />
                </div>
                <div class="flex flex-column">
                    <label>Nome</label>
                    <InputText v-model="filters.nome" />
                </div>
            </div>
            <div class="flex justify-content-center gap-2 mt-2">
                <Button severity="success" icon="pi pi-search" @click="getEmpresas()" />
                <Button icon="pi pi-undo" @click="undoFilters"/>
                <Button severity="danger" icon="pi pi-times" @click="emit('close')"/>
            </div>
        </template>

        <template #empty>
            Nenhum registro encontrado
        </template>

        <template #loading>
            <ProgressSpinner />
        </template>

        <Column field="numcgm" header="CGM"></Column>
        <Column field="cgccpf" header="CNPJ">
            <span v-if="data.data.cgccpf">
                {{ toCnpj(data.data.cgccpf) }}
            </span>
        </Column>
        <Column field="nome" header="Nome"></Column>

        <template #footer>
            <Paginator
                v-if="empresasTotal > 1"
                :rows="empresasLimit"
                :totalRecords="empresasTotal"
                @page="getEmpresas"
                :rowsPerPageOptions="[10, 30, 50]"
            />
        </template>
    </DataTable>
</template>
